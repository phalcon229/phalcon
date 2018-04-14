<?php

class RechargeController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];
        $this->rechargeLogic = new RechargeLogic();
        $this->actLogic = new ActivityLogic();
        $this->sysConfLogic = new SysConfigLogic;
        $this->payChannelLogic = new PayChannelLogic;
        $this->walletLogic = new WalletLogic;
    }

    //返回支付方式
    public function indexAction()
    {
        if ($activity = $this->request->getQuery('aId'))
        {
            if (!$act = $this->actLogic->info($activity))
                return $this->showmsg('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_type'] != 3)
                return $this->showmsg('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_starttime'] > time())
                return $this->showmsg('未到活动时间', '/recharge/index');

            if ($act['pa_endtime'] < time())
                return $this->showmsg('活动已结束', '/recharge/index');
        }

        // 获取支付渠道
        $channels = $this->payChannelLogic->getAllChannels(1);
        $type = $this->di['config']['payType'][1];
        $tmp = [];
        foreach ($channels as $key => $v) {
            if (array_key_exists($v['pcc_type'], $type))
            {
                $tmp[$v['pcc_type']]['pay_type'] = $v['pcc_type'];
                $tmp[$v['pcc_type']]['type_name'] = $type[$v['pcc_type']];
                $tmp[$v['pcc_type']]['logo'] = $this->di['config']['payIcon'][$v['pcc_type']];
                $tmp[$v['pcc_type']]['pcc_min'] = $v['pcc_min'];
                $tmp[$v['pcc_type']]['pcc_max'] = $v['pcc_max'];
                $tmp[$v['pcc_type']]['channel'][] = [
                    'pcc_id' => $v['pcc_id'],
                    'pcc_name' => $v['pcc_name']
                ];
            }
        }
        $data['qrcode'] = array_values($tmp);

        $bank = $this->payChannelLogic->detailById(15);//银行线下汇款
        $data['bank'] = [
            'min' => $bank['pcc_min'],
            'max' => $bank['pcc_max'],
            'list' => $this->di['config']['rechargebank']
        ];

        return $this->di['helper']->resRet($data, 200);
    }

    //创建充值订单
    public function createAction()
    {
        if (!$channel = $this->request->getPost('channel'))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        if (!$amount = $this->request->getPost('amount'))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        $act = [];
        $giftMoney = 0;
        if ($aId = intval($this->request->getQuery('aId')))
        {
            if (!$act = $this->actLogic->info($aId))
                return $this->showmsg('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_type'] != 3)
                return $this->showmsg('活动不存在或已关闭', '/recharge/index');

            if ($act['pa_starttime'] > time())
                return $this->showmsg('未到活动时间', '/recharge/index');

            if ($act['pa_endtime'] < time())
                return $this->showmsg('活动已结束', '/recharge/index');

            if ($act['pa_money'] && $amount > $act['pa_money'])
                $giftMoney = $act['pa_gift_money'];
            else if(!$act['pa_money'])
                $giftMoney = $act['pa_gift_money'];
        }

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

        $bank = intval($this->request->getPost('bank'));
        $account = trim($this->request->getPost('account'));
        $name = trim($this->request->getPost('name'));
        //银行卡汇款时需填写相应信息
        if ($chanInfo['pcc_type'] == 15)
        {
            if (!$bank)
                return $this->di['helper']->resRet('请选择银行', 500);
            if (!$name)
                return $this->di['helper']->resRet('请输入银行户名', 500);
            if (!$account)
                return $this->di['helper']->resRet('请输入银行卡帐号', 500);
        }

        if (in_array($chanInfo['pcc_type'], [9,11]) && !$account)
            return $this->di['helper']->resRet('请输入付款帐号', 500);

        // 生成订单
        $orderSn = date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $data = [
            'u_id' => $this->uId,
            'u_name' => $this->uName,
            'ure_sn' => $orderSn,
            'ure_created_time' => $_SERVER['REQUEST_TIME'],
            'ure_money' => $amount,
            'ure_gift_money' => $giftMoney,
            'ure_pay_chan' => $chanInfo['pcc_type'],
            'ure_pay_way' => $channel,
            'ure_status' => 1,
            'ure_activity_id' => $aId,
            'ure_memo' => $account ? json_encode(['account' => $account, 'bank' => $bank, 'name' => $name]) : '',
        ];

        if (!$this->rechargeLogic->create($data))
            return $this->di['helper']->resRet('充值失败，请重新尝试', 500);

        $res = [
            'url' => '',
            'qrcode' => '',
            'bank' => []
        ];

        //银行转账，直接返回收款帐号资料
        if ($chanInfo['pcc_type'] == 15)
        {
            $bankInfo = json_decode($chanInfo['pcc_memo'], TRUE);
            $res['bank'] = [
                'bank' => $this->di['config']['rechargebank'][$bankInfo['bank']],
                'name' => $bankInfo['name'],
                'number' => $bankInfo['number']
            ];
            return $this->di['helper']->resRet($res);
        }

        //支付宝扫码付款，直接返回base64二维码
        if ($chanInfo['pcc_type'] == 9)
        {
            $res['qrcode'] = $this->di['redis']->get('aliqrcodebase64');
            return $this->di['helper']->resRet($res);
        }

        //微信扫码付款，直接返回base64二维码
        if ($chanInfo['pcc_type'] == 11)
        {
            $res['qrcode'] = $this->di['redis']->get('wxqrcodebase64');
            return $this->di['helper']->resRet($res);
        }

        // 获取支付接口参数
        $payMent = new \Components\Payment\Main($chanInfo['pcc_flag']);

        if (!$payParams = $payMent->buildPay($chanInfo['pcc_type'], $orderSn, $amount))
            return $this->di['helper']->resRet('充值渠道不存在或维护中', 500);

        if ($chanInfo['pcc_flag'] == 1)
        {
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
            switch ($payParams['params']['payType']) {
                case 1:
                    $payParams['params']['outOid'] = $orderSn;
                    $payParams['params']['payType'] = 10;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '微信充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'http://dazhongcai.vip/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
                case 3:
                    $payParams['params']['payType'] = 27;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = 'QQ钱包充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'http://dazhongcai.vip/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
                case 13:
                    $payParams['params']['payType'] = 50;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '银联扫码';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'http://dazhongcai.vip/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
                case 5:
                    $payParams['params']['payType'] = 11;
                    $payParams['params']['payAmount'] = $amount*100;
                    $payParams['params']['goodName'] = '支付宝充值';
                    $payParams['params']['extend1'] = $amount*100;
                    $payParams['params']['notifyUrl'] = 'http://dazhongcai.vip/pay/austcbsec';
                    $payParams['url'] = 'http://openapi.hqsfpay.com/openapi/pay/multipay/qrcodepay001';
                    $payParams['params']['sign'] = $this->rechargeLogic->sign($payParams['params']);
                    break;
            }
            $curl = new \Components\Utils\Curl();
            if (!$data = $curl->send($payParams['url'], $payParams['params'], 'post'))
                return false;
            else
            {
                $resp = json_decode($data,true);
                $resp['channel'] = 2;
                return $this->di['helper']->resRet($resp);
            }
        }

    }
}
