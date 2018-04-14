<?php

class RecommendModel extends ModelBase
{

    protected $table = 'le_user_recommend';

    public function __construct()
    {
        parent::__construct();
    }

    public function getRecommendInfoByUserId($uId,$start_day,$end_day, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and (ur_created_time< ? and ur_created_time> ? )', [$uId,$end_day,$start_day]);

        return $this->getAll();
    }

    public function getRecommendType($uId, $fields = 'ur_type')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getAll();
    }

    public function getTotal($uId)
    {
        $this->query('SELECT COUNT(ur_id) AS total FROM ' . $this->table . ' WHERE ur_status =1 AND u_id = ? LIMIT 1', [$uId]);
        return $this->getOne();
    }

    public function getLists($uId, $start, $perPage, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ur_status = 1 AND u_id = ? ORDER BY ur_id DESC LIMIT ?, ?', [$uId, $start, $perPage]);
        return $this->getAll();
    }

    public function addLink($uId, $code, $type, $rate)
    {
        return $this->insert(
            [
                'u_id' => $uId,
                'ur_code' => $code,
                'ur_type' => $type,
                'ur_created_time' => $_SERVER['REQUEST_TIME'],
                'ur_status' => 1,
                'ur_fandian' => $rate
            ]
        );
    }

    public function getDetail($urId)
    {
        $this->query('SELECT ur_id, u_id, ur_type, ur_fandian FROM ' . $this->table . ' WHERE ur_status = 1 AND ur_id = ? LIMIT 1', [$urId]);
        return $this->getRow();
    }

    public function getDetailByUrid($urId)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE ur_status = 1 AND ur_id = ? LIMIT 1', [$urId]);
        return $this->getRow();
    }

    public function updateLink($uId, $urId, $type, $rate)
    {
        $this->update(['ur_type' => $type, 'ur_fandian' => $rate, 'ur_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => 'ur_status = 1 AND u_id = ? AND ur_id = ?', 'values' => [$uId, $urId]]);

        return $this->affectRow();
    }

    public function delLink($uId, $urId)
    {
        $this->update(['ur_status' => 3], ['condition' => 'ur_status = 1 AND u_id = ? AND ur_id = ?', 'values' => [$uId, $urId]]);
        return $this->affectRow();
    }

    public function increVisit($code)
    {
        $this->query("UPDATE {$this->table} set ur_visitor_nums = ur_visitor_nums + 1 WHERE ur_code = ?", [$code]);
        return $this->affectRow();
    }

    public function detailByCode($code)
    {
        $this->query('SELECT u_id, ur_type, ur_fandian FROM ' . $this->table . ' WHERE ur_status = 1 AND ur_code = ? LIMIT 1', [$code]);
        return $this->getRow();
    }

    public function increReg($code)
    {
        $this->query("UPDATE {$this->table} set ur_reg_nums = ur_reg_nums + 1 WHERE ur_code = ?", [$code]);
        return $this->affectRow();
    }
}
