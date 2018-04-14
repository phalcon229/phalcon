<?php

class Encrypt
{
    private static $fileCharset = "UTF-8";
    private static $postCharset = "UTF-8";
    static $rsaPrivateKeyFilePath;
    public function __construct()
    {
            $this->goldCity = $GLOBALS['goldCity_config'];
    }

    /**
    * 校验$value是否非空
    *  if not set ,return true;
    *    if is null , return true;
    **/
    static function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    public static function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === Encrypt::checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = Encrypt::characet($v, self::$postCharset);
                if ($i == 0)
                    $stringToBeSigned .= "$k" . "=" . "$v";
                else
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    static function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = self::$fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0)
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
        }
        return $data;
    }

    static function sign($data, $rsaPrivateKeyFilePath, $signType = "RSA")
    {
        file_exists($rsaPrivateKeyFilePath) or die('RSA文件不存在');
        //读取私钥文件
        $priKey = file_get_contents($rsaPrivateKeyFilePath);

        //转换为openssl格式密钥
        $res = openssl_get_privatekey($priKey);

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType)
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        else
            openssl_sign($data, $sign, $res);

        if(!self::checkEmpty($rsaPrivateKeyFilePath))
            openssl_free_key($res);

        $sign = base64_encode($sign);
        return $sign;
    }

    static function verify($data, $sign, $rsaPublicKeyFilePath, $signType = 'RSA')
    {
        file_exists($rsaPublicKeyFilePath) or die('RSA文件不存在');

        //读取公钥文件
        $pubKey = file_get_contents($rsaPublicKeyFilePath);
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
        ($res) or die('RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType)
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        else
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);

        if(!self::checkEmpty($rsaPublicKeyFilePath))
            openssl_free_key($res);

        return $result;
    }

}