<?php
class RecommendLogic extends LogicBase
{

    public function __construct()
    {
        $this->recommendModel = new RecommendModel;
    }

    public function getLists($uId, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        $res['total'] = $this->recommendModel->getTotal($uId);
        $res['lists'] = $this->recommendModel->getLists($uId, $start, $perPage);

        return $res;
    }

    /**
     * 添加推广链接
     * @param [type] $uId  [description]
     * @param [type] $type [description]
     * @param [type] $rate [description]
     */
    public function addLink($uId, $type, $rate)
    {
        $random = new Phalcon\Security\Random();
        $code = $random->hex(8);

        return $this->recommendModel->addLink($uId, $code, $type, $rate);
    }

    /**
     * 获取推广链接详情
     * @param  [type] $urId [description]
     * @return [type]       [description]
     */
    public function getDetail($urId)
    {
        return $this->recommendModel->getDetail($urId);
    }

    /**
     * 获取推广链接详情
     * @param  [type] $urId [description]
     * @return [type]       [description]
     */
    public function getDetailByUrid($urId)
    {
        $info = $this->recommendModel->getDetailByUrid($urId);

        $info['ur_created_time'] = date('Y-m-d H:i:s',$info['ur_created_time']);
        if($info['ur_type'] == 1)
        {
            $info['ur_type'] = '会员';
        }
        else
        {
            $info['ur_type'] = '代理';
        }

        return $info;
    }

    /**
     * 更新推广链接
     * @param  [type] $uId  [description]
     * @param  [type] $urId [description]
     * @param  [type] $type [description]
     * @param  [type] $rate [description]
     * @return [type]       [description]
     */
    public function updateLink($uId, $urId, $type, $rate)
    {
        return $this->recommendModel->updateLink($uId, $urId, $type, $rate);
    }

    /**
     * 删除推广链接
     * @param  [type] $uId  [description]
     * @param  [type] $urId [description]
     * @return [type]       [description]
     */
    public function delLink($uId, $urId)
    {
        return $this->recommendModel->delLink($uId, $urId);
    }

    /**
     * 增加访问计数
     * @return [type] [description]
     */
    public function increVisit($code)
    {
        return $this->recommendModel->increVisit($code);
    }

    public function detailByCode($code)
    {
        return $this->recommendModel->detailByCode($code);
    }

    /**
     * 增加注册人数
     * @return [type] [description]
     */
    public function increReg($code)
    {
        return $this->recommendModel->increReg($code);
    }

    /**
     * API接口获取分页推广链接，不需要总条数
     * @param  [type] $uId     [description]
     * @param  [type] $page    [description]
     * @param  [type] $perPage [description]
     * @return [type]          [description]
     */
    public function lists($uId, $page, $perPage)
    {
        $start = ($page - 1) * $perPage;
        $fields = 'ur_id, ur_code, ur_type, ur_created_time, ur_fandian, ur_visitor_nums, ur_reg_nums';
        $res = $this->recommendModel->getLists($uId, $start, $perPage, $fields);

        return $res;
    }
}
