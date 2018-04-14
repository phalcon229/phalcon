<?php use Phalcon\Security;  
class MoneyTotalLogic extends LogicBase 
{      
    public function __construct()     
    {         
        $this->model = new MoneyTotalModel;     
        
    }      
    
    public function getInfoByTime($start,$end)
    {
        $info = $this->model->getInfoByTime($start,$end);
        for($i = 0; $i < count($info); $i++)
        {
            $info[$i]['st_date'] = date('Y-m-d',$info[$i]['st_date']);
        }
        return $info;
    }
    
}