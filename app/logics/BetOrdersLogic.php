<?php
class BetOrdersLogic extends LogicBase
{

    public function __construct()
    {
        $this->betOrdersModel = new BetOrdersModel;
        $this->model = new BetsConfigModel();
        $this->betrulesModel = new BetsRulesModel();
    }

    public function getAllAbnormal()
    {

        $res = $this->betOrdersModel->getAllAbnormal();
        return $this->uniqueBetid($res);
    }

    public function getAbnormalDetail($currentPage, $num, $betid, $issue )
    {
        $index = ($currentPage - 1) * $num;
        $res = $this->betOrdersModel->getAbnormalDetail($index, $num, $betid, $issue );

        $new = [];
        foreach ($res as $value) {

            $rules = explode('-',$value['bo_content']);
            $value['br_type'] = $rules[0];
            $value['br_base_type'] = $rules[1];
            array_push($new, $value);
        }
        return $new ;
    }

    public function getAbnormalTotal($condition, $value)
    {
       return $this->betOrdersModel->getAbnormalTotal($condition, $value)['total'];
    }

    public function getAbnormalDetailByBetid($betid )
    {

        $res = $this->betOrdersModel->getAbnormalDetailByBetid($betid);
        return $this->uniqueBetid($res);

    }

    public function uniqueBetid($res )
    {
        $data = [];
        foreach ($res as $value) {
            $value['unique'] = $value['bo_issue'].','.$value['bet_id'];
            array_push($data, $value['unique']);
        }
        $datas = array_unique($data);
        $news =[];
        foreach ($datas as $value) {
            array_push($news, explode(',', $value));
        }

        $bet =[];
        foreach ($news as $value) {
            $res = $this->model->getInfoById($value[1]);
            $value['bet_name'] = $res['bet_name'];
            array_push($bet, $value);
        }
        return $bet ;
    }

    /**
     * 分页获取用户订单记录
     * @param  [type] $betId   [description]
     * @param  [type] $issue   [description]
     * @param  [type] $page    [description]
     * @param  [type] $perPage [description]
     * @return [type]          [description]
     */
    public function getLists($betId, $issue, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        $res['total'] = $this->betOrdersModel->getTotal($betId, $issue);
        $res['list'] = $this->betOrdersModel->getLists($betId, $issue, $start, $perPage);

        return $res;
    }

    /**
     * 根据时间段，uid，采种获取所有的订单信息
     * @param type $uId
     * @param type $lotteryType
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getTotalInfo($uId,$lotteryType,$startDay,$endDay)
    {
        $info = $this->betOrdersModel->getTotalInfo($uId,$lotteryType,$startDay,$endDay);
        return $info;
    }

    /**
     * 根据时间段，uid，订单号，采种获取所有的订单信息
     * @param type $uId
     * @param type $lotteryType
     * @param type $serialNum
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getReportInfo($uId,$lotteryType,$serialNum,$startDay,$endDay)
    {
        $info = $this->betOrdersModel->getTotalInfo($uId,$lotteryType,$serialNum,$startDay,$endDay);
        return $info;
    }

    /**
     * 投注
     * @param  int $betId [description]
     * @param  int $uId   [description]
     * @param  array $rules [description]
     * @param  array $track 追号信息
     * @return [type]        [description]
     */
    public function create($betId, $uId, $uName, $rules, $track = [])
    {
        return $this->betOrdersModel->create($betId, $uId, $uName, $rules, $track);
    }

    public function newCreate($betId, $uId, $uName, $rules, $track = [])
    {
        return $this->betOrdersModel->newCreate($betId, $uId, $uName, $rules, $track);
    }

    //判断异常订单是否存在
    public function getOrderByBoid($boid)
    {
       return $this->betOrdersModel->getOrderByBoid($boid);
    }

    public function backOne($data)
    {
       return $this->betOrdersModel->backOne($data);
    }

    public function allback($betid, $issue)
    {
       return $this->betOrdersModel->allback($betid, $issue);
    }

    public function getWaittingOpenNumsByBetid($betId, $expect)
    {
        return $this->betOrdersModel->getNumsByBetidAndStatus($betId, $expect, 1, 5);
    }

    public function getWaittingOpenOrderByBetidAndPage($betId, $expect, $page, $eachNums)
    {
        return $this->betOrdersModel->getOrderByBetidAndStatus($betId, $expect, 1, 5, $page, $eachNums);
    }

