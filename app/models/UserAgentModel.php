<?php

class UserAgentModel extends ModelBase
{

    protected $table = 'le_user_agent';

    public function __construct()
    {
        parent::__construct();
    }

    public function getBaseInfo($uId,$startDay,$endDay)
    {
        $this->query('SELECT ua_u_id,ua_u_name,ua_type FROM ' . $this->table . ' WHERE u_id = ? and (ua_created_time>=? and ua_created_time<=?)', [$uId,$startDay,$endDay]);

        return $this->getAll();
    }

    public function lists($uId, $uName, $startTime, $endTime)
    {
        $where = 'ua.ua_u_id = ?';
        $match = [];
        array_push($match, $uId);

        if ($uName)
            $where .= ' AND u_name LIKE "%' . $uName . '%"';

        if ($startTime)
        {
            $where .= ' AND ua_created_time >= ?';
            array_push($match, $startTime);
        }

        if ($endTime)
        {
            $where .= ' AND ua_created_time <= ?';
            array_push($match, $endTime);
        }

        $this->query("SELECT ua_id, ua.u_id, u_name, ua_type, ua_rate, ua_status,ua_memo, ua_u_id, w_money FROM {$this->table} ua LEFT JOIN le_user_wallet uw ON ua.u_id = uw.u_id WHERE {$where} ORDER BY ua_id DESC", $match);
        return $this->getAll();
    }

