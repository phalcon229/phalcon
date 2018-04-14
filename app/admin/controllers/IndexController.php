<?php
use Phalcon\Security;
use Phalcon\Mvc\View;

class IndexController extends ControllerBase
{

    public function initialize()
    {
        $this->admin = new AdminLogic;
        $this->rechargeLogic = new RechargeLogic();
    }

    public function indexAction()
    {
        if (!$this->uInfo)
        {
           header("Location: /index/login");
        }
    }


    public function loginAction()
    {
         $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();
    }

    public function dologinAction()
    {
        $this->di['view']->disable();

        if ($this->uInfo)
            return $this->di['helper']->resRet(['url' => '/index']);

        if (!$uName = trim($this->request->getPost('user')))
            return $this->di['helper']->resRet('请输入用户名', 500);

        if (!$pwd = trim($this->request->getPost('pwd')))
            return $this->di['helper']->resRet('请输入密码', 500);

        if (!$captcha = trim($this->request->getPost('captcha')))
            return $this->di['helper']->resRet('请输入验证码', 500);

        //验证码检查
        if (strtolower($captcha) != strtolower($this->di['session']->get('captcha')))
            return $this->di['helper']->resRet('验证码输入错误', 500);

        //用户是否存在
        if (!$uInfo = $this->admin->getAdminByUser($uName))
            return $this->di['helper']->resRet('登录管理员不存在', 500);

        if ($uInfo['u_status'] != 1)
            return $this->di['helper']->resRet('禁止登录', 500);

        // 验证密码
        if (!$this->security->checkHash($pwd, $uInfo['u_pass']))
            return $this->di['helper']->resRet('用户名或密码错误', 500);

        //登录成功
        $lastLoginTime = $_SERVER['REQUEST_TIME'];
        if(! $this->admin->updateLoginInfo($uInfo['u_id'], $lastLoginTime))
            return $this->di['helper']->resRet('登录信息修改失败', 500);

        //判断是否超级管理员
        $super = $this->admin->isGruop($uInfo['u_id'])['ugr_rel_id'];
        //

        if ($super == 1)
            $recharge = $withdraw = 1;
        else {
            $recharge = $withdraw = 0;
            $menu = $this->admin->getMenu(['money', 'index', 'rechargeList']);
            $perm = $this->admin->getperm($super);
            if(in_array($menu['index']['m_parent_id'], json_decode($perm['perm_config'])))
                $recharge = 1;
            if(in_array($menu['recharge']['m_parent_id'], json_decode($perm['perm_config'])))
                $withdraw = 1;
        }

        $this->di['cookie']->set('auth', $uName . '_' . $pwd . '_' . $lastLoginTime, time() + 86400);
        $this->di['session']->set('uInfo', ['u_id' => $uInfo['u_id'], 'u_name' => $uInfo['u_name'], 'groupid' => $super]);
        $this->di['redis']->set('admin:login:'.$uInfo['u_id'],session_id());
        $this->di['session']->set('di', ['recharge' => $recharge, 'withdraw' => $withdraw]);
        if(!$this->redis->get('new:sn:'.$this->session->get('uInfo')['u_id']))
            $this->redis->set('new:sn:'.$this->session->get('uInfo')['u_id'], 1);
        if(!$this->redis->get('new:wd:'.$this->session->get('uInfo')['u_id']))
            $this->redis->set('new:sn:'.$this->session->get('uInfo')['u_id'], 1);
        // $time = $this->rechargeLogic->newOrder($time1 = '', $time2='');
        // if(empty($time['recharge'])) {
        //     $this->redis->set('new:sn:'.$this->di['session']->get('uInfo')['u_id'],1);
        // }
        // else {
        //     $this->redis->set('new:sn:'.$this->di['session']->get('uInfo')['u_id'],$time['recharge']['ure_created_time']);
        // }

        // if (empty($time['withdraw'])) {
        //     $this->redis->set('new:wd:'.$this->di['session']->get('uInfo')['u_id'],1);
        // }
        // else
        //     $this->redis->set('new:wd:'.$this->di['session']->get('uInfo')['u_id'],$time['withdraw']['uw_created_time']);

        //判断是否超级管理员
        if ($super == 1)
            return $this->di['helper']->resRet(['url' => '/index']);
        else
            return $this->di['helper']->resRet(['url' => '/index']);

    }

    /**
     * 退出登录
     * @return [type] [description]
     */
    public function logoutAction()
    {
        $this->admin->logout();

        header("Location: /index/login");
    }

    public function captchaAction()
    {
        $this->di['view']->disable();
        // 获取验证码
        $captcha = new \Components\Utils\Captcha();
        $captcha->doimg(147);
        $_SESSION['captcha'] = $captcha->getCode();
    }

    public function countMsgAction()
    {
        $data = [];
        $time1 = $this->redis->get('new:sn:'.$this->session->get('uInfo')['u_id']);
        $time2 = $this->redis->get('new:wd:'.$this->session->get('uInfo')['u_id']);

        $didi = $this->rechargeLogic->newOrder($time1, $time2);

        $data['recharge'] = $data['withdraw'] = 0;
        if (!empty($didi['recharge']['ure_created_time']) && $this->session->get('di')['recharge'] == 1 ) {
            $this->redis->set('new:sn:'.$this->session->get('uInfo')['u_id'],$didi['recharge']['ure_created_time']);
            $data['recharge'] = 1;
            if ($didi['recharge']['ure_pay_chan'] > 0)
                $this->di['cookie']->set('xsrecharge',1,time()+7*26*3600);
            else
                $this->di['cookie']->set('xxrecharge',1,time()+7*26*3600);
        }

        if(!empty($didi['withdraw']['uw_created_time']) &&  $this->session->get('di')['withdraw'] == 1 ) {
            $this->redis->set('new:wd:'.$this->session->get('uInfo')['u_id'],$didi['withdraw']['uw_created_time']);
            $this->di['cookie']->set('withdraw',1,time()+7*26*3600);
            $data['withdraw'] = 1;
        }
        return $this->di['helper']->resRet($data, 200);
    }
}
