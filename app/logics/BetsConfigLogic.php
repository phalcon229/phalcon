<?php
use Phalcon\Security;

class BetsConfigLogic extends LogicBase
{

    public function __construct()
    {
        $this->model = new BetsConfigModel();
    }
    /**
     * 获取所有彩种信息
     * @return type
     */
    public function getLotteryType()
    {
        return $this->model->getLotteryType();
    }

    /**
     * 根据彩种id获取采种信息
     * @param type $gameId
     * @return type
     */
    public function getInfoById($gameId, $type='')
    {
        return $this->model->getInfoById($gameId, $type);
    }

    public function getInfoByIdAllStatus($gameId, $type='')
    {
        return $this->model->getInfoByIdAllStatus($gameId, $type);
    }

    /**
     * 获取所有彩种
     * @param  boolean $status [description]
     * @return [type]          [description]
     */
    public function getAll($status = false)
    {
        return $this->model->allBets($status);
    }

    /**
     * 将所有彩种转换为一维数组
     * @param  boolean $status [description]
     * @return [type]          [description]
     */
    public function getBets($status = false)
    {
        $res = $this->getAll($status);
        $bets = [];
        foreach ($res as $value) {
            $bets[$value['bet_id']] = $value['bet_name'];
        }
        return $bets;
    }

    /**
     * 获取所有彩种信息
     * @return type
     */
    public function getAllBets()
    {
        return $this->model->getAllBets();
    }
    /**
     * 根据彩种ids查询采种信息
     * @param type $ids
     * @return type
     */
    public function getBetsInfoByIds($ids, $type)
    {
        return $this->model->getBetsInfoByIds($ids, $type);
    }

    /**
     * 根据采种ids查询不在ids里面的彩种的信息
     * @param type $ids
     * @return type
     */
    public function getBetsInfo($ids)
    {
        return $this->model->getBetsInfo($ids);
    }

    public function getBet()
    {
        return $this->model->getBets();
    }

    /**
     * 获取采种的单注最高与最低
     */
    public function getLimit($betId)
    {
        return $this->model->getLimit($betId);
    }

    /**
     * 获取开启中的彩种信息
     * @return type
     */
    public function getOpenBets($fields = '*')
    {
        return $this->model->getOpenBets($fields);
    }

    /**
     * 获取开启中的彩种信息
     * @return type
     */
    public function getOpenBetsId($type)
    {
        return $this->model->getOpenBetsId($type);
    }
}