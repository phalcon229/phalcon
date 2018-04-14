<?php

class BetsRulesLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new BetsRulesModel;
    }

    /**
     * 根据彩种id获取相应的配置信息
     * @param type $betId
     * @return type
     */
    public function getInfoById($betId)
    {
        return $this->model->getInfoById($betId);
    }

    public function getInfo($gameId, $type)
    {
        return $this->model->getInfo($gameId, $type);
    }
    /**
     * 查询判断某一赔率brId详情
     * @param  [type] $brId [description]
     * @return [type]       [description]
     */
    public function getRuleByBrId($brId, $status = false)
    {
        return $this->model->getRuleByBrId($brId, $status);
    }

    public function editBonus($brId, $bonus)
    {
        return $this->model->editBonus($brId, $bonus);
    }

    /**
     * 根据彩种id获取玩法信息
     * @param type $betId
     * @return type
     */
    public function getPlayWay($betId)
    {
        $info = $this->model->getPlayWay($betId);
        return $info;
    }

}