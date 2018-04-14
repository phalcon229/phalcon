<?php
class WalletRecordLogic extends LogicBase
{

    public function __construct()
    {
        $this->walletRecordModel = new WalletRecordModel();
    }

    /**
     * 根据uid获取出入账信息
     * @param type $uId
     * @return string
     */
    public function getWalletRecordInfoByUserId($uId)
    {
        $config = $this->di['config'];
        $type = $config['type'];

        $info=$this->walletRecordModel->getRecordInfoByUserId($uId);
        for($i=0;$i<count($info);$i++)
        {
            $info[$i]['uwr_created_time']=date('Y-m-d H:i:s',$info[$i]['uwr_created_time']);

            $info[$i]['uwr_Type'] = $type[$info[$i]['uwr_type']];

            $info[$i]['uwr_money'] = sprintf("%0.1f",$info[$i]['uwr_money']);
        }
        return $info;
    }

    /**
     * 根据uid和出入账主键id获取信息
     * @param type $uId
     * @param type $uwrId
     * @return string
     */
    public function getDetail($uId,$uwrId)
    {
        $config = $this->di['config'];
        $type = $config['type'];

        $info=$this->walletRecordModel->getDetail($uId,$uwrId);
        for($i=0;$i<count($info);$i++)
        {
            $info[$i]['uwr_created_time']=date('Y-m-d H:i:s',$info[$i]['uwr_created_time']);

            $info[$i]['uwr_Type'] = $type[$info[$i]['uwr_type']];

            $info[$i]['uwr_money'] = sprintf("%0.1f",$info[$i]['uwr_money']);

        }
        return $info;
    }

    /**
     * 根据uid和出入账类型获取信息
     * @param type $uId
     * @param type $uwrType
     * @param type $num
     * @return string
     */
    public function getInfoByType($uId,$uwrType,$num)
    {
        $config = $this->di['config'];
        $type = $config['type'];

        $info=$this->walletRecordModel->getInfoByType($uId,$uwrType,$num);

        for($i=0;$i<count($info);$i++)
        {
            $info[$i]['uwr_created_time']=date('Y-m-d H:i:s',$info[$i]['uwr_created_time']);

            $info[$i]['uwr_Type'] = $type[$info[$i]['uwr_type']];

            $info[$i]['uwr_money'] = sprintf("%0.1f",$info[$i]['uwr_money']);

        }

        return $info;
    }

    /**
     * 根据出入账类型和uid获取所有的出入账信息
     * @param type $uId
     * @param type $uwrType
     * @return type
     */
    public function getTotalInfo($uId,$uwrType)
    {
        return $this->walletRecordModel->getTotalInfo($uId,$uwrType);
    }

    public function getTotalByType($type,$startDay, $endDay)
    {
         return $this->walletRecordModel->getTotalByType($type,$startDay, $endDay);
    }

    public function getTotalBalance($startDay)
    {
        return $this->walletRecordModel->getTotalBalance($startDay);
    }

    /**
     * 后台获取个人出入账记录
     * @param  [type] $uId       [description]
     * @param  [type] $type      [description]
     * @param  [type] $startTime [description]
     * @param  [type] $endTime   [description]
     * @param  [type] $page      [description]
     * @param  [type] $nums      [description]
     * @return [type]            [description]
     */
    public function userRecordLists($uId, $type, $startTime, $endTime, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $res['total'] = $this->walletRecordModel->getRecordTotal($uId, $type, $startTime, $endTime);
        $res['lists'] = $this->walletRecordModel->userRecordLists($uId, $type, $startTime, $endTime, $start, $nums);

        return $res;
    }

    /**
     * API接口分页获取当前用户帐变记录
     * @param  [type] $uId  [description]
     * @param  [type] $page [description]
     * @param  [type] $nums [description]
     * @return [type]       [description]
     */
    public function getUserLists($uId, $type, $page, $nums)
    {
        $fields = 'uwr_id, uwr_money, uwr_type, uwr_created_time, uwr_memo';
        $start = ($page - 1) * $nums;
        return $this->walletRecordModel->getUserLists($uId, $type, $start, $nums, $fields);
    }

    /**
     * API接口统计用户所有帐变记录
     * @param  [type] $uId  [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getUserTotal($uId, $type)
    {
        return $this->walletRecordModel->getUserTotal($uId, $type);
    }

    /**
     * API接口获取批量用户及下线帐变记录列表
     * @param  [type] $uIds     [description]
     * @param  [type] $type     [description]
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @param  [type] $page     [description]
     * @param  [type] $nums     [description]
     * @return [type]           [description]
     */
    public function getListsByUids($uIds, $type, $startDay, $endDay, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $fields = 'u_id, uwr_money, uwr_type, uwr_created_time';
        return $this->walletRecordModel->getListsByUids($uIds, $type, $startDay, $endDay, $start, $nums, $fields);
    }

    /**
     * API接口获取批量用户帐变记录总数
     * @param  [type]  $uIds     [description]
     * @param  [type]  $type     [description]
     * @param  [type]  $startDay [description]
     * @param  [type]  $endDay   [description]
     * @return [type]            [description]
     */
    public function getListsTotal($uIds, $type, $startDay, $endDay)
    {
        return $this->walletRecordModel->getListsTotal($uIds, $type, $startDay, $endDay);
    }
}
