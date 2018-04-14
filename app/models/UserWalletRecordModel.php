<?php

class UserWalletRecordModel extends ModelBase
{

    protected $table = 'le_user_wallet_recorded';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取账变信息/没有下级的情况/第一次加载默认10条
     * @param type $name
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getRecordInfo($uId, $startDay, $endDay, $type, $num)
    { 
        $num = ($num-1) * 10;
        if($startDay == '')
        { 
            if($type == 0)
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ? order by a.uwr_id desc limit ?,10', [$uId, $num]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ? and a.uwr_type = ? order by a.uwr_id desc limit ?,10', [$uId, $type, $num]);

                return $this->getAll();
            }
        }
        else
        {
            if($type == 0)
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ? and (a.uwr_created_time>= ? and a.uwr_created_time < ?) order by a.uwr_id desc limit ?,10', [$uId, $startDay, $endDay, $num]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ? and a.uwr_type = ? and (a.uwr_created_time>= ? and a.uwr_created_time < ?) order by a.uwr_id desc limit ?,10', [$uId, $type, $startDay, $endDay, $num]);

                return $this->getAll();
            }
        }


    }
    /**
     * 获取所有的账变信息/没有下级的情况
     * @param type $uId
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getTotalInfo($uId, $startDay, $endDay, $type, $fields = '*')
    {
        if(empty($startDay))
        { 
            if($type == 0)
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ?', [$uId]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id = ? and a.uwr_type = ?', [$uId, $type]);

                return $this->getAll();
            }
        } 
        else 
        {
            if($type == 0)
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where u_id = ? and (uwr_created_time>= ? and uwr_created_time < ?) order by uwr_id desc', [$uId, $startDay, $endDay]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' where u_id = ? and uwr_type = ? and (uwr_created_time>= ? and uwr_created_time < ?) order by uwr_id desc', [$uId, $type, $startDay, $endDay]);

                return $this->getAll();
            }  
        }
        


    }
        /**
     * 获取账变信息/有下级的情况
     * @param type $ids
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getRecordNext($ids, $startDay, $endDay, $type, $num, $fields = '*')
    {
        $num = ($num-1) * 10;
        if(empty($startDay))
        {
            if($type == 0)
            {
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ') order by a.uwr_id desc limit ?,10', [$num]);

                return $this->getAll();
            }
            else
            {
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ') and a.uwr_type = ? order by a.uwr_id desc limit ?,10', [$type,$num]);

                return $this->getAll();
            }
        }
        else
        {
            if($type == 0)
            {
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ') and (a.uwr_created_time>= ? and a.uwr_created_time < ?) order by a.uwr_id desc limit ?,10', [$startDay, $endDay, $num]);

                return $this->getAll();
            }
            else
            {
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ') and a.uwr_type = ? and (a.uwr_created_time>= ? and a.uwr_created_time < ?) order by a.uwr_id desc limit ?,10', [$type, $startDay, $endDay, $num]);

                return $this->getAll();
            } 
        }
        
    }

    public function getRecordNextTotal($ids, $startDay, $endDay, $type, $fields = '*')
    {
        if(empty($startDay))
        {
            if($type == 0){
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ')');

                return $this->getAll();
            }
            else
            {
                $ids = implode(',', $ids);

                $this->query('SELECT a.*,b.u_name FROM ' . $this->table . ' a INNER JOIN le_users b on a.u_id = b.u_id and a.u_id in (' . $ids . ') and a.uwr_type = ?',[$type]);

                return $this->getAll();
            }
        }
        else
        {
            if($type == 0)
            {
                $ids = implode(',', $ids);

                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id in ('.$ids.') and (uwr_created_time>= ? and uwr_created_time < ?) order by uwr_id desc', [$startDay, $endDay]);

                return $this->getAll();
            }
            else
            {
                $ids = implode(',', $ids);

                $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id in ('.$ids.') and uwr_type = ? and (uwr_created_time>= ? and uwr_created_time < ?) order by uwr_id desc', [$type, $startDay, $endDay]);

                return $this->getAll();
            }  
        }
    }
    
}
