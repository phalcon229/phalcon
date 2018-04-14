<?php

class WalletRecordModel extends ModelBase
{

    protected $table = 'le_user_wallet_recorded';

    public function __construct()
    {
        parent::__construct();
    }

    public function getRecordInfoByUserId($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? order by uwr_id desc limit 10', [$uId]);

        return $this->getAll();
    }

    public function getDetail($uId,$uwrId,$fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND uwr_id = ?', [$uId,$uwrId]);

        return $this->getAll();
    }

    public function getInfoByType($uId,$uwrType,$num,$fields = '*')
    {
        if($num==1)
        {
            if($uwrType==0)
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? order by uwr_id desc limit 10', [$uId]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND uwr_type = ? order by uwr_id desc limit 10', [$uId,$uwrType]);

                return $this->getAll();
            }
        }
        else
        {
            $num=($num-1)*10;
            if($uwrType==0)
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? order by uwr_id desc limit '.$num.',10', [$uId]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND uwr_type = ? order by uwr_id desc limit '.$num.',10', [$uId,$uwrType]);

                return $this->getAll();
            }
        }
    }

    public function getTotalInfo($uId,$uwrType)
    {
        if($uwrType==0)
        {
            $this->query('SELECT count(*) num, sum(uwr_money) uwr_money FROM ' . $this->table . ' WHERE u_id = ? ', [$uId]);

            return $this->getAll();
        }
        else
        {
            $this->query('SELECT count(*) num, sum(uwr_money) uwr_money FROM ' . $this->table . ' WHERE u_id = ? AND uwr_type = ? ', [$uId,$uwrType]);

            return $this->getAll();
        }

    }

    public function getTotalByType($type,$startDay, $endDay)
    {
        if(!empty($type))
        {
            $this->query('SELECT sum(uwr_money) uwr_money FROM ' . $this->table . ' WHERE uwr_type = ? and (uwr_created_time>= ? and uwr_created_time < ?) ', [$type, $startDay, $endDay]);

                return $this->getRow();
        }
        else
        {
            $this->query('SELECT sum(uwr_money) uwr_money FROM ' . $this->table . ' WHERE (uwr_created_time>= ? and uwr_created_time < ?) and uwr_type in(3,7,11,13,15)', [$startDay, $endDay]);

                return $this->getRow();
        }
    }

    public function getTotalBalance($startDay)
    {
        $this->query('SELECT sum(uwr_balance) uwr_balance from  le_user_wallet_recorded WHERE uwr_id in (SELECT MAX(uwr_id) FROM le_user_wallet_recorded where uwr_created_time <= ?  GROUP BY u_id); ', [$startDay]);

                return $this->getRow();
    }

    public function getRecordTotal($uId, $type, $startTime, $endTime)
    {
        $where = '';
        $data = [$uId];
        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        if ($startTime)
        {
            $where .= ' AND uwr_created_time >= ?';
            array_push($data, $startTime);
        }
        if ($endTime)
        {
            $where .= ' AND uwr_created_time <=?';
            array_push($data, $endTime);
        }

        $sql = 'SELECT count(uwr_id) as total FROM ' . $this->table . ' WHERE u_id = ?' . $where;
        $this->query($sql, $data);
        return $this->getOne();
    }

    public function userRecordLists($uId, $type, $startTime, $endTime, $start, $nums)
    {
        $where = '';
        $data = [$uId];
        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        if ($startTime)
        {
            $where .= ' AND uwr_created_time >= ?';
            array_push($data, $startTime);
        }
        if ($endTime)
        {
            $where .= ' AND uwr_created_time <= ?';
            array_push($data, $endTime);
        }

        array_push($data, $start);
        array_push($data, $nums);
        $sql = 'SELECT uwr_id, uwr_money, uwr_type, uwr_bussiness_id, uwr_created_time, uwr_memo, uwr_balance FROM ' . $this->table . ' WHERE u_id = ? ' . $where . ' ORDER BY uwr_id DESC LIMIT ?, ?';
        // echo $sql;var_dump($data);
        // exit;
        $this->query($sql, $data);

        return $this->getAll();
    }

    /**
     * 分页获取用户帐变记录
     * @param  [type] $uId    [description]
     * @param  [type] $type   [description]
     * @param  [type] $start  [description]
     * @param  [type] $nums   [description]
     * @param  [type] $fields [description]
     * @return [type]         [description]
     */
    public function getUserLists($uId, $type, $start, $nums, $fields)
    {
        $where = ' WHERE u_id = ?';
        $data = [$uId];

        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        array_push($data, $start);
        array_push($data, $nums);
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . $where . ' ORDER BY uwr_id DESC LIMIT ?,?', $data);

        return $this->getAll();
    }

    /**
     * 总计用户帐变记录
     * @param  [type] $uId  [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getUserTotal($uId, $type)
    {
        $where = ' WHERE u_id = ?';
        $data = [$uId];

        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        $this->query('SELECT COUNT(uwr_id) AS count, SUM(uwr_money) AS total FROM ' . $this->table . $where, $data);

        return $this->getRow();
    }

    public function getListsByUids($uIds, $type, $startDay, $endDay, $start, $nums, $fields)
    {
        $where = ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ')';
        $data = $uIds;
        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        if ($startDay)
        {
            $where .= ' AND uwr_created_time >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND uwr_created_time <= ?';
            array_push($data, $endDay);
        }

        array_push($data, $start);
        array_push($data, $nums);
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . $where . ' ORDER BY uwr_id DESC LIMIT ?,?', $data);
        return $this->getAll();
    }

    public function getListsTotal($uIds, $type, $startDay, $endDay)
    {
        $where = ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ')';
        $data = $uIds;
        if ($type)
        {
            $where .= ' AND uwr_type = ?';
            array_push($data, $type);
        }

        if ($startDay)
        {
            $where .= ' AND uwr_created_time >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND uwr_created_time <= ?';
            array_push($data, $endDay);
        }

        $sql = 'SELECT count(uwr_id) AS count, SUM(income) AS income, SUM(expend) AS expend FROM (SELECT uwr_id, if(uwr_money >= 0, uwr_money, 0) income, if(uwr_money < 0, uwr_money, 0) expend FROM ' . $this->table . $where . ' ORDER BY uwr_id DESC ) AS countWallet';

        $this->query($sql, $data);
        return $this->getRow();
    }
}
