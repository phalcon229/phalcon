<?php

class RechargeModel extends ModelBase
{

    protected $table = 'le_user_recharge';

    public function __construct()
    {
        parent::__construct();
    }
    public function add($uId, $name, $orderNum,$createTime,$money,$poor,$status,$source,$paySource,$payOrderNum,$updateTime,$activityId)
    {
        // 1、插入充值表，生成id
        $this->insert([
            'ure_id'=>"",
            'u_id' => $uId,
            'u_name' => $name,
            'ure_sn' => $orderNum,
            'ure_created_time' =>$createTime,
            'ure_money' => $money,
            'ure_gift_money' => $poor,
            'ure_status' =>$status,
            'ure_pay_way' =>$source,
            'ure_pay_from' =>$paySource,
            'ure_way_sn' =>$payOrderNum,
            'ure_updated_time'=>$updateTime,
            'ure_activity_id'=>$activityId,

        ]);

        $ureId = $this->lastInsertId();

        if(!$ureId)
        {
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        return $ureId;
    }

    public function getFinanceNums($type)
    {
        $where = '';
        $where = $type == 1 ? ' where ure_pay_chan <> 0 ':' where ure_pay_chan = 0 ';

        $this->query(' SELECT COUNT(ure_id) AS total FROM ' . $this->table . $where .' ');
        return $this->getRow()['total'];
    }

    public function getFinanceLimit($page, $perPage, $type, $fields = '*')
    {
        $index = ($page - 1) * $perPage;
        $where = '';
        $where = $type == 1 ? ' where ure_pay_chan <> 0 ':' where ure_pay_chan = 0 ';

        $this->query(' SELECT ' . $fields . ' FROM ' . $this->table . $where .' ORDER BY `ure_id` DESC LIMIT ' . $index . ', ' . $perPage );
        return $this->getAll();
    }
    public function getRechargeByName($page, $perPage, $type, $value, $fields = '*')
    {
        $index = ($page - 1) * $perPage;
        $data = '%' . $value . '%';
        $where = '';
        $where = $type == 1 ? ' where ure_pay_chan = 1 ':' where ure_pay_chan = 0 ';
        $where .= ' AND u_name LIKE ?';

        $this->query(' SELECT ' . $fields . ' FROM ' . $this->table . $where .' ORDER BY `ure_id` DESC LIMIT ' . $index . ', ' . $perPage, [$data]);
        return $this->getAll();
    }

    public function getRechargeByNameNums($type, $value, $fields = '*')
    {
        $where = '';
        $where = $type == 1 ? ' where ure_pay_chan = 1 ':' where ure_pay_chan = 0 ';
        $where .= ' AND u_name LIKE ?';
        $data = '%' . $value . '%';

        $this->query(' SELECT COUNT(ure_id) AS total FROM ' . $this->table . $where .' LIMIT 1 ',[$data]);
        return $this->getRow()['total'];
    }

    public function refuse($ureId)
    {
        return $this->update(['ure_status' => 5], ['condition' => ' ure_status = 1 and ure_id = ? ', 'values' => [$ureId]] );
    }

    /**
     * 汇款/补单前状态判断
     * @param  [type] $ureId [description]
     * @return [type]        [description]
     */
    public function getDataByUreId($ureId)
    {
        $this->query(' SELECT u_id, ure_money, ure_gift_money,ure_sn,ure_memo FROM ' . $this->table . ' where ure_id = ? and ure_status = 1 LIMIT 1 ', [$ureId]);
        return $this->getRow();
    }

    /**
     * 汇款充值/补单
     * @param  [type] $ureId [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function recharge($ureId, $data)
    {
        $this->begin();

        // 修改订单状态
        if(!$this->update(['ure_status' => 3, 'ure_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => ' ure_id = ? ', 'values' => [$ureId] ]))
        {
            $this->rollback();
            throw new ModelException(' update ' . $this->table . ' failed. sql:' . $this->sql);
        }

        $this->table = 'le_user_wallet';
        //获取钱包金额
        $this->query(' SELECT w_money, w_history_recharge FROM ' . $this->table . ' where  u_id = ? LIMIT 1 ', [$data['u_id']]);
        if(!$wall = $this->getRow())
        {
            $this->rollback();
            throw new ModelException(' select from ' . $this->table . ' failed. sql:' . $this->sql);
        }

        //若历史充值为0，则代理表的上级有效人数加1
        if($wall['w_history_recharge']==0)
        {
            $this->table = 'le_user_agent';
            $this->query(' SELECT ua_u_id FROM ' . $this->table . ' where  u_id = ? LIMIT 1 ', [$data['u_id']]);
            if(!$uaUid = $this->getRow())
            {
                $this->rollback();
                throw new ModelException(' select from ' . $this->table . ' failed. sql:' . $this->sql);
            }
            if(!$this->update(['ua_good_user_nums' => 1],['condition' => ' u_id = ? ', 'values' => [$data['u_id']]]))
            {
                $this->rollback();
                throw new ModelException(' update ' . $this->table . ' failed. sql:' . $this->sql);
            }
        }

        //修改用户钱包(备注:历史充值不包含赠送)
        $money = $data['ure_money'] + $data['ure_gift_money'];
        if(!$this->update(['w_money' => $money + $wall['w_money'], 'w_history_recharge' => $data['ure_money'] + $wall['w_history_recharge'], 'w_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => ' u_id = ? ', 'values' => [$data['u_id']]]))
        {
            $this->rollback();
            throw new ModelException(' update ' . $this->table . ' failed. sql:' . $this->sql);
        }

        //写出入账的记录
        $this->table = 'le_user_wallet_recorded';
        $lastid =$this->insert([
                'u_id' => $data['u_id'],
                'uwr_money' => $data['ure_money'],
                'uwr_type' => 1,
                'uwr_bussiness_id' => $ureId,
                'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                'uwr_memo'=> '充值',
                'uwr_balance' => $wall['w_money']+$data['ure_money']
            ]);

        if(!$lastid)
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        //判断是否用系统赠送金额,写入日志
        if($data['ure_gift_money'] > 0){
            $last =$this->insert([
                    'u_id' => $data['u_id'],
                    'uwr_money' => $data['ure_gift_money'],
                    'uwr_type' => 9,
                    'uwr_bussiness_id' => $ureId,
                    'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                    'uwr_memo'=> '充值系统赠送',
                    'uwr_balance' => $wall['w_money']+$data['ure_gift_money']
                ]);

            if(!$last)
            {
                $this->rollback();
                throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
            }
        }

        $this->commit();
        return true;
    }

    public function create($data)
    {
        return $this->insert($data);
    }

    public function ChangeMemo($id, $memo)
    {
        return $this->update(['ure_memo' => $memo, 'ure_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => 'ure_id = ? AND ure_status = 1 AND ure_pay_way = 7', 'values' => [$id]]);

    }

    public function detailBySn($orderSn)
    {
        $this->query("SELECT * FROM {$this->table} WHERE ure_sn = ? LIMIT 1", [$orderSn]);
        return $this->getRow();
    }

    public function confirmRecharge($orderSn, $thirdSn, $status, $memo)
    {
        $this->begin();

        // 获取订单信息
        if (!$order = $this->detailBySn($orderSn)) {
            $this->rollback();
            return false;
        }

        // 订单状态
        if ($order['ure_status'] != 1) {
            $this->rollback();
            return false;
        }

        $money = $order['ure_money'];
        $uId = $order['u_id'];

        // step 1 修改订单状态
        if($memo == '')
        {
            if (!$this->update(['ure_status' => $status, 'ure_way_sn' => $thirdSn, 'ure_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => 'ure_sn = ? AND ure_status = 1', 'values' => [$orderSn]]))
            {
                $this->rollback();
                return false;
            }
        }
        else
        {
            if (!$this->update(['ure_status' => $status, 'ure_memo' => $memo, 'ure_way_sn' => $thirdSn, 'ure_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => 'ure_sn = ? AND ure_status = 1', 'values' => [$orderSn]]))
            {
                $this->rollback();
                return false;
            }
        }

        $this->query('SELECT w_money, w_status,w_history_recharge, w_history_spent, w_withdraw_consume FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$order['u_id']]);
        if (!$wallet = $this->getRow())
            throw new ModelException('用户不存在或已冻结');

        //若历史充值为0，则代理表的上级有效人数加1
        if($wallet['w_history_recharge']==0)
        {
            $allId = $this->getAgentInfoByUid($order['u_id']);
            $len = count($allId);
            if($len>1){
                $allId = implode(',', $allId);
                $this->query('UPDATE le_user_agent SET ua_good_user_nums = ua_good_user_nums + 1 WHERE u_id in ('.$allId.')');
                if(!$this->affectRow())
                {
                    $this->rollback();
                    throw new ModelException(' update le_user_agent failed. sql:' . $this->sql);
                }
            }
        }

        //统计入款
        $date = strtotime(date('Y-m-d'));
        $agentModel = new UserAgentModel();
        $uIds = $agentModel->getAgentInfoByUid($order['u_id']);
        foreach ($uIds as $key => $value) {
            $this->query('SELECT ar_id FROM le_agent_report WHERE ar_date = ? AND u_id = ? AND bet_id = 0 LIMIT 1', [$date, $value['u_id']]);
            $row = $this->getRow();
            if(!$row)
            {
                $this->query('INSERT INTO le_agent_report SET ar_date = ?, u_id = ?, u_name = ?, bet_id = 0, ar_parent_uid = ?, u_type = ?, ar_team_recharge_money = ?',[$date, $value['u_id'], $value['u_name'], $value['ua_u_id'], $value['ua_type'], $money]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            else
            {
                $this->query('SELECT ar_id FROM le_agent_report WHERE ar_id = ? LIMIT 1 FOR UPDATE',  [$row['ar_id']]);
                $this->query('UPDATE le_agent_report SET ar_team_recharge_money = ar_team_recharge_money + ? WHERE ar_id = ?',
                        [$money, $row['ar_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }
            }
        }
        //入款统计结束

        // step 2 修改用户余额
        if($wallet['w_withdraw_consume']<$wallet['w_history_spent'])
        {
            $this->query("UPDATE le_user_wallet SET w_money = w_money + ?, w_history_recharge = w_history_recharge + ? , w_withdraw_consume = w_history_spent + ? WHERE u_id = ?", [
                $money, $money, $money, $uId
            ]);
        }
        else
        {
            $this->query("UPDATE le_user_wallet SET w_money = w_money + ?, w_history_recharge = w_history_recharge + ? , w_withdraw_consume = w_withdraw_consume + ? WHERE u_id = ?", [
                $money, $money, $money, $uId
            ]);
        }
        if (!$this->affectRow())
        {
            $this->rollback();
            return false;
        }

        // step 3 增加钱包日志
        $this->table = 'le_user_wallet_recorded';
        $this->insert([
            'u_id' => $uId,
            'uwr_money' => $money,
            'uwr_type' => 1,
            'uwr_bussiness_id' => $order['ure_id'],
            'uwr_created_time' => $_SERVER['REQUEST_TIME'],
            'uwr_memo' => $orderSn,
            'uwr_balance' => $wallet['w_money']+$money
        ]);

        // step 4 增加每日报表统计
        $teamModel = new TeamModel();
        if (!$teamModel->updateWithdrawOrRecharge($uId, 0, $money))
        {
            $this->rollback();
            return false;
        }

        // step 5 是否参与活动
        if ($aId = $order['ure_activity_id'])
        {
            $actModel = new ActivityModel();
            $actModel->join(3, $aId, $uId, $order['ure_id']);
        }

        $this->commit();
        return true;
    }

    public function getAgentInfoByUid($uId)
    {
        $func = function($uId) {
            $this->query('SELECT ua_u_id FROM le_user_agent WHERE u_id = ? LIMIT 1', [$uId]);
            $rs = $this->getRow();
            if(!$rs)
                return false;
            else
                return $rs;
        };

        $res = [];
        $rs = $func($uId);
        if($rs)
            $res[] = $rs['ua_u_id'];
        else
            return false;

        while($rs && $rs['ua_u_id'] > 0)
        {
            $rs = $func($rs['ua_u_id']);
            if($rs)
                $res[] = $rs['ua_u_id'];
        }
        return $res;
    }

    public function newOrder($time1, $time2)
    {
        $data1 = $data2= [];
        $whur = $whuw='';
        if (!empty($time1)) {
            $whur = ' WHERE ure_created_time > ? ';
            array_push($data1, $time1);
        }
        if (!empty($time2)) {
            $whuw = ' WHERE uw_created_time > ? ';
            array_push($data2, $time2);
        }
        $sql = 'SELECT ure_id, ure_created_time,ure_pay_chan FROM '. $this->table . $whur .' ORDER BY ure_created_time DESC LIMIT 1';
        $this->query($sql,$data1);
        $res['recharge'] = $this->getRow();

        $sql1 = 'SELECT uw_id, uw_created_time FROM le_user_withdraw ' . $whuw .' ORDER BY uw_created_time DESC LIMIT 1';
        $this->query($sql1,$data2);
        $res['withdraw'] = $this->getRow();
        return $res;
    }
}