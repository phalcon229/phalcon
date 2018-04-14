<?php

class BankModel extends ModelBase
{

    protected $table = 'le_user_bank_cards';

    public function __construct()
    {
        parent::__construct();
    }

    public function addBank($uId, $bankId, $uname,$province,$city,$number,$name,$phone)
    {
        // 1、插入银行表，生成id
        $this->insert([
            'ubc_id'=>"",
            'u_id' => $uId,
            'ubc_bank_id' => $bankId,
            'ubc_name' => $uname,
            'ubc_province' =>$province,
            'ubc_city' => $city,
            'ubc_number' => $number,
            'ubc_status' =>1,
            'ubc_created_time' =>time(),
            'ubc_updated_time' =>time(),
            'ubc_uname'=>$name,
            'ubc_mobi'=>$phone,
        ]);

        $ubcId = $this->lastInsertId();

        if(!$ubcId)
        {
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        return $ubcId;
    }

    public function getInfoByUserId($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND ubc_status = 1', [$uId]);

        return $this->getAll();
    }

    public function getBankInfoByUserId($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? ORDER BY ubc_status asc', [$uId]);

        return $this->getAll();
    }

    public function getUpdateInfo($ubcId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ubc_id = ? AND ubc_status = 1',[ $ubcId]);

        return $this->getAll();
    }

    public function updateBank($uId, $bankId, $name,$province,$city,$number,$ubcId)
    {
        $res=$this->update([
                'ubc_id'=>$ubcId,
                'u_id' => $uId,
                'ubc_bank_id' => $bankId,
                'ubc_name' => $name,
                'ubc_province' =>$province,
                'ubc_city' => $city,
                'ubc_number' => $number,
                'ubc_status' =>1,
                'ubc_created_time' =>time(),
                'ubc_updated_time' =>time(),
            ],['condition' => 'ubc_id = ?', 'values' => [$ubcId]]);
        return $res;
    }

    public function delBank($ubcId)
    {
        $res=$this->update([
            'ubc_status'=>3,
        ],['condition' => 'ubc_id = ?', 'values' => [$ubcId]]);
        return $res;
    }

    public function getBank($uId)
    {
        $this->query('SELECT ubc_id,ubc_number FROM ' . $this->table . ' WHERE u_id = ? AND ubc_status = 1',[ $uId]);

        return $this->getAll();
    }

    public function getBankInfo($uId,$bankId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ubc_id = ? AND u_id = ? AND ubc_status = 1', [$bankId,$uId]);

        return $this->getRow();
    }

    public function getName($ubcId, $fields = 'ubc_uname')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ubc_id = ? ',[ $ubcId]);

        return $this->getRow();
    }

    public function getBankById($bankId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ubc_id = ? AND ubc_status = 1', [$bankId]);

        return $this->getRow();
    }
}
