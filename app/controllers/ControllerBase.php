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
        if ($this->request->isPost() || $this->request->isAjax())
            $this->di['view']->disable();

        $ctrl = $this->dispatcher->getControllerName();

        if ($ctrl != 'auth' && $ctrl != 'pay')
        {
            $uId = $this->di['session']->get('uInfo')['u_id'];
            $redisRkey = 'user_'.$uId.'_login';
            $redisSession = $this->di['cookie']->get($redisRkey)->getValue();
            $sessionId = session_id();

            $usersLogic = new UsersLogic(); 
            if($redisSession !== $sessionId || !$usersLogic->checkLogin($uId,$this->di['session']->get('uInfo')['u_name']))
            {
                // $this->di['session']->destroy();
                $this->di['session']->remove('uInfo');
                if ($this->request->isAjax())
                    return $this->di['helper']->resRet('', 401);
                else
                    header("Location:/auth/login");

                return false;
            }
  
            $key = $this->di['session']->get('uInfo')['u_id'].':count';
            $res = $this->di['redis']->get($key);

            $type = $usersLogic->getType($this->di['session']->get('uInfo')['u_id'])['u_type'];

            if(!$res)
            {
                $this->di['redis']->setex($key, 1800 , 1);
            }

            $this->di['view']->setVars([
                'uName' => $this->di['session']->get('uInfo')['u_name'],
                'uId' => $this->di['session']->get('uInfo')['u_id'],
                'type' =>$type,
                'ctrl' => $ctrl,
                'action' => $this->dispatcher->getActionName()
            ]);
        }
    }

    protected function showmsg($msg = '', $reirect = '')
    {
        $refer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://' . $_SERVER['SERVER_NAME'] . $reirect;
        echo '<script>alert(" ' . $msg . ' "); window.location.href = "' . $refer . '"</script>';
        return false;
    }

    protected function showmassage($msg = '', $reirect = '')
    {
        $refer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] . $reirect : 'http://' . $_SERVER['SERVER_NAME'] . $reirect;
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

     public function checkPass($uid, $pwd,$wPwd) {
        $num = $this->redis->get('pass:'.$uid.':');
        $num = $num ? $num : 0;
        if($num==3)
            return -1;
        if(!$this->security->checkHash($pwd, $wPwd)) {
            $time = strtotime(date('Ymd')) + 86400 - time();
            $this->redis->setex('pass:'.$uid.':', $time, $num+1);
            return -2;
        }
        if($num > 0)
            $this->redis->del('pass:'.$uid.':');
        return 1;
    }
}
