<?php

class SysConfigModel extends ModelBase
{

    protected $table = 'le_sys_conf';

    public function getSysConfig($scId, $fields)
    {
        $where = '';
        $data = [];
        if ($scId)
        {
            $where .= ' WHERE sc_id = ?';
            array_push($data, $scId);
        }

        $this->query('SELECT ' . $fields . ' FROM '. $this->table . $where, $data);

        return $scId ? $this->getRow() : $this->getAll();
    }
    /**
     * 根据配置id获取配置的提现配置信息
     * @param type $scId
     * @param type $scId
     * @return type
     */
    public function getRechargeLimit($scId, $id)
    {
        $this->query(' SELECT * FROM ' . $this->table . ' where sc_id = ? or sc_id = ? ', [$scId,$id]);
        return $this->getAll();
    }

}
