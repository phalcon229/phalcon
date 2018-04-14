<?php
use Phalcon\Security;

class LogLogic extends LogicBase
{

    public function __construct()
    {
        $this->logModel = new LogModel;
    }

    /**
     * 添加日志信息
     * @param [type] $adminId    [description]
     * @param [type] $uName      [description]
     * @param [type] $roleId      [description]
     * @param [type] $controller [description]
     * @param [type] $action     [description]
     * @param [type] $content    [description]
     * @param [type] $logip      [description]
     */
    public function addLog($adminId, $uName, $roleId, $controller, $action, $logip, $content = '')
    {
        return $this->logModel->addInfo($adminId, $uName, $roleId, $controller, $action, $logip, $content);
    }

    /**
     * 分页获取列表
     * @param  integer $currentPage [description]
     * @param  integer $perRows   [description]
     * @return [type]           [description]
     */
    public function getListByPage($currentPage = 1, $perRows = 10, $uName = false, $roleId, $startTime = 0, $endTime = 0)
    {
        $index = ($currentPage - 1) * $perRows;

        return $this->logModel->getInfoLimit($index, $perRows, $uName, $roleId, $startTime, $endTime);
    }

    /**
     * 获取日志记录总数
     * @return [type] [description]
     */
    public function getTotal($uName = false, $roleId = false, $startTime = 0, $endTime = 0)
    {
        return $this->logModel->getTotalNum($uName, $roleId, $startTime, $endTime)['total'];
    }

    /**
     * 删除日志
     * @param  [type] $adminId [description]
     * @return [type]          [description]
     */
    public function delLog($logIds)
    {
        return $this->logModel->delInfo($logIds);
    }
}