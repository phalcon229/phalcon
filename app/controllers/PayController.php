<?php

class PayController extends ControllerBase
{

    public function initialize()
    {
        $this->rechargeLogic = new RechargeLogic();
    }

    /**
     * AUSTPAY 异步
     * @return [type] [description]
     */
    public function austcbAction()
    {
        $this->view->disable();

        if (!$data = $this->request->getQuery())
            return false;

        if (!$this->austpay($data))
            return false;

        echo '<result>yes</result>';
    }

    public function austcbsecAction()
    {
        $this->view->disable();

        if (!$data = $this->request->getQuery())
            return false;

        if (!$this->austsec($data))
            return false;

        echo '<result>yes</result>';
    }

    /**
     * AUSTPAY 同步
     */
    public function austAction()
    {
        $this->view->disable();

        if (!$data = $this->request->getQuery())
            return $this->response->redirect('recharge/index');

        if (!$this->austpay($data))
            return $this->response->redirect('recharge/index');

        $url = rtrim($this->di['config']['baseInfo']['domain'], '/') . '/wallet/show';
        echo '<script>alert("充值成功"); window.location.href = "' . $url . '"</script>';
    }

    /**
     * AUSTPAY 支付回调
     * @return [type] [description]
     */
    public function austpay($data)
    {
        if (!$orderSn = $data['out_trade_no'])
            return false;

        if (!isset($data['trade_status']))
            return false;

        // 获取平台订单
        if (!$order = $this->rechargeLogic->detailBySn($orderSn))
            return false;

        if ($order['ure_status'] == 3)
            return true;

        // 订单状态
        if ($order['ure_status'] != 1)
            return false;

        // 验证金额
        if ($order['ure_money'] != $data['total_amount']/100)
            return false;

        $status = $data['trade_status'] != 1 ? 5 : 3; // 3-充值成功    5-充值失败
        return $this->rechargeLogic->confirmRecharge($orderSn, $data['trade_no'], $status, json_encode($data));
    }

    public function austsec($data)
    {
        var_dump($data);exit;
        if (!$orderSn = $data['outOid'])
            return false;

        if (!isset($data['orderStatus']))
            return false;

        // 获取平台订单
        if (!$order = $this->rechargeLogic->detailBySn($orderSn))
            return false;

        if ($order['ure_status'] == 3)
            return true;

        // 订单状态
        if ($order['ure_status'] != 1)
            return false;

        // 验证金额
        if ($order['ure_money'] != $data['payAmount']/100)
            return false;

        $status = $data['orderStatus'] != 2 ? 5 : 3; // 3-充值成功    5-充值失败
        return $this->rechargeLogic->confirmRecharge($orderSn, $data['outOid'], $status, json_encode($data));
    }
}
