<?php

class UsersModel extends ModelBase
{

    protected $table = 'le_users';

    public function __construct()
    {
        parent::__construct();
    }

    public function reg($type, $regType, $uNick, $uName, $pwd, $rate, $parentUid = 0, $unionid)
    {
        $this->begin();
        // 1、插入用户表，生成id
        $this->table = 'le_users';
        $this->insert([
            'u_name' => $uName,
            'u_nick' => $uNick,
            'u_pass' => $pwd,
            'u_type' => $type,
            'u_reg_time' => $_SERVER['REQUEST_TIME'],
            'u_reg_ip' => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])),
            'u_reg_type' => $regType,
            'u_last_ip' => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])),
            'u_mobile' => '',
            'u_email' => '',
            'u_last_time' => $_SERVER['REQUEST_TIME'],
            'u_wx_unionid' => $unionid
        ]);

        $uId = $this->lastInsertId();

        if(!$uId)
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        // 2、插入钱包
        $UserWalletModel = new UserWalletModel;
        $UserWalletModel->insert(['u_id' => $uId, 'w_pass' => '']);
        if (!$UserWalletModel->affectRow())
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $UserWalletModel->getTable() . ' failed. sql:' . $UserWalletModel->getSql());
        }

        // 3、插入代理表
        $parentUname = '';
        if ($parentUid)
        {
            if (!$parent = $this->getInfoByUid($parentUid, 'u_name'))
            {
                $this->rollback();
                throw new ModelException('信息错误');
            }
            $parentUname = $parent['u_name'];
        }

        $this->table = 'le_user_agent';
        $this->insert([
            'u_id' => $uId,
            'u_name' => $uName,
            'ua_u_id' => $parentUid,
            'ua_u_name' => $parentUname,
            'ua_rate' => $rate,
            'ua_created_time' => $_SERVER['REQUEST_TIME'],
            'ua_status' => 1,
            'ua_type' => $type,
            'ua_reg_type' => $regType
        ]);

        $userAgentModel = new \UserAgentModel;
        $agentInfo = $userAgentModel->getAgentInfoByUid($uId);
        $len = count($agentInfo);

        // 4、更新注册人数
//        if ($regType == 5 && $parentUid)
        if ($regType !== 1 && $len > 1)
        {
            foreach ($agentInfo as $key=>$value)
            {
                if($value['ua_u_id'] !=0)
                {
                    $this->query("UPDATE le_user_agent SET ua_reg_nums = ua_reg_nums + 1 WHERE u_id = ?", [$value['ua_u_id']]);
                    if (!$this->affectRow())
                    {
                        $this->rollback();
                        throw new ModelException($this->getSql() . $value['ua_u_id']);
                    }
                }
            }
        }

        // 5、更新总人数