    public function getAgentStat($uaUid, $startDay, $endDay, $fields = '*')
    {
        $where = ' WHERE ua_u_id = ?';
        $data = [$uaUid];
        if ($startDay)
        {
            $where .= ' AND ua_created_time >= ?';
            array_push($data, $startDay);
        }
        if ($endDay)
        {
            $where .= ' AND ua_created_time <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . $where, $data);

        return $this->getAll();
    }

    public function getDetail($uaId)
    {
        $this->query("SELECT ua_u_id, ua_u_name, ua_type, w_money FROM {$this->table} ua LEFT JOIN le_user_wallet uw ON ua.ua_u_id = uw.u_id WHERE ua_id = ?", [$uaId]);

        return $this->getRow();
    }

    public function getIds($uId)
    {
        $this->query('SELECT ua_u_name FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getRow();
    }

    public function getInfo($uId, $fields)
    {
        $this->query("SELECT {$fields} FROM {$this->table} WHERE u_id = ? LIMIT 1", [$uId]);
        return $this->getRow();
    }

    public function updateRate($uId, $rate)
    {
        return $this->update(['ua_rate' => $rate], ['condition' => 'u_id = ?', 'values' => [$uId]]);
    }

    public function getAgentName($uId)
    {
        $this->query("SELECT a.ua_u_id, b.u_name FROM le_user_agent as a LEFT JOIN le_users as b ON a.u_id = b.u_id WHERE a.ua_u_id in ( ". $uId .")");
        return $this->getAll();
    }

    public function analWhere($name, $min, $max)
    {
        $where = ' WHERE 1 = 1';
        $match = [];

        if ($name)
        {
            $where .= ' AND ua.u_name LIKE ?';
            $match[] = '%' . $name . '%';
        }
        if($min)
        {
            $where .= 'AND w.w_money >= ?';
            $match[] = $min;
        }
        if($max)
        {
            $where .= 'AND w.w_money <= ?';
            $match[] = $max;
        }

        return ['where' => $where, 'match' => $match];
    }

    public function getTotalAgent($name, $minMoney, $maxMoney)
    {
        $where = $this->analWhere($name, $minMoney, $maxMoney)['where'];
        $match = $this->analWhere($name, $minMoney, $maxMoney)['match'];

        $this->query('SELECT COUNT(ua.ua_id) as total FROM ' . $this->table . ' AS ua LEFT JOIN le_user_wallet AS w ON ua.u_id = w.u_id ' . $where . ' LIMIT 1', $match);
        return $this->getOne();
    }

    public function getAgentInfo($name, $minMoney, $maxMoney, $start, $perPage)
    {
        $where = $this->analWhere($name, $minMoney, $maxMoney)['where'];
        $match = $this->analWhere($name, $minMoney, $maxMoney)['match'];
        array_push($match, $start);
        array_push($match, $perPage);

        $this->query('SELECT ua.u_id, ua.u_name, ua.ua_type, ua.ua_created_time, ua.ua_status, ua.ua_rate, w.w_money FROM ' . $this->table . ' AS ua LEFT JOIN le_user_wallet AS w ON ua.u_id = w.u_id ' . $where . ' ORDER BY ua_id DESC LIMIT ?, ?', $match);
        return $this->getAll();
    }

    public function setAgentRate($uId, $rate)
    {
        $this->update(['ua_rate' => $rate, 'ua_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => 'u_id = ?', 'values' => [$uId]]);
        return $this->affectRow();
    }

    public function getTeamNum($uId)
    {
        $this->query('SELECT ua_team_num, ua_reg_nums FROM ' . $this->table . ' WHERE u_id = ? ', [$uId]);

        return $this->getRow();
    }

    public function getAllAgent($uId)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE ua_u_id = ?', [$uId]);
        return $this->getAll();
    }

    public function getCountNext($uId)
    {
        $this->query('SELECT count(ua_id) as total FROM ' . $this->table . ' WHERE ua_u_id = ?', [$uId]);
        return $this->getRow();
    }

    public function getNextUid($uId, $start, $perPage)
    {
        $this->query('SELECT u_id FROM ' . $this->table . ' WHERE ua_u_id = ? limit ?, ?', [$uId, $start, $perPage]);
        return $this->getAll();
    }

    public function getBackAgent($uId,$start, $perPage)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE ua_u_id = ? limit ?, ?', [$uId, $start, $perPage]);
        return $this->getAll();
    }

    public function getAgent($uId, $start, $perPage)
    {
        if($uId === 0)
        {
            $this->query('SELECT * FROM ' . $this->table . ' where ua_u_id = ? limit ?,?', [$uId, $start, $perPage]);
            return $this->getAll();
        }
        else
        {
            $this->query('SELECT * FROM ' . $this->table . ' where u_name like "%'.$uId.'%" limit ?,?', [$start, $perPage]);
            return $this->getAll();
        }
    }

    public function getAgentByUid($uId, $start, $perPage)
    {
        $this->query('SELECT * FROM ' . $this->table . ' where ua_u_id =? limit ?,?', [$uId, $start, $perPage]);
        return $this->getAll();
    }

    public function getTypeByUid($uId, $start, $perPage)
    {
        $this->query('SELECT ua_type FROM ' . $this->table . ' WHERE u_id = ? ', [$uId]);

        return $this->getRow();
    }

    public function getAgentInfoByUid($uId)
    {
        $func = function($uId) {
            $this->query('SELECT u_id, ua_u_id, ua_rate, u_name, ua_type FROM ' . $this->table . ' WHERE u_id = ? LIMIT 1', [$uId]);
            $rs = $this->getRow();
            if(!$rs)
                return false;
            else
                return $rs;
        };

        $res = [];
        $rs = $func($uId);
        if($rs)
            $res[] = $rs;
        else
            return false;

        while($rs && $rs['ua_u_id'] > 0)
        {
            $rs = $func($rs['ua_u_id']);
            if($rs)
                $res[] = $rs;
        }

        return $res;
    }

    public function getDown($uId)
    {
        $func = function($uId) {
            $this->query('SELECT u_id FROM ' . $this->table . ' WHERE ua_u_id IN ('.$uId.') ');
            $rs = $this->getAll();
            if(!$rs)
            {
                return false;
            }
            else
            {
                for($i = 0; $i < count($rs); $i++)
                {
                    $r[$i] = $rs[$i]['u_id'];
                }
                $rs = implode(',',$r);
                return $rs;
            }
        };

        $res = [];
        $rs = $func($uId);
        if($rs)
            $res = $rs.',';
        else
            return false;

        while($rs)
        {
            $rs = $func($rs);
            if($rs)
                $res .= $rs.',';
        }

        return $res;
    }

    public function getUserAgentByUids($uIds)
    {
        $this->query('SELECT u_id, ua_team_num, ua_good_user_nums FROM ' . $this->table . ' WHERE ua_type = 3 AND u_id IN (' . $this->generatePhForIn(count($uIds)) . ')', $uIds);
        return $this->getAll();
    }

    public function getAgentMaxRate($uId, $status)
    {
        $where = ' WHERE ua_u_id = ?';
        $data = [$uId];
        if ($status)
        {
            $where .= ' AND ua_status = ?';
            array_push($data, $status);
        }

        $this->query('SELECT MAX(ua_rate) AS max FROM ' . $this->table . $where, $data);
        return $this->getOne();
    }

    public function getAgentByUids($uIds)
    {
        $this->query('SELECT u_id, ua_u_name, ua_rate FROM ' . $this->table . ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ') ORDER BY find_in_set(u_id, "' . implode(',', $uIds) . '")', $uIds);
        return $this->getAll();
    }

    public function getAgentByName($name)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE u_name LIKE "%'.$name.'%" ');
        return $this->getAll();
    }

    public function editMemo($info, $uId)
    {
        $this->update([
                'ua_memo' =>$info,
            ],['condition' => 'u_id = ?', 'values' => [$uId]]);
        return $this->affectRow();
    }

    public function getMemo($uId)
    {
        $this->query('SELECT ua_memo from ' . $this->table . ' WHERE u_id = ?', [$uId]);
        return $this->getRow();
    }

    public function getMaxRate($uId)
    {
        $this->query('SELECT max(ua_rate) ua_rate from ' . $this->table . ' WHERE ua_u_id = ?', [$uId]);
        return $this->getRow();
    }

    public function getAgentInfosByUids($uIds, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ') ORDER BY find_in_set(u_id, "' . implode(',', $uIds) . '")', $uIds);
        return $this->getAll();
    }

    public function getAgentLists($data, $start, $nums, $fields)
    {
        $where = 'ua_u_id = ?';
        $match = [$data['uId']];

        if ($data['uName'])
            $where .= ' AND u_name LIKE "%' . $data['uName'] . '%"';

        if ($data['startTime'])
        {
            $where .= ' AND ua_created_time >= ?';
            array_push($match, $data['startTime']);
        }

        if ($data['endTime'])
        {
            $where .= ' AND ua_created_time <= ?';
            array_push($match, $data['endTime']);
        }

        array_push($match, $start);
        array_push($match, $nums);
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ' . $where . ' ORDER BY ua_id DESC LIMIT ?,?', $match);
        return $this->getAll();
    }

    public function getUpRate($uId)
    {
        $this->query('SELECT  ua_rate from ' . $this->table . ' WHERE u_id = ?', [$uId]);
        return $this->getRow();
    }
}
