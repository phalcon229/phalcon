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
        $this->recommendLogic = new RecommendLogic();
        $this->captchaLogic = new CaptchaLogic();
    }

    /**
     * 用户注册
     * @return [type] [description]
     */
    public function regAction()
    {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false )
        {
            // 推广链接，增加访问计数
            if ($c = $this->request->getQuery('c'))
            {
                if (!$rmdInfo = $this->recommendLogic->detailByCode($c))
                {
                    echo '<script>alert("推广链接不存在或已失效");</script>';
                    return false;
                }
                else
                {
                    $type = $rmdInfo['ur_type'];
                }
                $this->recommendLogic->increVisit($c);
            }

            $code = $this->request->getQuery('code');
            $state = $this->request->getQuery('state');
            // 回调地址
            $url = urlencode("https://wx.sfk92.cn/auth/reg");
            // 公众号的id和secret
            $appid = 'wx94a91d62011e25c6';
            $appsecret = '4888e7a9cb842068c666c0c582d06d09';

            if(empty($code))
            {
                $urlcode = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$url."&response_type=code&scope=snsapi_userinfo&state=".$c."#wechat_redirect";
                $this->response->redirect( $urlcode, true );
            }
            else
            {
                $urlaccess = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code" ;
                $curl = new \Components\Utils\Curl();
                $res = $curl->send($urlaccess);
                $res = json_decode($res,true);
                if(!empty($res['access_token']))
                {
                    $this->wxRegAction($res['access_token'],$res['openid'], $state);
                }
                $this->response->redirect( '/', true );
            }
        }
        else
        {
            if (!$this->request->isPost())
            {
                if ($this->di['session']->get('uInfo'))
                    header("Location:/");
                $type = 1;
                // 推广链接，增加访问计数
                if ($c = $this->request->getQuery('c'))
                {
                    if (!$rmdInfo = $this->recommendLogic->detailByCode($c))
                    {
                        echo '<script>alert("推广链接不存在或已失效");</script>';
                        return false;
                    }
                    else
                    {
                        $type = $rmdInfo['ur_type'];
                    }
                    $this->recommendLogic->increVisit($c);
                }

                $tag = !empty($this->request->getQuery('tag'))?$this->request->getQuery('tag'):1;
                $tag1 = !empty($this->request->getQuery('tag'))?$this->request->getQuery('tag'):1;
                $this->di['view']->setVars([
                    'title' => '注册',
                    'c' => isset($c) ? $c : '',
                    'reg' => $tag,
                    'login' => $tag1,
                    'type' => $type,
                    'hideBack' => true
                ]);
                return true;
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

            return $this->di['helper']->resRet(['url' => '/']);
        }
    }

    public function wxlAction()
    {
        $this->view->disable();
        echo "<script>alert('此账号已被冻结');</script>";
        return false;
    }
    /**
     * 用户登录
     * @return [type] [description]
     */
    public function loginAction()
    {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false )
        {
            $uId = $this->di['session']->get('uInfo')['u_id'];
            $redisRkey = 'user_'.$uId.'_login';
            $redisSession = $this->di['cookie']->get($redisRkey)->getValue();
            if(!empty($redisSession) && !empty($uId))
            {
                $this->response->redirect( '/', true );
            }
            else
            {
                $code = $this->request->getQuery('code');
                $state = $this->request->getQuery('state');
                // 回调地址
                $url = urlencode("https://wx.sfk92.cn/auth/login");
                // 公众号的id和secret
                $appid = 'wx94a91d62011e25c6';
                $appsecret = '4888e7a9cb842068c666c0c582d06d09';
                if(empty($code))
                {
                    $urlcode = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
                    $this->response->redirect( $urlcode, true );
                }
                else
                {
                    $urlaccess = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code" ;
                    $curl = new \Components\Utils\Curl();
                    $res = $curl->send($urlaccess);
                    $res = json_decode($res,true);
                    if(!empty($res['access_token']))
                    {
                        $this->wxRegAction($res['access_token'],$res['openid'],'');
                    }
                    $this->response->redirect( '/', true );
                }
            }
        }
        else
        {
            if (!$this->request->isPost())
            {
                if ($this->di['session']->get('uInfo'))
                    header("Location:/");

                $tag = !empty($this->request->getQuery('tag'))?2:1;

                $tag1 = !empty($this->request->getQuery('tag'))?$this->request->getQuery('tag'):'';

                $remUname = $this->cookies->get('user')->getValue()?$this->cookies->get('user')->getValue():'';

                $remPwd = $this->cookies->get('pwd')->getValue()?$this->cookies->get('pwd')->getValue():'';

                $remmenber = $this->cookies->get('remmenber')->getValue()?$this->cookies->get('remmenber')->getValue():'';

                if(intval($remmenber) !== 1)
                    $remPwd = '';

                $this->di['view']->setVar('title', '登录');
                $this->di['view']->setVar('login', $tag);
                $this->di['view']->setVar('reg', $tag1);
                $this->di['view']->setVar('remUname', $remUname);
                $this->di['view']->setVar('remPwd', $remPwd);
                $this->di['view']->setVar('hideBack', true);

                return true;
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

            $remUname = $this->cookies->get('user')->getValue()?$this->cookies->get('user')->getValue():'';

            $remPwd = $this->cookies->get('pwd')->getValue()?$this->cookies->get('pwd')->getValue():'';

            $key = $uName.'_'.substr(md5($_SERVER['HTTP_USER_AGENT']),8,16).'_'.$remPwd;

            $realPwd = $this->di['redis']->get($key);
            if($realPwd)
            {
                if($uName == $remUname && !empty($remUname))
                {
                    if ($realPwd !== $uInfo['u_pass'])
                        return $this->di['helper']->resRet('用户名或密码错误', 500);

                    if($rem == 'on')
                    {
                        $loginRkey = $uInfo['u_name'].'_'.substr(md5($_SERVER['HTTP_USER_AGENT']),8,16).'_'.mt_rand(10000000,99999999);

                        $this->di['redis']->set($loginRkey,$uInfo['u_pass']);

                        $this->di['cookie']->set('pwd',explode('_',$loginRkey)[2]);

                        $this->di['cookie']->set('remmenber',1,time()+7*24*3600);
                    }
                    else
                    {
                        $this->di['cookie']->set('pwd','',time()-1);
                        $this->di['redis']->del($key);
                        $this->di['cookie']->set('remmenber',1,time()-1);
                    }

                    $this->logic->saveLogin($uInfo['u_id'], $uInfo['u_name'], $uInfo['u_type']);

                    return $this->di['helper']->resRet(['url' => '/']);
                }
            }
            else
            {
                // 验证密码
                if (!$this->security->checkHash($pwd, $uInfo['u_pass']))
                    return $this->di['helper']->resRet('用户名或密码错误', 500);

                $loginRkey = $uInfo['u_name'].'_'.substr(md5($_SERVER['HTTP_USER_AGENT']),8,16).'_'.mt_rand(10000000,99999999);

                $this->di['redis']->set($loginRkey,$uInfo['u_pass']);

                $this->di['cookie']->set('user',$uInfo['u_name']);

                $this->di['cookie']->set('pwd',explode('_',$loginRkey)[2]);

                // 记录session
                $this->logic->saveLogin($uInfo['u_id'], $uInfo['u_name'], $uInfo['u_type']);

                if($rem == 'on')
                {
                    $this->di['cookie']->set('remmenber',1,time()+7*24*3600);
                }
                else
                {
                    $this->di['cookie']->set('remmenber',1,time()-1);
                }

                return $this->di['helper']->resRet(['url' => '/']);
            }
        }
    }

    public function logoutAction()
    {
        $this->logic->logout();

        header("Location: /auth/login");
    }

    public function captchaAction()
    {
        $this->di['view']->disable();
        // 获取验证码
        $captcha = new \Components\Utils\Captcha();
        $captcha->doimg();
        $this->session->set('captcha', $captcha->getCode());
    }

    //
    public function forgetAction()
    {
            $tag = !empty($this->request->getQuery('tag'))?$this->request->getQuery('tag'):1;
            $tag1 = !empty($this->request->getQuery('tag'))?$this->request->getQuery('tag'):1;
            $this->di['view']->setVars([
                'title' => '找回密码',
                'reg' => $tag,
                'login' => $tag1,
                'hideBack' => true
            ]);
    }

    public function validateAction()
    {
        $mobi = $this->request->getPost('mobi');
        $uInfo = $this->logic->getInfoByMobi($mobi);

        if (empty($uInfo))
            return $this->di['helper']->resRet('用户不存在', 500);

        return $this->di['helper']->resRet('',200);
    }

    public function mobiCapAction()
    {
        if (!$captcha = trim($this->request->getPost('imgcap')))
            return $this->di['helper']->resRet('请输入验证码', 500);

        if (strtolower($captcha) != strtolower($this->di['session']->get('captcha')))
            return $this->di['helper']->resRet('验证码输入错误', 501);

        $mobi = $this->request->getPost('mobi');
        $uInfo = $this->logic->getInfoByMobi($mobi);

        if (empty($uInfo))
            return $this->di['helper']->resRet('用户不存在', 500);

        $info = $this->captchaLogic->getCaptcha(1, $uInfo['u_mobile']);
        if (!empty($info)) {
            if ($_SERVER['REQUEST_TIME'] <= $info['expire']) {
                return $this->di['helper']->resRet('验证码发送频繁，请1分钟后再试',500);
            }
        }
        $code = rand(100000, 999999);

        if(!$this->captchaLogic->send(1, $uInfo['u_mobile'], $code))
            return $this->di['helper']->resRet('短信发送失败', 501);

        // if(!empty($uInfo['u_email'])) {
        //     $email = new \Components\Utils\Email();
        //     $data = $this->config['email'];
        //     $data['content'] = sprintf($data['content'][1], $code);
        //     $email->send($uInfo['u_email'], $data);
        // }
        return $this->di['helper']->resRet('验证码已发送致手机', 200);
    }

    public function checkCodeAction()
    {
        $mobi = $this->request->getPost('mobi');
        if(empty($mobi))
            return $this->di['helper']->resRet('请输入手机号', 500);
        $uInfo = $this->logic->getInfoByMobi($mobi);

        if (empty($uInfo))
            return $this->di['helper']->resRet('用户不存在', 500);

        $code = trim($this->request->getPost('mobicap'));
        if(empty($code))
            return $this->di['helper']->resRet('请输入验证码', 500);

        $res = $this->captchaLogic->checkCaptcha($mobi, 1, $code);
        if ($res['code'] > 0)
            return $this->di['helper']->resRet($res['msg'], 500);
        $data['id'] = $uInfo['u_id'];
        $data['mobi'] = $uInfo['u_mobile'];
        $random = new \Phalcon\Security\Random();
        $data['rand'] = $random->hex(6);
        $this->redis->setex('rand'.$mobi, 300, $data['rand']);

         if(!empty($uInfo['u_wx_unionid']) && empty($uInfo['u_pass']))
            $data['isWx'] = 1;
        else
            $data['isWx'] = 0;
        return $this->di['helper']->resRet($data ,200);
    }

    public function doUpdateAction()
    {
        $info = $this->request->getPost('data');
        foreach ($info as $key => $value) {
            if(empty($value))
                return $this->di['helper']->resRet('参数错误' ,500);
        }

        if($info['rand'] != $this->redis->get('rand'.$info['mobi']))
            return $this->di['helper']->resRet('修改密码失败' ,500);

        if($info['pass'] != $info['cpass'])
            return $this->di['helper']->resRet('修改密码失败' ,500);

        if(!empty($info['nick'])) {
            if ($err = $this->di['check']->username($info['nick']))
                return $this->di['helper']->resRet($err, 500);

            if ($this->logic->unameExists($info['nick']))
                return $this->di['helper']->resRet('昵称已存在', 500);
        }

        $newPass = $this->security->hash($info['pass']);
        if (!$this->logic->changePwdByMobi($info, $newPass))
            return $this->di['helper']->resRet('修改密码失败' ,500);

        $this->redis->del('rand'.$info['mobi']);
        return $this->di['helper']->resRet('修改密码成功' ,200);
    }

    public function wxLoginAction()
    {
        $accessToken = $this->request->getPost('accessToken');
        if(empty($accessToken))
            return $this->di['helper']->resRet('invaldata' ,500);

        $openId = $this->request->getPost('openId');
        if(empty($openId))
            return $this->di['helper']->resRet('invaldata' ,500);

        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken.'&openid='.$openId;
        $curl = new \Components\Utils\Curl();
        $res = $curl->send($url);
        $res = json_decode($res,true);
        if (empty($res['unionid']))
            return $this->di['helper']->resRet('注册失败' ,500);
        $info = $this->logic->getUserbyUnionid($res['unionid']);
        if(!empty($info['u_status']) && $info['u_status'] != 1 )
        {
            echo "<script>alert('此账号已被冻结');</script>";exit;
        }
       
        if (!$info)
        {
            if (!$uid = $this->logic->reg(1, 1, '', $res['nickname'],'' , 0, 0 ,$res['unionid']))
                return $this->di['helper']->resRet('注册失败' ,500);
        } else {
            $uid = $info['u_id'];
            $this->logic->updateName($res['nickname'], $uid);
        }
        $this->logic->saveLogin($uid,$res['nickname'], 1);
        return $this->di['helper']->resRet(['url' => '/'],200);
    }

    public function oauthcbAction()
    {

    }

    public function wxRegAction($accessToken, $openId, $c)
    {
        $accessToken = $accessToken;
        $openId = $openId;

        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken.'&openid='.$openId;
        $curl = new \Components\Utils\Curl();
        $res = $curl->send($url);
        $res = json_decode($res,true);
        if (empty($res['unionid']))
            return $this->di['helper']->resRet('注册失败' ,500);

        $info = $this->logic->getUserbyUnionid($res['unionid']);
        if(!empty($info['u_status']) && $info['u_status'] != 1 )
        {
            echo "<script>alert('此账号已被冻结');</script>";exit;
        }
        if (!$info)
        {
            if(!empty($c))
            {
                // 推广链接
                if (!$rmdInfo = $this->recommendLogic->detailByCode($c))
                    return $this->di['helper']->resRet('推广链接不存在或已失效', 500);

                $userType = !empty($rmdInfo) ? $rmdInfo['ur_type'] : 1; // 会员类型
                $regType = !empty($rmdInfo) ? 3 : 1; // 注册渠道
                $rate = !empty($rmdInfo) ? $rmdInfo['ur_fandian'] : 0;
                $parentUid = !empty($rmdInfo) ? $rmdInfo['u_id'] : 0;

                if (!$uId = $this->logic->reg($userType, $regType, '' , $res['nickname'], '', $rate, $parentUid, $res['unionid']))
                    return $this->di['helper']->resRet('注册失败，请重新尝试', 500);

                // 更新推广链接注册人数
                $c ? $this->recommendLogic->increReg($c) : '';
            }
            else
            {   $regType = 1;
                if (!$uId = $this->logic->reg($regType, 1, '', $res['nickname'],'' , 0, 0 ,$res['unionid']))
                    return $this->di['helper']->resRet('注册失败' ,500);
            }
        }
        else
        {
            $uId = $info['u_id'];
           
            $this->logic->updateName($res['nickname'], $uId);
            $regType = $info['u_type'];
        }

        $this->logic->saveLogin($uId,$res['nickname'], $regType);
        return $this->di['helper']->resRet(['url' => '/'],200);

    }
}