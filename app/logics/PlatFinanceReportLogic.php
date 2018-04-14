<?php use Phalcon\Security;  
class PlatFinanceReportLogic extends LogicBase 
{      
    public function __construct()     
    {         
        $this->model = new PlatFinanceReportModel;     
        
    }
    
    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        return $this->model->getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay);
    }

    public function getTeamTime($ids,$startDay,$endDay)
    {
        return $this->model->getTeamTime($ids,$startDay,$endDay);
    }
    
}