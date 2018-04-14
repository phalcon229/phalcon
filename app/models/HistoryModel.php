<?php

class HistoryModel extends ModelBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getHisNum($bId)
    {
        $this->query('SELECT count(bres_id) as total FROM le_bets_result WHERE bres_plat_isopen = 1 AND bet_id = ?', [$bId]);
        return $this->getOne();
    }

    public function getHisLimit($bId, $page, $start, $limit)
    {

        $this->query('SELECT bres_id, bet_id, bres_memo, bres_periods, bres_result, bres_plat_open_time,bres_created_time,bres_open_time FROM le_bets_result WHERE bres_plat_isopen = 1 AND bet_id = ? ORDER BY bres_id DESC LIMIT '. $start . ',' . $limit, [$bId]);
        return $this->getAll();
    }
}