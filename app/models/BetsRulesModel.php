<?php

class BetsRulesModel extends ModelBase
{

    protected $table = 'le_bets_rules';
    protected $tableConfig = 'le_bets_base_conf';

    public function __construct()
    {
        parent::__construct();
    }

    public function getInfoById($betId)
    {
        $this->query("SELECT * FROM {$this->table} WHERE bet_id = ? AND br_status = 1 ORDER BY br_type ASC, br_base_type ASC", [$betId]);
        return $this->getAll();
    }

    public function getInfo($gameId, $type)
    {
        $this->query("SELECT * FROM {$this->table} WHERE bet_id = ? AND br_status = 1 AND br_type = ?", [$gameId, $type]);
        return $this->getAll();
    }
    //都的方法
    public function getRulesByBrId($brId)
    {
        $this->query("SELECT * FROM {$this->table} WHERE br_id = ? AND br_status = 1 limit 1", [$brId]);
        return $this->getRow();
    }

    public function getRuleByBrId($brId, $status)
    {
        $where = '';
        if ($status)
        {
            $where .= ' AND br_status = 1';
        }

        $this->query('SELECT * FROM ' . $this->table . ' WHERE br_id = ? ' . $where . ' LIMIT 1', [$brId]);
        return $this->getRow();
    }

    public function editBonus($brId, $bonus)
    {
        $this->update(['br_bonus' => $bonus], ['condition' => 'br_status = 1 AND br_id = ? ' , 'values' =>[$brId]]);

        return $this->affectRow();
    }

    public function getPlayWay($betId)
    {
        $this->query('SELECT * FROM ' . $this->tableConfig . ' where bet_id = ?', [$betId]);
        return $this->getAll();
    }

}