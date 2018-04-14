<?php use Phalcon\Security;  
class NoticeLogic extends LogicBase 
{      
    public function __construct()     
    {         
        $this->model = new NoticeModel;     
        
    }      
    
    /**
     * 获取所有的广告信息
     * @return type
     */
    public function getNotice()     
    {         
        return $this->model->getNotice();       
    }  
    
}