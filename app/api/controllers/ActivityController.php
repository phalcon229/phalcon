<?php

class ActivityController extends ControllerBase
{
    public function initialize()
    {
        $this->activityLogic = new ActivityLogic;
    }

    //活动列表
    public function indexAction()
    {
        $activityInfo = $this->activityLogic->getActivityLists();

        return $this->di['helper']->resRet($activityInfo, 200);
    }

    //活动详情
    public function detailAction()
    {
        if (!$paId = intval($this->request->get('paId')))
            return $this->di['helper']->resRet('参数错误', 500);

        $detail = $this->activityLogic->detail($paId);

        if (!$detail)
            return $this->di['helper']->resRet('活动不存在', 500);

        return $this->di['helper']->resRet($detail);
    }

}