//        if ($parentUid)
        if ($len > 1)
        {
            foreach ($agentInfo as $key=>$value)
            {
                if($value['ua_u_id'] !=0)
                {
                    $this->query("UPDATE le_user_agent SET ua_team_num = ua_team_num + 1 WHERE u_id = ?", [$value['ua_u_id']]);
                    if (!$this->affectRow())
                    {
                        $this->rollback();
                        throw new ModelException($this->getSql() . $value['ua_u_id']);
                    }
                }
            }
        }
        // 6、处理注册活动
        //2017/12/14修改->只有微信登录才赠送
        if(strlen((string)$unionid) > 3)
        {
            $this->query("SELECT pa_id FROM le_pay_activity WHERE pa_type = 1 AND pa_status = 1 ORDER BY pa_id DESC LIMIT 1");
            if ($activity = $this->getRow())
            {
                $actModel = new ActivityModel();
                $actModel->join(1, $activity['pa_id'], $uId);
            }
        }

        $this->commit();
        return $uId;
    }

    public function getInfoByName($uName, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_name = ? LIMIT 1', [$uName]);
        return $this->getRow();
    }

    public function getInfoByMobi($mobi, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_mobile = ? LIMIT 1', [$mobi]);
        return $this->getRow();
    }

    public function getInfoByUid($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND u_status = 1 LIMIT 1', [$uId]);
        return $this->getRow();
    }

    public function getUserName($uId, $fields = 'u_name')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getRow();
    }

    public function changePwd($uId, $newPwd)
    {
        return $this->update(['u_pass' => $newPwd], ['condition' => ' u_id = ? ', 'values' => [$uId]]);
    }

    public function changePwdByMobi($info, $newPwd)
    {
        $data=[$info['id'], $info['mobi']];
        if(!empty($info['nick']))
            return $this->update(['u_pass' => $newPwd, 'u_name' => $info['nick']], ['condition' => ' u_id = ? and u_mobile = ? and u_status = 1 ', 'values' => $data]);
        else
            return $this->update(['u_pass' => $newPwd], ['condition' => ' u_id = ? and u_mobile = ? and u_status = 1 ', 'values' => $data]);
    }

    public function getType($uId,$fields = 'u_type')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getRow();
    }

    public function getName($uId,$fields = 'u_name')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getRow();
    }

    public function getAllUser($index, $num, $condition, $value, $fields = ' distinct b.u_id as ubc_id, a.*')
    {
        $where = ' where 1=1';
        $match = [];
        if(!empty($value))
        {
            //1user
            if($condition == 1){
                $where .= " AND a.u_name LIKE ?";
                array_push($match, '%' . $value . '%');
            }

            //3mobi
            if($condition == 7){
                $where .= " AND a.u_id = ? ";
                array_push($match, $value );
            }
        }

        array_push($match, $index);
        array_push($match, $num);

        $this->query('SELECT ' . $fields . ' FROM ' . $this->table .' as a left join le_user_bank_cards as b ON a.u_id = b.u_id AND b.ubc_status = 1 ' . $where . ' ORDER BY a.u_id DESC LIMIT ?, ?', $match);

        return $this->getAll();
    }

    public function getTotalNum($condition, $value)
    {
        $where = ' where 1 = 1';
        $match = [];
        if(!empty($value))
        {
            //1user
            if($condition == 1){
                $where .= " AND u_name LIKE '%".$value."%' ";
                $match['u_name'] = $value;
            }

            //3mobi
            if($condition == 3){
                $where .= " AND u_mobile LIKE '%".$value."%' ";
                $match['u_mobile'] = $value;
            }
        }

        $this->query('SELECT COUNT(u_id) AS total FROM ' . $this->table . $where , $match);

        return $this->getRow();
    }

    public function stop($uId)
    {

        return $this->update(['u_status' => 3], ['condition' => ' u_id = ? and u_status = 1 ', 'values' => [$uId]]);
    }

    public function pass($uId)
    {
        return $this->update(['u_status' => 1], ['condition' => ' u_id = ? and u_status = 3 ', 'values' => [$uId]]);
    }

    public function stopMoney($uId)
    {
        $this->table = 'le_user_wallet';
        return $this->update(['w_status' => 3], ['condition' => ' u_id = ? and w_status = 1 ', 'values' => [$uId]]);
    }

    public function passMoney($uId)
    {
        $this->table = 'le_user_wallet';
        return $this->update(['w_status' => 1], ['condition' => ' u_id = ? and w_status = 3 ', 'values' => [$uId]]);
    }

    public function isUser($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? LIMIT 1', [$uId]);

        return $this->getRow();
    }

    public function getUnick($uId)
    {
        $this->query('SELECT u_nick FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);
        return $this->getRow();
    }

    public function getUid($name)
    {
        $this->query('SELECT u_id FROM ' . $this->table . ' WHERE u_name = ?', [$name]);
        return $this->getRow();
    }

    public function getUserNums($uType, $startDay, $endDay)
    {
        $this->query('SELECT COUNT(u_id) AS total FROM ' . $this->table . ' WHERE u_type = ? AND u_reg_time >= ? AND u_reg_time <= ?', [$uType, $startDay, $endDay]);

        return $this->getOne();
    }

    public function  getAllids()
    {
        $this->query("SELECT u_id FROM le_users ");
        return $this->getAll();
    }

    public function doBase($uId, $fields)
    {
        return $this->update($fields, ['condition' => ' u_id = ? and u_status = 1 ', 'values' => [$uId]]);
    }

    public function doBases($uId, $fields, $pass)
    {
        $this->begin();
        if (!$this->update($fields, ['condition' => ' u_id = ? and u_status = 1 ', 'values' => [$uId]]))
        {
            $this->rollback();
            return false;
        }

        $this->table= 'le_user_wallet';
        if(!$this->update(['w_pass' => $pass], ['condition' => 'u_id = ?', 'values' => [$uId]]))
        {
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }

    public function getUserbyUnionid($unionid)
    {
        $this->query("SELECT u_id, u_name, u_type, u_status FROM le_users WHERE u_wx_unionid = ? LIMIT 1",[$unionid]);
        return $this->getRow();
    }

    public function getUserbyMobi($mobi)
    {
        $this->query("SELECT u_id FROM le_users WHERE u_mobile = ? LIMIT 1",[$mobi]);
        return $this->getRow();
    }


    public function getUserByIds($ids, $fields)
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id IN (' . $this->generatePhForIn(count($ids)) . ')', $ids);
        return $this->getAll();
    }

    public function getManeyUser($ids)
    {
        $this->query('SELECT b.ubc_number,c.* FROM le_user_bank_cards as b right join (SELECT a.* from le_users a WHERE a.u_id in('.$ids.') ) as c on b.u_id = c.u_id ORDER BY b.u_id DESC');

        return $this->getAll();
    }

    public function editMemo($info, $uId)
    {
        $this->update([
                'u_nick' =>$info,
            ],['condition' => 'u_id = ?', 'values' => [$uId]]);
        return $this->affectRow();
    }

    public function updateName($name,$uid)
    {
        $this->update([
                'u_name' =>$name,
                'u_last_ip' => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR'])),
                'u_last_time' => $_SERVER['REQUEST_TIME'],
            ],['condition' => 'u_id = ?', 'values' => [$uid]]);
        return $this->affectRow();
    }

}