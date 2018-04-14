<?php
class AgentLogic extends LogicBase
{

    public function __construct()
    {
        $this->agentModel = new AgentModel;
    }
/**
 * 通过uid获取团队总人数
 * @param type $uId
 * @return type
 */
    public function getAgentNumByUserId($uId)
    {
        return $this->agentModel->getNumByUserId($uId);
    }

    public function lists($uId, $uName, $startTime, $endTime)
    {
        return $this->agentModel->lists($uId, $uName, $startTime, $endTime);
    }
}
