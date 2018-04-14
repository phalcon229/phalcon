<?php

class ActivityController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];
        $this->activityLogic = new ActivityLogic;
    }

    public function indexAction()
    {
        $time = strtotime(date('Y-m-d H:i:s'), time());
        $activityInfo = $this->activityLogic->getActivityInfo($time);

        $this->view->setVar('activityInfo', $activityInfo);
        $this->view->setVar('title', '活动');
    }

    public function detailAction()
    {
        $paId = trim($this->request->get('paId'));
        $detail = $this->activityLogic->getDetailById($paId);

        $this->view->setVar('detail', $detail);
        $this->view->setVar('title', '活动详情');
    }

}
