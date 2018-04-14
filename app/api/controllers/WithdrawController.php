<?php

class WithdrawController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->walletLogic=new WalletLogic;
        $this->bankLogic = new  BankLogic;
        $this->withdrawLogic = new WithdrawLogic;
        $this->sysConfLogic = new SysConfigLogic;
        $this->usersLogic = new UsersLogic;
    }

    /**
     * 提现规则
     * @return [type] [description]
     */
    public function indexAction()
    {
        $min = $this->sysConfLogic->getSysConfig(6, 'sc_value')['sc_value'];
        $max = $this->sysConfLogic->getSysConfig(7, 'sc_value')['sc_value'];
        $limit = $this->sysConfLogic->getSysConfig(8, 'sc_value')['sc_value'];
        $bank = $this->bankLogic->getBank($this->uId);

        $condition = $this->walletLogic->getInfo($this->uId);
        $spent = $condition['w_history_spent'];
        $consume = $condition['w_withdraw_consume'];

        $whether = $spent >= $consume ? 1 : 0;

        $data = [
            'min' =>  $min,
            'max' => $max,
            'limit' => $limit,
            'spent' => $spent,
            'consume' => $consume,
            'whether' => $whether,
            'bank' => $bank
        ];

        return $this->di['helper']->resRet($data, 200);
    }

    /**
     * 申请提现
     * @return [type] [description]
     */
    public function applyAction()
    {
        $bankId = intval($this->request->getPost('bank'));
        $money = floatval($this->request->getPost('money'));
        $moneyPwd = trim($this->request->getPost('moneyPwd'));
        $wPwd = $this->walletLogic->pass($this->uId);
        if(empty($wPwd))
            return $this->di['helper']->resRet("未设置资金密码", 500);

        if (!$money || !$bankId)
            return $this->di['helper']->resRet("Invalid Data!", 500);

        $res = $this->usersLogic->checkPass($this->uId, $moneyPwd,$wPwd);
        if($res == -1)
            return $this->di['helper']->resRet("输错密码已达本日最大次数", 500);
        if($res == -2)
            return $this->di['helper']->resRet("资金密码错误", 500);

        // $moneyCheck = fmod($money,100);
        // if($moneyCheck <> 0)
        //     return $this->di['helper']->resRet("提现必须为整百", 500);

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

        if (!$bankInfo = $this->bankLogic->getBankInfo($this->uId, $bankId))
            return $this->di['helper']->resRet("银行记录不存在", 500);

        $ubcBank = $bankInfo['ubc_name'];
        $ubcProvince = $bankInfo['ubc_province'];
        $ubcCity = $bankInfo['ubc_city'];
        $ubcNumber = $bankInfo['ubc_number'];
        $ubcMobi = $bankInfo['ubc_mobi'];
        // 生成订单
        $orderSn = 'w'.date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $withId = $this->withdrawLogic->add($this->uId, $bankId, $ubcBank,$ubcProvince,$ubcCity,$ubcNumber,$money,$ubcMobi,$orderSn);
        if(empty($withId))
            return $this->di['helper']->resRet("申请失败", 500);

        // 判断提现是否需要后台审核
        $sys = $this->sysConfLogic->getSysConfig(1);
        if ($sys['sc_value'] != 1)  // 提现无需审核，直接调接口
        {
            $remoteRes = $this->withdrawLogic->apply($withId);
            // 修改提现订单状态
            $status = $remoteRes ? 3 : 5;
            $this->withdrawLogic->confirm($withId, $status);
            // $this->di['redis']->incrByFloat('withdraw:' . date('Y-m-d') . ':' . $this->uId, $money);//统计用户今日总提现金额
        }

        return !isset($remoteRes) || $remoteRes ? $this->di['helper']->resRet() : $this->di['helper']->resRet("提现失败", 500);
    }
}
