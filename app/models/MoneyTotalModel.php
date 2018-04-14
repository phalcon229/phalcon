<?php

class MoneyTotalModel extends ModelBase
{

    protected $table = 'le_money_total';

    public function __construct()
    {
        parent::__construct();
    }

    public function addCount($date, $give, $recharge,$withdraw,$platMoney,$platEarn,$afterMoney)
    {
        // 1、金额统计表
        $this->insert([
            'st_id'=>"",
            'st_date' => $date,
            'st_give' => $give,
            'st_recharge' => $recharge,
            'st_withdraw' =>$withdraw,
            'st_before_money' => $platMoney,
            'st_plat_earn' => $platEarn,
            'st_after_money' => $afterMoney,
        ]);

        $sId = $this->lastInsertId();

        if(!$sId)
        {
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        return $sId;
    }
    
    public function getInfoByTime($start,$end)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE (st_date >= ? and st_date <= ?)',[$start,$end]);

        return $this->getAll();
    }
}
