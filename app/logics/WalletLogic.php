<?php
use Phalcon\Security;

class WalletLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new WalletModel();
    }

    /**
     * 根据uid获取钱包余额
     * @param type $uId
     * @return type
     */
    public function money($uId)
    {
        return $this->model->getWallet($uId, 'w_money')['w_money'];
    }

    public function pass($uId)
    {
        return $this->model->getWallet($uId, 'w_pass')['w_pass'];
    }

    public function changePwd($uId, $newPwd)
    {
        return $this->model->changePwd($uId, $newPwd);
    }

    //设置资金密码和个人手机个人邮箱
    public function changePwdAndMobi($uId, $newPwd, $mobi, $email)
    {
        return $this->model->changePwdAndMobi($uId, $newPwd, $mobi, $email);
    }

    /**
     * 根据uid获取钱包信息
     * @param type $uId
     * @return type
     */
    public function getInfo($uId){
        return $this->model->getWallet($uId);
    }

    /**
     * 根据uid获取钱包余额
     * @param type $ids
     * @return type
     */
    public function balance($ids){
        return $this->model->balance($ids);
    }

    public function getTotalMoney()
    {
        return $this->model->getTotalMoney();
    }

    public function moneyRest($ids)
    {
        return $this->model->Money($ids);
    }

    /**
     * 后台为用户充值提现
     * @param  [type] $uId    [description]
     * @param  [type] $type   [description]
     * @param  [type] $money  [description]
     * @param  [type] $reason [description]
     * @return [type]         [description]
     */
    public function updateUserMoney($uId, $type, $money, $reason)
    {
        return $this->model->updateUserMoney($uId, $type, $money, $reason);
    }

    /**
     * 批量获取用户钱包余额，所有钱包状态
     * @param  [type] $uIds [description]
     * @return [type]       [description]
     */
    public function getWalletByUids($uIds)
    {
        return $this->model->getWalletByUids($uIds);
    }

    public function getWalletTotal()
    {
        return $this->model->getWalletTotal();
    }

    public function getUserList($start,$limit)
    {
        $index = ($start - 1) * $limit;
        return $this->model->getUserList($index,$limit);
    }

    public function getUserdownList($start,$limit)
    {
        $index = ($start - 1) * $limit;
        return $this->model->getUserdownList($index,$limit);
    }
}