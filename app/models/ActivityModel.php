<?php

class ActivityModel extends ModelBase
{

    protected $table = 'le_pay_activity';

    public function __construct()
    {
        parent::__construct();
    }

    public function getActivityInfo($fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE pa_status = 1 order by pa_endtime asc');

        return $this->getAll();
    }

    public function getRecharge($fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE pa_status = 1 order by pa_endtime asc');

        return $this->getAll();
    }

    public function getDetailById($paId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE pa_id = ? and pa_status = 1 ', [$paId]);

        return $this->getRow();
    }

    /**
     * 参与活动
     * @param  [type] $type 类型   1-注册    3-充值送
     * @param  [type] $paId 活动ID
     * @param  [type] $uId  用户ID
     * @return [type]       [description]
     */
    public function join($type, $paId, $uId, $orderId = 0)
    {
        $this->query("SELECT pa_money, pa_gift_money, pa_per_nums, pa_history_money FROM le_pay_activity WHERE pa_id = ? AND pa_status = 1 AND pa_type = ? LIMIT 1", [$paId, $type]);
        if (!$actInfo = $this->getRow())
            // 活动不存在或已关闭
            return false;

        if (!$actInfo['pa_gift_money'])
            return false;

        // 是否满足赠送金额
        if (intval($type) == 3)
        {
            // 判断参与活动次数
            $this->query("SELECT count(ure_id) total FROM le_user_recharge WHERE ure_activity_id = ?", [$paId]);
            $joinInfo = $this->getRow();
            if (intval($joinInfo['total']) >= intval($actInfo['pa_per_nums']))
            {
                $this->rollback();
                throw new ModelException('您已参与过该活动');
            }

            $this->query("SELECT ure_money FROM le_user_recharge WHERE ure_id = ? LIMIT 1", [$orderId]);
            if (!$orderInfo = $this->getRow())
                return false;

            if ($orderInfo['ure_money'] < $actInfo['pa_money'])
                return false;
        }

        // 赠送金额
        $this->query("UPDATE le_user_wallet SET w_money = w_money + ?, w_withdraw_consume = w_withdraw_consume + ? WHERE u_id = ?", [$actInfo['pa_gift_money'], $actInfo['pa_history_money'], $uId]);
        if (!$this->affectRow())
        {
            $this->rollback();
            throw new ModelException('活动参与失败');
        }

        $this->query('SELECT w_money, w_status FROM le_user_wallet WHERE u_id = ? AND w_status = 1', [$uId]);
        if (!$wallet = $this->getRow())
            throw new ModelException('用户不存在或已冻结');

        // 写入财务日志
        $memo = $type == 3 ? '充值活动赠送，充' . $actInfo['pa_money'] . '送' . $actInfo['pa_gift_money'] : '注册活动赠送' . $actInfo['pa_gift_money'];

        $this->table = 'le_user_wallet_recorded';
        $this->insert([
            'u_id' => $uId,
            'uwr_money' => $actInfo['pa_gift_money'],
            'uwr_type' => 9,    // 赠送
            'uwr_bussiness_id' => $orderId,
            'uwr_created_time' => $_SERVER['REQUEST_TIME'],
            'uwr_memo' => $memo,
            'uwr_balance' => $wallet['w_money']
        ]);
        if(!$this->lastInsertId())
        {
            $this->rollback();
            throw new ModelException('活动参与失败');
        }

        return true;
    }
}
