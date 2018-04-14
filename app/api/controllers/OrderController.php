<?php

class OrderController extends ControllerBase
{
    const NUM_PER_PAGE = 20;
    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->betsOrdersLogic = new BetOrdersLogic;
        $this->resLogic = new BetsResultLogic;
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->gameLogic = new GameLogic;
    }

    //订单报表
    public function reportAction()
    {
        $betId = intval($this->request->getQuery('bet_id'));
        if ($betId && !$betInfo = $this->betsConfigLogic->getInfoById($betId))
            return $this->di['helper']->resRet('彩种异常', 500);

        $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));
        $startDay = !empty($this->request->getQuery('startTime')) ? strtotime(trim($this->request->getQuery('startTime'))): $today ;
        $endDay = !empty($this->request->getQuery('endTime')) ? strtotime(trim($this->request->getQuery('endTime'))) : $today;

        // //一天一天查询整合
        // $count = ($endDay - $startDay) == 0 ? 0 : ceil(($endDay-$startDay)/86400);

        // $tmp = $lists = [];
        // $total['num'] = 0;
        // $total['money'] = 0;
        // $total['win'] = 0;
        // $total['water'] = 0;
        // $total['earn'] = 0;
        // for($i = 0; $i<=$count; $i++)
        // {
        //     $startTime = $startDay + $i*86400;
        //     $dayInfo = $this->betsOrdersLogic->getDayOrder($this->uId, $betId, $startTime, $startTime+86399);
        //     if ($dayInfo['count'])
        //     {
        //         $tmp['money'] = $dayInfo['bo_money'];
        //         $tmp['water'] = $dayInfo['bo_back_money'];
        //         $tmp['win'] = $dayInfo['bo_bonus'] - $dayInfo['bo_money'];
        //         $tmp['earn'] = $tmp['win'] + $dayInfo['bo_back_money'];
        //         $tmp['num'] = $dayInfo['count'];
        //         $tmp['date'] = $dayInfo['days'];
        //         $lists[] = $tmp;
        //         $total['num'] += $tmp['num'];
        //         $total['money'] += $tmp['money'];
        //         $total['win'] += $tmp['win'];
        //         $total['water'] += $tmp['water'];
        //         $total['earn'] += $tmp['earn'];
        //     }
        // }

        //一次性查询所有日期
        $betsTotalInfo = $this->betsOrdersLogic->getOrderByDate($this->uId, $betId, $startDay, $endDay+86399);
        $total['num'] = 0;
        $total['money'] = 0;
        $total['win'] = 0;
        $total['water'] = 0;
        $total['earn'] = 0;
        $lists = [];
        foreach ($betsTotalInfo as $v) {
            $list['money'] = $list['win'] = $list['water'] = $list['earn'] = $list['num'] = 0;
            $list['money'] = $v['bo_money'];
            $list['water'] = $v['bo_back_money'];
            $list['win'] = $list['win'] - $v['bo_money'] + $v['bo_bonus'];
            $list['earn'] = $list['win'] + $v['bo_back_money'];
            $list['num'] = $v['count'];
            $list['date'] = $v['days'];
            $total['num'] += $list['num'];
            $total['money'] += $list['money'];
            $total['win'] += $list['win'];
            $total['water'] += $list['water'];
            $total['earn'] += $list['earn'];
            $lists[] = $list;
        }

        return $this->di['helper']->resRet(['lists' => $lists, 'total' => $total]);
    }

    //订单详情
    public function detailAction()
    {
        if (!$boId = intval($this->request->getQuery('boId')))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        $fields = 'u_name, bo_sn, bet_id, bo_played_name, bo_created_time, bo_money, bo_content, bo_issue, bo_draw_result, bo_bonus, bo_back, bo_back_money, bo_odds';
        if (!$detail = $this->betsOrdersLogic->getOrderDetail($boId, $fields))
            return $this->di['helper']->resRet('该订单记录不存在！', 500);

        if ($detail['bo_draw_result'] == 5)
            $detail['bres_result'] = '';
        else
            $detail['bres_result'] = $this->resLogic->getResultNum($detail['bet_id'], $detail['bo_issue'])['bres_result'];

        //开奖号码处理
        if (in_array($detail['bet_id'], [3,4,5]))
        {
            $result = explode(',', $detail['bres_result']);
            $len = count($result);
            for($i = 0; $i < $len; $i++)
            {
                $tmp[$i] = intval($result[$i]);
                if($result[$i]<10)
                {
                    $tmp[$i] = substr($result[$i], -1);
                }
            }
            $detail['bres_result'] = implode(',', $tmp);
        }

        $betName = $this->betsConfigLogic->getInfoById($detail['bet_id'])['bet_name'];
        $boContent = explode('-', $detail['bo_content']);//投注号码
        $game = $this->di['config']['game'];//获取玩法规格配置
        if (in_array($detail['bet_id'], [14,15,16,17,18,19,20]))
            $content = $game['rule_type'][$boContent[0]] . '(' . $boContent[1] . '/' . $detail['bo_odds'] .')';
        else
            $content = $game['rule_type'][$boContent[0]] . '(' . $game['rule_base_type'][$boContent[1]] . '/' . $detail['bo_odds'] .')';
        $detail['content'] = $content;
        $detail['bet_name'] = $betName;
        $detail['act_money'] = $detail['bo_bonus'] + $detail['bo_back_money'] - $detail['bo_money'];
        unset($detail['bet_id']);
        unset($detail['bo_odds']);
        unset($detail['bo_bonus']);
        unset($detail['bo_content']);

        return $this->di['helper']->resRet($detail, 200);
    }

    //日订单列表
    public function reportDetailAction()
    {
        $betId = intval($this->request->getQuery('bet_id'));
        if ($betId && !$betInfo = $this->betsConfigLogic->getInfoById($betId))
            return $this->di['helper']->resRet('彩种异常', 500);
        $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));
        $startDay = strtotime(trim($this->request->getQuery('startTime'))) ?: $today ;
        $endDay = strtotime(trim($this->request->getQuery('endTime'))) ?: $today;
        $type = intval($this->request->getQuery('type')) ?: 1;
        $issue = intval($this->request->getQuery('issue')) ?: 0;//期号
        if (!in_array($type, [1,3,5]))
            return $this->di['helper']->resRet('类型错误', 500);

        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('nums')) ?: SELF::NUM_PER_PAGE;

        $lists = $this->betsOrdersLogic->getOrderLists($this->uId, $betId, $type, $issue, $startDay, $endDay+86399, $page, $nums);

        if (!$lists)
            return $this->di['helper']->resRet();

        $data = $tmp = [];
        foreach ($lists as $v) {
            $tmp = $v;
            $content = explode('-', $v['bo_content']);
            if (in_array($v['bet_id'], [14,15,16,17,18,19,20]))
                $tmp['bo_played_name'] = $v['bo_played_name'] . '(' . $content[1] . ')';
            else
                $tmp['bo_played_name'] = $v['bo_played_name'] . '(' . $this->di['config']['game']['rule_type'][$content[0]].':' . $this->di['config']['game']['rule_base_type'][$content[1]] . ')';
            unset($tmp['bo_content']);
            unset($tmp['bet_id']);
            $data[] = $tmp;
        }

        return $this->di['helper']->resRet($data, 200);
    }

    //撤销订单
    public function cancelAction()
    {
        if (!$boId = intval($this->request->getPost('boId')))
            return $this->di['helper']->resRet('Invalid Data!', 500);

        $fields = 'u_id, bet_id, bo_issue, bo_draw_result, bo_status';
        if (!$detail = $this->betsOrdersLogic->getOrderDetail($boId, $fields))
            return $this->di['helper']->resRet('该订单记录不存在！', 500);

        if ($detail['u_id'] != $this->uId)
            return $this->di['helper']->resRet('不是您的订单，无权取消！', 500);

        if ($detail['bo_status'] != 1)
            return $this->di['helper']->resRet('订单状态异常', 500);

        if ($detail['bo_draw_result'] != 5)
            return $this->di['helper']->resRet('该订单已经开奖了！', 500);

        if (!$this->gameLogic->checkAvail($detail['bet_id'], $detail['bo_issue']))
            return $this->di['helper']->resRet('已封单，不能撤销！', 500);

        if (!$this->betsOrdersLogic->cancelOrderById($boId, $this->uId))
            return $this->di['helper']->resRet('撤销失败，请重试！', 500);

        return $this->di['helper']->resRet();
    }
}