    public function openBets($betId, $expect, $openCode, $betRes, $order)
    {
        $backMoney = 0.0000;
        $earnMoney = 0.0000;
        $isWin = 0;
        switch ($betId) {
            case 1:
            case 21:
                // 校验该订单中奖以及金额和回水等
                if(in_array($order['bo_content'], $betRes) === true) // 中奖
                {
                    $isWin = 1;
                    $earnMoney = $order['bo_money'] * $order['bo_odds'];
                    if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                    {
                        $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                        if($trackCode == -1)
                        {
                            $trackCode = $order['bo_id'];
                        }
                        $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                        if(count($otherTrack) > 0)
                        {
                            foreach ($otherTrack as $key => $value) {
                                $this->betOrdersModel->backOne($otherTrack[$key]);
                            }
                        }
                    }
                }
                break;
            case 2:
            case 3:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:

                // 校验该订单中奖以及金额和回水等
                if(in_array($order['bo_content'], $betRes) === true) // 中奖
                {
                    $isWin = 1;
                    $earnMoney = $order['bo_money'] * $order['bo_odds'];
                    if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                    {
                        $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                        if($trackCode == -1)
                        {
                            $trackCode = $order['bo_id'];
                        }
                        $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                        if(count($otherTrack) > 0)
                        {
                            foreach ($otherTrack as $key => $value) {
                                $this->betOrdersModel->backOne($otherTrack[$key]);
                            }
                        }
                    }
                }
                break;

            case 4:
                if(in_array($order['bo_content'],['6-10','6-11'] ))
                {
                    if(in_array('6-53', $betRes))
                    {
                        $isWin = 2;
                        break;
                    }
                    else
                    {
                        if(in_array($order['bo_content'], $betRes) === true) // 中奖
                        {
                            $isWin = 1;
                            $earnMoney = $order['bo_money'] * $order['bo_odds'];
                            if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                            {
                                $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                                if($trackCode == -1)
                                {
                                    $trackCode = $order['bo_id'];
                                }
                                $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                                if(count($otherTrack) > 0)
                                {
                                    foreach ($otherTrack as $key => $value) {
                                        $this->betOrdersModel->backOne($otherTrack[$key]);
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
                else
                {
                    if(in_array($order['bo_content'], $betRes) === true) // 中奖
                    {
                        $isWin = 1;
                        $earnMoney = $order['bo_money'] * $order['bo_odds'];
                        if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                        {
                            $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                            if($trackCode == -1)
                            {
                                $trackCode = $order['bo_id'];
                            }
                            $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                            if(count($otherTrack) > 0)
                            {
                                foreach ($otherTrack as $key => $value) {
                                    $this->betOrdersModel->backOne($otherTrack[$key]);
                                }
                            }
                        }
                    }
                    break;
                }
                break;
            case 10:
            case 11:
            case 12:
            case 13:
                // 校验该订单中奖以及金额和回水等
                if(in_array($order['bo_content'],['6-10','6-11'] ))
                {
                    if(in_array('6-53', $betRes))
                    {
                        $isWin = 2;
                        break;
                    }
                    else
                    {
                        if(in_array($order['bo_content'], $betRes) === true)
                        {
                            $isWin = 1;
                            $earnMoney = $order['bo_money'] * $order['bo_odds'];
                            if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                            {
                                $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                                if($trackCode == -1)
                                {
                                    $trackCode = $order['bo_id'];
                                }
                                $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                                if(count($otherTrack) > 0)
                                {
                                    foreach ($otherTrack as $key => $value) {
                                        $this->betOrdersModel->backOne($otherTrack[$key]);
                                    }
                                }
                            }
                            break;
                        }
                        break;
                    }
                }
                $dxds = ['1-10','1-11','1-12','1-13',
                         '2-10','2-11','2-12','2-13',
                         '3-10','3-11','3-12','3-13',
                         '4-10','4-11','4-12','4-13',
                         '5-10','5-11','5-12','5-13',
                        ];

                    if(in_array($order['bo_content'],$dxds))
                    {
                        if(in_array(explode('-', $order['bo_content'])[0].'-27',$betRes))
                        {
                            $isWin = 2;
                        }
                        else
                        {
                            if(in_array($order['bo_content'], $betRes) === true) // 中奖
                            {
                                $isWin = 1;
                                $earnMoney = $order['bo_money'] * $order['bo_odds'];
                                if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                                {
                                    $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                                    if($trackCode == -1)
                                    {
                                        $trackCode = $order['bo_id'];
                                    }
                                    $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                                    if(count($otherTrack) > 0)
                                    {
                                        foreach ($otherTrack as $key => $value) {
                                            $this->betOrdersModel->backOne($otherTrack[$key]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if(in_array($order['bo_content'], $betRes) === true) // 中奖
                        {
                            $isWin = 1;
                            $earnMoney = $order['bo_money'] * $order['bo_odds'];
                            if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                            {
                                $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                                if($trackCode == -1)
                                {
                                    $trackCode = $order['bo_id'];
                                }
                                $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                                if(count($otherTrack) > 0)
                                {
                                    foreach ($otherTrack as $key => $value) {
                                        $this->betOrdersModel->backOne($otherTrack[$key]);
                                    }
                                }
                            }
                        }
                    }
                    break;
            case 14:
            case 15:
            case 16:
                $res = $this->winOfkuai3($betRes, $order['bo_content']);
                if($res == 1) // 中奖
                {
                    $isWin = 1;
                    $earnMoney = $order['bo_money'] * $order['bo_odds'];
                    if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                    {
                        $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                        if($trackCode == -1)
                        {
                            $trackCode = $order['bo_id'];
                        }
                        $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                        if(count($otherTrack) > 0)
                        {
                            foreach ($otherTrack as $key => $value) {
                                $this->betOrdersModel->backOne($otherTrack[$key]);
                            }
                        }
                    }
                }
                break;
            case 17:
            case 18:
            case 19:
            case 20:
                $res = $this->winOf11xuan5($betRes, $order['bo_content']);
                if($res == 1) // 中奖
                {
                    $isWin = 1;
                    $earnMoney = $order['bo_money'] * $order['bo_odds'];
                    if($order['bo_is_track'] == 3 && $order['bo_track_stop'] == 1)
                    {
                        $trackCode = $this->betOrdersModel->getOrderTrackInfo($order['bo_id'])['bo_track_code'];
                        if($trackCode == -1)
                        {
                            $trackCode = $order['bo_id'];
                        }
                        $otherTrack = $this->betOrdersModel->getTrackInfo($trackCode, $order['u_id'], $order['bo_issue']);
                        if(count($otherTrack) > 0)
                        {
                            foreach ($otherTrack as $key => $value) {
                                $this->betOrdersModel->backOne($otherTrack[$key]);
                            }
                        }
                    }
                }
                break;
            }

        // // 校验该订单中奖以及金额和回水等
        // if(in_array($order['bo_content'], $betRes) === true) // 中奖
        // {
        //     $isWin = 1;
        //     $earnMoney = $order['bo_money'] * $order['bo_odds'];
        // }



            if($order['bo_back'] > 0)
            {
                $backMoney = $order['bo_money'] * $order['bo_back'];
            }
            if($isWin==2)
            {
                $this->betOrdersModel->backOrderById($order['bo_id'],$order['u_id']);
                return true;
            }
            else
            {
                $this->betOrdersModel->openOrders($order, $isWin, $earnMoney, $backMoney);

                // 更新该彩种的当日总盈亏
                $this->di['redis']->incrByFloat('earn:' . $betId . ':' . date('Y-m-d'), $isWin == 1 ? -($earnMoney - $order['bo_money'] + $backMoney) : ($order['bo_money'] - $backMoney));
                //更新今日平台盈亏
                $this->di['redis']->incrByFloat('finance:' . date('Y-m-d').'planTotal', $isWin == 1 ? -($earnMoney - $order['bo_money'] + $backMoney) : ($order['bo_money'] - $backMoney));
                // 发送到计算代理提成的队列
                $order['bo_bonus'] = $earnMoney;
                $order['bo_back_money'] = $backMoney;
                $this->di['redis']->rpush('agent:earn', $order);
                $this->di['redis']->rpush('agent:report', $order);
                return true;
            }

    }

    function winOf11xuan5($openCode, $content)
    {
            $type = strchr($content, '-', true);
            $con = explode(',', substr($content, 3));
            $count = count($con);
            $num=$count;
            switch ($type) {
                case 26:
                    if ($con[0] == $openCode[0])
                        return 1;
                    else
                        return 0;
                case 35:
                case 37:
                $con = explode('|', substr($content, 3));
                $count = count($con);
                for($i = 0; $i < $count; $i++) {
                    if ($con[$i] != $openCode[$i])
                        return 0;
                }
                    return 1;
                    break;
                case 34:
                case 36:
                    $n = $type==34 ? 2 : 3;
                    for ($i = 0; $i < $n; $i++)
                        $code[$i] = $openCode[$i];
                    foreach ($con as $val) {
                        if (!in_array($val, $code))
                            return 0;
                    }
                    return 1;
                    break;
                default:
                    if ($count < 6) {
                        for ($i=0; $i < $count; $i++)
                        {
                            if(!in_array($con[$i], $openCode))
                                return 0;
                        }
                    } else {
                        for ($i=0; $i < $count; $i++)
                        {
                            if(!in_array($con[$i], $openCode)) {
                                $num = $num - 1;
                                if ($num < 5)
                                    return 0;
                            }
                        }
                    }
                    return 1;
                    break;
            }
    }
    public function winOfkuai3($openCode, $content)
    {
        $type = strchr($content, '-', true);
        $con = explode(',', substr($content, 3));
        foreach ($openCode as $key => $value) {
            if ($type == $key) {
                if ($value == '*')
                    return 1;
                else {
                    switch($key) {
                        case 41:
                        case 45:
                            $i = 0;
                            foreach ($con as $val) {
                                if(!in_array($val, $value))
                                    $i++;
                            }
                            if ($i > 0)
                                return 0;
                            else
                                return 1;
                        break;
                        default:
                        if ($value == $con[0])
                                return 1;
                            else
                                return 0;
                        break;
                    }
                }
            }
        }
    }

    public function setExceptionToOrder($boId)
    {
        return $this->betOrdersModel->setExceptionStatusByBoid($boId, 1);
    }

    /**
     * 根据时间段，uid，订单号，采种获取所有的订单信息  $type空位常规订单，不空为追号订单
     * @param type $uId
     * @param type $lotteryType
     * @param type $serialNum
     * @param type $startDay
     * @param type $endDay
     * @return string
     */
    public function getOrderInfo($uId,$lotteryType,$serialNum,$startDay,$endDay,$type)
    {
        $info = $this->betOrdersModel->getOrderInfo($uId,$lotteryType,$serialNum,$startDay,$endDay,$type);
        for($i = 0;$i < count($info); $i++)
        {
            $info[$i]['bo_created_time'] = date('Y-m-d H:i:s',$info[$i]['bo_created_time']);
            if($info[$i]['bo_draw_result'] == 1)
            {
                $info[$i]['bo_draw_result'] = '中奖';
            }
            else if($info[$i]['bo_draw_result'] == 3)
            {
                $info[$i]['bo_draw_result'] = '未中奖';
            }
            else
            {
                $info[$i]['bo_draw_result'] = '未开奖';
            }

            if($info[$i]['bo_status'] == 1)
            {
                $info[$i]['bo_statusInfo'] = '待开奖';
            }
            else if($info[$i]['bo_status'] == 3)
            {
                $info[$i]['bo_statusInfo'] = '已开奖';
            }
            else
            {
                $info[$i]['bo_statusInfo'] = '已撤销';
            }
        }
        return $info;
    }

    /**
     * 根据订单表主键id查询订单信息
     * @param type $boId
     * @return string
     */
    public function getDetailById($boId)
    {
        $config = $this->di['config'];
        $ruleType = $config['game']['rule_type'];
        $baseType = $config['game']['rule_base_type'];
        $info = $this->betOrdersModel->getDetailById($boId);
        for( $i = 0; $i < count($info); $i++)
        {
            $info[$i]['bo_created_time'] = date('Y-m-d',$info[$i]['bo_created_time']);
            $info[$i]['br_type'] = $ruleType[$info[$i]['br_type']];
            $info[$i]['br_base_type'] = $baseType[$info[$i]['br_base_type']];

            if($info[$i]['bo_status']==5)
            {
                $info[$i]['bo_draw_result'] = '已撤单';
            }
            else
            {
                if($info[$i]['bo_draw_result'] == 1)
                {
                    $info[$i]['bo_draw_result'] = '中奖';
                }
                else if($info[$i]['bo_draw_result'] == 3)
                {
                    $info[$i]['bo_draw_result'] = '未中奖';
                }
                else
                {
                    $info[$i]['bo_draw_result'] = '未开奖';
                }
            }

        }

        return $info;
    }

    /**
     * 撤销订单
     * @param  [type] $boId [description]
     * @return [type]       [description]
     */
    public function cancelOrderById($boId, $uId)
    {
        return $this->betOrdersModel->cancelOrderById($boId, $uId);
    }

    public function backOrderById($boId, $uId)
    {
        return $this->betOrdersModel->backOrderById($boId, $uId);
    }

    /**
     * 获取未开奖的或者已撤销的订单
     * @param type $uId
     * @param type $lotteryType
     * @param type $serialNum
     * @param type $startDay
     * @param type $endDay
     */
    public function getCanCancle($uId,$lotteryType,$serialNum,$startDay,$endDay)
    {
        $info = $this->betOrdersModel->getCancelInfo($uId,$lotteryType,$serialNum,$startDay,$endDay);
        for($i = 0;$i < count($info); $i++)
        {
            $info[$i]['bo_created_time'] = date('Y-m-d H:i:s',$info[$i]['bo_created_time']);
            if($info[$i]['bo_draw_result'] == 1)
            {
                $info[$i]['bo_draw_result'] = '中奖';
            }
            else if($info[$i]['bo_draw_result'] == 3)
            {
                $info[$i]['bo_draw_result'] = '未中奖';
            }
            else
            {
                $info[$i]['bo_draw_result'] = '未开奖';
            }
        }
        return $info;
    }

    public function getMyList($uId, $betId, $isTrack)
    {
        return $this->betOrdersModel->getMyList($uId, $betId, $isTrack);
    }

    public function getBetMoney($ids, $betId, $qishu)
    {
        return $this->betOrdersModel->getBetMoney($ids, $betId, $qishu);
    }

    public function getOrder($uId,$betId,$issue)
    {
        return $this->betOrdersModel->getOrder($uId,$betId,$issue);
    }

    public function getOpen($uId,$betId,$issue)
    {
        return $this->betOrdersModel->getOpen($uId,$betId,$issue);
    }

    public function getOrderDetail($boid, $fields = '*')
    {
        return $this->betOrdersModel->getOrderDetail($boid, $fields);
    }

    public function combination($a, $m) {
        $r = array();
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }
        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = $this->combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }
        return $r;
    }

    /**
     * API接口按日期统计个人报表
     * @param  [type] $uId         [description]
     * @param  [type] $lotteryType [description]
     * @param  [type] $startDay    [description]
     * @param  [type] $endDay      [description]
     * @return [type]              [description]
     */
    public function getOrderByDate($uId, $lotteryType, $startDay, $endDay)
    {
        return $this->betOrdersModel->getOrderByDate($uId, $lotteryType, $startDay, $endDay);
    }

    /**
     * API接口获取用户订单列表
     * @param  [type] $uId      [description]
     * @param  [type] $betId    [description]
     * @param  [type] $type     [description]
     * @param  [type] $issue    [description]
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @param  [type] $page     [description]
     * @param  [type] $nums     [description]
     * @return [type]           [description]
     */
    public function getOrderLists($uId, $betId, $type, $issue, $startDay, $endDay, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $fields = 'ord.bo_id, ord.bo_money, ord.bo_played_name, ord.bet_id, ord.bo_content, ord.bo_issue, ord.bo_created_time, ord.bo_draw_result, bet.bet_name';
        return $this->betOrdersModel->getOrderLists($uId, $betId, $type, $issue,$startDay, $endDay, $start, $nums, $fields);
    }

    /**
     * API接口按天统计每日订单
     * @param  [type] $uId      [description]
     * @param  [type] $betId    [description]
     * @param  [type] $startTime [description]
     * @param  [type] $endTime   [description]
     * @return [type]           [description]
     */
    public function getDayOrder($uId, $betId, $startTime, $endTime)
    {
        return $this->betOrdersModel->getDayOrder($uId, $betId, $startTime, $endTime);
    }

    public function getAllOrder($issue)
    {
        
    }
}
