<?php

class CaptchaLogic extends LogicBase
{

    public function send($type, $mobi, $code)
    {

       $conf = $this->config['mobiConf'];
        $fields['userID'] = $conf['userID'];
        $fields['cpKey'] = $conf['cpKey'];
        $fields['cpSubID'] = $conf['cpSubID'][$type];
        $fields['uPhone'] = $mobi;

        $fields['content'] = sprintf($conf['content'][$type], $code);
        $curl = new \Components\Utils\Curl();
        if($curl->send($conf['url'], $fields, 'post') === '0') {
            $capData = ['mobi' => $mobi, 'code' => $code, 'expire' => $_SERVER['REQUEST_TIME'] + $this->config['captchaExpire']];
            return $this->redis->setex($this->key($type, $mobi), $this->config['captchaAlive'], $capData);
        }

        else
            return 0;


    }

    public function getCaptcha($type, $mobi)
    {
        return $this->redis->get($this->key($type, $mobi));
    }

    /**
     * 验证校验码
     * @param  [type] $mobi    [description]
     * @param  [type] $type    [description]
     * @param  [type] $captcha [description]
     * @return [type]          [description]
     */
    public function checkCaptcha($mobi, $type, $captcha)
    {
        if (!$storeInfo = $this->redis->get($this->key($type, $mobi)))
            return ['code' => 1, 'msg' => '验证码已失效'];

        if ($storeInfo['mobi'] != $mobi || $storeInfo['code'] != $captcha)
            return ['code' => 2, 'msg' => '验证码错误'];

        // 验证通过后删除数据
        $this->redis->del($this->key($type, $mobi));
        return ['code' => 0, 'msg' => ''];
    }

    public function setNextTag($type, $mobi)
    {
        $tagVal = \Library\Helper::random(6);
        $this->session->set($this->key($type, $mobi), $tagVal);
        $this->redis->setex('next:' . $tagVal, $this->di['conf']['captchaAlive'] , 1);
    }

    public function getNextTag($type, $mobi)
    {
        $redisKey = $this->session->get($this->key($type, $mobi));
        return $this->redis->get('next:' . $redisKey);
    }

    private function key($type, $mobi)
    {
        return 'captcha:' . $type . ':' . $mobi;
    }
}
