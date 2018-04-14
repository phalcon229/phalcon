<?php
namespace Components\Payment;
use Phalcon\Mvc\User\Component;

class Austpay extends Component implements PayInterface
{
    private $cfg;

    public function __construct()
    {
        $this->cfg = $this->di['config']['newpayment'];
    }
    /**
     * 生成支付链接
     * @return [type]      [description]
     */
    public function buildPay($payType, $orderSn, $amount, $channel)
    {   
        $channel = $channel;
        switch ($channel) {
               case 1:
                   $type = $this->cfg['pay_type'];
                   $api_url = $this->cfg['pay_type'] == 80003?'https://api.rn3.cc/gateway/v1/trade/bank':'https://api.rn3.cc/gateway/v1/trade';
                    if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ){  
                        $queryArr = [
                            'url' => $api_url,
                            'params' => [
                                'total_amount' => $amount*100,
                                'pay_type' => $payType,
                                'type' => 3,
                                'subject' =>'支付',
                                'out_trade_no' => $orderSn,
                                'return_url' => $this->cfg['return_url'],
                                'notify_url' => $this->cfg['notify_url'],
                            ]
                        ];
                    } 
                    else
                    {
                       $queryArr = [
                            'url' => $api_url,
                            'params' => [
                                'total_amount' => $amount*100,
                                'pay_type' => $payType,
                                'type' => 3,
                                'subject' =>'支付',
                                'out_trade_no' => $orderSn,
                                'return_url' => 'https://dazhongcai.cn/pay/aust',
                                'notify_url' => 'https://dazhongcai.cn/pay/austcb',
                            ]
                        ]; 
                    }
                   break;
               
               case 2:
                    $queryArr = array(
                        'url' => '',
                        'params' =>[
                            'outOid'        =>  $orderSn,  //外部订单号
                            'merchantCode'  =>  'MB3g35r74o17n41y4i',   //环球商户编号
                            'mgroupCode'    =>  '',    //环球集团商户编号, 若该商户为集团商户，必填
                            'payType'       =>  $payType,   //支付类型
                            'payAmount'     =>  $amount,   //支付金额,单位： 分
                            'goodName'      =>  '支付',   //商品名称
                            'goodNum'       =>  '',    //商品数量
                            'goodDesc'      =>  '',     //商品描述
                            'busType'       =>  '',     //业务类型
                            'notifyUrl'     =>  '',     //异步通知地址
                            'extend1'       =>  '',     //扩展字段1
                            'extend2'       =>  '',     //扩展字段2
                            'extend3'       =>  '',     //扩展字段3
                        ]
                    );

                   break;
           }   
        return $queryArr;
    }

    public function notify($req)
    {

    }

    /**
     * 提现
     * @return [type] [description]
     */
    public function withdraw($amount, $bankName, $accountName, $accountNumber, $bankDetail, $mobi, $city, $province, $bankId)
    {
        $bankCode = $this->cfg['bank_code'];
        if (!$customerlianhang = $bankCode[$bankId])
            return false;

        $orderSn = date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $secure = hash("sha256", md5($orderSn . $amount . $this->cfg['merchant_id'] . $accountNumber . $this->cfg['sign_string'] . $customerlianhang));
        $secure = hash("sha512", $secure);
        $secure = strtoupper(base64_encode($secure));
        $params = [
            'merchantid' => $this->cfg['merchant_id'],
            'amount' => $amount,
            'bankname' => $bankName,
            'accountname' => $accountName,
            'accountnumber' => $accountNumber,
            'bankdetails' => $bankDetail,
            'customeremail' => 'test@test.com',
            'customerphone' => $mobi,
            'customercity' => $city,
            'customersheng' => $province,
            'customerlianhang' => $customerlianhang,
            'order_id' => $orderSn,
            'secure_sign_string' => $secure
        ];

        $curl = new \Components\Utils\Curl();
        if (!$data = $curl->send($this->cfg['with_url'], $params, 'post'))
            return false;

        $xmlparser = xml_parser_create();
        xml_parse_into_struct($xmlparser, $data, $res);

        foreach ($res as $value) {
            if (strtolower($value['tag']) == 'result' && strtolower($value['value']) == "submit_ok")
                return true;
        }

        // 记录日志
        error_log('[' . date('Y-m-d H:i:s') . '] 返回：' . $data . ' 参数：' . json_encode($params) . "\n", 3, $this->cfg['log']);

        return true;
    }
}