<?php

class LogController extends ControllerBase
{
    public function initialize()
    {
        $this->logLogic = new LogLogic;
        $this->adminLogic = new AdminLogic;
    }

    public function indexAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        if (empty($page))
            $page = 1;
        $uname =  $this->request->getQuery('name') ?: '';
        $roleId = $this->request->getQuery('role') ?: '';
        $startTime = $this->request->getQuery('startTime') ?: '';
        $endTime = $this->request->getQuery('endTime') ?: '';

        $total = $this->logLogic->getTotal($uname, $roleId, strtotime($startTime), strtotime($endTime));
        $logList = $this->logLogic->getListByPage($page, $limit, $uname, $roleId, strtotime($startTime), strtotime($endTime));

        //获取用户类型分组列表
        $groups = [];
        $groupInfo = $this->adminLogic->groupLists();
        foreach ($groupInfo as $value) {
            $groups[$value['pg_id']] = $value['pg_name'];
        }

        $this->view->setVars([
            'lists' => $logList,
            'groups' => $groups,
            'total' => $total,
            'numsPage' => $perpage,
            'nums' => $limit
        ]);
    }

}
