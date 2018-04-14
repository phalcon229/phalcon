<?php

class RechargeController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];
        $this->userAgentLogic = new UserAgentLogic();
        $this->rechargeLogic = new RechargeLogic();
        $this->actLogic = new ActivityLogic();
        $this->sysConfLogic = new SysConfigLogic;
        $this->payChannelLogic = new PayChannelLogic;
        $this->walletLogic = new WalletLogic;
    }
    public function indexAction()
    {
        $t = intval($this->request->getQuery('t'));
        $pccType = intval($this->request->getQuery('pcc_type'))?intval($this->request->getQuery('pcc_type')):11;
        $payType = $this->di['config']['payType'];
        $type = !empty($payType[$t]) ? $t : 1;
        $activity = intval($this->request->getQuery('aid'))>0?intval($this->request->getQuery('aid')):-1;
        if( $activity != -1 && $activity != 0)
        {
            if (!$act = $this->actLogic->info($activity))
                return $this->showmassage('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_type'] != 3)
                return $this->showmassage('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_starttime'] > time())
                return $this->showmassage('未到活动时间', '/recharge/index');

            if ($act['pa_endtime'] < time())
                return $this->showmassage('活动已结束', '/recharge/index');
        }
        $switch = $this->di['redis']->get('payTypeStatus');//后台开关（存redis）

        $usersLogic = new UsersLogic;
        $userInfo = $usersLogic->getInfoByUid($this->uId);
        $ifwxlogin = 0;
        if($userInfo['u_wx_unionid'] !== '0')
        {
            $ifwxlogin = 1;
        }
        $accountC = '';
        $banknameC = '';

        // 获取支付渠道
        $channelType = $type==2?15:'';
        if($type == 2)
        {
            $channelType = 15;
            $accountKey = $this->uId.':accountR';
            $accountC = $this->di['cookie']->get($accountKey)->getValue();
            $bankKey = $this->uId.':bankR';
            $banknameC = $this->di['cookie']->get($bankKey)->getValue();
        }
        else
        {
            $channelType = $pccType;
        }
        $channel = $this->payChannelLogic->getLists($channelType);
        $wallet = $this->walletLogic->getInfo($this->uId);

        if(empty($wallet['w_pass']))
            $log = 1;
        else
            $log=0;

        $this->view->setVars([
            'title' => '充值',
            'type' => $type,
            'log' => $log,
            'payType' => $payType,
            'channel' => $channel,
            'bankcfg' => $this->di['config']['rechargebank'],
            'ptype' => $channelType,
            'switch' => $switch,
            'banknameC' => $banknameC,
            'accountC' => $accountC,
            'uname' => $this->uName,
            'ifwxlogin' => $ifwxlogin,
            'aId' => $activity,
        ]);
    }

    public function createAction()
    {
        if (!$channel = $this->request->getPost('channel'))
            return $this->di['helper']->resRet('', 500);

        if (!$amount = $this->request->getPost('amount'))
            return $this->di['helper']->resRet('', 500);

        // 验证用户钱包状态
        $wallet = $this->walletLogic->getInfo($this->uId);
        if (!$wallet || $wallet['w_status'] != 1)
            return $this->di['helper']->resRet('您的钱包已禁用', 500);

        // if (!$wallet['w_pass'])
        //     return $this->di['helper']->resRet('请先设置资金密码', 500);

        // 验证金额
        if (!$chanInfo = $this->payChannelLogic->detailById($channel))
            return $this->di['helper']->resRet('支付渠道不存在或已关闭', 500);

        if ($amount < $chanInfo['pcc_min'])
            return $this->di['helper']->resRet('充值金额不少于' . $chanInfo['pcc_min'], 500);

        if ($amount > $chanInfo['pcc_max'])
            return $this->di['helper']->resRet('充值金额不大于' . $chanInfo['pcc_max'], 500);

        // for test
        // $amount = 0.1;

        // 生成订单
        $orderSn = date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // 充值活动
        $aId = intval($this->request->getPost('aId'));
        if( $aId != -1)
        {
            if (!$actInfo = $this->actLogic->info($aId))
                return $this->di['helper']->resRet('活动不存在或已关闭', 500);

            if (intval($actInfo['pa_type']) != 3)
                return $this->di['helper']->resRet('活动不存在或已关闭', 500);

            $data['ure_activity_id'] = $aId;
            $data['ure_gift_money'] = $actInfo['pa_gift_money'];
        }
        if(!empty($data))
        {
            $data = [
                'u_id' => $this->uId,
                'u_name' => $this->uName,
                'ure_sn' => $orderSn,
                'ure_created_time' => $_SERVER['REQUEST_TIME'],
                'ure_money' => $amount,
                'ure_gift_money' => $actInfo['pa_gift_money']?$actInfo['pa_gift_money']:0,
                'ure_pay_way' => $chanInfo['pcc_type'],
                'ure_status' => $channel,
                'ure_status' => 1,
                'ure_activity_id' => $aId>0?$aId:0,
            ];     
        }
        else
        {
            $data = [
                'u_id' => $this->uId,
                'u_name' => $this->uName,
                'ure_sn' => $orderSn,
                'ure_created_time' => $_SERVER['REQUEST_TIME'],
                'ure_money' => $amount,
                'ure_pay_way' => $chanInfo['pcc_type'],
                'ure_status' => $channel,
                'ure_status' => 1,
            ];  
        }
                                                                          

        if (!$this->rechargeLogic->create($data))
            return $this->di['helper']->resRet('充值失败，请重新尝试', 500);

        // 获取支付接口参数
        $payMent = new \Components\Payment\Main($chanInfo['pcc_flag']);

        if($chanInfo['pcc_flag'] == 1){
            if (!$payParams = $payMent->buildPay($chanInfo['pcc_type'], $orderSn, $amount))
            {
                return $this->di['helper']->resRet('充值渠道不存在或维护中', 500);
            }
            switch ($payParams['params']['pay_type']) {
                case '1':
                    $payParams['params']['pay_type'] = 80002;
                    $payParams['params']['subject'] = '微信充值';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '3':
                    $payParams['params']['pay_type'] = 80004;
                    $payParams['params']['subject'] = 'QQ钱包充值';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '5':
                    $payParams['params']['pay_type'] = 80001;
                    $payParams['params']['subject'] = '支付宝充值';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '7':
                    $payParams['params']['pay_type'] = 80003;
                    $payParams['params']['subject'] = '网银充值';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '13':
                    $payParams['params']['pay_type'] = 80007;
                    $payParams['params']['subject'] = '银联扫码充值';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '17':
                    $payParams['params']['pay_type'] = 80005;
                    $payParams['params']['subject'] = '京东金融';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
                case '19':
                    $payParams['params']['pay_type'] = 80006;
                    $payParams['params']['subject'] = '百度钱包';
                    $payParams['url'] = 'https://api.rn3.cc/gateway/v1/trade';
                    break;
            }
            $payway = new GoldCityPaymentRequest();
            $res = $payway->orderPaymentConfig($payParams['params']);
            $resp = json_decode($res,true);
            if ($resp['code'] == 200) {
                $resp['channel'] = 1;
                $resp['url'] = $payParams['url'] ;
                return $this->di['helper']->resRet($resp);
            }
        }
        else
        {
            if (!$payParams = $payMent->buildPay($chanInfo['pcc_type'], $orderSn, $amount))
            {
                return $this->di['helper']->resRet('充值渠道不存在或维护中', 500);
            }

            switch($payParams['params']['payType'])
            {
                case 1:
                    $payParams['params']['payType'] = 10;
                    break;
                case 3:
                $payParams['params']['payType'] = 27;
                    break;
                case 5:
                $payParams['params']['payType'] = 11;
                    break;
                case 13:
                $payParams['params']['payType'] = 50;
                    break;
            }
            switch ($payParams['params']['payType']) {
                case '10':
                    $payParams['params']['outOid'] = $orderSn;
                    $payParams['params']['payType'] = 10;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '微信充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'https://wx.sfk92.cn/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
                case '27':
                    $payParams['params']['payType'] = 27;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = 'QQ钱包充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'https://wx.sfk92.cn/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    // var_dump($payParams);exit;
                    break;
                case '50':
                    $payParams['params']['payType'] = 50;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '银联扫码';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'https://wx.sfk92.cn/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
                case '11':
                    $payParams['params']['payType'] = 11;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '支付宝充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'https://wx.sfk92.cn/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
            }
            $curl = new \Components\Utils\Curl();
            if (!$data = $curl->send($payParams['url'], $payParams['params'], 'post'))
            {
            return false;
            }
            else
            {
                $resp = json_decode($data,true);
                // var_dump($resp);exit;
                $resp['channel'] = 2;
                return $this->di['helper']->resRet($resp);
            }
        }
    }

    public function scanAction()
    {
        $res = $this->request->getPost();
        $re = $res['data']['qrcodeUrl'];
        if(empty($re))
        {
            echo "获取扫码界面失败";return;
        }
        $code = $this->userAgentLogic->makeQR($re);
        $money = $res['data']['extend1']/100;

        $userId=$this->uId;
        $loginname=$this->uName;
        $name=$this->uName;
        $mtime=number_format(microtime(true),3,'','');
        $hashCode=md5(strtoupper(urlencode($userId.$loginname.$name.$mtime.'dazhongcai99889988')));
        $infoValue= urlencode('userId='.$userId.'&loginname='.$loginname.'&name='.$name.'&timestamp='.$mtime.'&hashCode='.$hashCode);
        $enterurl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

        $data = ['code' =>$code, 'money' =>$money,'infoValue' =>$infoValue,'enterurl'=>$enterurl ];
        return $this->di['helper']->resRet($data, 200);
    }

    public function manAction()
    {
        // $channel = 9;//支付渠道
        if (!$amount = $this->request->getPost('amount'))//金额
            return $this->di['helper']->resRet('', 500);

        $pccType = $this->request->getPost('pcc_type');//银行账号

        $account = $this->request->getPost('account');//银行账号

        $xxway = $this->request->getPost('xxway');//快捷支付的对应id

        $bank = $this->request->getPost('bank');//银行

        $bankname = $this->request->getPost('bankname');//银行名称

        $yltype = $this->request->getPost('yltype');//银联类型（扫码，银行卡）

        $aId = intval($this->request->getPost('aId'));
        if( $aId != -1)
        {
            if (!$actInfo = $this->actLogic->info($aId))
                return $this->di['helper']->resRet('活动不存在或已关闭', 500);

            if (intval($actInfo['pa_type']) != 3)
                return $this->di['helper']->resRet('活动不存在或已关闭', 500);

            $data['ure_activity_id'] = $aId;
            $data['ure_gift_money'] = $actInfo['pa_gift_money'];
        }

        $bankKey = $this->uId.':bankR';
        $banknameC = $this->di['cookie']->get($bankKey)->getValue();
        if($banknameC !== $bankname && !empty($bankname))
        {
            $this->di['cookie']->set($bankKey,$bankname,time()+24*3600*150);
        }

        $accountKey = $this->uId.':accountR';
        $accountC = $this->di['cookie']->get($accountKey)->getValue();
        if($accountC !== $account && !empty($account))
        {
            $this->di['cookie']->set($accountKey,$account,time()+24*3600*150);
        }
        // $ylscanchannel = $this->request->getPost('channel');//银联扫码才有

        $info = ['account'=> $account,
                 'yltype' => $yltype,
                 'bank' => $bank,
                 'name' => $bankname,
                 'xxway' => $pccType
                ];

        // 验证用户钱包状态
        $wallet = $this->walletLogic->getInfo($this->uId);
        if (!$wallet || $wallet['w_status'] != 1)
            return $this->di['helper']->resRet('您的钱包已禁用', 500);

        // if (!$wallet['w_pass'])
        //     return $this->di['helper']->resRet('请先设置资金密码', 500);

        // 验证金额
        if (!$chanInfo = $this->payChannelLogic->getLists($pccType)[0])
        return $this->di['helper']->resRet('支付渠道不存在或已关闭', 500);

        if ($amount < $chanInfo['pcc_min'])
            return $this->di['helper']->resRet('充值金额不少于' . $chanInfo['pcc_min'], 500);

        if ($amount > $chanInfo['pcc_max'])
            return $this->di['helper']->resRet('充值金额不大于' . $chanInfo['pcc_max'], 500);

        // 生成订单
        $orderSn = date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        if(!empty($data))
        {
            $data = [
                'u_id' => $this->uId,
                'u_name' => $this->uName,
                'ure_sn' => $orderSn,
                'ure_created_time' => $_SERVER['REQUEST_TIME'],
                'ure_money' => $amount,
                'ure_gift_money' => $actInfo['pa_gift_money']?$actInfo['pa_gift_money']:0,
                'ure_pay_chan' => 0,
                'ure_pay_way' => 7,
                'ure_status' => 1,
                'ure_updated_time' => $_SERVER['REQUEST_TIME'],
                'ure_activity_id' => $aId>0?$aId:0,
                'ure_memo' =>json_encode($info),
            ];
        }
        else
        {
            $data = [
                'u_id' => $this->uId,
                'u_name' => $this->uName,
                'ure_sn' => $orderSn,
                'ure_created_time' => $_SERVER['REQUEST_TIME'],
                'ure_money' => $amount,
                'ure_pay_chan' => 0,
                'ure_pay_way' => 7,
                'ure_status' => 1,
                'ure_updated_time' => $_SERVER['REQUEST_TIME'],
                'ure_memo' =>json_encode($info),
            ];
        }
                                                                          
        
        if (!$did=$this->rechargeLogic->create($data))
        {
            return $this->di['helper']->resRet('充值失败，请重新尝试', 500);
        }
        else
        {
            $userId=$this->uId;
            $loginname=$this->uName;
            $name=$this->uName;
            $mtime=number_format(microtime(true),3,'','');
            $hashCode=md5(strtoupper(urlencode($userId.$loginname.$name.$mtime.'dazhongcai99889988')));
            $infoValue= urlencode('userId='.$userId.'&loginname='.$loginname.'&name='.$name.'&timestamp='.$mtime.'&hashCode='.$hashCode);
            $enterurl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
            $money = $amount;
            if($pccType == 15)
            {
                $channel = $this->payChannelLogic->getLists($pccType);
                $code = json_decode($channel[0]['pcc_memo'],true)['number'];
                $bankname = $this->di['config']['rechargebank'][json_decode($channel[0]['pcc_memo'],true)['bank']];
                $scan = 0;
                $name = json_decode($channel[0]['pcc_memo'],true)['name'];
            }
            else if($pccType==9)
            {
                $code = $this->redis->get('aliqrcodebase64');
                $scan = 1;
                $bankname = '';
                $name = '支付宝';
            }
            else if($pccType==11)
            {
                $code = $this->redis->get('wxqrcodebase64');
                $scan = 1;
                $bankname = '';
                $name = '微信';
            }
            $data = ['code' =>$code, 'scan' =>$scan,'bankname'=>$bankname, 'name'=>$name,'money' =>$money,'infoValue' =>$infoValue,'enterurl'=>$enterurl,'id'=> $did,'pccType'=> $pccType ];
            return $this->di['helper']->resRet($data, 200);
        }
    }

    public function ChangeMemoAction()
    {
        $id = $this->request->getPost('id');
        $code = $this->request->getPost('code');
        $pccType = $this->request->getPost('pcc_type');

        if(empty($id) || empty($code) || empty($pccType))
            return $this->di['helper']->resRet('参数错误', 500);
        $info = [
                'account'=> $code,
                'yltype' => '',
                'bank' => '',
                'name' => '',
                'xxway' => $pccType
        ];
        $memo = json_encode($info);
        if(!$this->rechargeLogic->ChangeMemo($id, $memo))
            return $this->di['helper']->resRet('失败，请联系客服', 500);
        return $this->di['helper']->resRet('支付成功', 200);
    }
}