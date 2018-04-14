<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    protected $safeRequest;

    /**
     * set before execute route
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $ctrl = $this->dispatcher->getControllerName();

        if ($ctrl != 'auth' && $ctrl != 'pay')
        {
            $uId = $this->di['session']->get('uInfo')['u_id'];
            $redisRkey = 'user_'. $uId .'_login';
            $redisSession = $this->di['cookie']->get($redisRkey)->getValue();
            $sessionId = session_id();
            $usersLogic = new UsersLogic;
            if($redisSession !== $sessionId || !$usersLogic->checkLogin($uId, $this->di['session']->get('uInfo')['u_name']))
            {
                $this->di['session']->remove('uInfo');

                return $this->di['helper']->resRet('', 401);
                return false;
            }

            $key = $this->di['session']->get('uInfo')['u_id'] . ':count';
            $res = $this->di['redis']->get($key);

            if(!$res)
            {
                $this->di['redis']->setex($key, 1800 , 1);
            }
        }
    }

    protected function showmsg($msg = '', $reirect = '')
    {
        $refer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://' . $_SERVER['SERVER_NAME'] . $reirect;
        echo '<script>alert(" ' . $msg . ' "); window.location.href = "' . $refer . '"</script>';
        return false;
    }

    /**
     * set after execute route
     *
     *
     */
    public function afterExecuteRoute($dispatcher)
    {

    }
}
