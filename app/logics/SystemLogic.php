<?php
class SystemLogic extends LogicBase
{

    public function __construct()
    {
        $this->sysModel = new SystemModel;
    }

    public function getBets($bId)
    {
        return $this->sysModel->getBets($bId);
    }

    public function getBetsType()
    {
        return $this->sysModel->getBetsType();
    }

    public function getBetRules($bId)
    {
        return $this->sysModel->getBetRules($bId);
    }

    public function getBetRulesBybrid($brId)
    {
        return $this->sysModel->getBetRulesBybrid($brId);
    }

    public function editBetRule($brId, $bonus)
    {
        return $this->sysModel->editBetRule($brId, $bonus);
    }

    public function editBetRules($brIds, $bonus)
    {
        return $this->sysModel->editBetRules($brIds, $bonus);
    }

    public function doConfSet($key, $condition)
    {
        return $this->sysModel->doConfSet($key, $condition);
    }

    public function getAcList($page, $limit)
    {
        $start = ($page - 1) * $limit;
        $res['total'] = $this->sysModel->getAcNum();
        $res['list'] = $this->sysModel->getAcLimit($start, $limit);
        return $res;
    }

    public function getActive($aId)
    {
        return $this->sysModel->getActive($aId);
    }

    public function activeAdd($date)
    {
        return $this->sysModel->activeAdd($date);
    }

    public function activeEdit($aId, $atitle, $acontent, $img)
    {
        return $this->sysModel->activeEdit($aId, $atitle, $acontent, $img);
    }

    public function delActive($aId)
    {
        return $this->sysModel->delActive($aId);
    }

    public function getNtList($page, $limit)
    {
        $start = ($page - 1) * $limit;
        $res['total'] = $this->sysModel->getNtNum();
        $res['list'] = $this->sysModel->getNtLimit($start, $limit);
        return $res;
    }

    public function delNotice($ntId)
    {
        return $this->sysModel->delNotice($ntId);
    }

    public function getNotice($ntId)
    {
        return $this->sysModel->getNotice($ntId);
    }

    public function addNotice($condition)
    {
        return $this->sysModel->addNotice($condition);
    }

    public function getBase()
    {
        return $this->sysModel->getBase();
    }

    public function editBase($info)
    {
        return $this->sysModel->editBase($info);
    }

    public function noticeEdit($ntId, $ntitle, $ncontent)
    {
        return $this->sysModel->noticeEdit($ntId, $ntitle, $ncontent);
    }

    public function payList($page, $limit)
    {
        $start = ($page - 1) * $limit;
        $res['total'] = $this->sysModel->getPayNum();
        $res['list'] = $this->sysModel->getPayLimit($start, $limit);
        return $res;
    }

    public function doChangestatus($id, $status)
    {
        return $this->sysModel->DoChangestatus($id, $status);
    }

    public function payReg($id, $min, $max, $json)
    {
        return $this->sysModel->payReg($id, $min, $max, $json);
    }


    public function bannerList($page, $limit)
    {
        $start = ($page - 1) * $limit;
        $res['total'] = $this->sysModel->getBannerNum();
        $res['list'] = $this->sysModel->getBannerLimit($start, $limit);
        return $res;
    }

    public function getUrl()
    {
        return $this->sysModel->getUrl();
    }

    public function addBanner($title, $sort, $URL, $img)
    {
        $date['ib_sort'] = $sort;
        $date['ib_desc'] = $title;
        $date['ib_img'] = $img;
        $date['ib_url'] = $URL;
        $date['ib_created_time'] = $_SERVER['REQUEST_TIME'];
        $date['ib_status'] = 1;
        return $this->sysModel->addBanner($date);
    }

    public function delBanner($ibId)
    {
        return $this->sysModel->delBanner($ibId);
    }

    public function getBanner($ibId)
    {
        return $this->sysModel->getBanner($ibId);
    }

    public function editBanner($ibId, $title, $sort, $URL, $img)
    {
        if (!empty($title))
            $date['ib_desc'] = $title;
        if (!empty($img))
            $date['ib_img'] = $img;
        if (!empty($URL))
            $date['ib_url'] = $URL;
        if (!empty($sort))
            $date['ib_sort'] = $sort;

        return $this->sysModel->editBanner($ibId, $date);
    }

    public function getPayConf($type)
    {
        return $this->sysModel->getPayConf($type);
    }

    /**
     * 获悉系统返点率
     * @return [type] [description]
     */
    public function getRate()
    {
        return $this->sysModel->getValue(3)['sc_value'];
    }

    /**
     * 获取追号最大期数
     * @return [type] [description]
     */
    public function getTrackMax()
    {
        return $this->sysModel->getValue(9)['sc_value'];
    }

    /**
     * 获取获取封单时间
     * @return [type] [description]
     */
    public function getClosetime()
    {
        return $this->sysModel->getValue(2)['sc_value'];
    }

    public function stringLen($var, $type = 3)
    {
        $i = 0;
        $count = 0;
        $len = strlen($var);
        while($i < $len)
        {
            $chr = ord($var[$i]);
            $count++;
            $i++;
            if($i > $len)
                break;

            if($chr & 0x80)
            {
                $chr <<= 1;
                while($chr & 0x80)
                {
                    $i++;
                    $chr <<= 1;
                }

                if($type == 3)
                    $count++;
                else if($type == 1)
                    $count += 2;
            }
        }

        return $count;
    }
}
