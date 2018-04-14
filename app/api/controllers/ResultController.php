<?php
use Phalcon\Mvc\View;

class ResultController extends ControllerBase
{
    const NUM_PER_PAGE = 20;
    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new BetsResultLogic();
        $this->betsConfigLogic = new BetsConfigLogic;
    }

    public function indexAction()
    {
        $lotteryType = $this->betsConfigLogic->getOpenBets('bet_id');
        //获取所有采种信息的最新一条信息
        $res = array();
        for($i = 0; $i < count($lotteryType); $i++){
            $res[$i] = $this->logic->getResultByType($lotteryType[$i]['bet_id'], 'bet_id, bres_id, bres_periods, bres_result');
        }

        //处理中奖号码
        for($i = 0; $i < count($lotteryType); $i++)
        {
            if($res[$i] === false)
            {
                unset($res[$i]);
            }
            else
            {
                $res[$i]['bres_result'] = explode(',', $res[$i]['bres_result']);
            }
        }

        return $this->di['helper']->resRet($res);
    }

    public function detailAction()
    {
        if (!$betId = $this->request->getQuery('bet_id'))
            return $this->di['helper']->resRet('参数错误', 500);

        if (!$betInfo = $this->betsConfigLogic->getInfoById($betId))
            return $this->di['helper']->resRet('没有此彩种信息', 500);

        if ($betInfo['bet_isenable'] != 1)
            return $this->di['helper']->resRet('彩种未开放', 500);

        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('nums')) ?: SELF::NUM_PER_PAGE;
        // 获取开奖列表
        $lists = $this->logic->listinfo($betId, $page, $nums);
        $data = [];
        if ($betInfo['bet_play_type'] == 3)
        {
            foreach($lists as $v)
            {
                switch($betId) {
                    case 14:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 15:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 16:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 17:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 18:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 19:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 20:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                }
                unset($v['bres_memo']);
                unset($v['bres_plat_isopen']);
                $v['bres_result'] = $nums;
                $data[] = $v;
            }
        }
        elseif ($betInfo['bet_play_type'] == 1)
        {
            $gameConf = $this->di['config']['game']['rule_base_type'];
            foreach ($lists as $k => $v) {
                $memo = $v['bres_memo'] ? json_decode($v['bres_memo'], true) : [];
                $result = $v['bres_result'];
                $v['zh'] = !empty($memo['zh']) ? $memo['zh'] : '?';
                $v['ds'] = !empty($memo['ds']) ? $memo['ds'] : '?';
                $v['dx'] = !empty($memo['dx']) ? $memo['dx'] : '?';
                $v['lh'] = [];
                $v['title'] = '总和';
                switch ($betId) {
                    case 1:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        foreach($nums as $i => $n)
                        {
                            if ($i == 0)
                            {
                                $ruleIdx = 3;
                                $v['lh'][] = $gameConf[explode('-', $memo['detail'][$ruleIdx - 1])[1]];
                            }
                        }
                        break;
                    case 2:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 3:
                        $v['title'] = '冠亚军和';
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 10, '?');
                        foreach($nums as $i => $n)
                        {
                            if ($i < 5)
                            {
                                $ruleIdx = 6 + $i * 4;
                                $v['lh'][] = $gameConf[explode('-', $memo['detail'][$ruleIdx-1])[1]];
                            }
                        }
                        break;
                    case 4:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 8, '?');
                        foreach($nums as $i => $n)
                        {
                            if ($i < 4)
                            {
                                $ruleIdx = 5 + $i * 5;
                                $v['lh'][] = $gameConf[explode('-', $memo['detail'][$ruleIdx-1])[1]];
                            }
                        }
                        break;
                    case 5:
                        $v['title'] = '冠亚军和';
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 10, '?');
                        foreach($nums as $i => $n)
                        {
                            if ($i < 5)
                            {
                                $ruleIdx = 6 + $i * 4;
                                $v['lh'][] = $gameConf[explode('-', $memo['detail'][$ruleIdx-1])[1]];
                            }
                        }
                        break;
                    case 6:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 7:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 8:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 9:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 3, '?');
                        break;
                    case 10:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 11:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 12:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                    case 13:
                        $nums = $v['bres_result'] ? explode(',', $v['bres_result']) : array_fill(0, 5, '?');
                        break;
                }
                $v['bres_result'] = $nums;
                unset($v['bres_memo']);
                unset($v['bres_plat_isopen']);
                $data[] = $v;
            }
        }

        return $this->di['helper']->resRet($data);
    }
}
