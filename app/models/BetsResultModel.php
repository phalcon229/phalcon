<?php

class BetsResultModel extends ModelBase
{

    protected $table = 'le_bets_result';
    protected $typeTable='le_bets_base_conf';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据彩种id，期号查询开奖结果
     * @param type $betId
     * @param type $npre
     * @param type $fields
     * @return type
     */
    public function getResultInfo( $betId, $npre, $fields='*' )
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where bet_id = ? and bres_periods = ? limit 1',[$betId, $npre]);

        return $this->getAll();
    }

    public function getDayResult($begintime, $endtime)
    {
        $this->query('SELECT a.bres_id,a.bet_id,a.bres_periods,a.bres_result,b.bet_name FROM ' . $this->table . ' a LEFT JOIN '. $this->typeTable .' b on a.bet_id = b.bet_id AND (a.bres_created_time > ? AND a.bres_created_time < ?) WHERE a.bres_plat_isopen = 1 group by a.bet_id order by a.bres_created_time desc', [$begintime, $endtime]);

        return $this->getAll();
    }

    //获取每个采种的最新一条记录
    public function getResultByType($betId)
    {
        $this->query('SELECT a.bres_id,a.bet_id,a.bres_periods,a.bres_result,a.bres_plat_isopen,b.bet_id,b.bet_isenable,b.bet_name FROM ' . $this->table . ' a INNER JOIN '. $this->typeTable .' b on a.bet_id = b.bet_id WHERE a.bet_id = ? and a.bres_result <> \'\'  order by a.bres_open_time desc limit 1',[ $betId]);

        return $this->getRow();
    }
    //获取每个采种的所有记录
    public function getResult($betId, $num, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 1 order by bres_id desc limit ? ', [$betId, $num]);

        return $this->getAll();
    }
    //获取采种的所有开奖记录总数
    public function getAllResult($betId, $fields = '*')
    {
        $this->query('SELECT count(*) as total FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 1', [$betId]);

        return $this->getRow();
    }
    //获取大单总和
    public function total($betId, $begintime, $endtime)
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bet_id = ? AND (bres_created_time > ? AND bres_created_time < ?) AND bres_plat_isopen = 1', [$betId, $begintime, $endtime]);

        return $this->getAll();
    }

    public function getIssueByBetId($betId, $startTime, $endTime)
    {
        $this->query('SELECT bres_periods FROM ' . $this->table . ' WHERE bet_id = ?  AND bres_plat_open_time between ? AND ? AND bres_plat_isopen = 1 ORDER BY bres_open_time DESC', [$betId, $startTime, $endTime]);

        return $this->getAll();
    }


    public function getResultNum($betId, $issue)
    {
        $this->query('SELECT bres_result FROM ' . $this->table . ' where bet_id = ? AND bres_periods = ?  limit 1 ',[ $betId, $issue ]);
        return $this->getRow();
    }

    public function getLastResult($betId, $status)
    {
        $where = ' AND bres_plat_isopen = 1';
        $data = [$betId];
        if ($status)
        {
            $where .= ' AND bres_plat_isopen = ?';
            array_push($data, $status);
        }

        $this->query('SELECT * FROM ' . $this->table . ' WHERE bet_id = ? ' . $where . ' ORDER BY bres_created_time DESC, bres_id DESC LIMIT 1', $data);

        return $this->getRow();
    }

    public function lists($betId, $num)
    {
        $this->query('SELECT bres_id, bres_result, bres_plat_isopen, bres_periods, bres_memo FROM ' . $this->table . ' WHERE bet_id = ? AND bres_open_time < ? ORDER BY bres_id DESC LIMIT ?', [$betId, time(), $num]);
        return $this->getAll();
    }

    public function tolists($betId, $bresid, $num)
    {
        $this->query('SELECT bres_id, bres_result, bres_plat_isopen, bres_periods, bres_memo FROM ' . $this->table . ' WHERE bet_id = ? AND bres_id < ? ORDER BY bres_id DESC LIMIT ?', [$betId, $bresid, $num]);
        return $this->getAll();
    }

    public function getBresId($betId, $issue)
    {
        $this->query('SELECT bres_id, bres_result, bres_plat_isopen, bres_periods, bres_memo FROM ' . $this->table . ' WHERE bet_id = ? AND bres_periods = ? ORDER BY bres_id DESC LIMIT 1', [$betId, $issue]);
        return $this->getAll();
    }

    public function addPreRec($values)
    {
        $sql = 'INSERT INTO le_bets_result(bet_id, bres_periods, bres_created_time, bres_open_time)VALUES' . implode(',', $values);
        $this->query($sql);
	return $this->lastInsertId();
    }

    public function addtPreRec($values)
    {
        $sql = 'INSERT INTO le_bets_result(bet_id, bres_periods, bres_created_time, bres_open_time, bres_plat_isopen )VALUES' . implode(',', $values);

        $this->query($sql);
    return $this->lastInsertId();
    }

    public function getNextPerids($betId, $nums = 1, $perid)
    {
        $nums = intval($nums);
        $this->query("SELECT bet_id,bres_periods, bres_open_time FROM {$this->table} WHERE bet_id = ? AND bres_open_time > ? AND bres_periods > ? ORDER BY bres_open_time asc LIMIT ?", [$betId, time(), $perid, $nums]);
        return $this->getAll();
    }

    public function getNextTrackPerids($betId, $nums = 1, $perid)
    {
        $time = strtotime(date('Y-m-d'))+86400;
        $nums = intval($nums);
        $this->query("SELECT bet_id,bres_periods, bres_open_time FROM {$this->table} WHERE bet_id = ? AND bres_open_time > ? AND bres_periods > ? AND bres_open_time < ? ORDER BY bres_open_time asc LIMIT ?", [$betId, time(), $perid, $time, $nums]);
        return $this->getAll();
    }

    public function getNextAllPerids($betId, $nums = 1, $perid,$intval)
    {
        $nums = intval($nums);
        $this->query("SELECT bet_id,bres_periods, bres_open_time FROM {$this->table} WHERE bet_id = ? AND bres_open_time > ? AND bres_periods > ? ORDER BY bres_open_time asc LIMIT ?", [$betId, time()+intval($intval), $perid, $nums]);
        return $this->getAll();
    }

    public function listinfo($betId, $page, $nums = 10)
    {
        $index = ($page - 1) * $nums;

        $this->query("SELECT bres_id, bres_periods, bres_plat_open_time, bres_plat_isopen, bres_result, bres_memo FROM {$this->table} WHERE bet_id = ? AND bres_result <> '' ORDER BY bres_id DESC LIMIT ?, ?", [$betId, $index, $nums]);

        return $this->getAll();
    }

    public function getUnopenExpect($betId)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 3 AND bres_open_time < ? ORDER BY bres_id DESC LIMIT 1',
                [$betId, time()]);

        return $this->getRow();
    }

    public function getOpeningExpect($betId)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 3 AND bres_open_time > ? LIMIT 1',
                [$betId, time()]);

        return $this->getRow();
    }

    public function unOpenExpect($betId,$issue)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 7 AND bres_periods = ? LIMIT 1',
                [$betId, $issue]);

        return $this->getRow();
    }

    public function getExpectInfo($betId,$issue)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? AND bres_plat_isopen = 5 AND bres_periods = ? LIMIT 1',
                [$betId, $issue]);

        return $this->getRow();
    }

    public function getAbnormalExpectInfo($betId,$issue)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? AND bres_periods = ? LIMIT 1',
                [$betId, $issue]);

        return $this->getRow();
    }

    public function getOpeningCodeById($bresId)
    {
        $this->query('SELECT bet_id, bres_result, bres_memo FROM ' . $this->table . ' WHERE bres_id = ? AND bres_plat_isopen = 5 AND bres_result <> "" LIMIT 1',
                [$bresId]);

        return $this->getRow();
    }

    public function setOpeningById($bresId)
    {
        $this->update(['bres_plat_isopen' => 5], ['condition' => 'bres_id = ? AND bres_plat_isopen = 3', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setAbnormalOpeningById($bresId)
    {
        $this->update(['bres_plat_isopen' => 5], ['condition' => 'bres_id = ? AND bres_plat_isopen = 7', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setOpenedById($bresId)
    {
        $this->update(['bres_plat_isopen' => 5], ['condition' => 'bres_id = ? AND bres_plat_isopen = 7', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setAbnorById($bresId)
    {
        $this->update(['bres_plat_isopen' => 7], ['condition' => 'bres_id = ? AND bres_plat_isopen = 5', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setNoOpen($bresId)
    {
        $this->update(['bres_plat_isopen' => 1], ['condition' => 'bres_id = ?', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setGuanOpenedById($betid, $issue)
    {
        $this->update(['bres_plat_isopen' => 5], ['condition' => 'bet_id = ? AND bres_plat_isopen = 7 AND bres_periods = ?', 'values' => [$betid, $issue]]);
        return $this->affectRow();
    }

    public function setResultById($bresId, $result, $memo)
    {
        $this->update(['bres_result' => $result, 'bres_memo' => $memo], ['condition' => 'bres_id = ? AND bres_result = "" AND bres_memo = ""', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setResultByIssue($betId, $issue, $result, $memo)
    {
        $this->update(['bres_result' => $result, 'bres_memo' => $memo], ['condition' => 'bet_id = ? AND bres_result = "" AND bres_memo = "" AND bres_periods = ? ', 'values' => [$betId,$issue]]);
        return $this->affectRow();
    }

    public function settingResultById($bresId, $result, $memo, $time)
    {
        $this->update(['bres_result' => $result, 'bres_memo' => $memo,'bres_plat_isopen' => 1,'bres_open_time' => $time-20], ['condition' => 'bres_id = ? AND bres_result = "" AND bres_memo = ""', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function setExceptionById($bresId)
    {
        $this->update(['bres_plat_isopen' => 7], ['condition' => 'bres_id = ? AND bres_plat_isopen = 5', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    //获取异常
    public function getAbnormal()
    {
        $this->query('SELECT bres_id, bet_id, bres_periods,bres_plat_isopen FROM ' . $this->table . ' WHERE (bres_plat_isopen = 7 AND
        (bres_open_time+600)<UNIX_TIMESTAMP(now())) or (bres_plat_isopen = 5 AND
        (bres_open_time+600)<UNIX_TIMESTAMP(now())) ORDER BY bres_id ');

        return $this->getAll();
    }

    public function getAbnormalDetailByBetid($betid)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? and  bres_plat_isopen = 7 ORDER BY bres_id ', [$betid]);

        return $this->getAll();
    }

    public function getLastExpectByBetId($betId)
    {
        $this->query('SELECT bres_periods, bres_open_time FROM ' . $this->table . ' WHERE bet_id = ? ORDER BY bres_id DESC LIMIT 1', [$betId]);
        return $this->getRow();
    }

    public function setUpdateResultById($bresId)
    {
        $this->update(['bres_plat_isopen' => 1], ['condition' => 'bres_id = ? AND bres_plat_isopen = 7', 'values' => [$bresId]]);
        return $this->affectRow();
    }

    public function getAllAbnormal($betId)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods, bres_open_time FROM ' . $this->table . ' WHERE bet_id = ? and bres_plat_isopen = 7  ORDER BY bres_id DESC LIMIT 1000', [$betId]);
        return $this->getAll();
    }

    public function getGAbnormal($betId, $issue)
    {
        $this->query('SELECT bres_id, bet_id, bres_periods FROM ' . $this->table . ' WHERE bet_id = ? and bres_periods = ?  ORDER BY bres_id DESC LIMIT 1', [$betId, $issue]);
        return $this->getRow();
    }

    //获取超过当前时间且超过截止时间的未开奖期号信息
    public function getBetsNext($ids, $interval, $fields)
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bres_plat_isopen = 3 AND bres_open_time > ? AND bet_id IN (' . $this->generatePhForIn(count($ids)) . ') GROUP BY bet_id', array_merge([$_SERVER['REQUEST_TIME'] + $interval], $ids));

        return $this->getAll();
    }
}
