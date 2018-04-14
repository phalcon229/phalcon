<?php

class MoneyController extends ControllerBase
{
    public function initialize()
    {
        $this->logic = new WithdrawLogic();
        $this->rechargelogic = new RechargeLogic();
        $this->userWalletRecordLogic = new WalletRecordLogic();
        $this->walletLogic = new WalletLogic();
        $this->moneyLogic = new MoneyTotalLogic();
        $this->view->setVars([
            'name' => $this->dispatcher->getActionName(),
        ]);
    }

    public function indexAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $currentPage = 1;
        else
            $currentPage = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;

        // 1 widraw
        $condition = intval($this->request->getQuery('condition'));
        $value = trim($this->request->getQuery('value'));
        $res = $this->logic->getAllRecharge($currentPage, $limit, $condition, $value);
        $total = $this->logic->getRechargeTotal($condition, $value);

        $this->di['view']->setVars([
            'list' => $res,
            'total' => $total,
            'limit' => ceil($total/$limit),
            'perpage' => $perpage,
            'nums' => $limit
        ]);
    }


    public function rechargeListAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $currentPage = 1;
        else
            $currentPage = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $res = [];
        $data = [];
        $condition = !empty($this->request->getQuery('condition')) ? intval($this->request->getQuery('condition')) : 0;
        $value = trim($this->request->getQuery('value'));
        switch ($condition) {
            case 0:
                $data = $this->rechargelogic->getAllRecharge(intval($currentPage), intval($limit), 1 );
                $total = $data['total'];
                break;

            case 1:
                $data['list'][0] = $this->rechargelogic->detailBySn($value);
                $total = 1;
                break;
            case 2:
                $data = $this->rechargelogic->getRechargeByName(intval($currentPage), intval($limit),1, $value );
                $total = $data['total'];
                break;
        }

        //拼接充值方式数组
        $payType = $this->di['config']['payType'];//二维数组
        $payLists = [];
        foreach ($payType as $key => $value) {
            foreach ($value as $k => $v) {
                $payLists[$k] = $v;
            }
        }
        $payLists['-1'] = '后台手动充值';//添加后台充值方式

        $this->di['view']->setVars([
            'info' => $data['list'],
            'total' => $total,
            'limit' => ceil($total/$limit),
            'perpage' => $perpage,
            'payType' => $payLists,
            'nums' => $limit
        ]);
    }

    public function manualAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $bankcon = $this->di['config']['rechargebank'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $currentPage = 1;
        else
            $currentPage = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $res = [];
        $data = [];

        $condition = intval($this->request->getQuery('condition'));
        $value = trim($this->request->getQuery('value'));
        switch ($condition) {
            case 0:
                //人工汇款
                $datas = $this->rechargelogic->getAllRecharge($currentPage, $limit, 3 );
                $total = $datas['total'];
                break;

            case 1:
                $datas['list'][0] = $this->rechargelogic->detailBySn($value);
                $total = 1;
                break;
            case 2:
                $datas = $this->rechargelogic->getRechargeByName(intval($currentPage), intval($limit),0 , $value );
                $total = $data['total'];
                break;
        }
        $xxway = ['9'=>'支付宝扫码','11'=>'微信扫码','15'=>'银行卡汇款'];

        $this->di['view']->setVars([
            'infos' => $datas['list'],
            'total' => $total,
            'limit' => ceil($total/$limit),
            'perpage' => $perpage,
            'xxway' => $xxway,
            'nums' => $limit,
            'bankconf' => $bankcon
        ]);
    }

    /**
     *申请提款审核失败
     */
    public function stopAction()
    {
        $this->view->disable();
        if(!$uwId = intval($this->request->getPost('uwId')))
            return $this->di['helper']->resRet('数据参数错误', 500);

        if($this->logic->getInfoById($uwId)['uw_status'] != 1){
            return $this->di['helper']->resRet('当前状态不能修改', 500);
        }

        // 修改提现订单状态
        if(!$this->logic->confirm($uwId, 7))
            return $this->di['helper']->resRet('审核状态修改成失败', 500);

        $this->logContent = '修改申请订单id:'. $uwId .',审核状态修改成失败';
        return $this->di['helper']->resRet();
    }

    /**
     * 申请提款正在提现中
     * @return [type] [description]
     */
    public function passesAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $this->view->disable();
        $uwid = intval($this->request->getPost('uwId'));
        $withinfo = $this->logic->getInfoById($uwid);
        
        if($withinfo['uw_status'] != 1)
            return $this->di['helper']->resRet('当前状态不能修改', 500);

        //调用接口
        // $remoteRes = $this->logic->apply($uwid);
         // $status = $remoteRes ? 3 : 5;
        $status = 3;

        // 修改提现订单状态
        if(!$this->logic->confirm($uwid, $status))
            return $this->di['helper']->resRet('审核状态修改失败', 500);

        if($status == 3)
        {
            $keyWith = date('Y-m-d',time()).':'.$withinfo['u_id'].':withdraw:max';
            $this->di['redis']->incrByFloat($keyWith,intval($withinfo['uw_limit']));
        }

        $this->logContent = '修改申请订单id:'. $uwid .',审核状态修改成提现中';
        return $this->di['helper']->resRet();
    }

    /**
     * 补单操作
     * @return [type] [description]
     */
    public function repairAction()
    {
        $this->view->disable();
        if(!$ureId = intval($this->request->getPost('ureId')))
            return $this->di['helper']->resRet('订单数据参数错误', 500);

        if(!$data = $this->rechargelogic->getDataByUreId($ureId))
            return $this->di['helper']->resRet('订单不存在或者不是可补单状态', 500);

        //补单操作
        if(!$this->rechargelogic->confirmRecharge($data['ure_sn'], '', 3, '补单')){
            return $this->di['helper']->resRet('修改补单状态失败', 500);
        }

        $this->logContent = '修改充值订单id:'. $ureId .',补单成功';
        return $this->di['helper']->resRet();
    }

    /**
     * 第三方取消操作
     * @return [type] [description]
     */
    public function cancleAction()
    {
        $this->view->disable();
        if(!$ureId = intval($this->request->getPost('ureId')))
            return $this->di['helper']->resRet('订单数据参数错误', 500);

        if(!$data = $this->rechargelogic->getDataByUreId($ureId))
            return $this->di['helper']->resRet('订单不存在或者不是可补单状态', 500);

        //补单操作
        if(!$this->rechargelogic->refuse($ureId)){
            return $this->di['helper']->resRet('修改补单状态失败', 500);
        }

        $this->logContent = '修改充值订单id:'. $ureId .',补单成功';
        return $this->di['helper']->resRet();
    }

    /**
     * 人工确认成功充值
     * @return [type] [description]
     */
    public function rechargeAction()
    {
        $this->view->disable();
        if(!$ureId = intval($this->request->getPost('ureId')))
            return $this->di['helper']->resRet('订单数据参数错误', 500);

        //(目前默认为等待中才能进行操作)
        if(!$data = $this->rechargelogic->getDataByUreId($ureId))
            return $this->di['helper']->resRet('订单不存在或者不是审核待审核状态', 500);

        //人工确认成功充值
        if($data['ure_memo'] == '')
        {
            if(!$this->rechargelogic->confirmRecharge($data['ure_sn'], '', 3, '人工汇款充值')){
                return $this->di['helper']->resRet('人工汇款充值失败', 500);
            }

        }
        else
        {
            if(!$this->rechargelogic->confirmRecharge($data['ure_sn'], '', 3, '')){
                return $this->di['helper']->resRet('人工汇款充值失败', 500);
            }
        }
        
        $this->logContent = '修改充值订单id:'. $ureId .',人工汇款充值成功';
        return $this->di['helper']->resRet();
    }

    /**
     * 人工审核不通过(充值失败)
     * @return [type] [description]
     */
    public function refuseAction()
    {
        $this->view->disable();
        if(!$ureId = intval($this->request->getPost('ureId')))
            return $this->di['helper']->resRet('订单数据参数错误', 500);

        if(!$this->rechargelogic->getDataByUreId($ureId))
            return $this->di['helper']->resRet('订单不存在或者不是审核待审核状态', 500);

        if(!$this->rechargelogic->refuse($ureId))
            return $this->di['helper']->resRet('审核状态修改失败', 500);

        $this->logContent = '修改充值订单id:'. $ureId .',人工汇款充值失败';
        return $this->di['helper']->resRet();
    }

    public function  reportAction()
    {
        $startDay = $this->request->getQuery('startTime') ?: date('Y-m-d');
        $endDay = $this->request->getQuery('endTime') ?: date('Y-m-d');

        $conditions['startTime'] = strtotime($startDay);
        $conditions['endTime'] = strtotime($endDay);
        
        //1-充值 3-开奖结算 5-提现 7-投注,9-系统赠送 11-返点 13-系统回水 15-退款
        if($conditions['startTime'] == $conditions['endTime'])
        {
            if($conditions['startTime'] == strtotime(date('Y-m-d')))
            {
                $give = $this->userWalletRecordLogic->getTotalByType(9,$conditions['startTime'], time());//z赠送

                $recharge = $this->userWalletRecordLogic->getTotalByType(1,$conditions['startTime'], time());//充值

                $withdraw = $this->userWalletRecordLogic->getTotalByType(5,$conditions['startTime'], time());//提现

                $balance = $this->userWalletRecordLogic->getTotalBalance($conditions['startTime']);//余额

                $balanceAfter = $this->userWalletRecordLogic->getTotalBalance(time());//余额

                $type = '';
                $earn = $this->userWalletRecordLogic->getTotalByType($type,$conditions['startTime'], time());//盈亏
                $info[0] = ['st_date'=>date('Y-m-d'),'st_give'=>$give['uwr_money'],'st_recharge'=>$recharge['uwr_money'],'st_withdraw'=>$withdraw['uwr_money'],'st_before_money'=>$balance['uwr_balance'],'st_after_money'=>$balanceAfter['uwr_balance'],'st_plat_earn'=>$earn['uwr_money']];
            }
            else
            {
                $info = $this->moneyLogic->getInfoByTime($conditions['startTime'], $conditions['endTime']+86399);
            }

        }
        else
        {
            $info = $this->moneyLogic->getInfoByTime($conditions['startTime'], $conditions['endTime']);
            foreach ($info as $key => $value) {
                $total['st_plat_earn'] += $value['st_plat_earn'];
                $total['st_give'] += $value['st_give'];
                $total['st_recharge'] += $value['st_recharge'];
                $total['st_withdraw'] += $value['st_withdraw'];
            }
        }

        $this->di['view']->setVars([
            'info' => $info,
            'total' => empty($total)?'':$total
        ]);
    }

    public function msgAction()
    {
        $data['xsrecharge'] = $this->di['cookie']->has('xsrecharge') ? $this->di['cookie']->get('xsrecharge')->getValue() : 0;
        $data['xxrecharge'] = $this->di['cookie']->has('xxrecharge') ? $this->di['cookie']->get('xxrecharge')->getValue() : 0;
        $data['withdraw'] = $this->di['cookie']->has('withdraw') ? $this->di['cookie']->get('withdraw')->getValue() : 0 ;
        if(!empty($data['xsrecharge']))
            $this->di['cookie']->set('xsrecharge',1,time()-3600);
        if (!empty($data['xxrecharge']))
            $this->di['cookie']->set('xxrecharge',1,time()-3600);
        if(!empty($data['withdraw']))
            $this->di['cookie']->set('withdraw',1,time()-3600);
       return $this->di['helper']->resRet($data, 200);
    }
}