<?php
use Phalcon\Mvc\View;

class UserController extends ControllerBase
{
    const NUM_PER_PAGE = 20;
    public function initialize()
    {
        $this->logic = new UsersLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->walletLogic = new WalletLogic();
        $this->teamLogic = new TeamLogic();
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->betRuleLogic = new BetsRulesLogic;
        $this->dealflowLogic = new DealflowLogic;
        $this->captchaLogic = new CaptchaLogic;
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uName = $this->uInfo['u_name'];
        $this->uId = $this->di['session']->get('uInfo')['u_id'];
    }

    //修改登录密码
    public function setpwdAction()
    {
        if (!$oldPwd = trim($this->request->getPost('old_pwd')))
            return $this->di['helper']->resRet('请输入旧密码', 500);

        $pwd = trim($this->request->getPost('new_pwd'));
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        $confirm = trim($this->request->getPost('confirm'));
        if ($pwd != $confirm)
            return $this->di['helper']->resRet('密码不一致', 500);

        // 获取用户信息
        if (!$userInfo = $this->logic->getInfoByUid($this->uId, 'u_pass'))
            return $this->di['helper']->resRet('用户状态有误', 500);

        // 校验旧密码
        if (!$this->security->checkHash($oldPwd, $userInfo['u_pass']))
            return $this->di['helper']->resRet('原密码错误', 500);

        // 执行修改
        if (!$this->logic->changePwd($this->uId, $this->security->hash($pwd)))
            return $this->di['helper']->resRet('密码修改失败', 500);

        $this->logic->logout();
        return $this->di['helper']->resRet();
    }

    public function pwdmoneyAction()
    {
        // 判断是否已设置资金密码
        $moneyPass = $this->walletLogic->pass($this->uId);


        if ($moneyPass)
        {
            if (!$oldPwd = trim($this->request->getPost('old_pwd')))
                return $this->di['helper']->resRet('请输入旧密码', 500);

            // 校验旧密码
            if (!$this->security->checkHash($oldPwd, $moneyPass))
                return $this->di['helper']->resRet('原密码错误', 500);
        }

        $pwd = trim($this->request->getPost('new_pwd'));
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        // 执行修改
        if (!$this->walletLogic->changePwd($this->uId, $this->security->hash($pwd)))
            return $this->di['helper']->resRet('提交失败', 500);

        return $this->di['helper']->resRet('', 200);
    }

    //分页获取下级列表
    public function listAction()
    {
        $uName = trim($this->request->getQuery('username'));
        $uId = intval($this->request->getQuery('uId')) ?: $this->uId;

        $startTime = strtotime(trim($this->request->getQuery('starttime')));
        $endTime = strtotime(trim($this->request->getQuery('endtime')));
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('nums')) ?: SELF::NUM_PER_PAGE;

        $endTime = $endTime ? strtotime($endTime) + 86399 : 0;

        $data = [
            'uId' => $uId,
            'uName' => $uName,
            'startTime' => $startTime,
            'endTime' => $endTime
        ];

        $lists = $this->userAgentLogic->getAgentLists($data, $page, $nums);
        if (!$lists)
            return $this->di['helper']->resRet($lists);

        $res = $uIds = [];
        foreach ($lists as $list) {
            $uIds[] = $list['u_id'];
        }
        //批量获取用户钱包数据
        $walletInfos = $this->walletLogic->getWalletByUids($uIds);
        foreach ($lists as $key => $list) {
            $tmp = $list;
            if ($list['u_id'] == $walletInfos[$key]['u_id'])
                $tmp['w_money'] = $walletInfos[$key]['w_money'];

            $res[] = $tmp;
        }

