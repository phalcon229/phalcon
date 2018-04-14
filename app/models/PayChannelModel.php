<?php

class PayChannelModel extends ModelBase
{
    protected $table = 'le_pay_channel_conf';

    public function __construct()
    {
        parent::__construct();
    }

    public function getLists($type)
    {
        $this->query("SELECT * FROM {$this->table} WHERE pcc_status = 1 AND pcc_type = ?", [$type]);

        return $this->getAll();
    }

    public function detailById($channelId)
    {
        $this->query("SELECT pcc_id, pcc_flag, pcc_type, pcc_min, pcc_max, pcc_memo FROM {$this->table} WHERE pcc_status = 1 AND pcc_id = ?", [$channelId]);

        return $this->getRow();
    }

    public function getAllChannels($status, $fields)
    {
        $where = ' WHERE 1 = 1';
        $data = [];
        if ($status)
        {
            $where .= ' AND pcc_status = ?';
            array_push($data, $status);
        }

        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . $where, $data);

        return $this->getAll();
    }
}