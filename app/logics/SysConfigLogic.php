<?php

class SysConfigLogic extends LogicBase
{
    public function __construct()
    {
        $this->model = new SysConfigModel;
    }

    /**
     * 获取单个或所有系统配置
     * @param  boolean $scId [description]
     * @return [type]        [description]
     */
    public function getSysConfig($scId = false, $fields = '*')
    {
        return $this->model->getSysConfig($scId, $fields);
    }

    /**
     * 获取充值或者提现的金额限制
     * @param type $scId
     * @param type $id
     * @return type
     */
    public function getRechargeLimit($scId, $id)
    {
        return $this->model->getRechargeLimit($scId, $id);
    }
}