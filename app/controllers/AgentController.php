<?php
test;
class AgentController extends ControllerBase
{

    public function initialize()
    {
        $this->logic = new AgentLogic();
        $this->userLogic = new UsersLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->syscfgLogic = new SystemLogic();
        $this->sysconfigLogic = new SysConfigLogic();
        $this->recommendLogic = new RecommendLogic();
        $this->uId = $this->di['session']->get('uInfo')['u_id'];
        $this->uName = $this->di['session']->get('uInfo')['u_name'];
    }

    public function addAction()
    {
        if (!$this->request->isPost())
        {
            if ($this->userLogic->getInfoByUid($this->uId, 'u_type')['u_type'] != 3)
                return $this->showmsg('您不是代理，不能注册用户');
            $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
            // 返点率和计算返点奖金
            $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'] ?: 0;
            $maxRate = $this->syscfgLogic->getRate();
            $rateMoney = substr(sprintf("%.4f",$bonus - ($bonus * $maxRate / 100)),0,-1);

            $this->di['view']->setVars([
                'title' => '注册管理',
                'rate' => $rate,
                'bonus' => $bonus,
                'rateMoney' => $rateMoney
            ]);
            return true;
        }

        if (!$type = intval($this->request->getPost('reg_type')))
            return $this->di['helper']->resRet('请选择用户类型', 500);

        $rate = trim($this->request->getPost('rate')) ?: 0;

        $uName = $this->request->getPost('username');
        if ($err = $this->di['check']->username($uName))
            return $this->di['helper']->resRet($err, 500);

        if ($this->userLogic->unameExists($uName))
            return $this->di['helper']->resRet('用户名已存在', 500);

        $pwd = $this->request->getPost('pwd');
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        if ($this->request->getPost('pwd') != $this->request->getPost('confirm'))
            return $this->di['helper']->resRet('两次密码输入不一致', 500);

        // 添加用户
        return $this->userLogic->reg($type, 5, '', $uName, $this->security->hash($pwd), $rate, $this->uId, $this->uName) ? $this->di['helper']->resRet(['url' => '/user/list']) : $this->di['helper']->resRet('注册失败', 500);
    }

    public function linkAction()
    {
        $this->assets
            ->addJs('js/clipboard.min.js');
        if ($this->userLogic->getInfoByUid($this->uId, 'u_type')['u_type'] != 3)
                return $this->showmsg('您不是代理，不能添加推广链接');

        // 返点率和计算返点奖金
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'] ?: 0;
        $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
        $maxRate = $this->syscfgLogic->getRate();
        $rateMoney = substr(sprintf("%.4f", ($bonus - ($bonus * $maxRate / 100))),0,-1);
        //取配置的url
        $url = $this->sysconfigLogic->getSysConfig(10)['sc_value'] ?: $this->di['config']['url'][0];
        //去logo参数，logo为1则页面定在详情页
        $logo = $this->request->get('logo') ?: 0;
        $recommendInfo = $this->recommendLogic->getLists($this->uId, 1, 10);
        for($i=0;$i<count($recommendInfo['lists']);$i++)
        {
            $recommendInfo['lists'][$i]['ur_created_time'] = date('Y-m-d H:i:s',$recommendInfo['lists'][$i]['ur_created_time']);
            $recommendInfo['lists'][$i]['url'] = $url;
            if($recommendInfo['lists'][$i]['ur_type'] == 1)
            {
                $recommendInfo['lists'][$i]['ur_type'] = '会员';
            }
            else
            {
                $recommendInfo['lists'][$i]['ur_type'] = '代理';
            }
            $code = $url.'auth/reg?c='.$recommendInfo['lists'][$i]['ur_code'];
            $recommendInfo['lists'][$i]['qr'] = $this->userAgentLogic->makeQR($code);
        }
// var_dump($rateMoney);exit;
        $this->di['view']->setVars([
            'title' => '赚取佣金',
            'rate' => $rate,
            'rateMoney' => $rateMoney,
            'bonus' => $bonus,
            'recommendInfo' => $recommendInfo,
            'logo' => $logo
        ]);
    }

    public function linkaddAction()
    {
        $rate = $this->request->getPost('rate') ?: 0;
        $type = intval($this->request->getPost('type'));
        if($type == 0)
            return $this->di['helper']->resRet('请选择用户类型', 500);
        $res = $this->recommendLogic->addLink($this->uId, $type, $rate);

        return $this->di['helper']->resRet($res, 200);
    }

    public function editAction()
    {
        $url = $this->sysconfigLogic->getSysConfig(10)['sc_value'] ?: $this->di['config']['url'][0];
        $urId = $this->request->getPost('urId');
        $detail = $this->recommendLogic->getDetailByUrid($urId);
        $detail['url'] = $url.'auth/reg';
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'];
        $detail['upRate'] = $rate;
        return $this->di['helper']->resRet($detail, 200);
    }

    public function updateAction()
    {
        $rate = $this->request->getPost('rate');
        $type = $this->request->getPost('type');
        $urId = $this->request->getPost('urId');

        $res = $this->recommendLogic->updateLink($this->uId, $urId, $type, $rate);
        if(!empty($res))
        {
            return $this->di['helper']->resRet($res, 200);
        }
        else
        {
            return $this->di['helper']->resRet('更新出错', 500);
        }
    }

    public function delAction()
    {
        $urId = $this->request->getPost('urId');
        $res = $this->recommendLogic->delLink($this->uId,$urId);
        if(!empty($res))
        {
            return $this->di['helper']->resRet($res, 200);
        }
        else
        {
            return $this->di['helper']->resRet('操作失败', 500);
        }
    }


}
