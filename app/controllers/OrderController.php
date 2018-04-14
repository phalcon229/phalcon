<?php

class OrderController extends ControllerBase
{

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

        //私彩开奖
    public function testAction()
    {
        $orderInfo =$this->betsOrdersLogic->getAllOrder(20170703097);
        var_dump($orderInfo);exit;
    }

    public function indexAction()
    {
        $orderInfo =$this->betsOrdersLogic->getAllOrder(20170703097);
        var_dump($orderInfo);exit;
        $lottery = $this->betsConfigLogic->getAll();
        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d ', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $now);

        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast);

        $lotteryType = $this->request->getPost('lotteryType');
        $serialNum = $this->request->getPost('serialNum');
        $type = $this->request->get('zh');
        $orderInfo =$this->betsOrdersLogic->getOrderInfo($this->uId,$lotteryType,$serialNum,$tWeekF,$tWeekL,$type);
        $this->view->setVars([
            'title' => '订单查询' ,
            'orderInfo' => $orderInfo ,
            'base' => $base ,
            'othorInfo' => $othorInfo ,
            'lottery' => $lottery ,
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd ,
            'lWeekLast' => $lWeekLast ,
            'lWeekFirst' => $lWeekFirst ,
            'tWeekFirst' => $tWeekFirst ,
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast ,
            'tMonthFirst' => $tMonthFirst ,
            'tMonthFirst' => $tMonthFirst ,
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast ,
            'todayBegin' => $todayBegin ,
            'todayEnd' => $todayEnd,
            'type' => $type
        ]);
    }

    public function dataFreshAction()
    {
        $gameConf = $this->di['config']['game'];
        $now = time();

        $todayBegin = date('Y-m-d ', $now);//今天

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));
        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast);

        $lotteryType = $this->request->getPost('lotteryType');
        $serialNum = trim($this->request->getPost('serialNum'));
        $startDay = strtotime(trim($this->request->getPost('startDay')));
        $endDay =strtotime(trim($this->request->getPost('endDay')))+86400;
        $type = trim($this->request->getPost('type'));
        if(empty($startDay))
        {
            $startDay = $tWeekF;
            $endDay = $tWeekL;
        }

        $orderInfo =$this->betsOrdersLogic->getOrderInfo($this->uId,$lotteryType,$serialNum,$startDay,$endDay,$type);
        if(!empty($orderInfo)){
            $data = [ 'orderInfo' => $orderInfo,'conf'=>$gameConf];
            return $this->di['helper']->resRet($data, 200);
        }
        else
        {
            $msg = '无数据';
            return $this->di['helper']->resRet($msg, 500);
        }

    }

    public function detailAction()
    {
        $boId = $this->request->get('boId');
        $betId = $this->request->get('betId');
        $npre = $this->request->get('nper');
        $res = $this->resLogic->getResultInfo($betId, $npre);
        //开奖号码处理
        if(empty($res))
        {
            $res[0]['bres_result'] = '未开奖';
        }
        if($res[0]['bet_id'] == 3 || $res[0]['bet_id'] == 4 || $res[0]['bet_id'] == 5)
        {
            $res[0]['bres_result'] = explode(',',$res[0]['bres_result']);
            $len = count($res[0]['bres_result']);
            for($i = 0; $i < $len; $i++)
            {
                $res[0]['bres_result'][$i] = intval($res[0]['bres_result'][$i]);
                if($res[0]['bres_result'][$i]<10)
                {
                    $res[0]['bres_result'][$i] = substr($res[0]['bres_result'][$i], 0,1);
                }
            }
            $res[0]['bres_result'] = implode(',',$res[0]['bres_result']);
        }
        
        $detail = $this->betsOrdersLogic->getDetailById($boId);
        $this->view->setVar('uName', $this->uName);
        $this->view->setVar('res', $res);
        $this->view->setVar('detail', $detail);
        $this->view->setVar('title', '订单详情');
    }

    public function reportAction()
    {
        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $now);

        $tWeekF = strtotime($todayBegin);
        $tWeekL = strtotime($todayBegin)+86400;
        $lotteryType = $this->betsConfigLogic->getAll(1);

        $betId = 0;

        $today = strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME']));
        $startDay = $today ;
        $endDay =  $today;

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
            $total['water'] += sprintf("%.2f",$list['water']);
            $total['earn'] += $list['earn'];
            $lists[] = $list;
        }

        $this->view->setVars([
            'result' => $lists ,
            'lotteryType' => $lotteryType ,
            'todayBegin' => $todayBegin ,
            'todayEnd' => $todayEnd ,
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd ,
            'lWeekLast' => $lWeekLast ,
            'lWeekFirst' => $lWeekFirst ,
            'tWeekFirst' => $tWeekFirst ,
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast ,
            'tMonthFirst' => $tMonthFirst ,
            'tMonthFirst' => $tMonthFirst ,
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast  ,
            'total' => $total,
            'title' => '个人报表' ]);
    }

    public function reportFreshAction()
    {
        $now = time();

        $todayBegin = date('Y-m-d ', $now);//今天

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));
        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast);

        $startDay = strtotime(trim($this->request->getPost('startDay')));
        $endDay =strtotime(trim($this->request->getPost('endDay')))+86400;


        $betId = intval($this->request->getPost('lotteryType'));
        if ($betId && !$betInfo = $this->betsConfigLogic->getInfoById($betId))
            return $this->di['helper']->resRet('彩种异常', 500);

        $betsTotalInfo = $this->betsOrdersLogic->getOrderByDate($this->uId, $betId, $startDay, $endDay);
        $total['num'] = 0;
        $total['money'] = 0;
        $total['win'] = 0;
        $total['water'] = 0;
        $total['earn'] = 0;
        $lists = [];
        foreach ($betsTotalInfo as $v) {
            $list['money'] = $list['win'] = $list['water'] = $list['earn'] = $list['num'] = 0;
            $list['money'] = $v['bo_money'];
            $list['water'] = sprintf("%.2f",$v['bo_back_money']);
            $list['win'] = $list['win'] - $v['bo_money'] + $v['bo_bonus'];
            $list['earn'] = $list['win'] + $v['bo_back_money'];
            $list['num'] = $v['count'];
            $list['date'] = $v['days'];
            $total['num'] += $list['num'];
            $total['money'] += $list['money'];
            $total['win'] += $list['win'];
            $total['water'] += sprintf("%.2f",$list['water']);
            $total['earn'] += $list['earn'];
            $lists[] = $list;
        }

        if(!empty($lists)){
            $data = [ 'betsTotalInfo' => $lists, 'total' => $total];
            return $this->di['helper']->resRet($data, 200);
        }
        else
        {
            $msg = '无数据';
            return $this->di['helper']->resRet($msg, 500);
        }
    }

    public function reportDetailAction()
    {
        $gameConf = $this->di['config']['game'];
        $lottery = $this->betsConfigLogic->getAll();

        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $now);

        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast);

        $lotteryType = $this->request->getPost('lotteryType');

        $serialNum = $this->request->getPost('serialNum');
        $zh = $this->request->get('zh');
        $log = $this->request->get('log') ? 1:2;

        $startDay = strtotime($this->request->get('day'));
        if(empty($startDay))
        {
            $startDay = strtotime($tWeekFirst);
            $endDay = strtotime($tWeekLast);
        }
        else
        {
            $day = $this->request->get('day');
            $endDay = $startDay + 86400;
            $log = 1;
        }
        $type = $zh;
        $orderInfo =$this->betsOrdersLogic->getOrderInfo($this->uId,$lotteryType,$serialNum,$startDay,$endDay,$type);

        $this->view->setVars([
            'title' => '订单查询' ,
            'conf' =>$gameConf,
            'orderInfo' => $orderInfo ,
            // 'base' => $base ,
            // 'othorInfo' => $othorInfo ,
            'lottery' => $lottery ,
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd ,
            'lWeekLast' => $lWeekLast ,
            'lWeekFirst' => $lWeekFirst ,
            'tWeekFirst' => $tWeekFirst ,
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast ,
            'tMonthFirst' => $tMonthFirst ,
            'tMonthFirst' => $tMonthFirst ,
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast ,
            'todayBegin' => $todayBegin ,
            'todayEnd' => $todayEnd,
            'day' => $day, 'zh' => $zh,
            'log' => $log ]);
    }

    public function cancelOrderAction()
    {
        $gameConf = $this->di['config']['game'];
        $now = time();

        $todayBegin = date('Y-m-d ', $now);//今天

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));
        $order = [];
        $lotteryType = $this->request->getPost('lotteryType');
        $serialNum = $this->request->getPost('serialNum');
        $startDay = strtotime($this->request->getPost('startDay'));
        $endDay = strtotime($this->request->getPost('endDay'))+86400;
        if(empty($startDay))
        {
            $startDay = strtotime($tWeekFirst);
            $endDay = strtotime($tWeekLast);
        }
        if (!$boId = intval($this->request->getPost('boId')))
            return $this->di['helper']->resRet('操作错误', 500);
        try {
            $res = $this->betsOrdersLogic->cancelOrderById($boId, $this->uId);
            if($res)
            {
                $orderInfo = $this->betsOrdersLogic->getCanCancle($this->uId,$lotteryType,$serialNum,$startDay,$endDay);
                for($i = 0; $i < count($orderInfo); $i++)
                {
                    if($orderInfo[$i]['bo_status'] <> 5)
                    {
                        $check = $this->gameLogic->checkAvail($orderInfo[$i]['bet_id'],$orderInfo[$i]['bo_issue']);
                        if(!$check)
                        {
                            $order[] = $orderInfo[$i];
                        }
                    }
                }
                $orderInfo = array_merge($orderInfo);
                if(!empty($orderInfo)){
                    $data = [ 'orderList' => $order,'conf'=>$gameConf];
                    return $this->di['helper']->resRet($data, 200);
                }
                else
                {
                    return $this->di['helper']->resRet('无数据', 500);
                }
            }
            else
            {
                return $this->di['helper']->resRet('订单撤销失败，请重新尝试', 501);
            }
        } catch (Exception $e) {
            return $this->di['helper']->resRet($e->getMessage(), 502);
        }
    }
        public function cancelAction()
        {
            $gameConf = $this->di['config']['game'];
            $lottery = $this->betsConfigLogic->getAll();
            $date = date('Y-m-d',time());
            $now = time();

            $timestamp = strtotime($date);//上月
            $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
            $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

            $tMonthFirst = date("Y-m-01",strtotime($date));//本月
            $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

            $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
            $tWeekFirst = date('Y-m-d', $time);
            $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

            $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
            $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
            $lWeekFirst = date('Y-m-d', $lastMonday);
            $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

            $todayBegin = date('Y-m-d ', $now);//今天
            $todayEnd = date('Y-m-d', $now);

            $times = strtotime('-1 day', $now);//昨天
            $yBegin = date('Y-m-d', $times);
            $yEnd = date('Y-m-d', $now);

            $startDay = strtotime($this->request->get('day'));
            if(empty($startDay))
            {
                $startDay = strtotime($tWeekFirst);
                $endDay = strtotime($tWeekLast);
            }
            else
            {
                $day = $this->request->get('day');
                $endDay = $startDay + 86400;
            }
            $lotteryType = $this->request->getPost('lotteryType');
            $serialNum = $this->request->getPost('serialNum');
            $orderInfo = $this->betsOrdersLogic->getCanCancle($this->uId,$lotteryType,$serialNum,$startDay,$endDay);

            $orderList = [];
            for($i = 0; $i < count($orderInfo); $i++)
            {
                if($orderInfo[$i]['bo_status'] <> 5)
                {
                    $check = $this->gameLogic->checkAvail($orderInfo[$i]['bet_id'],$orderInfo[$i]['bo_issue']);
                    if($check)
                        continue;
                    array_push($orderList, $orderInfo[$i]);
                }
            }

            $this->view->setVars([
                'title' => '订单查询' ,
                'orderList' => $orderList ,
                'base' => $base ,
                'conf' =>$gameConf,
                'othorInfo' => $othorInfo ,
                'lottery' => $lottery ,
                'yBegin' => $yBegin ,
                'yEnd' => $yEnd ,
                'lWeekLast' => $lWeekLast ,
                'lWeekFirst' => $lWeekFirst ,
                'tWeekFirst' => $tWeekFirst ,
                'tWeekLast' => $tWeekLast ,
                'tMonthLast' => $tMonthLast ,
                'tMonthFirst' => $tMonthFirst ,
                'tMonthFirst' => $tMonthFirst ,
                'lMonthFirst' => $lMonthFirst ,
                'lMonthLast' => $lMonthLast ,
                'todayBegin' => $todayBegin ,
                'todayEnd' => $todayEnd,
                'day' => $day ]);
    }

    public function cancelDataFreshAction()
    {
        $gameConf = $this->di['config']['game'];
        $now = time();
        $todayBegin = date('Y-m-d ', $now);//今天

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));
        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast);

        $lotteryType = $this->request->getPost('lotteryType');
        $serialNum = trim($this->request->getPost('serialNum'));
        $startDay = strtotime(trim($this->request->getPost('startDay')));
        $endDay =strtotime(trim($this->request->getPost('endDay')))+86400;
        if(empty($startDay))
        {
            $startDay = $tWeekF;
            $endDay = $tWeekL;
        }

        $orderInfo =$this->betsOrdersLogic->getCanCancle($this->uId,$lotteryType,$serialNum,$startDay,$endDay);
        
        $orderList = [];
        for($i = 0; $i < count($orderInfo); $i++)
        {
            if($orderInfo[$i]['bo_status'] <> 5)
            {
                $check = $this->gameLogic->checkAvail($orderInfo[$i]['bet_id'],$orderInfo[$i]['bo_issue']);
                if($check)
                    continue;
                array_push($orderList, $orderInfo[$i]);
            }
        }
        
        if(!empty($orderList)){
            $data = [ 'orderList' => $orderList,'conf'=>$gameConf];
            return $this->di['helper']->resRet($data, 200);
        }
        else
        {
            $msg = '无数据';
            return $this->di['helper']->resRet($msg, 500);
        }

    }
}
