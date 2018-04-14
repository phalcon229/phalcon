<?php
class TeamLogic extends LogicBase
{

    public function __construct()
    {
        $this->teamModel = new TeamModel;
        $this->agentModel=new AgentModel;
        $this->recommendModel=new RecommendModel();
        $this->betOrdersModel=new BetOrdersModel();
        $this->userModel=new UsersModel();
        $this->userAgentModel=new UserAgentModel();
    }

    /**
     * 根据uid获取团队日表信息
     * @param type $uId
     * @return type
     */
    public function getTeamInfoByUserId($uId)
    {
        return $this->teamModel->getInfoByUserId($uId);
    }

    /**
     * 根据彩种id，uids，时间段获取团队日表信息
     * @param type $ids
     * @param type $lotteryType
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        return $this->teamModel->getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay);
    }

    /**
     * 根据uids，时间段获取团队日表信息
     * @param type $ids
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getTeamTime($ids,$startDay,$endDay)
    {
        return $this->teamModel->getTeamTime($ids,$startDay,$endDay);
    }

        /**
     * 获取今日统计
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getTodayCount($uId)
    {
        $startTime = strtotime(date("Y-m-d"));
        $endTime = $startTime + 86399;
        return $this->teamModel->getTeamCount($uId, $startTime, $endTime);
    }

    /**
     * 获取昨日统计
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getYestodayCount($uId)
    {
        $startTime = strtotime(date("Y-m-d")) - 86400;
        $endTime = $startTime + 86399;
        return $this->teamModel->getTeamCount($uId, $startTime, $endTime);
    }

    /**
     * 根据用户id和彩种获取对应日期区间的统计数据
     * @param  [type]  $betId    [description]
     * @param  boolean $startDay [description]
     * @param  boolean $endDay   [description]
     * @return [type]            [description]
     */
    public function getTeamStats($uaUid = 0, $betId = false, $startDay = false, $endDay = false)
    {
        $res['recharge'] = $res['withdraw'] = $res['earn_money'] = $res['bet_money'] = $res['reback_money'] = $res['pay_bonuses'] = 0;
        $stats = $this->teamModel->getTeamStats($uaUid, $betId, $startDay, $endDay);
        foreach ($stats as $value) {
            $res['recharge'] += $value['tsd_recharge'];
            $res['withdraw'] += $value['tsd_withdraw'];
            $res['earn_money'] += $value['tsd_earn_money'];
            $res['bet_money'] += $value['tsd_bet_money'];
            $res['reback_money'] += $value['tsd_reback_money'];
            $res['pay_bonuses'] += $value['tsd_pay_bonuses'];

        }

        return $res;
    }

    /**
     * 获取团队数据
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @return [type]           [description]
     */
    public function getAllTeamStat($startDay, $endDay)
    {
        return $this->teamModel->getAllTeamStat($startDay, $endDay);
    }

    /**
     * 获取指定日期用户的团队数据列表
     * @param  [type] $name     [description]
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @param  [type] $page     [description]
     * @param  [type] $perPage  [description]
     * @return [type]           [description]
     */
    public function getTeamLists($name, $startDay, $endDay, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        $res['total'] = count($this->teamModel->getTeamTotal($name, $startDay, $endDay));
        $res['list'] = $this->teamModel->getTeamLists($name, $startDay, $endDay, $start, $perPage);

        return $res;
    }
    
    public function getTeamListsByUid($uid, $startDay, $endDay, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        $res['total'] = count($this->teamModel->getTeamTotalByUid($uid, $startDay, $endDay));
        $res['list'] = $this->teamModel->getTeamListsByUid($uid, $startDay, $endDay, $start, $perPage);

        return $res;
    }
    
}
