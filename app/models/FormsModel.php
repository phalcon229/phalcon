<?php

class FormsModel extends ModelBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getNums($conditions)
    {
        $date= [];
        if ($conditions['bet_id'] != 0) {
            $date[] = $conditions['bet_id'];
            $where = ' AND bet_id = ?';
        } else
            $where = '';

        if($conditions['uid'] !='') {
                $where .= ' AND u_id = '.$conditions['uid'];
        }
        if(!empty($conditions['radio']) && ( !empty($conditions['startTime']) || !empty($conditions['periods']) || !empty($conditions['sn']) ) )
        {
            foreach($conditions as $key => $condition)
            {
                if($key == 'periods' && $condition != '')
                    $where .= " AND bo_issue = ".$condition;
                if ($key == 'startTime' && $condition !='')
                    $where .=" AND bo_created_time > " .$condition;
                if ($key == 'endTime' && $condition !='')
                    $where .=" AND bo_created_time < " .$condition;
                if ($key == 'sn' && $condition !='')
                    $where .= ' AND bo_sn =' . $condition;
            }
        } else if($conditions['nick'] !='') {
                $name = $conditions['nick'];
                $where .= ' AND u_name LIKE "%'. $name .'%"';
        } else {
            $where .= " AND bo_created_time between ? AND ?";
            array_push($date, strtotime(date('Y-m-d')));
            array_push($date, $_SERVER['REQUEST_TIME']);
        }

        $this->query('SELECT count(bo_id) as total FROM le_bet_orders WHERE 1=1  '. $where, $date);
        return $this->getOne();
    }

    public function getLimit($page, $limit, $conditions)
    {
        $date= [];
        $start = ($page - 1) * $limit;
        if ($conditions['bet_id'] != 0) {
            $date[] = $conditions['bet_id'];
            $where = ' AND bet_id = ?';
        } else
            $where = '';
        if ($conditions['nick'] !='') {
            $name = $conditions['nick'];
            $where .= ' AND u_name LIKE "%'. $name .'%"';
        }
        if($conditions['uid'] !='') {
                $where .= ' AND u_id = '.$conditions['uid'];
        }
        if(!empty($conditions['radio']) && ( !empty($conditions['startTime']) || !empty($conditions['periods']) || !empty($conditions['sn']))  )
        {
            foreach($conditions as $key => $condition)
            {
                if($key == 'periods' && $condition != '')
                    $where .= " AND bo_issue = ".$condition;
                if ($key == 'startTime' && $condition !='')
                    $where .=" AND bo_created_time > " .$condition;
                if ($key == 'endTime' && $condition !='')
                    $where .=" AND bo_created_time < " .$condition;
                if ($key == 'sn' && $condition !='')
                    $where .= ' AND bo_sn =' . $condition;
            }
        } else if($conditions['nick'] !='') {
                $name = $conditions['nick'];
                $where .= 'AND u_name LIKE "%'. $name .'%"';
        }
         else {
            $where .= " AND bo_created_time between ? AND ?";
            array_push($date, strtotime(date('Y-m-d')));
            array_push($date, $_SERVER['REQUEST_TIME']);
        }

        $this->query('SELECT bet_id, bo_first_odds, bo_status, bo_u_name, bo_rs_tax, bo_played_name, bo_content, u_id, bo_created_time, u_name, br_id, bo_sn, bo_draw_result, bo_money, bo_issue, bo_back, bo_odds, bo_bonus FROM le_bet_orders WHERE 1 = 1 '. $where .' ORDER BY bo_id DESC LIMIT '.$start .' , '. $limit, $date);
        return $this->getAll();
    }

    public function getFromDate($bId)
    {
        $start = strtotime(date('Y-m-d'));
        $end = $_SERVER['REQUEST_TIME'];
        $this->query('SELECT bet_id, bres_periods FROM le_bets_result WHERE bres_plat_isopen = 1 AND bet_id = ? AND bres_plat_open_time between ? AND ? ORDER BY bres_id DESC', [$bId, $start, $end]);
        return $this->getAll();
    }

    public function getAllBetTotalLimit($page, $limit, $conditions, $issue)
    {
        if(!empty($issue)&&$conditions['bet_id']!==0)
        {
            $this->query('SELECT bet_id, bt_date, bt_periods, sum(bt_all_in) as bt_all_in, sum(bt_all_out) as bt_all_out, sum(bt_all_orders) as bt_all_orders, sum(bt_all_reback) as bt_all_reback, sum(bt_all_earn) as bt_all_earn FROM le_bets_total'
                    . ' WHERE bet_id = ? and bt_periods = ?',[$conditions['bet_id'], $issue]);
            $info = $this->getAll();
        }
        else
        {
            $start = ($page - 1) * $limit;
            $data= [];
            if ($conditions['bet_id'] != 0) {
                $data[] = $conditions['bet_id'];
                $where = ' AND a.bet_id = ?';
            } else
                $where = '';

            $where .= " AND a.bt_date >= " .$conditions['startTime']. " AND a.bt_date <=". $conditions['endTime'];
            $this->query('SELECT  bet_id,sum(bt_all_in) as bt_all_in, sum(bt_all_out) as bt_all_out, sum(bt_all_orders) as bt_all_orders, sum(bt_all_reback) as bt_all_reback, sum(bt_all_earn) as bt_all_earn FROM le_bets_total as a WHERE a.bt_type = 1  '. $where .' GROUP BY bet_id LIMIT '.$start .' , '. $limit, $data);
            $info = $this->getAll();
        }
        return $info;
    }

    public function  getDayBetTotalNums($conditions)
    {
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = 'AND bet_id = ?';
        } else
            $where = '';
        $where .= " AND bt_date >= " .$conditions['startTime']. " AND  bt_date <=". $conditions['endTime'];

        $this->query('SELECT count(bt_id) as total FROM le_bets_total WHERE bt_type = 1 '. $where, $data);
        return $this->getOne();
    }

    public function getDayBetTotalLimit($page, $limit, $conditions)
    {
        $start = ($page - 1) * $limit;
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = ' AND a.bet_id = ?';
        } else
            $where = '';

        $where .= " AND a.bt_date >= " .$conditions['startTime']. " AND a.bt_date <=". $conditions['endTime'];
        $this->query('SELECT a.* FROM le_bets_total as a WHERE a.bt_type = 1  '. $where .' LIMIT '.$start .' , '. $limit, $data);
        $info = $this->getAll();

        return $info;
    }
     public function  getPerBetTotalNums($conditions)
    {
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = 'AND bet_id = ?';
        } else
            $where = '';
        $where .= " AND bt_date = " .$conditions['date'];

        $this->query('SELECT count(bet_id) as total FROM le_bets_total WHERE bt_type = 3 '. $where , $data);
        return $this->getOne();
    }

    public function getPerBetTotalLimit($page, $limit, $conditions)
    {
        $start = ($page - 1) * $limit;
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = ' AND a.bet_id = ?';
        } else
            $where = '';

        $where .= " AND a.bt_date = " .$conditions['date'];
        $this->query('SELECT a.* FROM le_bets_total as a WHERE a.bt_type = 3'. $where .' LIMIT '.$start .' , '. $limit, $data);
        $info = $this->getAll();

        return $info;
    }

     public function  getBetTotalNums($conditions)
    {
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = 'AND a.bet_id = ?';
        } else
            $where = '';
        $where .= " AND a.bt_periods = " .$conditions['date'];

        $this->query('SELECT count(b.bo_id) as total FROM le_bets_total as a LEFT JOIN le_bet_orders as b ON a.bt_periods =  b.bo_issue WHERE a.bt_type = 3 AND b.bo_status = 3 '. $where, $data);
        return $this->getOne();
    }

    public function getBetTotalLimit($page, $limit, $conditions)
    {
        $start = ($page - 1) * $limit;
        $data= [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = ' AND a.bet_id = ?';
        } else
            $where = '';

        $where .= " AND a.bt_periods = " .$conditions['date'];
        $this->query('SELECT a.bet_id,a.bt_periods, b.bo_money as bt_all_in, b.bo_created_time as bt_date, b.bo_bonus as bt_all_out,  b.bo_back_money as bt_all_reback FROM le_bets_total as a LEFT JOIN le_bet_orders as b ON a.bt_periods =  b.bo_issue WHERE a.bt_type = 3 AND b.bo_status = 3 '. $where .' LIMIT '.$start .' , '. $limit, $data);
        $info = $this->getAll();

        return $info;
    }

    public function excel($conditions, $v, $uid,$issue)
    {
        $data = [];
        if ($conditions['bet_id'] != 0) {
            $data[] = $conditions['bet_id'];
            $where = ' AND a.bet_id = ?';
        } else
            $where = '';
        switch ($v) {
            case 1:
                if(!empty($issue))
                {
                    $where .= " AND a.bt_periods = ".$issue;
                    $this->query('SELECT  bet_id,sum(bt_all_in) as bt_all_in, sum(bt_all_out) as bt_all_out, sum(bt_all_orders) as bt_all_orders, sum(bt_all_reback) as bt_all_reback, sum(bt_all_earn) as bt_all_earn FROM le_bets_total as a WHERE a.bt_type = 3  '. $where .' GROUP BY bet_id ', $data);
                }
                else
                {
                    $where .= " AND a.bt_date >= " .$conditions['startTime']. " AND a.bt_date <=". $conditions['endTime'];
                    $this->query('SELECT  bet_id,sum(bt_all_in) as bt_all_in, sum(bt_all_out) as bt_all_out, sum(bt_all_orders) as bt_all_orders, sum(bt_all_reback) as bt_all_reback, sum(bt_all_earn) as bt_all_earn FROM le_bets_total as a WHERE a.bt_type = 1  '. $where .' GROUP BY bet_id ', $data);
                }
                break;

            case 2:
                if(empty($issue))
                {
                    $this->query('SELECT a.u_name, a.ua_type, a.ua_good_user_nums, if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0) tsd_bet_money, if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0) tsd_pay_bonuses, '
                            . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0) tsd_reback_money, if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0) tsd_earn_money FROM le_user_agent as a INNER JOIN le_team_stats_day as b on a.u_id = b.u_id and a.ua_id = ? and b.bet_id = ? and (b.tsd_date > ? and b.tsd_date < ?)  ',[[$uid], $conditions['bet_id'],$conditions['startTime'],$conditions['endTime']]);
                }
                else
                {
                    $this->query('SELECT a.u_name, a.ua_type, a.ua_good_user_nums, if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0) tsd_bet_money, if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0) tsd_pay_bonuses, '
                            . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0) tsd_reback_money, if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0) tsd_earn_money FROM le_user_agent as a INNER JOIN le_team_stats_day as b on a.u_id = b.u_id and a.ua_id = ? and b.bet_id = ? and b.nper = ?  ',[[$uid], $conditions['bet_id'],$issue]);

                }
                break;
            case 3://没有用到
                 $where .= " AND a.bt_date = " .$conditions['date'];
                $this->query('SELECT a.* FROM le_bets_total as a WHERE a.bt_type = 3 '. $where , $data);
                break;
            case 4:
                if(empty($issue))
                {
                    $this->query('SELECT * FROM le_bet_orders where u_id = ? and bet_id = ? and (bo_created_time > ? and bo_created_time < ?) ', [$uid, $conditions['bet_id'], $conditions['startTime'], $conditions['endTime']]);
                }
                else
                {
                    $this->query('SELECT * FROM le_bet_orders where u_id = ? and bet_id = ? and bo_issue = ? ', [$uid, $conditions['bet_id'], $issue]);

                }
                break;
        }

        return $this->getAll();
    }

    public function getOrdersInfo($page, $limit, $uid, $conditions,$issue)
    {
        $start = ($page - 1) * $limit;
        if(!empty($issue)){
            if($conditions['bet_id']==0){
                $this->query('SELECT * FROM le_bet_orders where u_id= '.$uid.' and bo_issue = ? limit ?,?',[$issue,$start,$limit]);
            }
            else
            {
                $this->query('SELECT * FROM le_bet_orders where u_id= '.$uid.' and bet_id = ? and bo_issue = ? limit ?,?',[$conditions['bet_id'],$issue,$start,$limit]);
            }
        }
        else
        {
            if($conditions['bet_id']==0){
                $this->query('SELECT * FROM le_bet_orders where u_id= '.$uid.' and (bo_created_time > ? and bo_created_time < ?) limit ?,?',[$conditions['start'],$conditions['end'],$start,$limit]);
            }
            else
            {
                $this->query('SELECT * FROM le_bet_orders where u_id= '.$uid.' and bet_id = ? and (bo_created_time > ? and bo_created_time < ?) limit ?,?',[$conditions['bet_id'], $conditions['start'],$conditions['end'],$start,$limit]);
            }
        }
        $info = $this->getAll();
        return $info;
    }

    public function getOrdersInfoTotal($uid, $conditions,$issue)
    {
        if(!empty($issue))
        {
            $this->query('SELECT sum(bo_money) as bo_money,sum(bo_bonus) as bo_bonus,sum(bo_back_money) as bo_back_money FROM le_bet_orders where u_id= '.$uid.' and bet_id = ? and bo_issue = ? and (bo_created_time > ? and bo_created_time < ? and bo_status <> 5 ) ',[$conditions['bet_id'], $issue, $conditions['start'],$conditions['end']]);
        }
        else
        {
           $this->query('SELECT sum(bo_money) as bo_money,sum(bo_bonus) as bo_bonus,sum(bo_back_money) as bo_back_money FROM le_bet_orders where u_id= '.$uid.' and bet_id = ? and (bo_created_time > ? and bo_created_time < ? and bo_status <> 5)',[$conditions['bet_id'], $conditions['start'],$conditions['end']]);
        }
        $info = $this->getRow();

        return $info;
    }

    public function  getOrdersNums($uid, $conditions,$issue)
    {
        if(empty($issue))
        {
            if($conditions['bet_id']==0){
                $this->query('SELECT count(bo_id) as total FROM le_bet_orders where u_id= ? and (bo_created_time > ? and bo_created_time < ?)',[$uid, $conditions['start'],$conditions['end']]);
            }
            else
            {
                $this->query('SELECT count(bo_id) as total FROM le_bet_orders where u_id= ? and bet_id = ? and (bo_created_time > ? and bo_created_time < ?)',[$uid, $conditions['bet_id'], $conditions['start'],$conditions['end']]);
            }
        }
        else
        {
            if($conditions['bet_id']==0){
                $this->query('SELECT count(bo_id) as total FROM le_bet_orders where u_id= ? and bo_issue = ?',[$uid, $issue]);
            }
            else
            {
                $this->query('SELECT count(bo_id) as total FROM le_bet_orders where u_id= ? and bet_id = ? and bo_issue = ?',[$uid, $conditions['bet_id'], $issue]);
            }
        }
        return $this->getOne();
    }
    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay,$issue)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT u_name, u_type, if(SUM(pfr_team_bet_money)<>\'\',SUM(pfr_team_bet_money),0) pfr_team_bet_money,'
                        . 'if(SUM(pfr_team_earn_money)<>\'\',SUM(pfr_team_earn_money),0.0000) pfr_team_earn_money,if(SUM(pfr_team_back_money)<>\'\',SUM(pfr_team_back_money),0.0000) pfr_team_back_money ,'
                        . 'if(SUM(pfr_team_reback_money)<>\'\',SUM(pfr_team_reback_money),0.0000) pfr_team_reback_money ,if(SUM(pfr_team_plat_money)<>\'\',SUM(pfr_team_plat_money),0.0000) pfr_team_plat_money '
                        . ',if(SUM(pfr_my_bet_money)<>\'\',SUM(pfr_my_bet_money),0.0000) pfr_my_bet_money ,if(SUM(pfr_my_earn_money)<>\'\',SUM(pfr_my_earn_money),0.0000) pfr_my_earn_money,'
                        . 'if(SUM(pfr_my_reback_money)<>\'\',SUM(pfr_my_reback_money),0.0000) pfr_my_reback_money,if(SUM(pfr_my_back_money)<>\'\',SUM(pfr_my_back_money),0.0000) pfr_my_back_money FROM le_plat_finance_report WHERE '
                        . ' ar_parent_uid = ? and bet_periods = ?', [$id,$issue]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
        else
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT if(SUM(pfr_team_bet_money)<>\'\',SUM(pfr_team_bet_money),0) pfr_team_bet_money,'
                        . 'if(SUM(pfr_team_earn_money)<>\'\',SUM(pfr_team_earn_money),0.0000) pfr_team_earn_money,if(SUM(pfr_team_back_money)<>\'\',SUM(pfr_team_back_money),0.0000) pfr_team_back_money ,'
                        . 'if(SUM(pfr_team_reback_money)<>\'\',SUM(pfr_team_reback_money),0.0000) pfr_team_reback_money ,if(SUM(pfr_team_plat_money)<>\'\',SUM(pfr_team_plat_money),0.0000) pfr_team_plat_money '
                        . ',if(SUM(pfr_my_bet_money)<>\'\',SUM(pfr_my_bet_money),0.0000) pfr_my_bet_money ,if(SUM(pfr_my_earn_money)<>\'\',SUM(pfr_my_earn_money),0.0000) pfr_my_earn_money,'
                        . 'if(SUM(pfr_my_reback_money)<>\'\',SUM(pfr_my_reback_money),0.0000) pfr_my_reback_money,if(SUM(pfr_my_back_money)<>\'\',SUM(pfr_my_back_money),0.0000) pfr_my_back_money FROM le_plat_finance_report WHERE '
                        . ' u_id = ? and bet_id = ? and bet_periods = ?', [$id,$lotteryType,$issue]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }

    public function getNewTimeTeamInfo($uid,$lotteryType,$issue,$start,$limit)
    {
        $this->query('SELECT u_name, u_id,u_type, pfr_team_bet_money,'
        . ' pfr_team_earn_money, pfr_team_back_money ,'
        . ' pfr_team_reback_money , pfr_team_plat_money '
        . ', pfr_my_bet_money , pfr_my_earn_money,'
        . ' pfr_my_reback_money, pfr_my_back_money  FROM le_plat_finance_report WHERE '
        . ' ar_parent_uid = ? and bet_id = ? and bet_periods = ? limit ?,?', [$uid,$lotteryType,$issue,$start,$limit]);
        $res = $this->getAll();
        return $res;
    }

    public function getNewTimeTeamInfocount($uid,$lotteryType,$issue)
    {
        $this->query('SELECT count(*) total  FROM le_plat_finance_report WHERE '
        . ' ar_parent_uid = ? and bet_id = ? and bet_periods = ? ', [$uid,$lotteryType,$issue]);
        $res = $this->getRow();
        return $res;
    }

    public function getAgentTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT u_name, u_type, if(SUM(pfr_team_bet_money)<>\'\',SUM(pfr_team_bet_money),0) pfr_team_bet_money,'
                        . 'if(SUM(pfr_team_earn_money)<>\'\',SUM(pfr_team_earn_money),0.0000) pfr_team_earn_money,if(SUM(pfr_team_back_money)<>\'\',SUM(pfr_team_back_money),0.0000) pfr_team_back_money ,'
                        . 'if(SUM(pfr_team_reback_money)<>\'\',SUM(pfr_team_reback_money),0.0000) pfr_team_reback_money ,if(SUM(pfr_team_plat_money)<>\'\',SUM(pfr_team_plat_money),0.0000) pfr_team_plat_money '
                        . ',if(SUM(pfr_my_bet_money)<>\'\',SUM(pfr_my_bet_money),0.0000) pfr_my_bet_money ,if(SUM(pfr_my_earn_money)<>\'\',SUM(pfr_my_earn_money),0.0000) pfr_my_earn_money,'
                        . 'if(SUM(pfr_my_reback_money)<>\'\',SUM(pfr_my_reback_money),0.0000) pfr_my_reback_money,if(SUM(pfr_my_back_money)<>\'\',SUM(pfr_my_back_money),0.0000) pfr_my_back_money  FROM le_plat_finance_reportWHERE '
                        . ' u_id = ? and bet_periods = 0 and (pfr_date >= ? and pfr_date < ?)', [$id,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
        else
        {
            $res = array();
            foreach($ids as $key => $val)
            {
                $idss[] = $val;
            }
            for($i = 0; $i <count($idss); $i++)
            {
                $id = $idss[$i];
                $this->query('SELECT u_name, u_type, if(SUM(pfr_team_bet_money)<>\'\',SUM(pfr_team_bet_money),0.0000) pfr_team_bet_money,'
                        . 'if(SUM(pfr_team_earn_money)<>\'\',SUM(pfr_team_earn_money),0.0000) pfr_team_earn_money , if(SUM(pfr_team_back_money)<>\'\',SUM(pfr_team_back_money),0.0000) pfr_team_back_money ,'
                        . 'if(SUM(pfr_team_reback_money)<>\'\',SUM(pfr_team_reback_money),0.0000) pfr_team_reback_money ,if(SUM(pfr_team_plat_money)<>\'\',SUM(pfr_team_plat_money),0.0000) pfr_team_plat_money'
                        . ',if(SUM(pfr_my_bet_money)<>\'\',SUM(pfr_my_bet_money),0.0000) pfr_my_bet_money ,if(SUM(pfr_my_earn_money)<>\'\',SUM(pfr_my_earn_money),0.0000) pfr_my_earn_money,'
                        . 'if(SUM(pfr_my_reback_money)<>\'\',SUM(pfr_my_reback_money),0.0000) pfr_my_reback_money,if(SUM(pfr_my_back_money)<>\'\',SUM(pfr_my_back_money),0.0000) pfr_my_back_money FROM le_plat_finance_report WHERE '
                        . ' u_id = ? and bet_periods =0 and bet_id = ? and (pfr_date >= ? and pfr_date < ?)', [$id,$lotteryType,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }

    public function getNewAgentTeamInfo($lotteryType,$uid,$startDay,$endDay,$start,$limit)
    {
        $this->query('SELECT u_name, u_type,u_id, pfr_team_bet_money,'
                . ' pfr_team_earn_money ,  pfr_team_back_money ,'
                . ' pfr_team_reback_money , pfr_team_plat_money'
                . ', pfr_my_bet_money , pfr_my_earn_money,'
                . 'pfr_my_reback_money, pfr_my_back_money FROM le_plat_finance_report WHERE '
                . '   bet_periods =0 and bet_id = ? and ar_parent_uid = ? and (pfr_date >= ? and pfr_date < ?) order by pfr_id desc limit ?,?', [$lotteryType,$uid,$startDay,$endDay,$start,$limit]);
        $res = $this->getAll();
        return $res;
    }

    public function getNewAgentTeamInfocount($lotteryType,$uid,$startDay,$endDay)
    {
        $this->query('SELECT count(*) total FROM le_plat_finance_report WHERE '
                . '   bet_periods =0 and bet_id = ? and ar_parent_uid = ? and (pfr_date >= ? and pfr_date < ?)', [$lotteryType,$uid,$startDay,$endDay]);
        $res = $this->getRow();
        return $res;
    }

}
