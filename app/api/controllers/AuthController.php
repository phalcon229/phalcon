<?php
use Phalcon\Security;
use Phalcon\Security\Random;

class AuthController extends ControllerBase
{
    public function initialize()
    {
        $this->logic = new UsersLogic();
        $this->sysLogic = new SystemLogic();
        $this->actLogic = new ActivityLogic();
        $this->captchaLogic = new CaptchaLogic();
        $this->recommendLogic = new RecommendLogic();
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo ? $this->uInfo['u_id'] :'';
    }

    /**
     * 用户注册
     * @return [type] [description]
     */
    public function regAction()
    {
        if (!$this->request->isPost())
        {
            if ($this->di['session']->get('uInfo'))
                return $this->di['helper']->resRet(['url' => '/','u_id' => $uInfo['u_id'], 'u_name' => $uInfo['u_name'], 'u_type' => $uInfo['u_type']], 200);

            // 推广链接，增加访问计数
            if ($c = $this->request->getQuery('c'))
            {
                if (!$rmdInfo = $this->recommendLogic->detailByCode($c))
                    return $this->di['helper']->resRet("推广链接不存在或已失效");

                $this->recommendLogic->increVisit($c);
            }

            return $this->di['helper']->resRet('', 200);
        }

        $uName = trim($this->request->getPost('username'));
        if ($err = $this->di['check']->username($uName))
            return $this->di['helper']->resRet($err, 500);

        $nick = trim($this->request->getPost('nick')) ?: '';

        $pwd = trim($this->request->getPost('pwd'));
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        if (!$captcha = trim($this->request->getPost('captcha')))
            return $this->di['helper']->resRet('请输入验证码', 500);

        if (strtolower($captcha) != strtolower($this->di['session']->get('captcha')))
            return $this->di['helper']->resRet('验证码输入错误', 501);

        // 验证用户名是否存在
        if ($this->logic->unameExists($uName))
            return $this->di['helper']->resRet('用户名已存在', 500);

        // 推广链接
        if ($c = $this->request->getPost('c'))
            if (!$rmdInfo = $this->recommendLogic->detailByCode($c))
                return $this->di['helper']->resRet('推广链接不存在或已失效', 500);

        $userType = !empty($rmdInfo) ? $rmdInfo['ur_type'] : 1; // 会员类型
        $regType = !empty($rmdInfo) ? 3 : 1; // 注册渠道
        $rate = !empty($rmdInfo) ? $rmdInfo['ur_fandian'] : 0;
        $parentUid = !empty($rmdInfo) ? $rmdInfo['u_id'] : 0;

        if (!$uId = $this->logic->reg($userType, $regType, $nick , $uName, $this->security->hash($pwd), $rate, $parentUid))
            return $this->di['helper']->resRet('注册失败，请重新尝试', 500);

        // 更新推广链接注册人数
        $c ? $this->recommendLogic->increReg($c) : '';

        // 记录session
        $this->logic->saveLogin($uId, $uName, $userType);
        return $this->di['helper']->resRet('', 200);
    }

    /**
     * 用户登录
     * @return [type] [description]
     */
    public function loginAction()
    {
        if (!$this->request->isPost())
        {
            if ($uInfo = $this->di['session']->get('uInfo'))
                return $this->di['helper']->resRet(['url' => '/', 'u_id' => $uInfo['u_id'], 'u_name' => $uInfo['u_name'], 'u_type' => $uInfo['u_type']], 200);

            $remUname = $this->cookies->get('user')->getValue()?$this->cookies->get('user')->getValue():'';

            $remPwd = $this->cookies->get('pwd')->getValue()?$this->cookies->get('pwd')->getValue():'';

            $remmenber = $this->cookies->get('remmenber')->getValue()?$this->cookies->get('remmenber')->getValue():'';

            if(intval($remmenber) !== 1)
                $remPwd = '';

            $data = [
                'remUname' => $remUname,
                'remPwd' => $rempwd,
            ];

            return $this->di['helper']->resRet($data, 300);
        }

        if (!$uName = trim($this->request->getPost('username')))
            return $this->di['helper']->resRet('请输入用户名', 500);

        if (!$pwd = trim($this->request->getPost('pwd')))
            return $this->di['helper']->resRet('请输入密码', 500);

        if (!$uInfo = $this->logic->getInfoByName($uName))
            return $this->di['helper']->resRet('用户名或密码错误', 500);

        if ($uInfo['u_status'] != 1)
            return $this->di['helper']->resRet('禁止登录', 500);

        $rem = trim($this->request->getPost('rem'));

        $remUname = $this->cookies->get('user')->getValue() ?: '';

        $remPwd = $this->cookies->get('pwd')->getValue() ?: '';

        $key = $uName . '_' . substr(md5($_SERVER['HTTP_USER_AGENT']),8,16) . '_' . $remPwd;

        $realPwd = $this->di['redis']->get($key);
        if ($realPwd)
        {
            if ($uName == $remUname && !empty($remUname))
            {
                if ($realPwd !== $uInfo['u_pass'])
                    return $this->di['helper']->resRet('用户名或密码错误', 500);

                if ($rem == 1)
                {
                    $random = mt_rand(10000000,99999999);
                    $loginRkey = $uInfo['u_name'] . '_' . substr(md5($_SERVER['HTTP_USER_AGENT']), 8, 16) . '_' . $random;

                    $this->di['redis']->set($loginRkey, $uInfo['u_pass']);

                    $this->di['cookie']->set('pwd', $random);

                    $this->di['cookie']->set('remmenber', 1, time()+7*24*3600);
                }
                else
                {
                    $this->di['cookie']->set('pwd','',time()-1);
                    $this->di['redis']->del($key);
                    $this->di['cookie']->set('remmenber',1,time()-1);
                }

                $this->logic->saveLogin($uInfo['u_id'], $uInfo['u_name'], $uInfo['u_type']);

                return $this->di['helper']->resRet(['url' => '/', 'u_id' => $uInfo['u_id'], 'u_name' => $uInfo['u_name'], 'u_type' => $uInfo['u_type']]);
            }
        }
        else
        {
            // 验证密码
            if (!$this->security->checkHash($pwd, $uInfo['u_pass']))
                return $this->di['helper']->resRet('用户名或密码错误', 500);

            $random = mt_rand(10000000,99999999);
            $loginRkey = $uInfo['u_name'] . '_' . substr(md5($_SERVER['HTTP_USER_AGENT']), 8, 16) . '_' . $random;

            $this->di['redis']->set($loginRkey,$uInfo['u_pass']);

            $this->di['cookie']->set('user', $uInfo['u_name']);

            $this->di['cookie']->set('pwd', $random);

            // 记录session
            $this->logic->saveLogin($uInfo['u_id'], $uInfo['u_name'], $uInfo['u_type']);

            if($rem == 1)
                $this->di['cookie']->set('remmenber', 1, time()+7*24*3600);
            else
                $this->di['cookie']->set('remmenber', 1, time()-1);


            return $this->di['helper']->resRet(['url' => '/', 'u_id' => $uInfo['u_id'], 'u_name' => $uInfo['u_name'], 'u_type' => $uInfo['u_type']]);
        }
    }

