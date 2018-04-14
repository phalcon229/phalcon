<?php

class WithdrawController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->walletLogic=new WalletLogic();
        $this->bankLogic = new  BankLogic();
        $this->walletLogic = new WalletLogic();
        $this->withdrawLogic = new WithdrawLogic();
        $this->sysConfLogic = new SysConfigLogic;
    }
    public function indexAction()
    {
        $limit = $this->sysConfLogic->getRechargeLimit(6,7);
        $bank = $this->bankLogic->getBank($this->uId);

        //资金密码
        $wPwd = $this->walletLogic->pass($this->uId);
        $condition = $this->walletLogic->getInfo($this->uId);
        $spent = $condition['w_history_spent'];
        $consume = $condition['w_withdraw_consume'];
        $whether = '';
        $pound = 0;
        $msg = '';
        if($spent >= $consume)
        {
            $whether = '是';
            $can = 1;
            $msg = '免手续费';
        }
        else
        {
            $whether = '否';
            $can = 0;
            $pound = $consume*0.5;
            $msg = '免手续费';
        }
        $diff =  $consume - $spent;
        $this->view->setVar('title','提现');
        $this->view->setVar('wPwd',$wPwd);
        $this->view->setVar('msg',$msg);
        $this->view->setVar('limit',$limit);
        $this->view->setVar('spent',$spent);
        $this->view->setVar('consume',$consume);
        $this->view->setVar('whether',$whether);
        $this->view->setVar('bank',$bank);
        $this->view->setVar('can',$can);
        $this->view->setVar('diff',$diff);
    }

    public function freshAction()
    {
        $bankId = trim($this->request->getPost('bank'));
        $money = trim($this->request->getPost('money'));
        $moneyPwd = trim($this->request->getPost('moneyPwd'));
        $wPwd = $this->walletLogic->pass($this->uId);
        if(empty($wPwd))
            return $this->di['helper']->resRet("未设置资金密码", 500);

        $res = $this->checkPass($this->uId, $moneyPwd,$wPwd);
        if($res == -1)
            return $this->di['helper']->resRet("输错密码已达本日最大次数", 500);
        if($res == -2)
            return $this->di['helper']->resRet("资金密码错误", 499);
        // if(!$this->security->checkHash($moneyPwd, $wPwd))
        //     return $this->di['helper']->resRet("资金密码错误", 500);
        $keyWith = date('Y-m-d',time()).':'.$this->uId.':withdraw:max';
        $maxVal = $this->di['redis']->get($keyWith);
        $limitPerDay = $this->sysConfLogic->getRechargeLimit(7,8);
        if($maxVal)
        {
            if(intval($maxVal) >= $limitPerDay[1]['sc_value'])
                return $this->di['helper']->resRet("已超出日最高提款额度", 500);
        }
        $moneyCheck = fmod($money,100);
        // if($moneyCheck <> 0)
        // {
        //     return $this->di['helper']->resRet("提现必须为整百", 500);
        // }

        // 判断用户钱包状态
        if (!$wallet = $this->walletLogic->getInfo($this->uId))
            return $this->di['helper']->resRet("用户状态异常，提现失败", 500);

        if ($wallet['w_status'] != 1)
            return $this->di['helper']->resRet("用户钱包已冻结，禁止提现", 500);

        // 判断可用余额是否足够
        if ($wallet['w_money'] < $money)
            return $this->di['helper']->resRet("帐户余额不足", 500);
        // 判断是否达到提现金额标准
        $limit = $this->sysConfLogic->getRechargeLimit(6,7);
        if ($limit[0]['sc_value'] > $money)
            return $this->di['helper']->resRet("提现金额不低于" . $limit[0]['sc_value'], 500);

        if ($limit[1]['sc_value'] < $money)
            return $this->di['helper']->resRet("提现金额不高于" . $limit[1]['sc_value'], 500);

        $bankInfo = $this->bankLogic->getBankInfo($this->uId, $bankId);
        $ubcBank = $bankInfo['ubc_name'];
        $ubcProvince = $bankInfo['ubc_province'];
        $ubcCity = $bankInfo['ubc_city'];
        $ubcNumber = $bankInfo['ubc_number'];
        $ubcMobi = $bankInfo['ubc_mobi'];
        // 生成订单
        $orderSn = 'w'.date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $withId = $this->withdrawLogic->add($this->uId, $bankId, $ubcBank,$ubcProvince,$ubcCity,$ubcNumber,$money,$ubcMobi,$orderSn);
        if(empty($withId))
        {
            return $this->di['helper']->resRet("申请失败", 500);
        }
        else
        {
            // 判断提现是否需要后台审核
            $sys = $this->sysConfLogic->getSysConfig(1);
            if ($sys['sc_value'] != 1)  // 提现无需审核，直接调接口
            {
                $remoteRes = $this->withdrawLogic->apply($withId);
                // 修改提现订单状态
                $status = $remoteRes ? 3 : 5;
                $this->withdrawLogic->confirm($withId, $status);
                if($status == 3)
                {
                    $this->di['redis']->incrByFloat($keyWith,intval($money));
                }  
            }

            return !isset($remoteRes) || $remoteRes ? $this->di['helper']->resRet("申请成功", 200) : $this->di['helper']->resRet("提现失败", 500);
        }
    }


}
