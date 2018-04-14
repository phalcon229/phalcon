<?php

class BetOrdersModel extends ModelBase
{

    protected $table = 'le_bet_orders';
    protected $betTable = 'le_bets_base_conf';
    protected $ruleTable = 'le_bets_rules';

    public function __construct()
    {
        parent::__construct();
    }

    public function getBetOrdersInfoByUserId($uId,$start_day,$end_day, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and (bo_draw_time< ? and bo_draw_time> ? )', [$uId,$end_day,$start_day]);

        return $this->getAll();
    }

    public function getAllAbnormal($fields = 'bo_issue, bet_id')
    {
          $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bo_unusual_status = 1 ORDER BY `bet_id` ');

          return $this->getAll();
    }

    public function getAbnormalDetail($index, $num, $betid, $issue, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bet_id = ? and bo_issue = ? and (bo_unusual_status = 1 or bo_draw_result = 5 ) ORDER BY `bo_id` DESC LIMIT ' . $index . ', ' . $num , [$betid,$issue]);

        return $this->getAll();
    }

    public function getAbnormalTotal($betid, $issue )
    {
        $this->query('SELECT COUNT(bo_id) AS total FROM ' . $this->table . ' WHERE bet_id = ? and bo_issue = ? and (bo_unusual_status = 1 or bo_draw_result = 5 ) LIMIT 1 ', [$betid, $issue]);

        return $this->getRow();
    }

    public function getAbnormalDetailByBetid($betid, $fields = 'bo_issue, bet_id' )
    {
          $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE bo_unusual_status = 1 and bet_id = ? ORDER BY `bo_id` ', [$betid ]);

        return $this->getAll();
    }

    public function getTotal($betId, $issue)
    {
        $this->query('SELECT COUNT(bo_id) as total FROM ' . $this->table . ' WHERE bo_unusual_status = 3 AND bet_id = ? AND bo_issue = ?', [$betId, $issue]);

        return $this->getOne();
    }

    public function getLists($betId, $issue, $start, $perpage)
    {
        $this->query('SELECT * FROM ' . $this->table . ' WHERE bo_unusual_status = 3 AND bet_id = ? AND bo_issue = ? LIMIT ?, ?', [$betId, $issue, $start, $perpage]);
        return $this->getAll();
    }

    public function getTotalInfo($uId,$lotteryType,$startDay,$endDay, $fields = '*')
    {
        if ($lotteryType == 0)
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and (bo_created_time >= ? and bo_created_time <= ?) ', [$uId,$startDay,$endDay]);
        else
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and bet_id = ? and (bo_created_time >= ? and bo_created_time <= ?)', [$uId,$lotteryType,$startDay,$endDay]);

        return $this->getAll();
    }

    public function getReportInfo($uId,$lotteryType,$serialNum,$startDay,$endDay, $fields = '*')
    {
        if($lotteryType == 0 && empty($serialNum) ){
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and (bo_draw_time >= ? and bo_draw_time <= ?) ', [$uId,$startDay,$endDay]);
        }
        else if($lotteryType == 0 && !empty($serialNum))
        {
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and bo_issue = ? and (bo_draw_time >= ? and bo_draw_time <= ?)', [$uId,$serialNum,$startDay,$endDay]);
        }
        else if($lotteryType !== 0 && empty($serialNum))
        {
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and bet_id = ? and (bo_draw_time >= ? and bo_draw_time <= ?)', [$uId,$lotteryType,$startDay,$endDay]);
        }
        else
        {
            $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ? and bet_id = ? and bo_issue = ? and (bo_draw_time >= ? and bo_draw_time <= ?)', [$uId,$lotteryType,$serialNum,$startDay,$endDay]);
        }

        return $this->getAll();
    }

