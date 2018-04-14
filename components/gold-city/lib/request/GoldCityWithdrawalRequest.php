<?php

class GoldCityWithdrawalRequest
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

    public function withdrawalMoney($fields = [])
    {

        if (empty($fields['total_amount']))
            die("缺少提现接口必填参数total_amount！");
        else if ($fields['total_amount'] < 5000)
            die("提现金额不能小于50元！");
        else if (empty($fields['out_trade_no']))
            die("缺少提现接口必填参数out_trade_no！");
        else if (empty($fields['account_name']))
            die("缺少提现接口必填参数account_name！");
        else if (empty($fields['card_no']))
            die("缺少提现接口必填参数card_no！");
        if (!Helper::isCard($fields['card_no']))
            die("提现接口必填参数card_no格式错误！");
        else if (!empty($fields['account_mobile']) && !Helper::isMobi($fields['account_mobile']))
            die("提现接口参数account_mobile格式错误！");
        else if (empty($fields['bank_name']))
            die("缺少提现接口必填参数bank_name！");
        else if (empty($fields['bank_code']))
            die("缺少提现接口必填参数bank_code！");

        //组装系统参数
        $sysParams['app_id'] = $this->appId;
        $sysParams['create_time'] = date('YmdHis');
        $sysParams['out_trade_no'] = $fields['out_trade_no'];
        $sysParams['total_amount'] = $fields['total_amount'];
        $sysParams['account_name'] = $fields['account_name'];
        $sysParams['card_no'] = $fields['card_no'];
        $sysParams['bank_code'] = $fields['bank_code'];
        $sysParams['bank_name'] = $fields['bank_name'];
        $sysParams['account_mobile'] = $fields['account_mobile'];

        $paramStr = Encrypt::getSignContent($sysParams);
        $sysParams['sign'] = Encrypt::sign($paramStr,$this->goldCity['rsaPrivateKeyFilePath'], $this->sign);

        $httpUrl = $this->goldCity['WITHDRAWALS_MONEY'];
        $resp = HttpUtils::send($httpUrl, $sysParams);
        if (!$resp)
            die("提现接口请求失败！");

        $respArray = json_decode($resp,true);
        if ($respArray['ret_code'] == 'FAIL')
            die($respArray['ret_code'] . '，' . $respArray['ret_msg']);

        $respSign = $respArray['sign'];
        unset($respArray['sign']);
        //平台RSA2解密
        $r = Encrypt::getSignContent($respArray);
        $checkResult = Encrypt::verify($r, $respSign, $this->goldCity['rsaPublicFilePath'], $this->sign);

        if (!$checkResult)
            die('验证签名失败,请检查平台私钥是否正确！');

        return Helper::resRet($respArray,200);
    }

    public function withdrawalMoneyQuery($fields = [])
    {
        if (empty($fields['create_time']))
            die("缺少提现查询接口必填参数create_time！");
        else if (empty($fields['sn']))
            die("缺少提现查询接口必填参数sn！");

        $sysParams['app_id'] = $this->appId;
        $sysParams['create_time'] = $fields['create_time'];
        $sysParams['sn'] = $fields['sn'];

        $paramStr = Encrypt::getSignContent($sysParams);
        $sysParams['sign'] = Encrypt::sign($paramStr,$this->goldCity['rsaPrivateKeyFilePath'], $this->sign);

        $resp = HttpUtils::send($this->goldCity['WITHDRaWALS_MONEY_QUERY'], $sysParams);
        if (!$resp)
            die('提现订单查询接口请求失败！');

        $respArray = json_decode($resp,true);
        if ($respArray['ret_code'] == 'FAIL')
            die($respArray['ret_code'] . '，' . $respArray['ret_msg']);

        $respSign = $respArray['sign'];
        unset($respArray['sign']);
        //平台RSA2解密
        $r = Encrypt::getSignContent($respArray);
        $checkResult = Encrypt::verify($r, $respSign, $this->goldCity['rsaPublicFilePath'], $this->sign);

        if (!$checkResult)
            die('验证签名失败,请检查平台私钥是否正确！');
        return Helper::resRet($respArray,200);
    }

}
