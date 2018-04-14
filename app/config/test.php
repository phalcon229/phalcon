<?php
    function set11expectsAction()
    {
        date_default_timezone_set('Asia/Shanghai');
        $betId = $this->bjpk11;
        // 查询最近一期已经开奖的期号
        $betsResultModel = new BetsResultModel;
        $expectInfo = $betsResultModel->getLastExpectByBetId($betId);
    }