    public function create($betId, $uId, $uName, $rules, $track)
    {
        try {
            $this->begin();

            // 获取用户信息
            $userModel = new UsersModel();
            if (!$uInfo = $userModel->getInfoByUid($uId))
                throw new ModelException('用户不存在或已禁用');

            // 获取用户钱包余额
            $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
            if (!$wallet = $this->getRow())
                throw new ModelException('用户不存在或已冻结');

            $orders = [];
            // 追号处理
            if ($track)
            {
                $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 9 LIMIT 1');
                $sysInfo = $this->getRow();
                if ($sysInfo['sc_value'] < $track['nums'])
                    throw new ModelException('超过最大追号期数');

                $perTimes = $track['start_times'];
                foreach ($track['perids'] as $no => $trackPerid)
                {
                    if ($no > 0 && $no % $track['per_perid'] == 0)
                        $perTimes = $perTimes * $track['per_times'];

                    foreach ($rules as $rule)
                    {
                        $rule['unit_price'] = $rule['unit_price'] * $perTimes;
                        $rule['perid'] = $trackPerid;
                        array_push($orders, $rule);
                    }
                }
            }
            else
                $orders = $rules;

            $isTrack = $track ? 3 : 1;
            if($isTrack == 3)
            {
                $isStop = $track['win_stop'] == 'true' ? 1:3;
            }
            else
            {
                $isStop = '';
            }

            // 获取彩票单注限额
            $this->query('SELECT bet_min, bet_max FROM le_bets_base_conf WHERE bet_id = ? LIMIT 1', [$betId]);
            if (!$betInfo = $this->getRow())
                throw new ModelException('数据异常');

            // 获取系统封单时间
            $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 2 LIMIT 1');
            $sysInfo = $this->getRow();

            // 获取系统返点率配置
            $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 3 LIMIT 1');
            $rateInfo = $this->getRow();
            $maxRate = $rateInfo['sc_value'];

            // 获取彩种列表
            $this->query('SELECT bet_id, bet_name FROM le_bets_base_conf WHERE bet_isenable = 1');
            $betsInfo = $this->getAll();
            $bets = [];
            foreach ($betsInfo as $value)
                $bets[$value['bet_id']] = $value['bet_name'];

            // 1、生成订单
            $totalPrice = 0;
            $times = 0;
            if($isTrack == 3)
            {
                $insertCode = -1;
            }
            else
            {
                $insertCode = '';
            }

            foreach ($orders as $order)
            {
                if ($order['unit_price'] < $betInfo['bet_min'])
                {
                    $this->rollback();
                    throw new ModelException('单注金额不得小于' . $betInfo['bet_min'] . '元');
                }

                if ($order['unit_price'] > $betInfo['bet_max'])
                {
                    $this->rollback();
                    throw new ModelException('单注金额不得大于' . $betInfo['bet_max'] . '元');
                }

                // 验证期数是否可投注
                $this->query("SELECT bres_periods, bres_open_time FROM le_bets_result WHERE bet_id = ? AND bres_plat_isopen = 3 AND bres_open_time > ? ORDER BY bres_open_time ASC LIMIT 1", [$betId, time()]);
                $current = $this->getRow();
                if ($order['perid'] < $current['bres_periods'] || time() > $current['bres_open_time'] - $sysInfo['sc_value'])
                {
                    $this->rollback();
                    throw new ModelException('第 ' . $order['perid'] . ' 期已截止，投注时请您确认您选的期号');
                }

                // 获取用户代理信息
                $this->query('SELECT ua_u_id, ua_u_name, ua_rate, ua_type FROM le_user_agent WHERE u_id = ? LIMIT 1', [$uId]);
                if (!$agentInfo = $this->getRow())
                {
                    $this->rollback();
                    throw new ModelException('数据异常');
                }

                // 获取玩法赔率信息
                $this->query('SELECT br_type, br_base_type, br_bonus FROM le_bets_rules WHERE br_id = ?', [$order['brid']]);
                if (!$rInfo = $this->getRow())
                {
                    $this->rollback();
                    throw new ModelException('获取赔率信息失败');
                }

                //判断玩法
                switch ($betId) {
                    case 1:
                    case 21:
                        $playName = $rInfo['br_base_type'] < 10 ? '特码' : '两面';
                    break;

                    case 2:
                    case 4:
                    case 6:
                        $playName = ($rInfo['br_base_type'] < 10 || (25 < $rInfo['br_base_type'] && $rInfo['br_base_type'] < 44))?'特码':'两面';
                    break;

                    case 5:
                    case 3:
                        $playName = ($rInfo['br_base_type'] < 10 || (25 < $rInfo['br_base_type'] && $rInfo['br_base_type'] < 36))?'特码':'两面';
                    break;

                    case 7:
                    case 8:
                    case 9:
                        $playName = $rInfo['br_base_type'] < 10 ? '特码' : '两面';
                    break;

                    case 10:
                    case 11:
                    case 12:
                    case 13:
                        $playName = ($rInfo['br_base_type'] < 10 || (25 < $rInfo['br_base_type'] && $rInfo['br_base_type'] < 44))?'特码':'两面';
                    break;
                }

                // 计算返点率
                $baseBonus = ceil($rInfo['br_bonus']);
                $myBonus = sprintf('%.3f', $rInfo['br_bonus'] - ($baseBonus * ($maxRate - $agentInfo['ua_rate']) / 100));
                $bonus = sprintf('%.3f', $myBonus - ($baseBonus * $order['percent'] / 100));

                $this->table = 'le_bet_orders';
                $this->insert([
                    'u_id' => $uId,
                    'u_name' => $uName,
                    'bo_sn' => date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    'bet_id' => $betId,
                    'br_id' => $order['brid'],
                    'bo_played_name' => $playName,
                    'bo_created_time' => $_SERVER['REQUEST_TIME'],
                    'bo_money' => $order['unit_price'],
                    'bo_content' => $rInfo['br_type'] . '-' . $rInfo['br_base_type'],
                    'bo_status' => 1,
                    'bo_issue' => $order['perid'],
                    'bo_odds' => $bonus,
                    'bo_back' => $order['percent'] / 100,
                    'bo_u_id' => $agentInfo['ua_u_id'],
                    'bo_u_name' => $agentInfo['ua_u_name'],
                    'bo_first_odds' => $rInfo['br_bonus'],
                    'bo_is_track' => $isTrack,
                    'bo_rs_tax' => $agentInfo['ua_rate'],
                    'u_type' => $agentInfo['ua_type'],
                    'bo_track_stop' => $isStop,
                    'bo_track_code' => $insertCode
                ]);

                $orderId = $this->lastInsertId();
                if($times == 0 && $isTrack == 3)
                {
                    $insertCode = $orderId;
                    $times ++;
                }
                if(!$orderId)
                {
                    $this->rollback();
                    throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
                }

                $totalPrice += $order['unit_price'];

                // 2、添加出入账记录
                $this->table = 'le_user_wallet_recorded';
                $this->insert([
                    'u_id' => $uId,
                    'uwr_money' => '-' . $order['unit_price'],
                    'uwr_type' => 7,    // 投注
                    'uwr_bussiness_id' => $orderId,
                    'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                    'uwr_memo' => $order['perid'] . '【' . $bets[$betId] . '】',
                    'uwr_balance' => $wallet['w_money']-$totalPrice
                ]);
                if(!$this->lastInsertId())
                {
                    $this->rollback();
                    throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
                }

                // 记录该期该玩法投注额
                $redisKey = 'bet:count:' . $betId . ':' . $order['perid'];
                $redisField = $rInfo['br_type'] . ':' . $rInfo['br_base_type'];
                $PerRedis = date('Y-m-d') . ':' . $betId . ':perdate:count';
                $AllRedisField = date('Y-m-d') . ':allbet:count';
                
                $this->di['redis']->incrByFloat($PerRedis,intval($order['unit_price']));

                $this->di['redis']->incrByFloat($AllRedisField,intval($order['unit_price']));

                if ($this->di['redis']->hGetAll($redisKey))
                {
                    $this->di['redis']->hIncrBy($redisKey, $redisField, $order['unit_price']);
                    $this->di['redis']->expire($redisKey, 7 * 86400);
                } else
                    $this->di['redis']->hIncrBy($redisKey, $redisField, $order['unit_price']);

            }

            if ($wallet['w_money'] < $totalPrice)
                throw new ModelException('账户余额不足');

            // 3、扣除余额
            $this->query('UPDATE le_user_wallet SET w_money = w_money - ? WHERE u_id = ? AND w_status = 1', [$totalPrice, $uId]);
            if (!$this->affectRow())
            {
                $this->rollback();
                throw new ModelException('');
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getOrderByBoid($boid)
    {
        $this->query('SELECT bo_id, bo_money, u_id, bet_id FROM ' . $this->table . ' WHERE bo_id = ? and bo_status = 1 and (bo_unusual_status = 1 or bo_draw_result = 5 ) LIMIT 1 ', [$boid]);

        return $this->getRow();
    }

    /**
     * 异常单人退款（后台）
     * @param  [type] $ureId [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function backOne($data)
    {
        $this->begin();

        // 修改订单状态
        $this->table = 'le_bet_orders';
        if(!$this->update(['bo_status' => 5 ], ['condition' => 'bo_id = ? AND bo_status = 1', 'values' => [$data['bo_id']]]))
        {
            $this->rollback();
            throw new ModelException('update ' . $this->table . ' failed. sql:' . $this->sql);
        }

        $this->table = 'le_user_wallet';
        //获取钱包金额
        $this->query(' SELECT w_money FROM ' . $this->table . ' where  u_id = ? LIMIT 1 ', [$data['u_id']]);
        if(!$wall = $this->getRow())
        {
            $this->rollback();
            throw new ModelException('select ' . $this->table . ' failed. sql:' . $this->sql);
        }

        //修改用户钱包

        if(!$this->update(['w_money' => $data['bo_money'] + $wall['w_money'], 'w_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => ' u_id = ? ', 'values' => [$data['u_id']] ]))
        {
            $this->rollback();
            throw new ModelException(' update ' . $this->table . ' failed. sql:' . $this->sql);
        }

        //写出入账的记录(日志没有退款类型)
        $this->table = 'le_user_wallet_recorded';
        $lastid =$this->insert([
                'u_id' => $data['u_id'],
                'uwr_money' => $data['bo_money'],
                'uwr_type' => 15,
                'uwr_bussiness_id' => $data['bo_id'],
                'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                'uwr_memo'=> '异常订单退款',
                'uwr_balance' => $wall['w_money']+$data['bo_money']
            ]);

        if(!$lastid)
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }
        $PerRedis = date('Y-m-d') . ':' . $data['bet_id'] . ':perdate:count';
        $AllRedisField = date('Y-m-d') . ':allbet:count';

        $this->di['redis']->decrBy($PerRedis,intval($data['bo_money']));

        $this->di['redis']->decrBy($AllRedisField,intval($data['bo_money']));

        $this->commit();
        return true;
    }

    /**
     * 整期退款（后台）
     * @param  [type] $ureId [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function allback($betid, $issue)
    {
        $this->begin();
        //获取整期已支付异常订单
        $this->query('SELECT bo_id, bo_money, u_id FROM ' . $this->table . ' WHERE bet_id= ? and bo_issue = ? and bo_status = 1 and (bo_unusual_status = 1 or bo_draw_result = 5 ) ', [$betid,$issue]);
        if(!$datas = $this->getAll())
        {
            $this->rollback();
            throw new ModelException('select ' . $this->table . ' failed. sql:' . $this->sql);
        }

        foreach ($datas as $data) {
            // 修改订单状态
            $this->table = 'le_bet_orders';
            if(!$this->update(['bo_status' => 5 ], ['condition' => ' bo_status = 1 and bo_id = ? ', 'values' => [$data['bo_id']] ]))
            {
                $this->rollback();
                throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
            }

            $this->table = 'le_user_wallet';
            //获取钱包金额
            $this->query(' SELECT w_money FROM ' . $this->table . ' where  u_id = ? LIMIT 1 ', [$data['u_id']]);
            if(!$wall = $this->getRow())
            {
                $this->rollback();
                throw new ModelException('select ' . $this->table . ' failed. sql:' . $this->sql);
            }

            //修改用户钱包
            if(!$this->update(['w_money' => $data['bo_money'] + $wall['w_money'], 'w_updated_time' => $_SERVER['REQUEST_TIME']], ['condition' => ' u_id = ? ', 'values' => [$data['u_id']] ]))
            {
                $this->rollback();
                throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
            }

            //写出入账的记录
            $this->table = 'le_user_wallet_recorded';
            $lastid =$this->insert([
                    'u_id' => $data['u_id'],
                    'uwr_money' => $data['bo_money'],
                    'uwr_type' => 15,
                    'uwr_bussiness_id' => $data['bo_id'],
                    'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                    'uwr_memo'=> '异常订单退款',
                    'uwr_balance' => $wall['w_money']+$data['bo_money']
                ]);

            if(!$lastid)
            {
                $this->rollback();
                throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
            }
            $PerRedis = date('Y-m-d') . ':' . $betid. ':perdate:count';
            $AllRedisField = date('Y-m-d') . ':allbet:count';
            
            $this->di['redis']->decrBy($PerRedis,intval($data['bo_money']));

            $this->di['redis']->decrBy($AllRedisField,intval($data['bo_money']));
        }

        $this->commit();
        return true;
    }

    public function getNumsByBetidAndStatus($betId, $expect, $status, $drawRes)
    {
        $this->query('SELECT COUNT(bo_id) as total FROM ' . $this->table . ' WHERE bet_id = ? AND bo_issue = ? AND bo_status = ? AND bo_draw_result = ?', [$betId, $expect, $status, $drawRes]);

        return $this->getOne();
    }

    public function getOrderByBetidAndStatus($betId, $expect, $status, $drawRes, $page, $limit)
    {
        $this->query('SELECT bo_id, bo_sn, bet_id, bo_issue, br_id, u_id, bo_money, bo_created_time, bo_content, bo_odds, bo_back, bo_u_id, bo_rs_tax, bo_first_odds, u_name, bo_is_track, u_type, bo_track_stop, bo_track_code FROM ' . $this->table . ' WHERE bet_id = ? AND bo_issue = ? AND bo_status = ? AND bo_draw_result = ? LIMIT ?, ?',
            [$betId, $expect, $status, $drawRes, $page*$limit, $limit]);

        return $this->getAll();
    }

    public function openOrders($order, $isWin, $earnMoney, $backMoney)
    {
        $this->begin();
        $this->table = 'le_bet_orders';
        // 修改订单记录
        $this->update(['bo_draw_result' => $isWin == 1 ? 1 : 3, 'bo_status' => 3, 'bo_bonus' => $earnMoney, 'bo_back_money' => $backMoney], ['condition' => 'bo_id = ? AND bo_status = 1 AND bo_draw_result = 5', 'values' => [$order['bo_id']]]);
        $a = $this->affectRow();
        if(!$a)
        {
            $this->rollback();
            throw new Exception('修改订单记录失败');
            return false;
        }

        // 开奖结算后计算历史花费
        $this->query('UPDATE le_user_wallet SET w_history_spent = w_history_spent + ? WHERE u_id = ? AND w_status = 1', [$order['bo_money'],$order['u_id']]);
        if (!$this->affectRow())
        {
            $this->rollback();
            throw new ModelException('');
        }

        $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$order['u_id']]);
        if (!$wallet = $this->getRow())
            throw new ModelException('用户不存在或已冻结');

        $type = 3;
        $memo = json_encode($order);
        $money = $earnMoney + $backMoney;
		if($money > 0)
		{
        	// 根据金额，修改钱包记录
        	// 修改钱包金额
        	$this->query('UPDATE le_user_wallet SET w_money = w_money + ?, w_history_win = w_history_win + ?, w_updated_time = ? WHERE u_id = ?', [$money, $money, $_SERVER['REQUEST_TIME'], $order['u_id']]);

        	if(!$this->affectRow())
        	{
        	    $this->rollback();
				throw new Exception('修改钱包金额失败');
        	    return false;
        	}
//            $allBanlance = $this->di['redis']->get('finance:'. date('Y-m-d') .'balanceTotal');
//            if($allBanlance)
//            {
//                $this->di['redis']->incrByFloat('finance:' . date('Y-m-d') .'balanceTotal', $money);
//            }
//            else
//            {
//                $this->query('SELECT sum(w_money) w_money FROM le_user_wallet WHERE w_status = 1');
//                $res = $this->getRow();
//                $allBanlance = $res['w_money'];
//                $this->di['redis']->set('finance:' . date('Y-m-d') .'balanceTotal', $allBanlance);
//                $this->di['redis']->incrByFloat('finance:' . date('Y-m-d') .'balanceTotal', $money);
//            }

        	// 增加帐变记录
        	$sql = 'INSERT INTO le_user_wallet_recorded (u_id, uwr_money, uwr_type, uwr_bussiness_id, uwr_created_time, uwr_memo, uwr_balance)VALUES';
        	$dot = '';
        	$val = [];
        	if($earnMoney > 0)
        	{
        	    $sql .= '(?, ?, ?, ?, ?, ?, ?)';
        	    $dot = ',';
        	    $val = [$order['u_id'], $earnMoney, 3, $order['bo_id'], $_SERVER['REQUEST_TIME'], $memo,$wallet['w_money']+$earnMoney];
        	}

        	if($backMoney > 0)
        	{
        	    $sql .= $dot . '(?, ?, ?, ?, ?, ?,?)';
        	    $val = array_merge($val, [$order['u_id'], $backMoney, 13, $order['bo_id'], $_SERVER['REQUEST_TIME'], $memo,$wallet['w_money']+$backMoney]);
        	}

        	$this->query($sql, $val);
        	if(!$this->lastInsertId())
        	{
        	    $this->rollback();
				throw new Exception('添加帐变失败');
        	    return false;
        	}
		}
        $date = strtotime(date('Y-m-d',$order['bo_created_time']));
        // 更新财务报表统计表
         $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_date = ? AND u_id = ? AND bet_id = ? LIMIT 1', [$date, $order['u_id'], $order['bet_id']]);
         $row = $this->getRow();
         if(!$row)
         {
             $this->query('INSERT INTO le_plat_finance_report SET pfr_date = ?, bet_id = ?, bet_periods = 0, ar_parent_uid = ?, u_id = ?, u_name = ?, pfr_my_bet_money = ?, pfr_my_earn_money = ?, pfr_my_reback_money = ?, u_type = ?',
                     [$date, $order['bet_id'], $order['bo_u_id'], $order['u_id'], $order['u_name'], $order['bo_money'], $earnMoney, $backMoney, $order['u_type'] ]);
             if(!$this->lastInsertId())
             {
                 $this->rollback();
				 throw new Exception('添加财务报表金额统计失败');
                 return false;
             }
         }
         else
         {
             $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_id = ? LIMIT 1 FOR UPDATE',  [$row['pfr_id']]);
             $this->query('UPDATE le_plat_finance_report SET pfr_my_bet_money = pfr_my_bet_money + ?, pfr_my_earn_money = pfr_my_earn_money + ?, pfr_my_reback_money = pfr_my_reback_money + ? WHERE pfr_id = ?',
                     [$order['bo_money'], $earnMoney, $backMoney, $row['pfr_id']]);

             if($this->affectRow() != 1)
             {
                 $this->rollback();
				 throw new Exception('更新财务报表金额统计失败');
                 return false;
             }
         }

         // 更新财务报表统计表（期数）
         $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_date = ? AND u_id = ? AND bet_id = ? AND bet_periods = ? LIMIT 1', [$date, $order['u_id'], $order['bet_id'], $order['bo_issue']]);
         $row = $this->getRow();
         if(!$row)
         {
             $this->query('INSERT INTO le_plat_finance_report SET u_id = ?, bet_id = ?,ar_parent_uid = ?, pfr_my_bet_money = ?, pfr_my_earn_money = ?, pfr_my_reback_money = ?, pfr_date = ?, u_name = ?, u_type = ?, bet_periods = ? ',
                     [$order['u_id'], $order['bet_id'], $order['bo_u_id'], $order['bo_money'], $earnMoney, $backMoney, $date, $order['u_name'], $order['u_type'], $order['bo_issue']]);

             if(!$this->lastInsertId())
             {
                 $this->rollback();
				 throw new Exception('添加财务报表金额统计失败');
                 return false;
             }
         }
         else
         {
             $this->query('SELECT pfr_id FROM le_plat_finance_report WHERE pfr_id = ? LIMIT 1 FOR UPDATE',  [$row['pfr_id']]);
             $this->query('UPDATE le_plat_finance_report SET pfr_my_bet_money = pfr_my_bet_money + ?, pfr_my_earn_money = pfr_my_earn_money + ?, pfr_my_reback_money = pfr_my_reback_money + ? WHERE pfr_id = ?',
                     [$order['bo_money'], $earnMoney, $backMoney, $row['pfr_id']]);

             if($this->affectRow() != 1)
             {
                 $this->rollback();
				 throw new Exception('更新财务报表金额统计失败');
                 return false;
             }
         }

        $userAgentModel = new \UserAgentModel;
        $agentInfo = $userAgentModel->getUpRate($order['bo_u_id']);
         // 更新代理报表统计表

         $this->query('SELECT ar_id FROM le_agent_report WHERE ar_date = ? AND u_id = ? AND bet_id = ? LIMIT 1', [$date, $order['u_id'], $order['bet_id']]);
         $row = $this->getRow();
         if(!$row)
         {
             $this->query('INSERT INTO le_agent_report SET u_id = ?, bet_id = ?, ar_parent_uid = ?, ar_my_bet_money = ?, ar_my_earn_money = ?, ar_my_reback_money = ?, ar_up_back_money = ?, ar_date = ?, u_name = ?, u_type = ?',
                     [$order['u_id'], $order['bet_id'], $order['bo_u_id'], $order['bo_money'], $earnMoney, $backMoney, ($agentInfo['ua_rate']- $order['bo_rs_tax'])*$order['bo_money']/100 , $date, $order['u_name'], $order['u_type']]);

             if(!$this->lastInsertId())
             {
                 $this->rollback();
				 throw new Exception('添加代理报表金额统计失败');
                 return false;
             }
         }
         else
         {
             $this->query('SELECT ar_id FROM le_agent_report WHERE ar_id = ? LIMIT 1 FOR UPDATE',  [$row['ar_id']]);
             $this->query('UPDATE le_agent_report SET ar_my_bet_money = ar_my_bet_money + ?, ar_my_earn_money = ar_my_earn_money + ?, ar_my_reback_money = ar_my_reback_money + ?, ar_up_back_money = ar_up_back_money + ? WHERE ar_id = ?',
                     [$order['bo_money'], $earnMoney, $backMoney, ($agentInfo['ua_rate']- $order['bo_rs_tax'])*$order['bo_money']/100, $row['ar_id']]);

             if($this->affectRow() != 1)
             {
                 $this->rollback();
				 throw new Exception('更新代理报表金额统计失败');
                 return false;
             }
         }


        // 更新彩种每日/每期总额统计表
        $this->query('SELECT bt_id FROM le_bets_total WHERE bt_date = ? AND bet_id = ? AND bt_type = 1 LIMIT 1', [$date, $order['bet_id']]);
        $row = $this->getRow();

        if(!$row)
        {
            // 插入日总统计记录
            $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ?, bt_type = 1, bt_all_in = ?, bt_all_out = ?, bt_all_earn = ?, bt_all_orders = ?',
                    [$order['bet_id'], $date, $order['bo_money'], $money, ($order['bo_money'] - $money), 1]);

            if(!$this->lastInsertId())
            {
                $this->rollback();
				throw new Exception('插入日总统计记录失败');
                return false;
            }

            // 插入期总统计记录
            $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ?, bt_type = 3, bt_periods = ?, bt_all_in = ?, bt_all_out = ?, bt_all_earn = ?, bt_all_orders = ?',
                    [$order['bet_id'], $date, $order['bo_issue'], $order['bo_money'], $money, ($order['bo_money'] - $money) , 1]);

            if(!$this->lastInsertId())
            {
                $this->rollback();
				throw new Exception('插入期总统计记录失败');
                return false;
            }
        }
        else
        {
            // 更新彩种每日/每期总额统计表
            $this->query('SELECT bt_id FROM le_bets_total WHERE bt_id = ? LIMIT 1 FOR UPDATE', [$row['bt_id']]);
            $this->query('UPDATE le_bets_total SET bt_all_in = bt_all_in + ?, bt_all_out = bt_all_out + ?, bt_all_earn = bt_all_earn + ?, bt_all_orders = bt_all_orders+1 WHERE bt_id = ?',
                    [$order['bo_money'], $money, ($order['bo_money'] - $money), $row['bt_id']]);

            if($this->affectRow() != 1)
            {
                $this->rollback();
				throw new Exception('更新日总统计记录失败');
                return false;
            }

            // 更新期总总计记录
            $this->query('SELECT bt_id FROM le_bets_total WHERE bet_id = ? AND bt_periods = ? LIMIT 1', [$order['bet_id'], $order['bo_issue']]);
            $row = $this->getRow();
            if(!$row)
            {
                // 插入期总统计记录
                $this->query('INSERT INTO le_bets_total SET bet_id = ?, bt_date = ?, bt_type = 3, bt_periods = ?, bt_all_in = ?, bt_all_out = ?, bt_all_earn = ?, bt_all_orders = ?',
                        [$order['bet_id'], $date, $order['bo_issue'], $order['bo_money'], $money, ($order['bo_money'] - $money), 1]);

                if(!$this->lastInsertId())
                {
                    $this->rollback();
					throw new Exception('插入期总统计记录失败');
                    return false;
                }
            }
            else
            {
                $this->query('SELECT bt_id FROM le_bets_total WHERE bt_id = ? LIMIT 1 FOR UPDATE', [$row['bt_id']]);
                $this->query('UPDATE le_bets_total SET bt_all_in = bt_all_in + ?, bt_all_out = bt_all_out + ?, bt_all_earn = bt_all_earn + ?, bt_all_orders = bt_all_orders+1 WHERE bt_id = ?',
                        [$order['bo_money'], $money, ($order['bo_money'] - $money), $row['bt_id']]);

                if($this->affectRow() != 1)
                {
                    $this->rollback();
					throw new Exception('更新期总统计记录失败');
                    return false;
                }
            }
        }

        $this->commit();

        // 返回结果
        return true;
    }

    public function newCreate($betId, $uId, $uName, $rules, $track)
    {

        try {
            $this->begin();

            // 获取用户信息
            $userModel = new UsersModel();
            if (!$uInfo = $userModel->getInfoByUid($uId))
                throw new ModelException('用户不存在或已禁用');

            // 获取用户钱包余额
            $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
            if (!$wallet = $this->getRow())
                throw new ModelException('用户不存在或已冻结');

            $orders = [];
            // 追号处理
            if ($track)
            {
                $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 9 LIMIT 1');
                $sysInfo = $this->getRow();
                if ($sysInfo['sc_value'] < $track['nums'])
                    throw new ModelException('超过最大追号期数');

                $perTimes = $track['start_times'];
                foreach ($track['perids'] as $no => $trackPerid)
                {
                    if ($no > 0 && $no % $track['per_perid'] == 0)
                        $perTimes = $perTimes * $track['per_times'];

                    foreach ($rules as $rule)
                    {
                        $rule['unit_price'] = $rule['unit_price'] * $perTimes;
                        $rule['perid'] = $trackPerid;
                        array_push($orders, $rule);
                    }
                }
            }
            else
                $orders = $rules;

            $isTrack = $track ? 3 : 1;
            if($isTrack == 3)
            {
                $isStop = $track['win_stop'] == 'true' ? 1:3;
            }
            else
            {
                $isStop = '';
            }

            // 获取彩票单注限额
            $this->query('SELECT bet_min, bet_max FROM le_bets_base_conf WHERE bet_id = ? LIMIT 1', [$betId]);
            if (!$betInfo = $this->getRow())
                throw new ModelException('数据异常');

            // 获取系统封单时间
            $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 2 LIMIT 1');
            $sysInfo = $this->getRow();

            // 获取系统返点率配置
            $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 3 LIMIT 1');
            $rateInfo = $this->getRow();
            $maxRate = $rateInfo['sc_value'];

            // 获取彩种列表
            $this->query('SELECT bet_id, bet_name FROM le_bets_base_conf WHERE bet_isenable = 1');
            $betsInfo = $this->getAll();
            $bets = [];
            foreach ($betsInfo as $value)
                $bets[$value['bet_id']] = $value['bet_name'];

            // 1、生成订单
            $totalPrice = 0;
            $times = 0;
            if($isTrack == 3)
            {
                $insertCode = -1;
            }
            else
            {
                $insertCode = '';
            }
            foreach ($orders as $order)
            {

                if ($order['unit_price'] < $betInfo['bet_min'])
                {
                    $this->rollback();
                    throw new ModelException('单注金额不得小于' . $betInfo['bet_min'] . '元');
                }

                if ($order['unit_price'] > $betInfo['bet_max'])
                {
                    $this->rollback();
                    throw new ModelException('单注金额不得大于' . $betInfo['bet_max'] . '元');
                }

                // 验证期数是否可投注
                $this->query("SELECT bres_periods, bres_open_time FROM le_bets_result WHERE bet_id = ? AND bres_plat_isopen = 3 AND bres_open_time > ? ORDER BY bres_open_time ASC LIMIT 1", [$betId, time()]);
                $current = $this->getRow();

                if ($order['perid'] < $current['bres_periods'] || time() > $current['bres_open_time'] - $sysInfo['sc_value'])
                {
                    $this->rollback();
                    throw new ModelException('第 ' . $order['perid'] . ' 期已截止，投注时请您确认您选的期号');
                }

                // 获取用户代理信息
                $this->query('SELECT ua_u_id, ua_u_name, ua_rate, ua_type FROM le_user_agent WHERE u_id = ? LIMIT 1', [$uId]);
                if (!$agentInfo = $this->getRow())
                {
                    $this->rollback();
                    throw new ModelException('数据异常');
                }

                // 获取玩法赔率信息
                $this->query('SELECT br_type, br_base_type, br_bonus FROM le_bets_rules WHERE br_id = ?', [$order['brid']]);
                if (!$rInfo = $this->getRow())
                {
                    $this->rollback();
                    throw new ModelException('获取赔率信息失败');
                }

                // 计算返点率
                $baseBonus = ceil($rInfo['br_bonus']);
                $myBonus = sprintf('%.3f', $rInfo['br_bonus'] - ($baseBonus * ($maxRate - $agentInfo['ua_rate']) / 100));
                $bonus = sprintf('%.3f', $myBonus - ($baseBonus * $order['percent'] / 100));

                if (($order['type'] == 35 || $order['type'] == 37 || $order['type'] == 44) && $order['dt'] !=1) {
                    $r = $order['ball'];
                } else if($order['dt'] ==1) {

                    if (count($order['ball'][0]) >= $order['num'])
                        return false;
                    $num = $order['num'] - count($order['ball'][0]);
                    $res = $this->combination($order['ball'][1], $num);
                    $r =[];
                        foreach ($res as $val) {
                           $r[]=array_merge($order['ball'][0],$val);
                        }

                } else {
                    $r = $this->combination($order['ball'], $order['num']);
                }
                foreach ($r as $value) {
                    if ($order['type'] == 35 || $order['type'] == 37 || $order['type'] == 44) {
                        $content = $order['type'].'-'.$value;
                    } else if($order['type'] == 39) {
                        $content = $order['type'] .'-'. '111,222,333,444,555,666' ;
                    } else if ($order['type'] == 42) {
                        $content = $order['type'] .'-'. '123,234,456' ;
                    } else {
                        $content = $order['type'].'-'.implode(',', $value);
                    }
                    $this->table = 'le_bet_orders';
                    $this->insert([
                        'u_id' => $uId,
                        'u_name' => $uName,
                        'bo_sn' => date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT),
                        'bet_id' => $betId,
                        'br_id' => $order['brid'],
                        'bo_played_name' => $order['dt']==1 ? '胆拖-'.$order['name'] : $order['name'],
                        'bo_created_time' => $_SERVER['REQUEST_TIME'],
                        'bo_money' => $order['unit_price'],
                        'bo_content' => $content,
                        'bo_status' => 1,
                        'bo_issue' => $order['perid'],
                        'bo_odds' => $bonus,
                        'bo_back' => $order['percent'] / 100,
                        'bo_u_id' => $agentInfo['ua_u_id'],
                        'bo_u_name' => $agentInfo['ua_u_name'],
                        'bo_first_odds' => $rInfo['br_bonus'],
                        'bo_is_track' => $isTrack,
                        'bo_rs_tax' => $agentInfo['ua_rate'],
                        'u_type' => $agentInfo['ua_type'],
                        'bo_track_stop' => $isStop,
                        'bo_track_code' => $insertCode
                    ]);

                    $orderId = $this->lastInsertId();
                    if($times == 0 && $isTrack == 3)
                    {
                        $insertCode = $orderId;
                        $times ++;
                    }
                    $totalPrice += $order['unit_price'];

                    // 2、添加出入账记录
                    $this->table = 'le_user_wallet_recorded';
                    $this->insert([
                        'u_id' => $uId,
                        'uwr_money' => '-' . $order['unit_price'],
                        'uwr_type' => 7,    // 投注
                        'uwr_bussiness_id' => $orderId,
                        'uwr_created_time' => $_SERVER['REQUEST_TIME'],
                        'uwr_memo' => $order['perid'] . '【' . $bets[$betId] . '】',
                        'uwr_balance' => $wallet['w_money']-$totalPrice
                    ]);
                    if(!$this->lastInsertId())
                    {
                        $this->rollback();
                        throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
                    }

                    // 记录该期该玩法投注额
                    $redisKey = 'bet:count:' . $betId . ':' . $order['perid'];
                    $redisField = $rInfo['br_type'] . ':' . $rInfo['br_base_type'];
                    $PerRedis = date('Y-m-d') . ':' . $betId . ':perdate:count';
                    $AllRedisField = date('Y-m-d') . ':allbet:count';
                    
                    $this->di['redis']->incrByFloat($PerRedis,intval($order['unit_price']));

                    $this->di['redis']->incrByFloat($AllRedisField,intval($order['unit_price']));

                    if ($this->di['redis']->hGetAll($redisKey))
                    {
                        $this->di['redis']->hIncrBy($redisKey, $redisField, $order['unit_price']);
                        $this->di['redis']->expire($redisKey, 7 * 86400);
                    } else
                        $this->di['redis']->hIncrBy($redisKey, $redisField, $order['unit_price']);
                }

            }

            if ($wallet['w_money'] < $totalPrice)
                throw new ModelException('账户余额不足');

            // 3、扣除余额
            $this->query('UPDATE le_user_wallet SET w_money = w_money - ? WHERE u_id = ? AND w_status = 1', [ $totalPrice, $uId]);
            if (!$this->affectRow())
            {
                $this->rollback();
                throw new ModelException('');
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function setExceptionStatusByBoid($boId, $status)
    {
        return $this->update(['bo_unusual_status' => $status], ['condition' => 'bo_id = ?', 'values' => [$boId]]);
    }

    public function getOrderInfo($uId,$lotteryType,$serialNum,$startDay,$endDay,$type)
    {
        //$type为空查询正常订单，为1查询可撤销或者已撤销的订单
        if($type == 0)
        {
            if($lotteryType == 0 && empty($serialNum)){
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=1 and a.bet_id = b.bet_id and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$startDay,$endDay]);

                return $this->getAll();
            }
            else if($lotteryType == 0 && !empty($serialNum))
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=1 and a.bet_id = b.bet_id and a.bo_issue = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$serialNum,$startDay,$endDay]);

                return $this->getAll();
            }
            else if($lotteryType !== 0 && empty($serialNum))
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=1 and a.bet_id = b.bet_id and a.bet_id = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$lotteryType,$startDay,$endDay]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=1 and a.bet_id = b.bet_id and a.bet_id = ? and a.bo_issue = ? order by a.bo_id desc', [$uId,$lotteryType,$serialNum]);

                return $this->getAll();
            }
        }
        else
        {
            if($lotteryType == 0 && empty($serialNum)){
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=3 and a.bet_id = b.bet_id and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$startDay,$endDay]);

                return $this->getAll();
            }
            else if($lotteryType == 0 && !empty($serialNum))
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=3 and a.bet_id = b.bet_id and a.bo_issue = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$serialNum,$startDay,$endDay]);

                return $this->getAll();
            }
            else if($lotteryType !== 0 && empty($serialNum))
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=3 and a.bet_id = b.bet_id and a.bet_id = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$lotteryType,$startDay,$endDay]);

                return $this->getAll();
            }
            else
            {
                $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and a.bo_is_track=3 and a.bet_id = b.bet_id and a.bet_id = ? and a.bo_issue = ? order by a.bo_id desc', [$uId,$lotteryType,$serialNum]);

                return $this->getAll();
            }
        }
    }

    public function getDetailById($boId)
    {
        $this->query('SELECT a.*,b.bet_name,c.* FROM ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.bet_id = b.bet_id and a.bo_id = ? INNER JOIN ' . $this->ruleTable . ' c on '
                . 'c.br_id = a.br_id', [$boId]);

        return $this->getAll();
    }
    //前端撤销订单
    public function cancelOrderById($boId, $uId)
    {
        $this->begin();
        $userModel = new UsersModel();
        if (!$uInfo = $userModel->getInfoByUid($uId))
            throw new ModelException('用户不存在或已禁用');

        $this->query('SELECT u_id, bo_money, bet_id, bo_issue FROM le_bet_orders WHERE bo_id = ? AND bo_status = 1 LIMIT 1', [$boId]);
        $info = $this->getRow();
        if (empty($info))
            throw new ModelException('该订单不可撤销');

        if ($info['u_id'] != $uId)
            throw new ModelException('警告,非法操作!');


        // 获取系统封单时间
        $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 2 LIMIT 1');
        $sysInfo = $this->getRow();

        // 验证期数是否可退款
        // $current = $this->di['redis']->get('bets:next:' . $info['bet_id']);
        $this->query("SELECT bres_periods, bres_open_time FROM le_bets_result WHERE bet_id = ? AND bres_periods = ? ORDER BY bres_open_time ASC LIMIT 1", [$info['bet_id'], $info['bo_issue']]);
        $current = $this->getRow();
        if ($info['bo_issue'] < $current['bres_periods'] || time() > ($current['bres_open_time'] - $sysInfo['sc_value']))
        {
            $this->rollback();
            throw new ModelException('该玩法本期已经封盘，不可撤销');
        }

        // 修改订单记录
        $this->query('UPDATE le_bet_orders SET bo_status = 5 WHERE bo_id = ? AND bo_status = 1',[$boId]);
        $res = $this->affectRow();

        if (!$res)
        {
            $this->rollback();
            throw new ModelException('update le_bet_orders failed. sql:' . $this->sql);
        }

        $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
        if (!$wallet = $this->getRow())
            throw new ModelException('用户不存在或已冻结');

        //修改出入帐记录
        $this->query('INSERT INTO le_user_wallet_recorded SET u_id = ?, uwr_money = ?, uwr_type = 15, uwr_bussiness_id = ?, uwr_created_time = ?, uwr_memo = ?,uwr_balance = ?',
                    [$info['u_id'], $info['bo_money'], $boId, $_SERVER['REQUEST_TIME'], '用户撤销订单退款id:'.$boId,$wallet['w_money']+$info['bo_money']]);

        if (!$this->lastInsertId())
        {
            $this->rollback();
            throw new ModelException('insert le_user_wallet_recorded failed. sql:' . $this->sql);
        }

        //修改用户钱包记录
        $this->query('UPDATE le_user_wallet SET w_money = w_money + ?,w_updated_time = ? WHERE u_id = ? AND w_status = 1', [$info['bo_money'], $_SERVER['REQUEST_TIME'], $info['u_id']]);
        $res = $this->affectRow();
        if (!$res)
        {
            $this->rollback();
            throw new ModelException('订单撤销失败，请重新尝试');
        }
        $PerRedis = date('Y-m-d') . ':' . $info['bet_id'] . ':perdate:count';
        $AllRedisField = date('Y-m-d') . ':allbet:count';
        
        $this->di['redis']->decrBy($PerRedis,intval($info['bo_money']));
 
        $this->di['redis']->decrBy($AllRedisField,intval($info['bo_money']));
        $this->commit();
        return true;
    }
    //开合撤销订单
    public function backOrderById($boId, $uId)
    {
        $this->begin();
        $userModel = new UsersModel();
        if (!$uInfo = $userModel->getInfoByUid($uId))
            throw new ModelException('用户不存在或已禁用');

        $this->query('SELECT u_id, bo_money, bet_id, bo_issue FROM le_bet_orders WHERE bo_id = ? and bo_status = 1 LIMIT 1', [$boId]);
        $info = $this->getRow();
        if (empty($info))
            throw new ModelException('该订单不可撤销');

        if ($info['u_id'] != $uId)
            throw new ModelException('警告,非法操作!');


        // 获取系统封单时间
        // $this->query('SELECT sc_value FROM le_sys_conf WHERE sc_id = 2 LIMIT 1');
        // $sysInfo = $this->getRow();

        // // 验证期数是否可退款
        // $current = $this->di['redis']->get('bets:next:' . $info['bet_id']);
        // if ($info['bo_issue'] < $current['expect'] || time() > ($current['openTime'] - $sysInfo['sc_value']))
        // {
        //     $this->rollback();
        //     throw new ModelException('该玩法本期已经封盘，不可撤销');
        // }

        // 修改订单记录
        $this->query('UPDATE le_bet_orders SET bo_status = 5 WHERE bo_id = ? AND bo_status = 1',[$boId]);
        $res = $this->affectRow();

        if (!$res)
        {
            $this->rollback();
            throw new ModelException('update le_bet_orders failed. sql:' . $this->sql);
        }

        $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
        if (!$wallet = $this->getRow())
            throw new ModelException('用户不存在或已冻结');

        //修改出入帐记录
        $this->query('INSERT INTO le_user_wallet_recorded SET u_id = ?, uwr_money = ?, uwr_type = 15, uwr_bussiness_id = ?, uwr_created_time = ?, uwr_memo = ?,uwr_balance = ?',
                    [$info['u_id'], $info['bo_money'], $boId, $_SERVER['REQUEST_TIME'], '用户撤销订单退款id:'.$boId,$wallet['w_money']+$info['bo_money']]);

        if (!$this->lastInsertId())
        {
            $this->rollback();
            throw new ModelException('insert le_user_wallet_recorded failed. sql:' . $this->sql);
        }

        //修改用户钱包记录
        $this->query('UPDATE le_user_wallet SET w_money = w_money + ?, w_updated_time = ? WHERE u_id = ? AND w_status = 1', [$info['bo_money'], $_SERVER['REQUEST_TIME'], $info['u_id']]);
        $res = $this->affectRow();
        if (!$res)
        {
            $this->rollback();
            throw new ModelException('退单失败，请重新尝试');
        }
        $PerRedis = date('Y-m-d') . ':' . $info['bet_id'] . ':perdate:count';
        $AllRedisField = date('Y-m-d') . ':allbet:count';
        
        $this->di['redis']->decrBy($PerRedis,intval($info['bo_money']));
 
        $this->di['redis']->decrBy($AllRedisField,intval($info['bo_money']));
        $this->commit();
        return true;
    }

    /**
     * 根据uid,彩种类型，交易号，时间段查询可以进行撤单或者已经撤单的数据
     * @param type $uId
     * @param type $lotteryType
     * @param type $serialNum
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getCancelInfo($uId,$lotteryType,$serialNum,$startDay,$endDay)
    {
        if($lotteryType == 0 && empty($serialNum)){
            $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and (a.bo_status=5 or bo_draw_result=5) and a.bet_id = b.bet_id and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$startDay,$endDay]);

            return $this->getAll();
        }
        else if($lotteryType == 0 && !empty($serialNum))
        {
            $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and (a.bo_status=5 or bo_draw_result=5) and a.bet_id = b.bet_id and a.bo_issue = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$serialNum,$startDay,$endDay]);

            return $this->getAll();
        }
        else if($lotteryType !== 0 && empty($serialNum))
        {
            $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and (a.bo_status=5 or bo_draw_result=5) and a.bet_id = b.bet_id and a.bet_id = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$lotteryType,$startDay,$endDay]);

            return $this->getAll();
        }
        else
        {
            $this->query('SELECT a.bo_id,a.bo_status,a.bet_id,a.bo_played_name, a.bo_created_time, a.bo_money, a.bo_issue,a.bo_draw_result,a.bo_content, b.bet_name from ' . $this->table . ' a INNER JOIN ' . $this->betTable . ' b ON a.u_id = ? and and (a.bo_status=5 or bo_draw_result=5) a.bet_id = b.bet_id and a.bet_id = ? and a.bo_issue = ? and (a.bo_created_time >= ? and a.bo_created_time <= ?) order by a.bo_id desc', [$uId,$lotteryType,$serialNum,$startDay,$endDay]);

            return $this->getAll();
        }
    }

    public function getUsersOrder($uIds, $betId, $startDay, $endDay, $fields = '*')
    {
        $where = '';
        $data = $uIds;
        if ($betId)
        {
            $where .= ' AND bet_id = ?';
            array_push($data, $betId);
        }

        if ($startDay)
        {
            $where .= ' AND bo_created_time >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND bo_created_time <= ?';
            array_push($data, $endDay);
        }

        $this->query('SELECT u_id, COUNT(bo_id) AS total, SUM(bo_money) AS order_money FROM ' . $this->table . ' WHERE u_id IN (' . $this->generatePhForIn(count($uIds)) . ') ' . $where . ' GROUP BY u_id', $data);
        return $this->getAll();
    }

    public function getMyList($uId, $betId, $isTrack = false)
    {
        $isTrack = $isTrack ? 3 : 1;
        $this->query("SELECT * FROM {$this->table} WHERE u_id = ? AND bet_id = ? AND bo_is_track = ? ORDER BY bo_id DESC LIMIT 20", [$uId, $betId, $isTrack]);
        return $this->getAll();
    }

    public function getBetMoney($ids, $betId, $qishu)
    {
        $this->query("SELECT SUM(bo_money) bo_money, br_id  FROM {$this->table} WHERE br_id = ? AND bet_id = ? and bo_issue = ?", [$ids, $betId, $qishu]);
        return $this->getRow();
    }

    public function getOrder($uId,$betId,$issue)
    {
        $this->query("SELECT *  FROM {$this->table} WHERE u_id = ? and bet_id = ? and bo_issue = ? limit 1", [$uId,$betId,$issue]);
        return $this->getAll();
    }

    public function getOpen($uId,$betId,$issue)
    {
        $this->query("SELECT *  FROM {$this->table} WHERE u_id = ? and bet_id = ? and bo_issue = ? and bo_status = 3
            limit 1", [$uId,$betId,$issue]);
        return $this->getAll();
    }

    public function getOrderDetail($boid, $fields)
    {
        $this->query("SELECT " . $fields . " FROM {$this->table} WHERE bo_id = ? limit 1", [$boid]);
        return $this->getRow();
    }

    public function getOrderTrackInfo($boid)
    {
        $this->query("SELECT bo_track_code FROM {$this->table} WHERE bo_id = ? limit 1", [$boid]);
        return $this->getRow();
    }

    public function getTrackInfo($code,$uId,$issue)
    {
        $this->query("SELECT bo_id, u_id, bo_money FROM {$this->table} WHERE bo_track_code = ? and u_id = ? and bo_issue > ? and bo_status = 1 ", [$code,$uId, $issue]);
        return $this->getAll();
    }

    public function combination($a, $m) {
        $r = array();
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }
        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i+1);
                $c = $this->combination($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }
        return $r;
    }

    /**
     * 按日期分组统计每日盈亏
     * @param  [type] $uId         [description]
     * @param  [type] $lotteryType [description]
     * @param  [type] $startDay    [description]
     * @param  [type] $endDay      [description]
     * @return [type]              [description]
     */
    public function getOrderByDate($uId, $lotteryType, $startDay, $endDay)
    {
        if (!$lotteryType)
            $this->query('SELECT  FROM_UNIXTIME(bo_created_time,"%Y-%m-%d") days, count(bo_id) AS count, SUM(bo_money) AS bo_money, SUM(bo_bonus) AS bo_bonus, SUM(bo_back_money) AS bo_back_money FROM ' . $this->table . ' WHERE u_id = ? and (bo_created_time >= ? and bo_created_time <= ?) AND bo_status != 5 GROUP BY days', [$uId,$startDay,$endDay]);
        else
            $this->query('SELECT FROM_UNIXTIME(bo_created_time,"%Y-%m-%d") days, count(bo_id) AS count, SUM(bo_money) AS bo_money, SUM(bo_bonus) AS bo_bonus, SUM(bo_back_money) AS bo_back_money FROM ' . $this->table . ' WHERE u_id = ? and bet_id = ? and (bo_created_time >= ? and bo_created_time <= ?) AND bo_status != 5 GROUP BY days', [$uId,$lotteryType,$startDay,$endDay]);

        return $this->getAll();
    }

    /**
     * 某一天盈亏
     * @param  [type] $uId      [description]
     * @param  [type] $betId    [description]
     * @param  [type] $startTime [description]
     * @param  [type] $endTime   [description]
     * @return [type]           [description]
     */
    public function getDayOrder($uId, $betId, $startTime, $endTime)
    {
        $where = ' WHERE u_id = ? AND bo_created_time >= ? AND bo_created_time <= ? AND bo_status != 5';
        $data = [$uId, $startTime, $endTime];
        if ($betId)
        {
            $where .= ' AND bet_id = ?';
            array_push($data, $betId);
        }

        $this->query('SELECT FROM_UNIXTIME(bo_created_time,"%Y-%m-%d") days, count(bo_id) AS count, SUM(bo_money) AS bo_money, SUM(bo_bonus) AS bo_bonus, SUM(bo_back_money) AS bo_back_money FROM ' . $this->table . $where . ' Limit 1', $data);

        return $this->getRow();
    }

    /**
     * 分页获取订单列表
     * @param  [type] $uId      [description]
     * @param  [type] $betId    [description]
     * @param  [type] $type     [description]
     * @param  [type] $issue    [description]
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @param  [type] $start     [description]
     * @param  [type] $nums     [description]
     * @return [type]           [description]
     */
    public function getOrderLists($uId, $betId, $type, $issue, $startDay, $endDay, $start, $nums, $fields)
    {
        $where = '';
        $data = [$uId];
        if ($betId)
        {
            $where .= ' AND ord.bet_id = ?';
            array_push($data, $betId);
        }

        if ($startDay)
        {
            $where .= ' AND ord.bo_created_time >= ?';
            array_push($data, $startDay);
        }

        if ($endDay)
        {
            $where .= ' AND ord.bo_created_time <= ?';
            array_push($data, $endDay);
        }

        if($issue)
        {
            $where .= ' AND ord.bo_issue = ?';
            array_push($data, $issue);
        }

        switch($type){
            case 1:
                $where .= ' AND ord.bo_is_track = 1';
                break;
            case 3:
                $where .= ' AND ord.bo_is_track = 3';
                break;
            case 5:
                $where .= ' AND (ord.bo_status = 5 OR ord.bo_draw_result = 5)';
        }

        array_push($data, $start);
        array_push($data, $nums);

        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' AS ord LEFT JOIN ' . $this->betTable .' AS bet ON ord.bet_id = bet.bet_id WHERE ord.u_id = ?' . $where . ' ORDER BY ord.bo_id DESC LIMIT ?,?', $data);

        return $this->getAll();
    }

    public function getAllOrder($issue)
    {
        $this->query('SELECT bo_odds, bo_money from ' . $this->table . ' where bo_issue = ?', [$issue]);

        return $this->getAll();
    }
}