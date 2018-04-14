<?php

class WalletModel extends ModelBase
{

    protected $table = 'le_user_wallet';

    public function __construct()
    {
        parent::__construct();
    }

    public function getWallet($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? AND w_status = 1 LIMIT 1', [$uId]);

        return $this->getRow();
    }

    public function changePwd($uId, $newPwd)
    {
        return $this->update(['w_pass' => $newPwd], ['condition' => 'u_id = ?', 'values' => [$uId]]);
    }
    //设置资金密码和个人手机个人邮箱
    public function changePwdAndMobi($uId, $newPwd, $mobi, $email)
    {
        $this->begin();
        if(!$this->update(['w_pass' => $newPwd], ['condition' => 'u_id = ?', 'values' => [$uId]]))
        {
            $this->rollback();
            return false;
        }
        $this->query('UPDATE le_users SET u_mobile =  ?, u_email = ? WHERE u_id = ?',
        [$mobi,$email,$uId]);

        if($this->affectRow() != 1)
        {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function balance($ids)
    {
        $ids = implode(',', $ids);

        $this->query('SELECT SUM(w_money) w_money FROM ' . $this->table . ' WHERE u_id in ('.$ids.') AND w_status = 1 order by w_updated_time DESC LIMIT 1');

        return $this->getRow();
    }

    public function money($ids)
    {
        $len = strlen($ids);
        $ids = substr($ids,0,$len-1);
        $this->query('SELECT SUM(w_money) w_money FROM ' . $this->table . ' WHERE u_id in ('.$ids.') AND w_status = 1 ');
        return $this->getRow();
    }

    public function getWalletMoney($uId, $fields = 'w_money, w_freeze_money, w_status')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? LIMIT 1', [$uId]);

        return $this->getRow();
    }

    public function getWalletByUids($uIds)
    {
        $this->query('SELECT u_id, w_money, w_status FROM ' . $this->table . ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ') ORDER BY find_in_set(u_id, "' . implode(',', $uIds) . '")', $uIds);

        return $this->getAll();
    }

    public function getTotalMoney()
    {
        $this->query('SELECT sum(w_money) w_money FROM ' . $this->table . ' WHERE w_status = 1');

        return $this->getRow();
    }

    //更新用户钱包并记录
    public function updateUserMoney($uId, $type, $money, $reason)
    {
        //获取用户名
        $this->query('SELECT u_name FROM le_users WHERE u_id = ?', [$uId]);
        $name = $this->getOne();

        $this->query('SELECT w_money, w_withdraw_consume, w_history_spent FROM ' . $this->table . ' WHERE w_status = 1 AND u_id = ? FOR UPDATE', [$uId]);
        $wMoney = $this->getRow();

        if ($type == 3 && (!$wMoney['w_money'] || ($wMoney['w_money'] < $money)))
            return false;
        $balance = 0;
        $set = ' SET w_updated_time = ?';
        if ($type == 1)
        {
            if($wMoney['w_history_spent']>$wMoney['w_withdraw_consume'])
            {
                $set .= ', w_money = w_money + ?, w_history_recharge = w_history_recharge + ?, w_withdraw_consume = w_history_spent + ? ';
            }
            else
            {
                $set .= ', w_money = w_money + ?, w_history_recharge = w_history_recharge + ?, w_withdraw_consume = w_withdraw_consume + ? ';
            }
            
            $balance = $wMoney['w_money'] + $money;
        }
        elseif($type == 3)
        {
            $set .= ', w_money = w_money - ?, w_history_withdraw = w_history_withdraw + ? ';
            $balance = $wMoney['w_money'] - $money;
        }

        $this->begin();
        if ($type == 1)
        {
            $this->query('UPDATE ' . $this->table . $set . ' WHERE w_status = 1 AND u_id = ?', [$_SERVER['REQUEST_TIME'], $money, $money, $money, $uId]);
        }
        else if($type == 3)
        {
            $this->query('UPDATE ' . $this->table . $set . ' WHERE w_status = 1 AND u_id = ?', [$_SERVER['REQUEST_TIME'], $money, $money, $uId]);
        }

        if (!$this->affectRow())
        {
            $this->rollback();
            return false;
        }

        $sn = 'h' . date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);//订单号
        //写入充值记录表或提现记录表
        if ($type == 1)
        {
            $data = [
                'u_id' => $uId,
                'u_name' => $name,
                'ure_sn' => $sn,
                'ure_created_time' => $_SERVER['REQUEST_TIME'],
                'ure_money' => $money,
                'ure_gift_money' => 0,
                'ure_status' => 3,
                'ure_pay_chan' => '-1',//支付渠道
                'ure_pay_way' => '-1', //系统后台充值
                'ure_way_sn' => '',//对应渠道的支付单号
                'ure_updated_time' => '',
                'ure_activity_id' => 0,//对应活动id
                'ure_memo' => '系统后台充值'
            ];
            $this->table = 'le_user_recharge';
            $this->insert($data);
        }else if($type == 3)
        {
            $data = [
                'u_id' => $uId,
                'uw_status' => 3,//提现成功
                'uw_limit' => $money,
                'ubc_sn' => $sn,
                'uw_updated_time' => $_SERVER['REQUEST_TIME'],
            ];
            $this->table = 'le_user_withdraw';
            $this->insert($data);
        }

        if (!$id = $this->lastInsertId())
        {
            $this->rollback();
            return false;
        }
        
        if($type == 1)
        {
            //统计入款
            $date = strtotime(date('Y-m-d'));
            $agentModel = new UserAgentModel();
            $uIds = $agentModel->getAgentInfoByUid($uId);
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
        }
        else if($type == 3)
        {
            //统计出款
            $date = strtotime(date('Y-m-d'));
            $agentModel = new UserAgentModel();
            $uIds = $agentModel->getAgentInfoByUid($uId);
            foreach ($uIds as $key => $value) {
                $this->query('SELECT ar_id FROM le_agent_report WHERE ar_date = ? AND u_id = ? AND bet_id = 0 LIMIT 1', [$date, $value['u_id']]);
                $row = $this->getRow();
                if(!$row)
                {
                    $this->query('INSERT INTO le_agent_report SET ar_date = ?, u_id = ?, u_name = ?, bet_id = 0, ar_parent_uid = ?, u_type = ?, ar_team_withdraw_money = ?',[$date, $value['u_id'], $value['u_name'], $value['ua_u_id'], $value['ua_type'], $money]);

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
                            [$money, $row['ar_id']]);

                    if($this->affectRow() != 1)
                    {
                        $this->rollback();
                        return false;
                    }
                }
            }
        }
        //出入账记录
        $data = [
            'u_id' => $uId,
            'uwr_money' => $type == 1 ? $money : '-' . $money,
            'uwr_type' => $type == 1 ? 1 : 5,//1为充值，5为提现
            'uwr_bussiness_id' => $id,
            'uwr_created_time' => $_SERVER['REQUEST_TIME'],
            'uwr_memo' => $reason,
            'uwr_balance' => $balance,
        ];

        $this->table = 'le_user_wallet_recorded';
        $this->insert($data);
        if (!$this->lastInsertId())
        {
            $this->rollback();
            return false;
        }

        $this->commit();
        return true;
    }

    public function getWalletTotal()
    {
        $this->query('SELECT count(*) as total FROM ' . $this->table . ' ');

        return $this->getRow();
    }

    public function getUserList($start,$limit)
    {
        $this->query('SELECT a.w_money, a.u_id, a.w_history_recharge, a.w_history_withdraw,b.u_name,b.u_type,b.u_nick,b.u_wx_unionid FROM ' . $this->table . ' a left join le_users b on a.u_id = b.u_id order by a.w_money desc LIMIT ?, ?',[$start,$limit]);

        return $this->getAll();
    }

    public function getUserdownList($start,$limit)
    {
        $this->query('SELECT a.w_money, a.u_id, a.w_history_recharge, a.w_history_withdraw,b.u_name,b.u_type,b.u_nick,b.u_wx_unionid FROM ' . $this->table . ' a left join le_users b on a.u_id = b.u_id order by a.w_money asc LIMIT ?, ?',[$start,$limit]);

        return $this->getAll();
    }
}