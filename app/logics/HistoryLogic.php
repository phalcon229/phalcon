<?php
class HistoryLogic extends LogicBase
{

    public function __construct()
    {
        $this->hisModel = new HistoryModel;
    }

    public function getHistoryResult($bId, $page, $limit)
    {
        $start = ($page - 1) * $limit;
        $res['total'] = $this->hisModel->getHisNum($bId);
        $res['list'] = $this->hisModel->getHisLimit($bId, $page, $start, $limit);
        return $res;
    }
}
