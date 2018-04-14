<?php

class AgentReportModel extends ModelBase
{

    protected $table = 'le_agent_report';

    public function __construct()
    {
        parent::__construct();
    }

    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT u_id, u_name, if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0) ar_my_reback_money, if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0) ar_team_withdraw_money,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0.0000) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money ,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money ,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money,if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and (ar_date>= ? and ar_date< ?)', [$id,$startDay,$endDay]);
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
                $this->query('SELECT u_id, u_name, if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0) ar_my_reback_money, if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0) ar_team_withdraw_money,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0.0000) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money ,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money ,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money, if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and bet_id = ? and (ar_date>= ? and ar_date< ?)', [$id,$lotteryType,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }

    public function getTimeTeamInfofront($ids,$lotteryType,$startDay,$endDay)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT  if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0) ar_my_reback_money, if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0) ar_team_withdraw_money,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0.0000) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money ,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money ,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money,if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money, if(SUM(ar_up_back_money)<>\'\',SUM(ar_up_back_money),0.0000) ar_up_back_money FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and (ar_date>= ? and ar_date< ?)', [$id,$startDay,$endDay]);
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
                $this->query('SELECT if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0) ar_my_reback_money, if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0) ar_team_withdraw_money,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0.0000) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money ,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money ,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money, if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money, if(SUM(ar_up_back_money)<>\'\',SUM(ar_up_back_money),0.0000) ar_up_back_money FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and bet_id = ? and (ar_date>= ? and ar_date< ?)', [$id,$lotteryType,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }

    public function getTeamTime($ids,$startDay,$endDay)
    {
//        $ids = implode(',', $ids);
//        var_dump($ids);exit;
        $this->query('SELECT if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0) ar_team_withdraw_money,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0.0000) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money ,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money ,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money,'
                . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money FROM ' . $this->table . ''
                    . ' WHERE u_id = ? and (ar_date>= ? and ar_date< ?)', [$ids,$startDay,$endDay]);

        return $this->getAll();
    }

    public function getInfoByTime($startDay,$endDay)
    {
        $this->query('SELECT if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money ,'
                        . 'if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0.0000) ar_team_withdraw_money ,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money FROM ' . $this->table . ''
                    . ' WHERE (ar_date>= ? and ar_date<= ?)', [$startDay,$endDay]);

        return $this->getRow();
    }

    public function getTeamListsByUid($uid, $startDay, $endDay, $name)
    {
        $len = count($uid);
        $res = array();
        if(empty($name))
        {
            for($i = 0; $i <$len; $i++)
            {
                $id = $uid[$i];
                $where = ' WHERE 1 = 1';
                $data = [];

                if ($uid)
                {
                    $where .= ' AND u_id = '. $id ;
                }
                if($name)
                {
                    $where .= ' AND u_name LIKE %'. $name .'%';
                }
                if ($startDay)
                {
                    $where .= ' AND ar_date >= ?';
                    array_push($data, $startDay);
                }

                if ($endDay)
                {
                    $where .= ' AND ar_date <= ?';
                    array_push($data, $endDay);
                }

                $this->query('SELECT if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money,'
                            . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money ,'
                            . 'if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0.0000) ar_team_withdraw_money ,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money,if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,'
                        . 'if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money,if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0.0000) ar_my_reback_money FROM ' . $this->table . $where . '  ', $data);

                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
        else
        {
            $this->query('SELECT if(SUM(ar_team_bet_money)<>\'\',SUM(ar_team_bet_money),0) ar_team_bet_money,if(SUM(ar_team_earn_money)<>\'\',SUM(ar_team_earn_money),0.0000) ar_team_earn_money,'
                        . 'if(SUM(ar_team_back_money)<>\'\',SUM(ar_team_back_money),0.0000) ar_team_back_money,if(SUM(ar_team_reback_money)<>\'\',SUM(ar_team_reback_money),0.0000) ar_team_reback_money ,'
                        . 'if(SUM(ar_team_withdraw_money)<>\'\',SUM(ar_team_withdraw_money),0.0000) ar_team_withdraw_money ,if(SUM(ar_team_recharge_money)<>\'\',SUM(ar_team_recharge_money),0.0000) ar_team_recharge_money,'
                        . 'if(SUM(ar_my_back_money)<>\'\',SUM(ar_my_back_money),0.0000) ar_my_back_money,if(SUM(ar_my_bet_money)<>\'\',SUM(ar_my_bet_money),0.0000) ar_my_bet_money,'
                        . 'if(SUM(ar_my_earn_money)<>\'\',SUM(ar_my_earn_money),0.0000) ar_my_earn_money,if(SUM(ar_my_reback_money)<>\'\',SUM(ar_my_reback_money),0.0000) ar_my_reback_money FROM ' . $this->table .' where u_name LIKE "%'.$name.'%" and  (ar_date >= ? and ar_date <= ?) ', [$startDay, $endDay]);

            return $this->getAll();
        }

    }

    public function agentReportTotal($data)
    {
        $where = ' WHERE 1 = 1';
        $value = [];
        if (!empty($data['name']))
        {
            $where .= ' AND u_name LIKE "%'. $data['name'] .'%"';
        }else{
            $where .= ' AND ar_parent_uid = ?';
            array_push($value, $data['uId']);
        }

        if (!empty($data['id']))
        {
            $where .= ' AND u_id = '. $data['id'];
        }

        if (!empty($data['start']))
        {
            $where .= ' AND ar_date >= ?';
            array_push($value, $data['start']);
        }

        if (!empty($data['end']))
        {
            $where .= ' AND ar_date <= ?';
            array_push($value, $data['end']);
        }

        $sql = 'SELECT COUNT(*) FROM (SELECT COUNT(*) FROM ' . $this->table . $where . ' GROUP BY u_id ) a';

        $this->query($sql, $value);
        return $this->getOne();
    }

    public function agentReportLists($data, $start, $nums)
    {
        $where = ' WHERE 1 = 1';
        $value = [];
        if (!empty($data['name']))
        {
            $where .= ' AND u_name LIKE "%'. $data['name'] .'%"';
        }else{
            $where .= ' AND ar_parent_uid = ?';
            array_push($value, $data['uId']);
        }
        if (!empty($data['start']))
        {
            $where .= ' AND ar_date >= ?';
            array_push($value, $data['start']);
        }

        if (!empty($data['id']))
        {
            $where .= ' AND u_id = '. $data['id'];
        }

        if (!empty($data['end']))
        {
            $where .= ' AND ar_date <= ?';
            array_push($value, $data['end']);
        }

        array_push($value, $start);
        array_push($value, $nums);

        $sql = 'SELECT u_id, u_name, SUM(ar_team_bet_money) AS ar_team_bet_money, SUM(ar_team_earn_money) AS ar_team_earn_money, SUM(ar_team_back_money) AS ar_team_back_money, SUM(ar_team_reback_money) AS ar_team_reback_money, SUM(ar_team_withdraw_money) AS ar_team_withdraw_money, SUM(ar_team_recharge_money) AS ar_team_recharge_money, SUM(ar_my_back_money) AS ar_my_back_money, SUM(ar_my_bet_money) AS ar_my_bet_money, SUM(ar_my_earn_money) AS ar_my_earn_money, SUM(ar_my_reback_money) AS ar_my_reback_money FROM ' . $this->table . $where . ' GROUP BY u_id  LIMIT ?, ?';

        $this->query($sql, $value);
        return $this->getAll();
    }


    public function agentReporttzTotal($data)
    {
        $where = ' WHERE 1 = 1';
        $value = [];
        if (!empty($data['name']))
        {
            $where .= ' AND u_name LIKE "%'. $data['name'] .'%"';
        }

        

        if (!empty($data['start']))
        {
            $where .= ' AND ar_date >= ?';
            array_push($value, $data['start']);
        }

        if (!empty($data['end']))
        {
            $where .= ' AND ar_date <= ?';
            array_push($value, $data['end']);
        }

        $sql = 'SELECT COUNT(*) FROM (SELECT COUNT(*) FROM ' . $this->table . $where . ' GROUP BY u_id ) a';

        $this->query($sql, $value);
        return $this->getOne();
    }

    public function agentReporttzLists($data, $start, $nums)
    {
        $where = ' WHERE 1 = 1';
        $value = [];
        if (!empty($data['name']))
        {
            $where .= ' AND u_name LIKE "%'. $data['name'] .'%"';
        }

        

        if (!empty($data['start']))
        {
            $where .= ' AND ar_date >= ?';
            array_push($value, $data['start']);
        }

        if (!empty($data['end']))
        {
            $where .= ' AND ar_date <= ?';
            array_push($value, $data['end']);
        }

        array_push($value, $start);
        array_push($value, $nums);

        $sql = 'SELECT u_id, u_name, SUM(ar_team_bet_money) AS ar_team_bet_money, SUM(ar_team_earn_money) AS ar_team_earn_money, SUM(ar_team_back_money) AS ar_team_back_money, SUM(ar_team_reback_money) AS ar_team_reback_money, SUM(ar_team_withdraw_money) AS ar_team_withdraw_money, SUM(ar_team_recharge_money) AS ar_team_recharge_money, SUM(ar_my_back_money) AS ar_my_back_money, SUM(ar_my_bet_money) AS ar_my_bet_money, SUM(ar_my_earn_money) AS ar_my_earn_money, SUM(ar_my_reback_money) AS ar_my_reback_money FROM ' . $this->table . $where . ' AND (ar_my_bet_money > 0 OR ar_team_recharge_money > 0)  GROUP BY u_id  LIMIT ?, ?';

        $this->query($sql, $value);
        return $this->getAll();
    }
}