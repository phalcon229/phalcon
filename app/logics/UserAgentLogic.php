<?php
class UserAgentLogic extends LogicBase
{

    public function __construct()
    {
        $this->userAgentModel = new UserAgentModel;
    }

    /**
     * 根据时间段，uid获取代理信息
     * @param type $uId
     * @param type $endDay
     * @param type $startDay
     * @return type
     */
    public function getTimeAgentNum($uId,$endDay,$startDay)
    {
        return $this->userAgentModel->getAllAgent($uId,$endDay,$startDay);
    }

    /**
     * 获取名字，代理类型等基础信息
     * @param type $uId
     * @param type $startDay
     * @param type $endDay
     * @return type
     */
    public function getBaseInfo($uId,$startDay,$endDay)
    {
        return $this->userAgentModel->getBaseInfo($uId,$startDay,$endDay);
    }

    public function lists($uId, $uName, $startTime, $endTime)
    {
        $res = $this->userAgentModel->lists($uId, $uName, $startTime, $endTime);
        foreach ($res as $key=>$value)
        {
            $res[$key]['w_money'] = sprintf("%.1f", $res[$key]['w_money']);
        }
        return $res;
    }

    /**
     * 获取团队人数及注册人数
     * @param  integer $uaUid    [description]
     * @param  boolean $startDay [description]
     * @param  boolean $endDay   [description]
     * @return [type]            [description]
     */
    public function getAgentStat($uaUid = 0, $startDay = false, $endDay = false)
    {
        $result = $this->userAgentModel->getAgentStat($uaUid, $startDay, $endDay);

        $res['ua_team_num'] = $res['ua_reg_nums'] = 0;
        foreach ($result as $value) {
            $res['ua_team_num'] += $value['ua_team_num'];
            $res['ua_reg_nums'] += $value['ua_reg_nums'];
        }
        return $res;
    }

    /**
     * 根据id获取代理信息
     * @param type $id
     * @param type $fields
     * @return type
     */
    public function getInfo($id, $fields = "*")
    {
        return $this->userAgentModel->getInfo($id, $fields);
    }

    public function getIds($uId)
    {
        return $this->userAgentModel->getIds($uId);
    }

    public function updateRate($uId, $rate)
    {
        return $this->userAgentModel->updateRate($uId, $rate);
    }

    public function getAgentName($uId)
    {
        return $this->userAgentModel->getAgentName($uId);
    }

    /**
     * 获取当前用户下级代理信息条数
     * @param  [type] $name     [description]
     * @param  [type] $minMoney [description]
     * @param  [type] $maxMoney [description]
     * @return [type]           [description]
     */
    public function getTotalAgent($name, $minMoney, $maxMoney)
    {
        return $this->userAgentModel->getTotalAgent($name, $minMoney, $maxMoney);
    }

    /**
     * 获取当前用户下级代理信息
     * @param  [type] $name     [description]
     * @param  [type] $minMoney [description]
     * @param  [type] $maxMoney [description]
     * @return [type]           [description]
     */
    public function getAgentInfo($name, $minMoney, $maxMoney, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        return $this->userAgentModel->getAgentInfo($name, $minMoney, $maxMoney, $start, $perPage);
    }

    /**
     * 设置对应代理的返点率
     * @param [type] $uId [description]
     * @param [type] $rate    [description]
     */
    public function setAgentRate($uId, $rate)
    {
        return $this->userAgentModel->setAgentRate($uId, $rate);
    }

    /**
     *获取团队总人数
     * @param type $uId
     */
    public function getTeamNum($uId)
    {
         return $this->userAgentModel->getTeamNum($uId);
    }

    /**
     * 获取所有下级人员信息
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getAllAgent($uId)
    {
        return $this->userAgentModel->getAllAgent($uId);
    }

    public function getBackAgent($uId,$page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        return $this->userAgentModel->getAllAgent($uId,$start,$perPage);
    }
    /**
     * 获取所有下级人员信息（根据页数）
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getAgent($name, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        return $this->userAgentModel->getAgent($name, $start, $perPage);
    }

    /**
     * 计算统计代理人员人数
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function countAgent($uId)
    {
        $agents = $this->getAllAgent($uId);
        $nums = 0;
        foreach ($agents as $value) {
            if ($value['ua_type'] == 3)
                $nums += 1;
            else
                continue;
        }

        return $nums;
    }
    /**
    获取用户直属下级人数
    **/
    public function getCountNext($uId)
    {
        return $this->userAgentModel->getCountNext($uId);
    }

    /**
    获取用户直属下级u_id
    **/
    public function getNextUid( $page, $perPage, $uId)
    {
        $start = ($page - 1) * $perPage;
        return $this->userAgentModel->getNextUid($uId, $start, $perPage);
    }
    /**
     * 获取登入用户的类型
     * @param type $uId
     */
    public function getTypeByUid($uId)
    {
        $info = $this->userAgentModel->getTypeByUid($uId);
        if($info['ua_type'] == 1)
            $info['ua_type'] = '会员';
        else
            $info['ua_type'] = '代理';
        return $info;

    }

    /**
     * 获取所有uIds用户的代理商信息
     * @param  [type] $uIds [description]
     * @return [type]       [description]
     */
    public function getUserAgentByUids($uIds)
    {
        return $this->userAgentModel->getUserAgentByUids($uIds);
    }

    /**
     * 获取用户所有下级代理中的最高返点率
     * @param  [type]  $uId    [description]
     * @param  boolean $status [description]
     * @return [type]          [description]
     */
    public function getAgentMaxRate($uId, $status = false)
    {
        return $this->userAgentModel->getAgentMaxRate($uId, $status);
    }

    public function getAgentByUid($uId, $start, $perPage)
    {
        return $this->userAgentModel->getAgentByUid($uId, $start, $perPage);
    }

    public function getAgentInfoByUid($uId)
    {
        return $this->userAgentModel->getAgentInfoByUid($uId);
    }

    public function getDown($uId)
    {
        return $this->userAgentModel->getDown($uId);
    }

    public function getAgentByName($name)
    {
        return $this->userAgentModel->getAgentByName($name);
    }

    public function editMemo($info, $uId)
    {
        return $this->userAgentModel->editMemo($info, $uId);
    }

    public function getMemo($uId)
    {
        return $this->userAgentModel->getMemo($uId);
    }

    public function makeQR($code)
    {
        require_once( ROOT_PATH . 'components/utils/phpqrcode.php');
        $enc = QRencode::factory(QR_ECLEVEL_L, 3, 4);
        $binarize = $enc->encode($code);
        $image = QRimage::image($binarize, 5, 0);
        ob_start();
        imagepng($image);
        $pngData = ob_get_contents();
        ob_end_clean();
        return 'data:image/png;base64,'.base64_encode($pngData);
    }

    public function getMaxRate($uId)
    {
        return $this->userAgentModel->getMaxRate($uId);
    }

    public function getAgentInfosByUids($uIds, $fields)
    {
        return $this->userAgentModel->getAgentInfosByUids($uIds, $fields);
    }

    /**
     * API接口根据条件得到下级代理列表，带分页
     * @param  [array] $data [description]
     * @param  [type] $page [description]
     * @param  [type] $nums [description]
     * @return [type]       [description]
     */
    public function getAgentLists($data, $page, $nums)
    {
        $start = ($page - 1) * $nums;
        $fields = 'u_id, u_name, ua_type, ua_rate, ua_status, ua_memo';
        return $this->userAgentModel->getAgentLists($data, $start, $nums, $fields);
    }
}
