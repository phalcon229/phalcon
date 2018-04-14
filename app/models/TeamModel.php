<?php

class TeamModel extends ModelBase
{

    protected $table = 'le_team_stats_day';

    public function __construct()
    {
        parent::__construct();
    }

    public function getInfoByUserId($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? LIMIT 1', [$uId]);

        return $this->getRow();
    }

    public function getTeamCount($uId, $startDate, $endDate)
    {
        $match = [];
        $where = 'u_id = ?';
        array_push($match, $uId);

        if ($startDate)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($match, $startDate);
        }

        if ($endDate)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($match, $endDate);
        }

        $this->query("SELECT tsd_recharge, tsd_withdraw, tsd_earn_money, tsd_date FROM {$this->table} WHERE {$where} ORDER BY tsd_id DESC", $match);
        return $this->getRow();
    }

    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and nper = 0 and (tsd_date>= ? and tsd_date< ?)', [$id,$startDay,$endDay]);
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
                $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and nper = 0 and bet_id = ? and (tsd_date>= ? and tsd_date< ?)', [$id,$lotteryType,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }
    
    public function getTeamTime($ids,$startDay,$endDay)
    {
        $ids = implode(',', $ids);

        $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ''
                    . ' WHERE u_id in (' . $ids . ') and nper = 0 and (tsd_date>= ? and tsd_date< ?)', [$startDay,$endDay]);

        return $this->getAll();
    }

    public function getTeamStats($uaUid, $betId, $startDay, $endDay, $fileds = '*')
    {
        $where = ' WHERE ua.ua_u_id = ?';
        $data = [$uaUid];
        if ($betId)
        {
            $where .= ' AND tsd.bet_id = ?';
            array_push($data, $betId);
        }

        if ($startDay)
        {
            $where .= ' AND tsd.tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd.tsd_date <= ?';
            array_push($data, $endDay);
        }
        $this->query('SELECT ' . $fileds . ' FROM le_user_agent AS ua LEFT JOIN ' . $this->table . ' AS tsd ON tsd.u_id = ua.u_id '.$where, $data);

        return $this->getAll();
    }

    public function getTeamStatLists($uIds, $betId, $startDay, $endDay, $fileds= '*')
    {
        $where = '';
        $data = $uIds;
        if ($betId)
        {
            $where .= ' AND bet_id = ?';
            array_push($data, $betId);
        }

        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT u_id, sum(tsd_withdraw) AS withdraw, sum(tsd_recharge) AS recharge, sum(tsd_bet_money) AS bet_money, sum(tsd_reback_money) AS reback, sum(tsd_pay_bonuses) AS pay_bonuses, sum(tsd_earn_money) AS earn' . ' FROM ' . $this->table . ' WHERE u_id IN ('. $this->generatePhForIn(count($uIds)) . ') ' . $where . ' GROUP BY u_id', $data);

        return $this->getAll();
    }

    public function updateWithdrawOrRecharge($uId, $withdraw = 0, $recharge = 0)
    {
        $date = strtotime(date('Y-m-d'));
        $this->query('SELECT tsd_id FROM ' . $this->table . ' WHERE tsd_date = ? AND u_id = ? LIMIT 1', [$date, $uId]);
        $row = $this->getRow();
        if(!$row)
        {
            $this->query('INSERT INTO ' . $this->table . ' SET u_id = ?, tsd_date = ?, tsd_withdraw = ?, tsd_recharge = ?', [$uId, $date, $withdraw, $recharge]);
            if(!$this->lastInsertId())
            {
                return false;
            }
        }
        else
        {
            $this->query('SELECT tsd_id FROM le_team_stats_day WHERE tsd_id = ? LIMIT 1 FOR UPDATE',  [$row['tsd_id']]);
            $this->query('UPDATE le_team_stats_day SET tsd_withdraw = tsd_withdraw + ?, tsd_recharge = tsd_recharge + ? WHERE tsd_id = ?',
                    [$withdraw, $recharge, $row['tsd_id']]);
            if($this->affectRow() != 1)
            {
                return false;
            }
        }

        return true;
    }

    public function getAllTeamStat($startDay, $endDay)
    {
        $where = ' WHERE 1 = 1';
        $data = [];
        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT SUM(tsd_withdraw) AS withdraw, SUM(tsd_recharge) AS recharge, SUM(tsd_bet_money) AS bet_money, SUM(tsd_earn_money) AS earn, SUM(tsd_reback_money) AS reback, SUM(tsd_pay_bonuses) AS pay_bonuses FROM ' . $this->table . $where, $data);

        return $this->getRow();
    }

    public function getTeamTotal($name, $startDay, $endDay)
    {

        $where = ' WHERE 1 = 1';
        $data = [];
        if ($name)
        {
            $where .= ' AND u_name LIKE ?';
            array_push($data, '%' . $name . '%');
        }

        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT COUNT(tsd_id) AS total FROM ' . $this->table . $where . ' GROUP BY u_id', $data);
        return $this->getAll();
    }
    
    public function getTeamTotalByUid($uid, $startDay, $endDay)
    {

        $where = ' WHERE 1 = 1';
        $data = [];
        $uid = implode(',', $uid);
        if ($uid)
        {
            $where .= ' AND u_id in (' . $uid . ') ';
        }

        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT COUNT(tsd_id) AS total FROM ' . $this->table . $where . ' GROUP BY u_id', $data);
        return $this->getAll();
    }

    public function getTeamLists($name, $startDay, $endDay, $start, $perPage)
    {
        $where = ' WHERE nper = 0';
        $data = [];
        if ($name)
        {
            $where .= ' AND u_name LIKE ?';
            array_push($data,'%' . $name . '%');
        }

        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $limit = '';
        if ($perPage)
        {
            $limit .= 'LIMIT ?, ?';
            array_push($data, $start);
            array_push($data, $perPage);
        }

        $this->query('SELECT u_id, u_name, u_type, SUM(tsd_recharge) recharge, SUM(tsd_withdraw) withdraw, SUM(tsd_bet_money) bet_money, SUM(tsd_earn_money) earn, SUM(tsd_reback_money) reback, SUM(tsd_pay_bonuses) pay_bonuses FROM ' . $this->table . $where . ' GROUP BY u_id  ' . $limit, $data);

        return $this->getAll();
    }
    
    public function getTeamListsByUid($uid, $startDay, $endDay, $start, $perPage)
    {
        $where = ' WHERE 1 = 1';
        $data = [];
        $uid = implode(',', $uid);
        if ($uid)
        {
            $where .= ' AND u_id in (' . $uid . ') ';
        }

        if ($startDay)
        {
            $where .= ' AND tsd_date >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND tsd_date <= ?';
            array_push($data, $endDay);
        }

        $limit = '';
        if ($perPage)
        {
            $limit .= 'LIMIT ?, ?';
            array_push($data, $start);
            array_push($data, $perPage);
        }

        $this->query('SELECT u_id, u_name, u_type, SUM(tsd_recharge) recharge, SUM(tsd_withdraw) withdraw, SUM(tsd_bet_money) bet_money, SUM(tsd_earn_money) earn, SUM(tsd_reback_money) reback, SUM(tsd_pay_bonuses) pay_bonuses FROM ' . $this->table . $where . ' GROUP BY u_id  ' . $limit, $data);

        return $this->getAll();
    }
}
