<?php
use Phalcon\Security;

class   BannerLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new BannerModel;
    }

    /**
     * 获取轮播信息
     * @return type
     */
    public function getBannerInfo()
    {
        $info = $this->model->getBannerInfo();
        return $info;
    }
}