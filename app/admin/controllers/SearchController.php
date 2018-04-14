<?php

class SearchController extends ControllerBase
{

    public function initialize()
    {
        $this->FormsLogic = new FormsLogic;
        $this->sysLogic = new SystemLogic;
        $this->userAgentLogic = new UserAgentLogic();
    }

    public function indexAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? intval($this->request->getQuery('limit')) : current($perpage);
        $conditions['bet_id'] = $this->request->getQuery('type');
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? intval($this->request->getQuery('page')) : 1;
        empty($page) AND $page=1;

        $betType = $this->sysLogic->getBetsType();
        $type[0] = '所有类型';
        foreach ($betType as $key => $value)
            $type[$value['bet_id']] = $value['bet_name'];

        if (!empty($conditions['bet_id'])) {
            $date = $this->FormsLogic->getFromDate($conditions['bet_id']);
            foreach ($date as $value)
                $bdate[] = $value['bres_periods'];
        }

        $conditions['radio'] = $this->request->getQuery('radio');
        if (!empty($conditions['radio']))
        {
            switch ($conditions['radio']) {
                case 1:
                    $conditions['periods'] = $this->request->getQuery('date');
                    break;

                case 3:
                    $conditions['startTime'] = !empty($this->request->getQuery('startTime')) ? strtotime($this->request->getQuery('startTime')) : '';
                    $conditions['endTime'] = !empty($this->request->getQuery('endTime')) ? strtotime($this->request->getQuery('endTime')) : '';
                    break;
                case 5:
                    $conditions['sn'] = !empty($this->request->getQuery('sn')) ? $this->request->getQuery('sn') : '';
                    break;
            }
        }
        $conditions['nick'] = !empty($this->request->getQuery('nick')) ? $this->request->getQuery('nick') : '';
        $conditions['uid'] = !empty($this->request->getQuery('uid')) ? $this->request->getQuery('uid') : '';

        $res = $this->FormsLogic->getFormsList($page, $limit, $conditions);
        $res['limit'] = ceil($res['total']/$limit);

        $this->di['view']->setVars([
            'type' => $type,
            'date' => $bdate,
            'info' => $res,
            'perpage' => $perpage,
            'game' => $this->di['config']['game']
        ]);
    }
}
