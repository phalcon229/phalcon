<?php

class BetsConfigModel extends ModelBase
{

    protected $table = 'le_bets_base_conf';

    public function __construct()
    {
        parent::__construct();
    }

    public function allBets($status)
    {
        $where = '';
        $data = [];
        if ($status)
        {
            $where .= ' WHERE bet_isenable = ?';
            array_push($data, $status);
        }

        $this->query("SELECT bet_id, bet_name, bet_play_type FROM {$this->table} {$where}", $data);
        return $this->getAll();
    }

    public function getInfoById($betId, $type = '')
    {
        if(empty($type))
        {
            $this->query("SELECT * FROM {$this->table} WHERE bet_isenable = 1 AND bet_id = ?", [$betId]);
            return $this->getRow();
        }
        else
        {
            $this->query("SELECT * FROM {$this->table} WHERE bet_isenable = 1 AND bet_id = ? AND bet_play_type = ?", [$betId, $type]);
            return $this->getRow();
        }

    }

    public function getInfoByIdAllStatus($betId, $type = '')
    {
        if(empty($type))
        {
            $this->query("SELECT * FROM {$this->table} WHERE bet_id = ?", [$betId]);
            return $this->getRow();
        }
        else
        {
            $this->query("SELECT * FROM {$this->table} WHERE bet_id = ? AND bet_play_type = ?", [$betId, $type]);
            return $this->getRow();
        }

    }

    public function getLotteryType( $fields='bet_id' )
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' ORDER BY `bet_id` asc ');

        return $this->getAll();
    }

    public function getAllBets($fields='*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . '');

        return $this->getAll();
    }

    public function getBetsInfoByIds($ids, $type, $fields='*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_id in ('.$ids.') and bet_isenable = 1 and bet_play_type = ? ORDER BY `bet_rank` desc ',[$type]);

        return $this->getAll();
    }

    public function getBetsInfo($ids,$fields='*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_id not in ('.$ids.') and bet_isenable = 1 ORDER BY `bet_rank` desc ');

        return $this->getAll();
    }

    public function getBets($fields='*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_isenable = 1 order by bet_id asc');

        return $this->getAll();
    }

    public function getlimit($betId)
    {
        $this->query('SELECT * FROM ' . $this->table . ' where bet_id = ?', [$betId]);
        return $this->getAll();
    }

    public function getOpenBets($fields='*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_isenable = 1 order by bet_rank desc');

        return $this->getAll();
    }

    public function getOpenBetsId($type,$fields='bet_id')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_isenable = 1 and bet_play_type = ? order by bet_rank desc',[$type]);

        return $this->getAll();
    }

}