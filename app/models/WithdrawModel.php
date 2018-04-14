<?php

class WithdrawModel extends ModelBase
{

    protected $table = 'le_user_withdraw';

    public function __construct()
    {
        parent::__construct();
    }
    public function add($uId, $bankId, $ubcBank,$province,$city,$number,$money,$ubcMobi,$orderSn)
    {
        $this->begin();

        // 1、获取用户钱包信息
        $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
        if (!$wallet = $this->getRow())
        {
            $this->rollback();
            throw new ModelException('用户不存在或已冻结');
        }

        // 2、余额是否足够
        if ($wallet['w_money'] < $money)
        {
            $this->rollback();
            throw new ModelException('当前用户余额不足');
        }

        // 3、扣除可用余额 && 增加冻结金额
        $this->query("UPDATE le_user_wallet SET w_money = w_money - ?, w_freeze_money = w_freeze_money + ?, w_updated_time = ? where u_id = ?", [
                $money, $money, $_SERVER['REQUEST_TIME'],$uId
            ]);
        if (!$this->affectRow())
        {
            $this->rollback();
            throw new ModelException('提现申请失败');
        }

        // 4、插入提现表
        $this->insert([
            'uw_id'=>"",
            'u_id' => $uId,
            'ubc_id' => $bankId,
            'ubc_name' => $ubcBank,
            'ubc_province' =>$province,
            'ubc_city' => $city,
            'ubc_number' => $number,
            'uw_limit' =>$money,
            'uw_created_time' =>time(),
            'uw_status' =>1,
            'uw_updated_time'=>time(),
            'ubc_mobi'=>$ubcMobi,
            'ubc_sn' =>$orderSn
        ]);

        if(!$uwId = $this->lastInsertId())
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        $this->commit();
        return $uwId;
    }

    public function getAllRecharge($index, $num, $condition, $value, $fields = '*')
    {
        $where = ' where 1=1';
        $match = [];
        if(!empty($value))
        {
            //1user
            if($condition == 1){
                $where .= " AND u.u_name LIKE '%".$value."%' ";
                $match['u_name'] = $value;
            }

            //2status
            if($condition == 2){
                $where .= " AND uw.ubc_sn = '" .trim($value)."'";
                $match['uw_status'] = $value;
            }

            //3wall
            if($condition == 3){
                $where .= " AND uw.ubc_number LIKE '%".$value."%' ";
                $match['ubc_number'] = $value;
            }
        }

        $this->query('SELECT uw.*, u.u_name,u.u_nick FROM `le_user_withdraw` AS uw LEFT JOIN `le_users` AS u ON uw.u_id = u.u_id  '.$where.' ORDER BY `uw_id` DESC LIMIT ' . $index . ', ' . $num , $match);

        return $this->getAll();
    }

    public function getRechargeTotal($condition, $value)
    {
        $where = ' where 1=1';
        $match = [];
        if(!empty($value))
        {
            //1user
            if($condition == 1){
                $where .= " AND u.u_name LIKE '%".$value."%' ";
                $match['u_name'] = $value;
            }

            //2status
            if($condition == 2){
                $where .= " AND uw.ubc_sn =  '" .trim($value)."'";
                $match['uw_status'] = $value;
            }

            //3wall
            if($condition == 3){
                $where .= " AND uw.ubc_number LIKE '%".$value."%' ";
                $match['ubc_number'] = $value;
            }
        }

        $this->query('SELECT COUNT(uw_id) AS total FROM `le_user_withdraw` AS uw LEFT JOIN `le_users` AS u ON uw.u_id = u.u_id  '.$where.' ORDER BY `uw_id` DESC LIMIT 1 ' , $match);

        return $this->getRow();
    }

    public function getInfoById($uwId,  $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE uw_id = ?', [$uwId]);

        return $this->getRow();
    }

    public function stop($uwId)
    {
        return $this->update(['uw_status' => 7], ['condition' => ' uw_id = ? and uw_status = 1 ', 'values' => [$uwId]] );
    }

    public function passes($uwId)
    {
        return $this->update(['uw_status' => 9], ['condition' => ' uw_id = ? and uw_status = 1 ', 'values' => [$uwId]] );
    }

