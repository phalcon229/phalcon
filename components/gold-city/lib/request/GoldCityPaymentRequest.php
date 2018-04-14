<?php

class GoldCityPaymentRequest
{
    public $appId;
    public $goldCity;

    public function __construct()
    {
        $this->goldCity = $GLOBALS['goldCity_config'];
        $this->appId = $this->goldCity['APPID'];
        $this->sign = $this->goldCity['SIGN_TYPE'];
        if (empty($this->appId))
            die("缺少支付接口必填参数appId！");
    }

    public function orderPaymentConfig($fields=[])
    {
        if (empty($fields['pay_type']) )
            die ("缺少支付接口必填参数pay_type！");
        else if (!in_array($fields['pay_type'], [80001, 80002, 80003, 80004, 80005, 80006, 80007]) )
            die ("缺少支付接口必填参数pay_type！");
        else if (empty($fields['out_trade_no']))
            die ("缺少支付接口必填参数out_trade_no！");
        else if (empty($fields['total_amount']))
            die ("缺少支付接口必填参数total_amount！");
        else if (empty($fields['subject']))
            die ("缺少支付接口必填参数subject！");
        else if (empty($fields['notify_url']))
            die ("缺少支付接口必填参数notify_url！");
        else if (empty($fields['return_url']))
            die ("缺少支付接口必填参数return_url！");
        else if (empty($fields['type']))
            die ("缺少支付接口必填参数type！");

        //组装系统参数
        $sysParams['app_id'] = $this->appId;
        $sysParams['create_time'] = date('YmdHis');
        $sysParams['subject'] = $fields['subject'];
        $sysParams['total_amount'] = $fields['total_amount'];
        $sysParams['out_trade_no'] = $fields['out_trade_no'];
        $sysParams['pay_type'] = $fields['pay_type'];
        $sysParams['body'] = !empty($fields['body']) ? $fields['body'] : '';
        $sysParams['return_url'] = $fields['return_url'];
        $sysParams['notify_url'] = $fields['notify_url'];
        $sysParams['bank_code'] = !empty($fields['bank_code']) ? $fields['bank_code'] : '';

        //商家RSA2加密
        $paramStr = Encrypt::getSignContent($sysParams);
        $sysParams['sign'] = Encrypt::sign($paramStr,$this->goldCity['rsaPrivateKeyFilePath'], $this->sign);
        $sysParams['type'] = $fields['type'];

        if($sysParams['type'] == 3)
        return Helper::resRet($sysParams,200);
        //发起请求
        $resp = HttpUtils::send($this->goldCity['ORDER_PAYMENT_CONFIG'], $sysParams);
        if (!$resp)
            die ("支付接口请求失败！");

        $respArray = json_decode($resp,true);
        if ($respArray['ret_code'] == 'FAIL')
            die($respArray['ret_code'] . '，' . $respArray['ret_msg']);

        $respSign = $respArray['sign'];
        unset($respArray['sign']);
        unset($respArray['type']);
        //平台RSA2解密
        $r = Encrypt::getSignContent($respArray);
        $checkResult = Encrypt::verify($r, $respSign, $this->goldCity['rsaPublicFilePath'], $this->sign);

        if (!$checkResult)
            die('验证签名失败,请检查平台私钥是否正确！');

        return Helper::resRet($respArray,200);
    }

    public function orderPaymentQuery($fields = [])
    {
        if (empty($fields['create_time']))
            die("缺少订单查询接口必填参数create_time！");
        else if (empty($fields['sn']))
            die("缺少订单查询接口必填参数sn！");

        $sysParams['app_id'] = $this->appId;
        $sysParams['create_time'] = $fields['create_time'];
        $sysParams['sn'] = $fields['sn'];

        $paramStr = Encrypt::getSignContent($sysParams);
        $sysParams['sign'] = Encrypt::sign($paramStr,$this->goldCity['rsaPrivateKeyFilePath'], $this->sign);

        $resp = HttpUtils::send($this->goldCity['ORDER_PAYMENT_QUERY'], $sysParams);
        if (!$resp)
            die("订单查询接口请求失败！");

        $respArray = json_decode($resp,true);
        if ($respArray['ret_code'] == 'FAIL')
            die($respArray['ret_code'] . '，' . $respArray['ret_msg']);

        $respSign = $respArray['sign'];
        unset($respArray['sign']);

        //平台RSA2解密
        $r = Encrypt::getSignContent($respArray);
        $checkResult = Encrypt::verify($r, $respSign, $this->goldCity['rsaPublicFilePath'], $this->sign);

        if (!$checkResult)
            die("验证签名失败,请检查平台私钥是否正确");

        return Helper::resRet($respArray,200);
    }

    public function handleNotify($resp=[])
    {
        //组装系统参数
        $respSign = $resp['sign'];
        unset($resp['sign']);
        $r = Encrypt::getSignContent($resp);

        //平台RSA2解密
        $checkResult = Encrypt::verify($r, $respSign, $this->goldCity['rsaPublicFilePath'], $this->sign);
        if (!$checkResult)
            die('验证签名失败,请检查平台私钥是否正确！');

        return Helper::resRet($resp,200);
    }

}