        return $this->di['helper']->resRet($res);
    }

    //编辑下级备注
    public function editAction()
    {
        $info = trim($this->request->getPost('info'));
        $uId = intval($this->request->getPost('uId'));

        if (!$agent = $this->userAgentLogic->getInfo($uId, 'ua_u_id'))
            return $this->di['helper']->resRet('无此用户信息', 500);

        if (!$agent['ua_u_id'] != $this->uId)
            return $this->di['helper']->resRet('非该用户上级，无法编辑', 500);

        if(!$this->userAgentLogic->editMemo($info, $uId))
            return $this->di['helper']->resRet('编辑失败', 500);
        else
            return $this->di['helper']->resRet();
    }

    /**
     * 下级返点详情
     * @return [type] [description]
     */
    public function rateDetailAction()
    {
        if (!$uId = trim($this->request->getQuery('uId')))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        // 获取用户基本信息
        if (!$agent = $this->userAgentLogic->getInfo($uId, 'ua_u_id, u_id, u_name, ua_rate'))
            return $this->di['helper']->resRet('无此用户信息', 500);

        if ($agent['ua_u_id'] != $this->uId)
            return $this->di['helper']->resRet('非该用户上级', 500);

        // 获取当前登录用户返点信息
        if (!$currentUser = $this->userAgentLogic->getInfo($this->uId, 'ua_rate'))
            return $this->di['helper']->resRet('用户状态异常', 500);

        //获取系统默认赔率
        $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
        $data = [
            'u_id' => $agent['u_id'],
            'u_name' => $agent['u_name'],
            'ua_rate' => $agent['ua_rate'],
            'rateMoney' => $this->di['helper']->rateMoney($bonus, $agent['ua_rate']),
            'max_rate' => $currentUser['ua_rate']
        ];

        return $this->di['helper']->resRet($data, 200);
    }

    //设置下级返点率
    public function setRateAction()
    {
        if (!$uId = intval($this->request->getPost('uId')))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        $rate = floatval($this->request->getPost('rate'));

        if (!$agent = $this->userAgentLogic->getInfo($uId, 'ua_u_id'))
            return $this->di['helper']->resRet('无此用户信息', 500);

        if ($agent['ua_u_id'] != $this->uId)
            return $this->di['helper']->resRet('非该用户上级，无法设置', 500);

        if ($rate < $this->userAgentLogic->getMaxRate($uId)['ua_rate'])
            return $this->di['helper']->resRet('本级返点不能比本级的下级低', 500);

        if ($rate == $agent['ua_rate'])
            return $this->di['helper']->resRet();

        if ($rate > $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'])
            return $this->di['helper']->resRet('返点率不得高于自身返点率', 500);

        if (!$this->userAgentLogic->updateRate($uId, $rate))
            return $this->di['helper']->resRet('设置失败', 500);

        return $this->di['helper']->resRet();
    }

    //个人信息
    public function baseAction()
    {
        $pwd = $this->walletLogic->pass($this->uId);
        $uInfo = $this->logic->getInfoByUid($this->uId, 'u_mobile, u_email');
        if (!$this->request->isPost() || $pwd || $uInfo['u_mobile'] || $uIndo['u_email'])
        {

            $mobile = $uInfo['u_mobile'] ? substr_replace($uInfo['u_mobile'], '*****', 3, -2) : '';
            $wechat = $uInfo['u_email'] ?: '';

            return $this->di['helper']->resRet([
                'pwd' => $pwd ? 1 : 0 ,
                'mobile' => $mobile,
                'wechat' => $wechat
            ]);
        }

        $pass = trim($this->request->getPost('pwd'));
        $mobile = intval($this->request->getPost('mobile'));
        $wechat = trim($this->request->getPost('wechat'));
        $captcha = trim($this->request->getPost('captcha'));

        if(empty($pass))
            return $this->di['helper']->resRet('密码不能为空', 500);
        if($pass != trim($this->request->getPost('confirm')))
            return $this->di['helper']->resRet('两次密码不一致', 500);

        if(empty($mobile))
        return $this->di['helper']->resRet('手机号不能为空', 500);
        if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$mobile))
        return $this->di['helper']->resRet('请输入正确的手机号', 500);

        if(empty($wechat))
            return $this->di['helper']->resRet('微信号不能为空', 500);

        if(empty($captcha))
            return $this->di['helper']->resRet('验证码不能为空', 500);

        $res = $this->captchaLogic->checkCaptcha($mobile, 3, $captcha);
        if ($res['code'] > 0)
            return $this->di['helper']->resRet($res['msg'], 500);

        if(!$this->logic->doBases($this->uId, ['u_mobile' => $mobile, 'u_email' => $wechat], $this->security->hash($pass)))
            return $this->di['helper']->resRet('设置失败', 500);

        return $this->di['helper']->resRet();
    }

    //校验短信验证码
    public function checkcapAction()
    {
        $type = intval($this->request->getPost('type'));
        $code = trim($this->request->getPost('msgcode'));

        if (empty($type) || empty($code))
            return $this->di['helper']->resRet('参数错误', 500);

        $uInfo = $this->logic->getInfoByUid($this->uId, 'u_mobile, u_email');
        $mobi = $uInfo['u_mobile'];

         $res = $this->captchaLogic->checkCaptcha($mobi, $type, $code);
        if ($res['code'] > 0)
            return $this->di['helper']->resRet($res['msg'], 500);

        $this->di['redis']->setex('change:'.$type.'user:'.$this->uId, 300, 1);
        return $this->di['helper']->resRet('ok', 200);
    }

    //修改个人信息
    public function setInfoAction()
    {
        if (!$type = intval($this->request->getPost('type')))
            return $this->di['helper']->resRet('Invalid Data', 500);

        if (!in_array($type, [2, 4, 5]))
            return $this->di['helper']->resRet('Invalid Data', 500);

        if(!$this->redis->get('change:'.$type.'user:'.$this->uId))
            return $this->di['helper']->resRet('页面已失效，请重新操作', 501);

        switch ($type) {
            //修改资金密码
            case 2:
                if (!$pass = trim($this->request->getPost('pwd')))
                    return $this->di['helper']->resRet('请输入资金密码', 500);

                if ($pass != trim($this->request->getPost('confirm')))
                    return $this->di['helper']->resRet('两次密码不一致', 500);

                $result = $this->walletLogic->changePwd($this->uId, $this->security->hash($pass));
                break;
            //修改手机号
            case 4:
                if (!$mobi = intval($this->request->getPost('mobi')))
                    return $this->di['helper']->resRet('请输入手机号码', 500);

                if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$mobile))
                    return $this->di['helper']->resRet('请输入正确的手机号', 500);

                if (!$mobicap = intval($this->request->getPost('mobicap')))
                    return $this->di['helper']->resRet('请输入短信验证码', 500);

                $res = $this->captchaLogic->checkCaptcha($data['mobi'], $type, $mobicap);
                if ($res['code'] > 0)
                    return $this->di['helper']->resRet($res['msg'], 500);

                $result = $this->logic->doBase($this->uId, ['u_mobile' => $mobi]);
                break;
            //修改微信号
            case 5:
                if (!$wechat = $this->request->getPost('wechat'))
                    return $this->di['helper']->resRet('请输入微信号', 500);

                $result = $this->logic->doBase($this->uId, ['u_email' => $wechat]);
                break;
            default:
                $result = false;
                break;
        }

        if (!$result)
            return $this->di['helper']->resRet('修改失败', 500);

        $this->di['redis']->del('change:'.$type.'user:'.$this->uId);
        return $this->di['helper']->resRet();
    }

    /**
     * 切换是否显示余额
     * @return [type] [description]
     */
    public function moneyAction()
    {
        $key = 'money:hide';
        $money = -1;
        if ($this->di['redis']->sIsMember($key, $this->uId))
        {
            $this->di['redis']->sRem($key, $this->uId);
            $money = (new WalletLogic())->money($this->uId) ?: 0.00;
        }
        else
            $this->di['redis']->sAdd($key, $this->uId);

        return $this->di['helper']->resRet($money, 200);
    }


    public function freshByTypeAction()
    {
        $betId = trim($this->request->get('type'));

        $limit = $this->betsConfigLogic->getLimit($betId);
        switch ($limit[0]['bet_id'])
        {
            case 1: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 2: $play =['总和大','总和大','总和单','总和单','总和810','总大单','总大单','总小单','总小单','第五名虎','冠亚和3','冠亚和4','冠亚和5','冠亚和6','冠亚和7',
                            '冠亚和8','冠亚和9','冠亚和10','冠亚和11','冠亚和12','冠亚和13','冠亚和14','冠亚和15','冠亚和16','冠亚和17','冠亚和18','冠亚和19','冠亚和值单','冠亚和值双',
                            '冠亚和值大','冠亚和值小','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1',
                            '冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1'];break;
            case 3: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 4: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 5: $play =['冠军龙','冠军虎','亚军龙','亚军虎','第三名龙','第三名虎','第四名龙','第四名虎','第五名龙','第五名虎','冠亚和3','冠亚和4','冠亚和5','冠亚和6','冠亚和7',
                            '冠亚和8','冠亚和9','冠亚和10','冠亚和11','冠亚和12','冠亚和13','冠亚和14','冠亚和15','冠亚和16','冠亚和17','冠亚和18','冠亚和19','冠亚和值单','冠亚和值双',
                            '冠亚和值大','冠亚和值小','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1',
                            '冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1'];break;
        }
        $data = ['limit' => $limit, 'play' => $play];
        return $this->di['helper']->resRet($data, 200);
    }



}
