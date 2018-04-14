<?php

class UserWalletModel extends ModelBase
{

    protected $table = 'le_user_wallet';

    public function __construct()
    {
        parent::__construct();
    }

    public function balance($ids)
    {
        $ids = implode(',', $ids);

        $this->query('SELECT sum(w_money) w_money FROM ' . $this->table . ' WHERE u_id in ('.$ids.') AND w_status = 1 order by LIMIT 1', [$uId]);

        return $this->getAll();
    }

    public function agentEarn($info)
    {
        $this->begin();
        $size = count($info);
        // $date = strtotime(date('Y-m-d'));
        $betId = $expect = $allBackMoney = 0;
        for($i = 0; $i < $size; $i++)
        {
            $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$info[$i]['u_id']]);
            if (!$wallet = $this->getRow())
                throw new ModelException('用户不存在或已冻结');
            
            $this->query('SELECT u_id FROM ' . $this->table . ' WHERE u_id = ? LIMIT 1 FOR UPDATE', [$info[$i]['u_id']]);
            if($info[$i]['back_money'] > 0)
            {
                $this->query('UPDATE ' . $this->table . ' SET w_money = w_money + ? WHERE u_id = ?', [$info[$i]['back_money'], $info[$i]['u_id']]);
                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }

                // 插入帐变记录
                $this->query('INSERT INTO le_user_wallet_recorded SET u_id = ?, uwr_money = ?, uwr_type = 11, uwr_bussiness_id = ?, uwr_created_time = ?, uwr_balance = ?', [$info[$i]['u_id'], $info[$i]['back_money'], $info[$i]['bo_id'], $_SERVER['REQUEST_TIME'],$wallet['w_money']+$info[$i]['back_money']]);
                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            $date = strtotime(date('Y-m-d',$info[$i]['bo_created_time']));
            // 更新财务报表统计表
            $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_date = ? AND u_id = ? AND bet_id = ? LIMIT 1', [$date, $info[$i]['u_id'], $info[$i]['bet_id']]);
            $row = $this->getRow();
            if(!$row)
            {
                
                $this->query('INSERT INTO le_plat_finance_report SET pfr_date = ?, u_id = ?, bet_id = ?, bet_periods = 0, ar_parent_uid = ?, pfr_team_bet_money = ?, pfr_team_earn_money = ?,pfr_team_back_money = ?, '
                        . 'pfr_team_reback_money = ?, pfr_team_plat_money = ?, pfr_my_back_money = ?, u_name = ?, u_type = ?',[$date, $info[$i]['u_id'], $info[$i]['bet_id'], $info[$i]['bo_u_id'], $info[$i]['bo_money'], 
                            $info[$i]['bo_bonus'], $info[$i]['back_money'] + $info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                            $info[$i]['bo_money'] - $info[$i]['back_money'] - $info[$i]['last_agent_back_money'] - $info[$i]['bo_back_money'] - $info[$i]['bo_bonus'],$info[$i]['back_money'],$info[$i]['u_name'], $info[$i]['ua_type']]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            else
            {
                $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_id = ? LIMIT 1 FOR UPDATE',  [$row['pfr_id']]);
                $this->query('UPDATE le_plat_finance_report SET pfr_team_bet_money = pfr_team_bet_money + ?, pfr_team_earn_money = pfr_team_earn_money + ?, pfr_team_back_money = pfr_team_back_money + ?, '
                        . 'pfr_team_reback_money = pfr_team_reback_money + ?, pfr_team_plat_money = pfr_team_plat_money + ?,pfr_my_back_money = pfr_my_back_money + ? WHERE pfr_id = ?',
                        [$info[$i]['bo_money'], $info[$i]['bo_bonus'], $info[$i]['back_money'] + $info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                            $info[$i]['bo_money'] - $info[$i]['back_money'] - $info[$i]['last_agent_back_money'] - $info[$i]['bo_back_money'] - $info[$i]['bo_bonus'], $info[$i]['back_money'], $row['pfr_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }
            }
            // 更新财务报表统计表(期数)
            $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_date = ? AND u_id = ? AND bet_id = ? AND bet_periods = ? LIMIT 1', [$date, $info[$i]['u_id'], $info[$i]['bet_id'], $info[$i]['bo_issue']]);
            $row = $this->getRow();
            if(!$row)
            {
                $this->query('INSERT INTO le_plat_finance_report SET pfr_date = ?, u_id = ?, bet_id = ?, bet_periods = ?, ar_parent_uid = ?, pfr_team_bet_money = ?, pfr_team_earn_money = ?,pfr_team_back_money = ?, '
                        . 'pfr_team_reback_money = ?, pfr_team_plat_money = ?, pfr_my_back_money = ?, u_name = ?, u_type = ?',[$date, $info[$i]['u_id'], $info[$i]['bet_id'], $info[$i]['bo_issue'], $info[$i]['bo_u_id'], $info[$i]['bo_money'], 
                            $info[$i]['bo_bonus'], $info[$i]['back_money'] + $info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                            $info[$i]['bo_money'] - $info[$i]['back_money'] - $info[$i]['last_agent_back_money'] - $info[$i]['bo_back_money'] - $info[$i]['bo_bonus'], $info[$i]['back_money'],$info[$i]['u_name'], $info[$i]['ua_type']]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            else
            {
                
                $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_id = ? LIMIT 1 FOR UPDATE',  [$row['pfr_id']]);
                $this->query('UPDATE le_plat_finance_report SET pfr_team_bet_money = pfr_team_bet_money + ?, pfr_team_earn_money = pfr_team_earn_money + ?, pfr_team_back_money = pfr_team_back_money + ?, '
                        . 'pfr_team_reback_money = pfr_team_reback_money + ?, pfr_team_plat_money = pfr_team_plat_money + ?, pfr_my_back_money = pfr_my_back_money + ? WHERE pfr_id = ?',
                        [$info[$i]['bo_money'], $info[$i]['bo_bonus'], $info[$i]['back_money'] + $info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                            $info[$i]['bo_money'] - $info[$i]['back_money'] - $info[$i]['last_agent_back_money'] - $info[$i]['bo_back_money'] - $info[$i]['bo_bonus'], $info[$i]['back_money'], $row['pfr_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }
               
            }
            // 更新代理报表统计表
            $this->query('SELECT ar_id FROM le_agent_report WHERE ar_date = ? AND u_id = ? AND bet_id = ? LIMIT 1', [$date, $info[$i]['u_id'], $info[$i]['bet_id']]);
            $row = $this->getRow();
            if(!$row)
            {
                $this->query('INSERT INTO le_agent_report SET ar_date = ?, u_id = ?, bet_id = ?, ar_parent_uid = ?, ar_team_bet_money = ?, ar_team_earn_money = ?,ar_team_back_money = ?, '
                        . 'ar_team_reback_money = ?, ar_my_back_money = ?, ar_up_back_money = ?, u_name = ?, u_type = ?',[$date, $info[$i]['u_id'], $info[$i]['bet_id'], $info[$i]['bo_u_id'], $info[$i]['bo_money'], 
                            $info[$i]['bo_bonus'], $info[$i]['back_money']+$info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                             $info[$i]['back_money'], $info[$i]['myEarn'], $info[$i]['u_name'], $info[$i]['ua_type']]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            else
            {
                $this->query('SELECT ar_id FROM le_agent_report WHERE ar_id = ? LIMIT 1 FOR UPDATE',  [$row['ar_id']]);
                $this->query('UPDATE le_agent_report SET ar_team_bet_money = ar_team_bet_money + ?, ar_team_earn_money = ar_team_earn_money + ?, ar_team_back_money = ar_team_back_money + ?, '
                        . 'ar_team_reback_money = ar_team_reback_money + ?, ar_my_back_money = ar_my_back_money + ?,ar_up_back_money = ar_up_back_money + ? WHERE ar_id = ?',
                        [$info[$i]['bo_money'], $info[$i]['bo_bonus'], $info[$i]['back_money'] + $info[$i]['last_agent_back_money'], $info[$i]['bo_back_money'], 
                           $info[$i]['back_money'], $info[$i]['myEarn'], $row['ar_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }
            }
            
            $betId == 0 ? $betId = $info[$i]['bet_id'] : '';
            $expect == 0 ? $expect = $info[$i]['bo_issue'] : '';
            $allBackMoney += $info[$i]['back_money'];//返点
        }

        // 更新彩种每日/每期总额统计表
        $this->query('SELECT bt_id FROM le_bets_total WHERE bt_date = ? AND bet_id = ? AND bt_type = 1 LIMIT 1', [$date, $betId]);
        $row = $this->getRow();
        if(!$row)
        {
            // 插入日总统计记录
            $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ?, bt_type = 1, bt_all_reback = ?, bt_all_earn = ?', [$betId, $date, $allBackMoney, -$allBackMoney]);

            if(!$this->lastInsertId())
            {
                $this->rollback();
                return false;
            }

            // 插入期总统计记录
            $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ? bt_type = 3, bt_periods = ?, bt_all_reback = ?, bt_all_earn = ?', [$betId, $date, $expect, $allBackMoney, -$allBackMoney]);

            if(!$this->lastInsertId())
            {
                $this->rollback();
                return false;
            }
        }
        else
        {
            // 更新彩种每日/每期总额统计表
            if($allBackMoney > 0)
            {
                $this->query('SELECT bt_id FROM le_bets_total WHERE bt_id = ? LIMIT 1 FOR UPDATE', [$row['bt_id']]);
                $this->query('UPDATE le_bets_total SET bt_all_reback = bt_all_reback + ?, bt_all_earn = bt_all_earn - ? WHERE bt_id = ?', [$allBackMoney, $allBackMoney, $row['bt_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
                    return false;
                }
            }
            
            // 更新期总总计记录
            $this->query('SELECT bt_id FROM le_bets_total WHERE bet_id = ? AND bt_periods = ? LIMIT 1', [$betId, $expect]);
            $row = $this->getRow();
            if(!$row)
            {
                // 插入期总统计记录
                $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ? bt_type = 3, bt_periods = ?, bt_all_reback = ?, bt_all_earn = ?', [$betId, $date, $expect, $allBackMoney, -$allBackMoney]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    return false;
                }
            }
            else
            {        
                if($allBackMoney > 0)
                {
                    $this->query('SELECT bt_id FROM le_bets_total WHERE bt_id = ? LIMIT 1 FOR UPDATE', [$row['bt_id']]);
                    $this->query('UPDATE le_bets_total SET bt_all_reback = bt_all_reback + ?, bt_all_earn = bt_all_earn - ? WHERE bt_id = ?', [$allBackMoney, $allBackMoney, $row['bt_id']]);

                    if($this->affectRow() != 1)
                    {
                        $this->rollback();
                        return false;
                    }
                }
            }
        }

        $this->commit();
        return true;
    }
}
