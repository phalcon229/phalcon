<?php
namespace Components\Payment;

use Phalcon\Mvc\User\Component;

class Main extends Component
{
    public function __construct($channelType)
    {
        $this->channelType = $channelType;
    }

    /**
     * 生成支付链接
     * @param  [type] $channelType 支付渠道类型id
     * @param  [type] $payType 支付方式
     * @param  [type] $orderSn 订单号
     * @param  [type] $amount  金额
     * @return [type]          [description]
     */
    public function buildPay($payType, $orderSn, $amount)
    {
        $obj = null;
        switch ($this->channelType) {
            case '1':
                // austPay
                $obj = new \Components\Payment\Austpay();

                break;

            case '2':
                // austPay
                $obj = new \Components\Payment\Austpay();
                break;
            default:
                return false;
                break;
        }

        if (!$payParams = $obj->buildPay($payType, $orderSn, $amount, $this->channelType))
            return false;

        return $payParams;
    }
}