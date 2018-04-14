<?php

class HistoryController extends ControllerBase
{

    public function initialize()
    {
        $this->sysLogic = new SystemLogic;
        $this->hisLogic = new HistoryLogic;

    }

    public function indexAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;
        $betType = $this->sysLogic->getBetsType();

        $bId = !empty($this->request->getQuery('type')) ? intval($this->request->getQuery('type')) : $betType[0]['bet_id'];

        $date = $this->hisLogic->getHistoryResult($bId, $page, $limit);
        $date['limit'] = ceil($date['total']/$limit);
        foreach ($betType as $key => $value) {
           $type[$value['bet_id']] = $value['bet_name'];
        }
        $this->di['view']->setVars([
                'date' => $date,
                'type' => $type,
                'perpage' => $this->di['config']['admin']['perPage'],
                'game' => $this->di['config']
            ]);
    }
}