    public function changeStat($id, $oldStat, $newStat)
    {
        return $this->update(['uw_status' => intval($newStat)], ['condition' => ' uw_id = ? and uw_status = ?', 'values' => [$id, $oldStat]] );
    }

    /**
     * 提现确认
     * @param  [type] $id   [description]
     * @param  Int $status 状态   3-提现成功   5-提现失败    7-审核取消
     * @return [type]       [description]
     */
    public function confirm($id, $status)
    {
        $this->begin();

        // 获取提现信息
        $this->query("SELECT * FROM le_user_withdraw WHERE uw_id = ?", [$id]);
        if (!$withdraw = $this->getRow())
        {
            $this->rollback();
            throw new ModelException('提现信息不存在');
        }

        // 1、更改提现申请记录状态
        if (!$this->update(['uw_status' => $status], ['condition' => ' uw_id = ? and uw_status = 1', 'values' => [$id]]))
        {
            $this->rollback();
            throw new ModelException('提现失败');
        }

        if ($status == 3)
        {
            // 成功，扣除冻结金额，增加提现历史金额
            $this->query("UPDATE le_user_wallet SET w_freeze_money = w_freeze_money - ?, w_history_withdraw = w_history_withdraw + ?, w_updated_time = ?, w_withdraw_consume = 0, w_history_spent = 0 where u_id = ?", [
                $withdraw['uw_limit'], $withdraw['uw_limit'], $_SERVER['REQUEST_TIME'],$withdraw['u_id']
            ]);
            if (!$this->affectRow())
            {
                $this->rollback();
                throw new ModelException('提现申请失败');
            }
            $this->query('SELECT w_money, w_status, w_freeze_money FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$withdraw['u_id']]);
                if (!$wallet = $this->getRow())
                    throw new ModelException('用户不存在或已冻结');
            //统计出款
            $date = strtotime(date('Y-m-d'));
            $agentModel = new UserAgentModel();
            $uId = $agentModel->getAgentInfoByUid($withdraw['u_id']);
            foreach ($uId as $key => $value) {
                $this->query('SELECT ar_id FROM le_agent_report WHERE ar_date = ? AND u_id = ? AND bet_id = 0 LIMIT 1', [$date, $value['u_id']]);
                $row = $this->getRow();
                if(!$row)
                {
                    $this->query('INSERT INTO le_agent_report SET ar_date = ?, u_id = ?, u_name = ?, bet_id = 0, ar_parent_uid = ?, u_type = ?, ar_team_withdraw_money = ?',[$date, $value['u_id'], $value['u_name'], $value['ua_u_id'], $value['ua_type'], $withdraw['uw_limit']]);

                    if(!$this->lastInsertId())
                    {
                        $this->rollback();
                        return false;
                    }
                }
                else
                {
                    $this->query('SELECT ar_id FROM le_agent_report WHERE ar_id = ? LIMIT 1 FOR UPDATE',  [$row['ar_id']]);
                    $this->query('UPDATE le_agent_report SET ar_team_withdraw_money = ar_team_withdraw_money + ? WHERE ar_id = ?',
                            [$withdraw['uw_limit'], $row['ar_id']]);

                    if($this->affectRow() != 1)
                    {
                        $this->rollback();
                        return false;
                    }
                }
            }
            //出款统计结束
            // 写入财务日志
            $this->table = 'le_user_wallet_recorded';
            $lastid =$this->insert([
                'u_id' => $withdraw['u_id'],
                'uwr_money' => '-' . $withdraw['uw_limit'],
                'uwr_type' => 5,
                'uwr_bussiness_id' => $withdraw['uw_id'],
                'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                'uwr_memo'=> '确认提现' . $withdraw['uw_limit'],
                'uwr_balance' => $wallet['w_money'] + $wallet['w_freeze_money']
            ]);
        }
        else
        {
            // 失败，扣除冻结金额，增加可用余额
            $this->query("UPDATE le_user_wallet SET w_freeze_money = w_freeze_money - ?, w_money = w_money + ?, w_updated_time = ? where u_id = ?", [
                $withdraw['uw_limit'], $withdraw['uw_limit'], $_SERVER['REQUEST_TIME'],$withdraw['u_id']
            ]);
            if (!$this->affectRow())
            {
                $this->rollback();
                throw new ModelException('提现申请失败');
            }
        }

        $this->commit();
        return true;
    }
}
