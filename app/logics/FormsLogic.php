<?php
use Phalcon\Security;

class FormsLogic extends LogicBase
{

    public function __construct()
    {
        $this->FormsModel = new FormsModel;
    }

    public function getFormsList($page, $limit, $condition)
    {
        $res['total'] = $this->FormsModel->getNums($condition);
        $res['list'] = $this->FormsModel->getLimit($page, $limit, $condition);
        return $res;
    }

    public function getFromDate($bId)
    {
        return $this->FormsModel->getFromDate($bId);
    }

    public function getAllBetTotalList($page, $limit, $condition, $issue)
    {

        $res['list'] = $this->FormsModel->getAllBetTotalLimit($page, $limit, $condition, $issue);
        return $res;
    }

     public function getDayBetTotalList($page, $limit, $condition)
    {
        $res['total'] = $this->FormsModel->getDayBetTotalNums($condition);
        $res['list'] = $this->FormsModel->getDayBetTotalLimit($page, $limit, $condition);
        return $res;
    }

    public function getPerBetTotalList($page, $limit, $condition)
    {
        $res['total'] = $this->FormsModel->getPerBetTotalNums($condition);
        $res['list'] = $this->FormsModel->getPerBetTotalLimit($page, $limit, $condition);
        return $res;
    }

    public function getBetTotalList($page, $limit, $condition)
    {
        $res['total'] = $this->FormsModel->getBetTotalNums($condition);
        $res['list'] = $this->FormsModel->getBetTotalLimit($page, $limit, $condition);
        return $res;
    }

    public function count($periods)
    {
        return $this->FormsModel->count($periods);
    }

    public function excel($conditions,$v,$uid,$issue)
    {
        return $this->FormsModel->excel($conditions,$v,$uid,$issue);
    }
    
    public function getOrdersInfo($page, $limit, $uid, $conditions,$issue)
    {
        $res['total'] = $this->FormsModel->getOrdersNums($uid, $conditions,$issue);
        $res['list'] = $this->FormsModel->getOrdersInfo($page, $limit, $uid, $conditions,$issue);
        return $res;
    }
    
    public function getOrdersInfoTotal($uid, $conditions,$issue)
    {
        return $this->FormsModel->getOrdersInfoTotal($uid, $conditions,$issue);
    }

    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay,$issue)
    {
        return $this->FormsModel->getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay,$issue);
    }

    public function getNewTimeTeamInfo($uid,$lotteryType,$issue,$start,$limit)
    {
        return $this->FormsModel->getNewTimeTeamInfo($uid,$lotteryType,$issue,$start,$limit);
    }
    
    public function getNewTimeTeamInfocount($uid,$lotteryType,$issue)
    {
        return $this->FormsModel->getNewTimeTeamInfocount($uid,$lotteryType,$issue);
    }

    public function getAgentTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        return $this->FormsModel->getAgentTeamInfo($ids,$lotteryType,$startDay,$endDay);
    }

    public function getNewAgentTeamInfo($lotteryType,$uid,$startDay,$endDay,$start,$limit)
    {
        return $this->FormsModel->getNewAgentTeamInfo($lotteryType,$uid,$startDay,$endDay,$start,$limit);
    }

    public function getNewAgentTeamInfocount($lotteryType,$uid,$startDay,$endDay)
    {
        return $this->FormsModel->getNewAgentTeamInfocount($lotteryType,$uid,$startDay,$endDay);
    }
}