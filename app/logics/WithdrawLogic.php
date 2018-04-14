<?php
class WithdrawLogic extends LogicBase
{

    public function __construct()
    {
        $this->withdrawModel = new WithdrawModel;
        $this->wallModel = new WalletModel;
        $this->bankModel = new BankModel;
        $this->cfg = $this->di['config']['newpayment'];
    }

    /**
     * 插入提款信息
     * @param type $uId
     * @param type $bankId
     * @param type $ubcBank
     * @param type $province
     * @param type $city
     * @param type $number
     * @param type $money
     * @return type
     */
    public function add($uId, $bankId, $ubcBank,$province,$city,$number,$money,$ubcMobi,$orderSn)
    {
        return $this->withdrawModel->add($uId, $bankId, $ubcBank,$province,$city,$number,$money,$ubcMobi,$orderSn);
    }

    public function getAllRecharge($currentPage, $num, $condition, $value)
    {
        $index = ($currentPage - 1) * $num;
        $res = $this->withdrawModel->getAllRecharge($index, $num, $condition, $value);
        $data = [];
        foreach ($res as $value) {
            $value['w_money'] =$this->wallModel->getWalletMoney($value['u_id'])['w_money'];
            $value['ubc_uname'] =$this->bankModel->getName($value['ubc_id'])['ubc_uname'];
            array_push($data, $value);
        }
        return $data;
    }

    public function getRechargeTotal($condition, $value)
    {
       return $this->withdrawModel->getRechargeTotal($condition, $value)['total'];
    }

    public function stop($uwid)
    {
       return $this->withdrawModel->stop($uwid);
    }

    public function getInfoById($uwid)
    {
       return $this->withdrawModel->getInfoById($uwid);
    }

    public function passes($uwid)
    {
       return $this->withdrawModel->passes($uwid);
    }

    /**
     * 提交取现
     * @param Int $withId 提现ID
     * @return [type] [description]
     */
    public function apply($withId)
    {
        // 获取提现信息
        if (!$info = $this->getInfoById($withId))
            return false;


        if (!$bankInfo = $this->bankModel->getBankById($info['ubc_id']))
            return false;

        $bankId = $bankInfo['ubc_bank_id'];

        $bankList = $this->di['config']['bank'];
        if (empty($bankList[$bankId]))
            return false;

        $bankName = $bankList[$bankId];     
        //开始
        $bankCode = $this->cfg['bank_code'];
                
        // if (!$customerlianhang = $bankCode[$bankId])
        //     return false;

        $orderSn = date('ymdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $params = [
        'total_amount' => $info['uw_limit']*100,
        'bank_name' => $bankName,
        'account_name' => $bankInfo['ubc_uname'],
        'card_no' => $info['ubc_number'],
        'account_mobile' => $info['ubc_mobi'],
        'out_trade_no' => $orderSn,
        'bank_code' => $this->di['config']['bank_code'][$bankId],
        ];
        $payway = new GoldCityWithdrawalRequest();
        $msg = $payway->withdrawalMoney($params);
        $info = json_decode($msg, true);
     
        error_log('[' . date('Y-m-d H:i:s') . '] 返回：' . $data . ' 参数：' . json_encode($params) . "\n", 3, $this->cfg['log']);

        if($info['data']['ret_code'] == 'SUCCESS'){
                return true;
            }
            else{
                return false;
            }


        // $payMent = new \Components\Payment\Austpay();
        // $payRes = $payMent->withdraw($info['uw_limit'], $bankName, $bankInfo['ubc_uname'], $info['ubc_number'], $info['ubc_name'], $info['ubc_mobi'], $info['ubc_city'], $info['ubc_province'], $bankId);

        // return $payRes;
    }

    /**
     * 提现确认
     * @param  [type] $id   [description]
     * @param  Int $status 状态   3-提现成功   5-提现失败    7-审核取消
     * @return [type]       [description]
     */
    public function confirm($id, $status)
    {
        return $this->withdrawModel->confirm($id, $status);
    }
}
