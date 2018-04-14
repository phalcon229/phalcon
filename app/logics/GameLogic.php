<?php

class GameLogic extends LogicBase
{
    public function __construct()
    {
        $this->systemLogic = new SystemLogic();
    }

    /**
     * 获取当前期信息
     * @return [type]        [description]
     */
    public function currentPerid($betId)
    {
        if (!$current = $this->di['redis']->get('bets:next:' . $betId))
            return false;
        $current['interval'] = $this->systemLogic->getClosetime() ?: 30;
        $current['nowTime'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $current['closeTime'] = $current['openTime'] - $current['interval'];
        return [$current];
    }

    /**
     * 验证彩种期数是否可操作（下注、撤单）
     * @return [type] [description]
     */
    public function checkAvail($betId, $perid)
    {
        if (!$current = $this->di['redis']->get('bets:next:' . $betId))
            return false;

        if (!$closeTime = $this->systemLogic->getClosetime())
            return false;

        return $perid >= $current['expect'] && time() <= ($current['openTime'] - $closeTime);
    }
}