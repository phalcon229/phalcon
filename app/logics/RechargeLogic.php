<?php
class RechargeLogic extends LogicBase
{

    public function __construct()
    {
        $this->rechargeModel = new RechargeModel;
    }

    /**
     * 插入充值信息
     * @param type $uId
     * @param type $name
     * @param type $orderNum
     * @param type $createTime
     * @param type $money
     * @param type $poor
     * @param type $status
     * @param type $source
     * @param type $paySource
     * @param type $payOrderNum
     * @param type $updateTime
     * @param type $activityId
     * @return type
     */
    public function add($uId, $name, $orderNum,$createTime,$money,$poor,$status,$source,$paySource,$payOrderNum,$updateTime,$activityId)
    {
        return $this->rechargeModel->add($uId, $name, $orderNum,$createTime,$money,$poor,$status,$source,$paySource,$payOrderNum,$updateTime,$activityId);
    }

    public function getAllRecharge($page, $perPage, $type)
    {
        $res['total'] = $this->rechargeModel->getFinanceNums($type);
        $res['list'] = $this->rechargeModel->getFinanceLimit($page, $perPage, $type);

        return $res;
    }

    public function getRechargeByName($page, $perPage, $type, $value )
    {
        $res['total'] = $this->rechargeModel->getRechargeByNameNums($type,  $value);
        $res['list'] = $this->rechargeModel->getRechargeByName($page, $perPage, $type, $value);

        return $res;
    }

    public function refuse($ureId)
    {
        return $this->rechargeModel->refuse($ureId);
    }

    public function getDataByUreId($ureId)
    {
        return $this->rechargeModel->getDataByUreId($ureId);
    }

    /**
     *汇款人充值
     * @param  [type] $ureId [description]
     * @return [type]        [description]
     */
    public function recharge($ureId, $data)
    {
        return $this->rechargeModel->recharge($ureId, $data);
    }

    public function create($data)
    {
        return $this->rechargeModel->create($data);
    }

    public function ChangeMemo($id, $memo)
    {
        return $this->rechargeModel->ChangeMemo($id, $memo);
    }

    public function detailBySn($orderSn)
    {
        return $this->rechargeModel->detailBySn($orderSn);
    }

    /**
     * 支付回调处理订单
     * @param  [type] $orderSn [订单号]
     * @param  [type] $thirdSn [第三方单号]
     * @param  [type] $status  [状态  3-成功    5-失败]
     * @param  [type] $memo    [备注]
     * @return [type]          [description]
     */
    public function confirmRecharge($orderSn, $thirdSn, $status, $memo)
    {
        return $this->rechargeModel->confirmRecharge($orderSn, $thirdSn, $status, $memo);
    }

    public function sign($data = array()){
        ksort($data);

        $str = '';
        foreach($data as $k=>$v){
            if($v && $k!='notifyUrl' && $k!='extend1'){
                $str .= '&'.$k.'='.$v;
            }
        }

        $str = trim($str, '&');
        $key = 'MK0sujsrcf7o3oh092m7nofqw8vb8bbnrtnewojngxvq8cgt8qdfmi5eb82tp0r5jm391sn83nyj2c79i6o2j0al7kue87j8ztltaaqml7rmkbs4aithtzd8rm9cesh0db';
        return strtoupper(md5($str.'&key='.$key));
    }

    public function newOrder($time1, $time2)
    {
        return $this->rechargeModel->newOrder($time1, $time2);
    }
}
