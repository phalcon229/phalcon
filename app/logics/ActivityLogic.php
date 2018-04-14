<?php
use Phalcon\Security;

class ActivityLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new ActivityModel;
    }

    /**
     * 获取所有的活动信息
     * @return string
     */
    public function getActivityInfo()
    {
        $info = $this->model->getRecharge();

        $now = strtotime(date('Y-m-d H:i:s',time()));
        for($i = 0; $i < count($info); $i++)
        {
            if($info[$i]['pa_starttime'] > $now)
            {
                $info[$i]['if_end'] = '未开始';
            }
            else
            {
                if($info[$i]['pa_endtime'] < $now)
                {
                    $info[$i]['if_end'] = '已结束';
                }
                else
                {
                    $info[$i]['if_end'] = '进行中';
                }
            }
            $info[$i]['pa_starttime'] = date('Y-m-d H:i:s',$info[$i]['pa_starttime']);
            $info[$i]['pa_endtime'] = date('Y-m-d H:i:s',$info[$i]['pa_endtime']);

        }
        return $info;
    }
    /**
     * 通过活动id查询活动详情信息
     * @param type $paId
     * @return string
     */
    public function getDetailById($paId)
    {
        $info = $this->model->getDetailById($paId);
        if(!empty($info))
            {
            $now = strtotime(date('Y-m-d H:i:s',time()));
            if($info['pa_starttime'] > $now)
            {
                $info['if_end'] = '未开始';
            }
            else
            {
                if($info['pa_endtime'] < $now)
                {
                    $info['if_end'] = '已结束';
                }
                else
                {
                    $info['if_end'] = '进行中';
                }
            }
            $info['pa_starttime'] = date('Y-m-d H:i:s',$info['pa_starttime']);
            $info['pa_endtime'] = date('Y-m-d H:i:s',$info['pa_endtime']);

        }
        return $info;
    }

    public function info($paId)
    {
        return $this->model->getDetailById($paId);
    }

    /**
     * API接口获取活动列表
     * @return [array] [description]
     */
    public function getActivityLists()
    {
        $lists = $this->model->getActivityInfo('pa_id, pa_title, pa_img, pa_starttime, pa_endtime');
        $now = $_SERVER['REQUEST_TIME'];
        $res = [];
        foreach ($lists as $k => $v) {
            if ($v['pa_starttime'] > $now)
                $v['if_end'] = 0;//活动未开始
            else
            {
                if ($v['pa_endtime'] < $now)
                    $v['if_end'] = 2;//已结束
                else
                    $v['if_end'] = 1;//进行中
            }
            unset($v['pa_starttime']);
            unset($v['pa_endtime']);
            $res[$k] = $v;
        }

        return $res;
    }

    /**
     * API接口获取活动详情
     * @param  [Int] $paId [description]
     * @return [array]       [description]
     */
    public function detail($paId)
    {
        $detail = $this->model->getDetailById($paId, 'pa_id, pa_title, pa_starttime, pa_endtime');
        if (!$detail)
            return $detail;

        $now = $_SERVER['REQUEST_TIME'];
        if ($detail['pa_starttime'] > $now)
            $detail['if_end'] = 0;//活动未开始
        else
        {
            if ($detail['pa_endtime'] < $now)
                $detail['if_end'] = 2;//已结束
            else
                $detail['if_end'] = 1;//进行中
        }

        return $detail;
    }
}