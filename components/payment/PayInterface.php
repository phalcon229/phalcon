<?php
namespace Components\Payment;

interface PayInterface
{
    public function buildPay($payType, $orderId, $amount, $channel);

    public function notify($req);
}