<?php use Phalcon\Security;
class AgentReportLogic extends LogicBase
{
    public function __construct()
    {
        $this->model = new AgentReportModel;

    }

    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        $res = $this->model->getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay);
        foreach($res as $key=>$value)
        {
            $res[$key]['ar_team_recharge_money'] = sprintf("%.1f", $res[$key]['ar_team_recharge_money']);
            $res[$key]['ar_team_bet_money'] = sprintf("%.1f", $res[$key]['ar_team_bet_money']);
            $res[$key]['ar_team_earn_money'] = sprintf("%.1f", $res[$key]['ar_team_earn_money']);
            $res[$key]['ar_team_back_money'] = sprintf("%.1f", $res[$key]['ar_team_back_money']);
            $res[$key]['ar_team_reback_money'] = sprintf("%.1f", $res[$key]['ar_team_reback_money']);
            $res[$key]['ar_my_back_money'] = sprintf("%.1f", $res[$key]['ar_my_back_money']);
            $res[$key]['ar_my_bet_money'] = sprintf("%.1f", $res[$key]['ar_my_bet_money']);
            $res[$key]['ar_my_earn_money'] = sprintf("%.1f", $res[$key]['ar_my_earn_money']);
            $res[$key]['ar_team_withdraw_money'] = sprintf("%.1f", $res[$key]['ar_team_withdraw_money']);
        }
        return $res;
    }

    public function getTimeTeamInfofront($ids,$lotteryType,$startDay,$endDay)
    {
        $res = $this->model->getTimeTeamInfofront($ids,$lotteryType,$startDay,$endDay);
        foreach($res as $key=>$value)
        {
            $res[$key]['ar_team_recharge_money'] = sprintf("%.1f", $res[$key]['ar_team_recharge_money']);
            $res[$key]['ar_team_bet_money'] = sprintf("%.1f", $res[$key]['ar_team_bet_money']);
            $res[$key]['ar_team_earn_money'] = sprintf("%.1f", $res[$key]['ar_team_earn_money']);
            $res[$key]['ar_team_back_money'] = sprintf("%.1f", $res[$key]['ar_team_back_money']);
            $res[$key]['ar_team_reback_money'] = sprintf("%.1f", $res[$key]['ar_team_reback_money']);
            $res[$key]['ar_my_back_money'] = sprintf("%.1f", $res[$key]['ar_my_back_money']);
            $res[$key]['ar_my_bet_money'] = sprintf("%.1f", $res[$key]['ar_my_bet_money']);
            $res[$key]['ar_my_earn_money'] = sprintf("%.1f", $res[$key]['ar_my_earn_money']);
            $res[$key]['ar_team_withdraw_money'] = sprintf("%.1f", $res[$key]['ar_team_withdraw_money']);
            $res[$key]['ar_up_back_money'] = sprintf("%.1f", $res[$key]['ar_up_back_money']);
        }
        return $res;
    }

    public function getTeamTime($ids,$startDay,$endDay)
    {
        $res = $this->model->getTeamTime($ids,$startDay,$endDay);
        foreach($res as $key=>$value)
        {
            $res[$key]['ar_team_recharge_money'] = sprintf("%.1f", $res[$key]['ar_team_recharge_money']);
            $res[$key]['ar_team_bet_money'] = sprintf("%.1f", $res[$key]['ar_team_bet_money']);
            $res[$key]['ar_team_earn_money'] = sprintf("%.1f", $res[$key]['ar_team_earn_money']);
            $res[$key]['ar_team_back_money'] = sprintf("%.1f", $res[$key]['ar_team_back_money']);
            $res[$key]['ar_my_back_money'] = sprintf("%.1f", $res[$key]['ar_my_back_money']);
            $res[$key]['ar_team_reback_money'] = sprintf("%.1f", $res[$key]['ar_team_reback_money']);
        }
        return $res;
    }
    public function getInfoByTime($startDay,$endDay)
    {
        return $this->model->getInfoByTime($startDay,$endDay);
    }
    public function getTeamListsByUid($uid, $startDay, $endDay, $name)
    {
        return $this->model->getTeamListsByUid($uid, $startDay, $endDay, $name);
    }

    public function getAgentReportLists($data, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $res['total'] = $this->model->agentReportTotal($data);
        $res['lists'] = $this->model->agentReportLists($data, $start, $nums);

        return $res;
    }

    public function getAgentReporttzLists($data, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $res['total'] = $this->model->agentReporttzTotal($data);
        $res['lists'] = $this->model->agentReporttzLists($data, $start, $nums);

        return $res;
    }
}