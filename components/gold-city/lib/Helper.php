<?php

class Helper
{
    /**
     * 返回值
     * @param  array   $data [description]
     * @param  integer $code [description]
     * @return [type]        [description]
     */
    static function resRet($data = [], $code = 200)
    {
        $ret = ['code' => $code];
        if($code != 200)
            $ret['msg'] = $data;
        else if(!empty($data))
            $ret['data'] = $data;

        return json_encode($ret);
    }

    /**
     * 验证手机号
     * @return boolean [description]
     */
    static function isMobi($mobi)
    {
        return preg_match('/1[34578]{1}+\d{9}$/', $mobi);
    }

    /**
    * Luhn算法
    *
    */

   static function isCard($card)
   {
        settype($card, 'string');
        $sumTable = array(
        array(0,1,2,3,4,5,6,7,8,9),
        array(0,2,4,6,8,1,3,5,7,9));
        $sum = 0;
        $flip = 0;
        for ($i = strlen($card) - 1; $i >= 0; $i--) {
            $sum += $sumTable[$flip++ & 0x1][$card[$i]];
        }
        return $sum % 10 === 0;
    }
}