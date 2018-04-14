<?php

use Admin\Utils;

class OrderController extends ControllerBase
{
    public function initialize()
    {
        $this->logic = new BetOrdersLogic;
        $this->betsLogic = new BetsConfigLogic;
        $this->rulesLogic = new BetsRulesLogic;
        $this->betsResultLogic = new BetsResultLogic;
        $this->sysConfigLogic = new SysConfigLogic;
        $this->resultLogic = new BetsResultLogic();
        $this->orderLogic = new BetOrdersLogic();
        $this->userLogic = new UsersLogic();

        //获取所有彩种
        $bets = $this->betsLogic->getBets(1);
        $this->view->setVars([
            'name' => $this->dispatcher->getActionName(),
            'bets' => $bets
        ]);
    }

    public function indexAction()
    {
        //获取所有彩种
        $bets = $this->betsLogic->getBets(1);

        //获取彩种id
        $betId = intval($this->request->get('bet')) ?: key($bets);

        //获取彩种的基础配置
        $baseInfo = $this->betsLogic->getInfoById($betId);

        //获取最近一期开奖结果
        $lastExpect = $this->betsResultLogic->getLastResult($betId, 1);

        //如果开奖结果为空，则根据彩种的开奖球数显示对应个数的问号
        if (!$result = $lastExpect['bres_result'])
        {
            for($i = 0;$i< $baseInfo['bet_ball_num']; $i++)
            {
                $result .= '?,';
            }
            $result = rtrim($result, ',');
        }
        if($betId == 3 || $betId == 4 || $betId == 5)
        {
            $result = explode(',',$result);
            $len = count($result);
            for($i = 0; $i < $len; $i++)
            {
                $result[$i] = intval($result[$i]);
                if($result[$i]<10)
                {
                    $result[$i] = substr($result[$i], 0,1);
                }
            }
            $result = implode(',',$result);
        }
        //获取本期信息
        $nextExpect = $this->resultLogic->getNextPerids($betId, 1, 0);

        //获取当前彩种今日总盈亏,对数值四舍五入，并固定小数点4位
        $earn = number_format(round($this->redis->get('earn:' . $betId . ':' . date('Y-m-d')), 4), 4) ?: 0;

        //获取本期投注注额
        $count = $this->redis->hGetAll('bet:count:' . $betId . ':' . $nextExpect[0]['bres_periods']);

        $PerRedis = date('Y-m-d') . ':' . $betId . ':perdate:count';
        $AllRedisField = date('Y-m-d') . ':allbet:count';

        $perbet = $this->redis->get($PerRedis)?$this->redis->get($PerRedis):0;
        $allbet = $this->redis->get($AllRedisField)?$this->redis->get($AllRedisField):0;

        //获取封盘配置参数
        $closeSecond =  $this->sysConfigLogic->getSysConfig(2)['sc_value'];

        //封盘时间
        $stopTime = date('Y-m-d H:i:s', $nextExpect[0]['bres_open_time'] - $closeSecond);

        //获取彩种赔率配置
        $rulesBase = $this->rulesLogic->getInfoById($betId);

        $doBet = array();

        for($i = 0; $i < count($rulesBase); $i++)
        {
            $betMoney = $this->orderLogic->getBetMoney($rulesBase[$i]['br_id'], $betId, $nextExpect[0]['bres_periods']);
            if(!empty($betMoney['bo_money']))
            {
                array_push($doBet, $betMoney);
            }
            else
            {
                array_push($doBet, ['bo_money'=>'']);
            }
            $rulesBase[$i] = array_merge($rulesBase[$i],$doBet[$i]); 
        }
        
        $rules = [];
        //整合类型
        foreach ($rulesBase as $rule) {
            if (empty($rules[$rule['br_type']])) $rules[$rule['br_type']] = [];
            $rules[$rule['br_type']][$rule['br_base_type']] = $rule;
        }
        $color = '';
        if($betId == 3 || $betId == 5)
        {
            $color = 1;
        }
        
//        $key = 'all:online';
//        $online = $this->di['redis']->get($key);
//        if(empty($online))
//        {
//            $allIds = $this->userLogic->getAllids();
//            $num = 0;
//            foreach ($allIds as $key=>$value)
//            {
//                $key = $value['u_id'].':count';
//                $res = $this->di['redis']->get($key);
//                if(!empty($res))
//                {
//                    $num ++;
//                }
//            }
//            $online = $num;
//            $this->di['redis']->setex($key, 180 , $num);
//        }

        //获取每一个彩种id和赔率id的投注金额
        $this->di['view']->setVars([
            'rules' => $rules,
//            'online' => $online,
            'bets' => $bets,
            'game' => $this->di['config']['game'],
            'result' => explode(',', $result),//最近一期开奖结果
            'lastExpect' => $lastExpect['bres_periods'],//最近一期开奖期号
            'time' => $this->di['config']['admin']['refreshTime'],
            'base' => $baseInfo,
            'stopTime' => $stopTime,
            'next' => $nextExpect,
            'count' => $count,
            'earn' => $earn,
            'color' =>$color,
            'ruleId' => $ruleId,
            'perbet' => $perbet,
            'allbet' => $allbet
        ]);
    }

    public function listAction()
    {
        $perPage = $this->di['config']['admin']['perPage'];
        $nums = intval($this->request->get('limit')) ?: current($perPage);
        $page = intval($this->request->get('page')) ?: 1;
        //获取所有彩种
        $bets = $this->betsLogic->getBets(1);

        //获取彩种id
        $betId = intval($this->request->get('bet')) ?: key($bets);

        //获取本期未开奖信息期号
       $unopenExpect[0] = $this->betsResultLogic->getOpeningExpect($betId)['bres_periods'];

        $date = strtotime(date('Y-m-d'));
        //获取今日已开奖彩票期数
        $periods = $this->betsResultLogic->getIssuesLimit($betId, $date);
        $periods = array_merge($unopenExpect,$periods);
        
        //获取期数id
        $issue = $this->request->get('issue') ?: $unopenExpect[0];

        $res = $this->logic->getLists($betId, $issue, $page, $nums);
        $page = new \Components\Utils\Pagination($res['total'], $nums, $page);
        
        //获取彩种的所有玩法配置
        $tmp = $lists = [];
        $rules = $this->rulesLogic->getInfoById($betId);
        foreach ($rules as $value) {
            $tmp[$value['br_id']] = $value;
        }

        foreach ($res['list'] as $v) {

            if (array_key_exists($v['br_id'], $tmp))
                $lists[] = array_merge($v, $tmp[$v['br_id']]);
        }

        $this->di['view']->setVars([
            'lists' => $lists,
            'issues' => $periods,
            'bets' => $bets,
            'game' => $this->di['config']['game'],
            'total' => $res['total'],
            'nums' => $nums,
            'numsPage' => $perPage,

        ]);
    }

    public function setbonusAction()
    {
        $this->view->disable();
        $brId = intval($this->request->getPost('id'));
        $bonus = floatval($this->request->getPost('bonus'));

        if (!$rule = $this->rulesLogic->getRuleByBrId($brId, 1))
            return $this->jsonMsg(0, '没有该配置id');

        if ($bonus == $rule['br_bonus'])
            return $this->jsonMsg(1, '修改成功');

        if (!$res = $this->rulesLogic->editBonus($brId, $bonus)){
            return $this->jsonMsg(0, '修改失败');
        }

        $this->logContent = '修改(ID：'. $brId .')赔率为：' . $bonus;
        $this->jsonMsg(1, '修改成功');
    }
}
