<?php
namespace Components\Utils;

use Phalcon\Mvc\User\Component;

Class Helper extends Component
{

    public function resRet($data = [], $code = 200)
    {
        $ret = ['code' => $code];
        if($code != 200)
            $ret['msg'] = $data;
        else if(!empty($data))
            $ret['data'] = $data;

        echo json_encode($ret);
        return false;
    }

    public function walkCals($weight, $steps)
    {
        return ($weight * $steps * 3 / (3.6 * 1.7)) / 1000;
    }

    public function runCals($weight, $steps)
    {
        return ($weight * $steps * 3 / (3.6 * 2.7)) / 1000;
    }

    public function stepsToMiles($steps)
    {
        return intval($steps * 0.49);
    }

    /**
     * 计算奖金
     * @return [type] [description]
     */
    public function rateMoney($bonus,$rate)
    {
        return $bonus*1000 * (0.97+$rate/100) ;
    }

    static function getIp()
    {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    }
}
