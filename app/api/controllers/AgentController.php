<?php
class AgentController extends ControllerBase
{
    const NUM_PER_PAGE = 15;//每页显示条数
    public function initialize()
    {
        $this->logic = new AgentLogic();
        $this->userLogic = new UsersLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->sysConfigLogic = new SysConfigLogic();
        $this->recommendLogic = new RecommendLogic();
        $this->uId = $this->di['session']->get('uInfo')['u_id'];
        $this->uName = $this->di['session']->get('uInfo')['u_name'];
    }

    public function addAction()
    {
        if ($this->userLogic->getInfoByUid($this->uId, 'u_type')['u_type'] != 3)
            return $this->di['helper']->resRet('您不是代理，不能注册用户', 500);

        if (!$type = intval($this->request->getPost('type')))
            return $this->di['helper']->resRet('请选择用户类型', 500);

        if (!in_array($type, [1, 3]))
            return $this->di['helper']->resRet('类型参数错误', 500);

        $rate = trim($this->request->getPost('rate')) ?: 0;
        //代理最高返点率不能超过自身返点率
        if ($rate > $myRate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'])
            return $this->di['helper']->resRet('返点率不能超过' . $myRate, 500);

        $uName = $this->request->getPost('username');
        if ($err = $this->di['check']->username($uName))
            return $this->di['helper']->resRet($err, 500);

        if ($this->userLogic->unameExists($uName))
            return $this->di['helper']->resRet('用户名已存在', 500);

        $pwd = $this->request->getPost('pwd');
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        if ($pwd != $this->request->getPost('confirm'))
            return $this->di['helper']->resRet('两次密码输入不一致', 500);

        // 添加用户
        return $this->userLogic->reg($type, 5, '', $uName, $this->security->hash($pwd), $rate, $this->uId, $this->uName) ? $this->di['helper']->resRet() : $this->di['helper']->resRet('注册失败', 500);
    }

    //获取登录用户的返点率及系统赔率等
    public function getrateAction()
    {
        // if ($this->userLogic->getInfoByUid($this->uId, 'u_type')['u_type'] != 3)
        //     return $this->di['helper']->resRet('您不是代理', 500);

        $bonus = $this->redis->get('nomalBonus') ?: 1.970 ;
        // 返点率和计算返点奖金
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'] ?: 0;
        $maxRate = $this->sysConfigLogic->getSysConfig(3, 'sc_value')['sc_value'];
        // $rateMoney = sprintf("%.3f", $bonus - ($bonus * $maxRate / 100));

        $data = [
            'rate' => $rate,
            'bonus' => $bonus,
            'maxRate' => $maxRate,
        ];

        return $this->di['helper']->resRet($data);
    }

    //推广链接列表
    public function linkAction()
    {
        if ($this->userLogic->getInfoByUid($this->uId, 'u_type')['u_type'] != 3)
            return $this->di['helper']->resRet('您不是代理！', 500);

        $page = intval($this->request->get('page')) ?: 1;
        $nums = intval($this->request->get('nums')) ?: SELF::NUM_PER_PAGE;

        //取配置的url
        $url = $this->sysConfigLogic->getSysConfig(10, 'sc_value')['sc_value'] ?: 'https://dazhongcai.cn/';

        $recommendLists = $this->recommendLogic->lists($this->uId, $page, $nums);

        $data = $list = [];
        foreach ($recommendLists as $k => $v) {
            $list = $v;
            $list['url'] = $url . 'auth/reg?c=' . $v['ur_code'];
            unset($list['ur_code']);
            $data[] = $list;
        }

        return $this->di['helper']->resRet($data);
    }

    //添加推广链接
    public function linkaddAction()
    {
        //判断用户权限
        $uInfo = $this->userAgentLogic->getInfo($this->uId, 'ua_rate, ua_type');
        if ($uInfo['ua_type'] != 3)
            return $this->di['helper']->resRet('您不是代理，不能添加推广链接！', 500);

        $rate = $this->request->getPost('rate') ?: 0;
        if ($rate > $myRate = $uInfo['ua_rate'])
            return $this->di['helper']->resRet('返点率不能超过' . $myRate, 500);

        if ($rate > $maxRate = $this->sysConfigLogic->getSysConfig(3, 'sc_value')['sc_value'])
            return $this->di['helper']->resRet('返点率不能超过' . $maxRate, 500);

        $type = intval($this->request->getPost('type'));
        if($type == 0)
            return $this->di['helper']->resRet('请选择用户类型', 500);
        if (!in_array($type, [1, 3]))
            return $this->di['helper']->resRet('类型参数错误', 500);

        if (!$res = $this->recommendLogic->addLink($this->uId, $type, $rate))
            return $this->di['helper']->resRet('代理链接生成失败', 500);

        return $this->di['helper']->resRet();
    }

    //编辑推广链接
    public function editAction()
    {
        //判断用户权限
        $uInfo = $this->userAgentLogic->getInfo($this->uId, 'ua_rate, ua_type');
        if ($uInfo['ua_type'] != 3)
            return $this->di['helper']->resRet('您不是代理，不能编辑推广链接', 500);

        $rate = floatval($this->request->getPost('rate'));
        $type = intval($this->request->getPost('type'));
        $urId = intval($this->request->getPost('id'));

        if (!$urId || !$type || $type && !in_array($type, [1, 3]))
            return $this->di['helper']->resRet('参数错误', 500);

        if (!$detail = $this->recommendLogic->getDetailByUrid($urId))
            return $this->di['helper']->resRet('该推广链接不存在', 500);

        //返点率和用户类型不变默认返回成功
        if ($rate == $detail['ur_fandian'] && $type == $detail['ur_type'])
            return $this->di['helper']->resRet();

        if ($rate < $detail['ur_fandian'])
            return $this->di['helper']->resRet('返点率不得低于已设值', 500);

        if ($rate > $uInfo['ua_rate'])
            return $this->di['helper']->resRet('返点率不得高于自身返点率', 500);

        if ($rate > $maxRate = $this->sysConfigLogic->getSysConfig(3, 'sc_value')['sc_value'])
            return $this->di['helper']->resRet('返点率不能超过' . $maxRate, 500);

        if (!$res = $this->recommendLogic->updateLink($this->uId, $urId, $type, $rate))
            return $this->di['helper']->resRet('编辑失败，请重试！', 500);

        return $this->di['helper']->resRet();
    }

}
