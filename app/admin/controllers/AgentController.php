<?php
use Phalcon\Mvc\View;

class AgentController extends ControllerBase
{
    public function initialize()
    {
        $this->logic = new UserAgentLogic;
        $this->teamLogic= new TeamLogic;
        $this->recommendLogic = new RecommendLogic;
        $this->usersLogic = new UsersLogic;
        $this->sysConfigLogic = new SysConfigLogic;
        $this->agentReportLogic = new AgentReportLogic();
        $this->userLogic = new UsersLogic();
        $this->userAgentModel = new UserAgentModel();
        $this->perPage = $this->di['config']['admin']['perPage'];
        //获取配置里面返点率最高值
        $this->rate = $this->sysConfigLogic->getSysConfig(3)['sc_value'];
        $this->view->setVars([
            'name' => $this->dispatcher->getActionName(),
        ]);
    }

    public function indexAction()
    {
        $startTime = !empty($this->request->getQuery('startTime')) ? strtotime($this->request->getQuery('startTime')) : mktime(0,0,0,date('m'),date('d'),date('Y'));;
        $endTime = !empty($this->request->getQuery('endTime'))? strtotime($this->request->getQuery('endTime')) + 86399: mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $pame = $this->request->getQuery('pame') ? $this->request->getQuery('pame') : 0;
        if (!empty($pame))
        {
            switch ($pame) {
                case 1:
                    $startTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                    break;
                case 2:
                    $startTime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                    break;
                case 3:
                    $startTime = mktime(0, 0 , 0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y"));
                    $endTime = mktime(23,59,59,date("m"),date("d")-(date("w")==0?7:date("w"))+7,date("Y"));                    
                    break;
                case 4:
                    $startTime = mktime(0,0,0,date('m'),date('d')-(date("w")==0?7:date("w"))+1-7,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('d')-(date("w")==0?7:date("w"))+7-7,date('Y'));
                    break;
                case 5:
                    $startTime = mktime(0,0,0,date('m'),1,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    break;
                case 6:
                    $startTime = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                    $endTime = mktime(23,59,59,date("m") ,0,date("Y"));
                    break;
                case 0:
                    $startTime = $startTime;
                    $endTime = $endTime;
                    break;
            }
        } 
        $name = '';
        $id = '';
        $condition = $this->request->getQuery('condition');
        if (!empty($condition))
        {
            if ($condition == 1)
              $name = trim($this->request->getQuery('value'))?:0;
            if ($condition ==3)
              $id = intval($this->request->getQuery('value')) ?:0;  
        }
        


        $uId = intval($this->request->getQuery('uid')) ?: 0;
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('limit')) ?: current($this->perPage);

        if ($name && $uId)
            $this->showMsg('Invalid Data!');

        $data = [
            'uId' => $uId,
            'id' => $id,
            'name' => $name,
            'start' => $startTime,
            'end' => $endTime
        ];
       
        $agentReportLogic = new AgentReportLogic();
        $res = $agentReportLogic->getAgentReportLists($data, $page, $nums);

        $total = $res['total'];
        $lists = $res['lists'];

        $allOnline = $this->di['redis']->get('all:online');//统计总在线人数
        if(empty($allOnline))
        {
            $allIds = $this->userLogic->getAllids();
            $number = 0;
            foreach ($allIds as $key => $value)
            {
                $onekey = $value['u_id'].':count';
                $res = $this->di['redis']->get($onekey);
                if(!empty($res))
                {
                    $number ++;
                }
            }
            $allOnline = $number;
            $this->di['redis']->setex('all:online', 900 , $number);
        }
        if (!$lists)
            return $this->view->setVars([
                'allonline' => $allOnline,
                'nums' => $nums,
                'total' => $total,
                'lists' => $lists,
                'numsPage' => $this->perPage,
                'pame' => $pame,
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);

        $uIds = $online = $result = [];
        foreach ($lists as $v) {
            array_push($uIds, $v['u_id']);

        }
        for($j = 0;$j < count($uIds) ; $j++)
        {
            $num = 0;
            $temp = $this->userAgentModel->getDown($uIds[$j]);
            $temp = explode(',', $temp);
            if(!empty($temp))
            {
                $l = count($temp);
                for($k = 0;$k < $l ; $k++)
                {
                    $key = $temp[$k].':count';
                    $res = $this->di['redis']->get($key);
                    if(!empty($res))
                    {
                        $num ++;
                    }
                }
            }
            array_push($online, $num);
        }

        //批量获取代理
        $agentInfos = $this->logic->getAgentInfosByUids($uIds, 'u_id, ua_type,ua_team_num, ua_good_user_nums');
        foreach ($agentInfos as $key => $value) {
            $value['online'] = $online[$key];
            $result[] = array_merge($lists[$key], $value);
        }

        $this->view->setVars([
            'allonline' => $allOnline,
            'nums' => $nums,
            'total' => $total,
            'lists' => $result,
            'pame' => $pame,
            'numsPage' => $this->perPage,
            'startTime' => $startTime,
            'endTime' => $endTime
        ]);

    }

    public function userAction()
    {
        $name = trim($this->request->getQuery('name')) ?: '';
        $minMoney = intval($this->request->getQuery('min')) ?: 0;
        $maxMoney = intval($this->request->getQuery('max')) ?: 0;
        $page = intval($this->request->getQuery('page')) ?: 1;

        $nums = intval($this->request->getQuery('limit')) ?: current($this->perPage);

        $total = $this->logic->getTotalAgent($name, $minMoney, $maxMoney);
        $lists = $this->logic->getAgentInfo($name, $minMoney, $maxMoney, $page, $nums);

        $this->view->setVars([
            'lists' => $lists,
            'total' => $total,
            'type' => $this->di['config']['agent'],
            'nums' => $nums,
            'perPage' => $this->perPage
        ]);
    }

    public function linkAction()
    {
        $page = intval($this->request->getQuery('page')) ?: 1;

        $nums = intval($this->request->getQuery('limit')) ?: current($this->perPage);

        //查询后台生成的用户id为0的链接
        $res = $this->recommendLogic->getLists(0, $page, $nums);

        $this->view->setVars([
            'lists' => $res['lists'],
            'total' => $res['total'],
            'utype' => $this->di['config']['agent'],
            'domain' => $this->sysConfigLogic->getSysConfig(10)['sc_value'] ?: $this->di['config']['admin']['webDomain'],//前台域名
            'nums' => $nums,
            'perPage' => $this->perPage,
        ]);
    }

    public function addlinkAction()
    {
        $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
        $this->view->setVars([
            'type' => $this->di['config']['agent'],
            'bonus' => $bonus,
            'rate' => $this->rate,
            'rateMoney' => substr(sprintf("%.4f",($bonus  - ($bonus  * $this->rate / 100) )),0,-1),
        ]);
    }

    public function ajaxRateAction()
    {
        $pl = !empty($this->request->getPost('br')) ? floatval($this->request->getPost('br'))  : 1.970;
        $this->redis->set('nomalBonus', $pl);

        $res = substr(sprintf("%.4f",($pl  - ($pl  * $this->rate / 100) )),0,-1);
        return $this->jsonMsg(2,$res);
    }

    public function doaddlinkAction()
    {
        $type = intval($this->request->getPost('type'));
        $rate =  round(floatval($this->request->getPost('rate')),1);

        if ($rate > $this->rate)
            return $this->showMsg('返点比例不得超过系统配置参数');

        if (!$type)
            return $this->showMsg('Invalid Data!');
        //添加链接，用户id为0
        if (!$urId = $this->recommendLogic->addLink(0, $type, $rate))
            return $this->showMsg('添加失败!');

        $this->Content = '添加推广链接(ID: ' . $urId . ')成功';
        $this->showMsg('添加成功!', false, '/agent/link');
    }

    public function editlinkAction()
    {
        $urId = intval($this->request->getQuery('id'));
        if (!$urId)
            $this->showMsg('Invalid Data!');

        if (!$info = $this->recommendLogic->getDetail($urId))
            $this->showMsg('推广记录不存在!');
        $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
        //$initRateMoney = 2000 - (2000 * $this->rate / 100);
        $rateMoney = $bonus - ($bonus  * $this->rate / 100);
        $initRateMoney = $bonus;
        $this->view->setVars([
            'info' => $info,
            'type' => $this->di['config']['agent'],
            'rate' => $this->rate,
            'bonus' => $bonus,
            'rateMoney' => substr(sprintf("%.4f",($rateMoney + ($bonus * $info['ur_fandian'] / 100))),0,-1),//默认奖金
            'initRateMoney' => $initRateMoney//系统初始化奖金
        ]);

    }

    public function doeditlinkAction()
    {
        $urId = intval($this->request->getPost('id'));
        $type = intval($this->request->getPost('type'));
        $rate = floatval($this->request->getPost('rate'));

        if (!$urId || !$type || !in_array($type, [1, 3]))
            return $this->jsonMsg('Invalid Data!');

        if (!$info = $this->recommendLogic->getDetail($urId))
            return $this->jsonMsg(0, '推广记录不存在!');

        if ($info['u_id'] != 0)
            return $this->jsonMsg(0, '没有权限修改用户的推广链接!');

        // if ($info['ur_type'] == $type && $rate == $info['ur_fandian'])
        //     return $this->jsonMsg(0, '数据与原参数一致!');

        if ($rate > $this->rate)
            return $this->showMsg('返点比例不得超过系统配置参数');

        //编辑后台生成的用户id为0的链接
        if (!$this->recommendLogic->updateLink(0, $urId, $type, $rate))
            return $this->jsonMsg(0, '修改失败!');

        $this->Content = '修改推广链接(ID：' . $urId . ')成功';
        $this->showMsg('修改成功!', false, '/agent/link');
    }

    public function dellinkAction()
    {
        $this->view->disable();
        $urId = intval($this->request->getPost('id'));
        if (!$urId)
            return $this->jsonMsg(0, 'Invalid Data!');

        //删除后台生成的用户id为0的链接
        if (!$this->recommendLogic->delLink(0, $urId))
            return $this->jsonMsg(0, '删除失败');

        $this->Content = '删除推广链接(ID：' . $urId . '成功)';
        $this->jsonMsg(1, '删除成功');
    }

    /**
     * 团队报表
     * @return [type] [description]
     */
    public function teamAction()
    {

        $startDay = $this->request->getQuery('startTime') ?: date('Y-m-d');
        $endDay = $this->request->getQuery('endTime') ?: date('Y-m-d');

        //获取用户对应类型人数
        $userTypeNums = $this->usersLogic->getUserTypeNums();

        $allTeamStat = $this->agentReportLogic->getInfoByTime(strtotime($startDay), strtotime($endDay) + 86399);
        //获取每一个彩种id和赔率id的投注金额
        $this->view->setVars([
            'stat' => $userTypeNums,
            'info' => $allTeamStat,
        ]);
    }

        /**
     * 团队预览
     * @return [type] [description]
     */
    public function userteamAction()
    {
        $uId = intval($this->request->getPost('uId'));

        //获取团队人数
        $stat = $this->logic->getAgentStat($uId);

        //获取团队收入
        $teamInfo = $this->teamLogic->getTeamStats($uId);

        //获取团队人员信息
        $agentNums = $this->logic->countAgent($uId);

        //获取用户最后登录时间
        $loginTime = $this->usersLogic->getInfoByUid($uId, 'u_last_time')['u_last_time'];

        $this->view->pick('agent/result/userteam');

        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $this->view->setVars([
            'stat' => $stat,
            'team' => $teamInfo,
            'agent' => $agentNums,
            'loginTime' => $loginTime
        ]);
    }


    public function tzAction()
    {
        $startTime = !empty($this->request->getQuery('startTime')) ? strtotime($this->request->getQuery('startTime')) : mktime(0,0,0,date('m'),date('d'),date('Y'));;
        $endTime = !empty($this->request->getQuery('endTime'))? strtotime($this->request->getQuery('endTime')) + 86399: mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $pame = $this->request->getQuery('pame') ? $this->request->getQuery('pame') : 0;
        if (!empty($pame))
        {
            switch ($pame) {
                case 1:
                    $startTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                    break;
                case 2:
                    $startTime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                    break;
                case 3:
                    $startTime = mktime(0, 0 , 0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y"));
                    $endTime = mktime(23,59,59,date("m"),date("d")-(date("w")==0?7:date("w"))+7,date("Y"));                    
                    break;
                case 4:
                    $startTime = mktime(0,0,0,date('m'),date('d')-(date("w")==0?7:date("w"))+1-7,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('d')-(date("w")==0?7:date("w"))+7-7,date('Y'));
                    break;
                case 5:
                    $startTime = mktime(0,0,0,date('m'),1,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    break;
                case 6:
                    $startTime = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                    $endTime = mktime(23,59,59,date("m") ,0,date("Y"));
                    break;
                case 0:
                    $startTime = $startTime;
                    $endTime = $endTime;
                    break;
            }
        }   
        $name = trim($this->request->getQuery('name')) ?: '';
        $uId = intval($this->request->getQuery('uid')) ?: 0;
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('limit')) ?: current($this->perPage);

        if ($name && $uId)
            $this->showMsg('Invalid Data!');

        $data = [
            'uId' => $uId,
            'name' => $name,
            'start' => $startTime,
            'end' => $endTime
        ];
        $agentReportLogic = new AgentReportLogic();
        $res = $agentReportLogic->getAgentReporttzLists($data, $page, $nums);

        $total = $res['total'];
        $lists = $res['lists'];

        
        if (!$lists)
            return $this->view->setVars([
                
                'nums' => $nums,
                'total' => $total,
                'lists' => $lists,
                'numsPage' => $this->perPage,
                'pame' => $pame,
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);

        $uIds = $online = $result = [];
        foreach ($lists as $v) {
            array_push($uIds, $v['u_id']);

        }
        for($j = 0;$j < count($uIds) ; $j++)
        {
            $num = 0;
            $temp = $this->userAgentModel->getDown($uIds[$j]);
            $temp = explode(',', $temp);
            if(!empty($temp))
            {
                $l = count($temp);
                for($k = 0;$k < $l ; $k++)
                {
                    $key = $temp[$k].':count';
                    $res = $this->di['redis']->get($key);
                    if(!empty($res))
                    {
                        $num ++;
                    }
                }
            }
            array_push($online, $num);
        }

        //批量获取代理
        $agentInfos = $this->logic->getAgentInfosByUids($uIds, 'u_id, ua_type,ua_team_num, ua_good_user_nums');
        foreach ($agentInfos as $key => $value) {
            $value['online'] = $online[$key];
            $result[] = array_merge($lists[$key], $value);
        }

        $this->view->setVars([
           
            'nums' => $nums,
            'total' => $total,
            'lists' => $result,
            'pame' => $pame,
            'numsPage' => $this->perPage,
            'startTime' => $startTime,
            'endTime' => $endTime
        ]);    
    }
}