    public function logoutAction()
    {
        $this->logic->logout();

        return $this->di['helper']->resRet(['url' => '/auth/login']);
    }

    //图形验证码
    public function captchaAction()
    {
        $captcha = new \Components\Utils\Captcha();
        $captcha->doimg();
        $this->session->set('captcha', $captcha->getCode());
    }

    //获取手机验证码
    public function mobiCapAction()
    {
        $type = intval($this->request->getPost('type'));

        //用户登录并且有手机号默认使用该号码
        if ($this->uId)
            $mobi = in_array($type, [2,4,5]) ? ($this->logic->getInfoByUid($this->uId, 'u_mobile, u_email')['u_mobile'] ?: $this->request->getPost('mobi')) : $this->request->getPost('mobi');
        else
            $mobi = $this->request->getPost('mobi');

        if (empty($mobi))
            return $this->di['helper']->resRet('请输入手机号', 500);

        if(!in_array($type, [1,2,3,4,5,6]))
            return $this->di['helper']->resRet('参数错误', 500);

        if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$mobi))
            return $this->di['helper']->resRet('请输入正确的手机号', 500);

        //注册、首次登记个人信息、修改手机号 需填写图形验证码
        if (in_array($type, [1, 3, 6])) {
            if (!$captcha = trim($this->request->getPost('imgcap')))
                return $this->di['helper']->resRet('请输入验证码', 500);

            if (strtolower($captcha) != strtolower($this->di['session']->get('captcha')))
                return $this->di['helper']->resRet('图片验证码输入错误', 501);

            if ($this->logic->getInfoByMobi($mobi))
            return $this->di['helper']->resRet('手机号已存在', 500);
        }

        $info = $this->captchaLogic->getCaptcha($type, $mobi);
        if (!empty($info)) {
            if ($_SERVER['REQUEST_TIME'] <= $info['expire']) {
                return $this->di['helper']->resRet(['time'=>$info['expire'] - $_SERVER['REQUEST_TIME']],503);
            }
        }
        $code = rand(100000, 999999);
        if(!$this->captchaLogic->send($type, $mobi, $code))
            return $this->di['helper']->resRet('短信发送失败', 501);

        return $this->di['helper']->resRet('验证码已发送致手机', 200);
    }

    //微信登录
    public function wxLoginAction()
    {
        $accessToken = $this->request->getPost('accessToken');
        if(empty($accessToken))
            return $this->di['helper']->resRet('Invalid Data', 500);

        $openId = $this->request->getPost('openId');
        if(empty($openId))
            return $this->di['helper']->resRet('Invalid Data', 500);

        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken.'&openid='.$openId;
        $curl = new \Components\Utils\Curl();
        $res = $curl->send($url);
        $res = json_decode($res, true);
        if (empty($res['unionid']))
            return $this->di['helper']->resRet('登录失败', 500);

        $info = $this->logic->getUserbyUnionid($res['unionid']);
        if (!$info)
        {
            $type = 1;
            if (!$uid = $this->logic->reg($type , 1, '', $res['nickname'],'' , 0, 0 ,$res['unionid']))
                return $this->di['helper']->resRet('注册失败', 500);
        }
        else
        {
            if ($info['u_status'] != 1)
                return $this->di['helper']->resRet('禁止登陆', 500);

            $uid = $info['u_id'];
            $type = $info['u_type'];
        }
        $this->logic->saveLogin($uid,$res['nickname'], 1);
        return $this->di['helper']->resRet(['url' => '/', 'u_id' => $uid, 'u_name' => $info['u_name'], 'u_type' => $type], 200);
    }
}