<?php
use Phalcon\Security;

class DealflowLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new UserWalletRecordModel;
    }
    
    /**
     * 获取账变信息/没有下级的情况/第一次加载默认10条
     * @param type $name
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getRecordInfo($uId, $startDay, $endDay, $type, $num)
    {
        $congif = $this->di['config'];
        $recordType = $congif['type'];
        $info = $this->model->getRecordInfo($uId, $startDay, $endDay, $type, $num);
            for($i = 0; $i < count($info); $i++)
            {
                $info[$i]['uwr_created_time'] = date('Y-m-d',$info[$i]['uwr_created_time']);
                $info[$i]['uwr_type'] = $recordType[$info[$i]['uwr_type']];
            }
        return $info;
    }
    /**
     * 获取所有的账变信息/没有下级的情况
     * @param type $uId
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getTotalInfo($uId, $startDay, $endDay, $type)
    {
        $info = $this->model->getTotalInfo($uId, $startDay, $endDay, $type);
        return $info;
    }

        /**
     * 获取账变信息/有下级的情况/默认10条
     * @param type $ids
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getRecordNext($ids, $startDay, $endDay, $type, $num)
    {
        $congif = $this->di['config'];
        $recordType = $congif['type'];
        $info = $this->model->getRecordNext($ids, $startDay, $endDay, $type, $num);
            for($i = 0; $i < count($info); $i++)
            {
                $info[$i]['uwr_created_time'] = date('Y-m-d',$info[$i]['uwr_created_time']);
                $info[$i]['uwr_type'] = $recordType[$info[$i]['uwr_type']];
            }
        return $info;
    }
    /**
     * 获取所有账变信息/有下级的情况
     * @param type $ids
     * @param type $startDay
     * @param type $endDay
     * @param type $type
     * @param type $fields
     * @return type
     */
    public function getRecordNextTotal($ids, $startDay, $endDay, $type)
    {
        return $this->model->getRecordNextTotal($ids, $startDay, $endDay, $type);
    }
    
}