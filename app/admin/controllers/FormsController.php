<?php

class FormsController extends ControllerBase
{

    public function initialize()
    {
        $this->FormsLogic = new FormsLogic;
        $this->sysLogic = new SystemLogic;
        $this->userAgentLogic = new UserAgentLogic();
        $this->teamLogic = new  TeamLogic();
         $this->logic = new BetOrdersLogic();
    }

    public function totalAction()
    {   
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = !empty($this->request->getQuery('limit')) ? $this->request->getQuery('limit') : current($perpage);
        $conditions['bet_id'] = $this->request->getQuery('type')?$this->request->getQuery('type'):0;
        $issue =  $this->request->getQuery('issue')?$this->request->getQuery('issue'):'';
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $page = 1;
        else
            $page = !empty($this->request->getQuery('page')) ? $this->request->getQuery('page') : 1;

        $betType = $this->sysLogic->getBetsType();
        $type[0] = '所有类型';
        foreach ($betType as $key => $value)
            $type[$value['bet_id']] = $value['bet_name'];

        if (!empty($conditions['bet_id'])) {

            $date = $this->FormsLogic->getFromDate($conditions['bet_id']);  
            foreach ($date as $value)
                $bdate[] = $value['bres_periods'];
        }

        $conditions['pame'] =  !empty($this->request->getQuery('pame')) ? $this->request->getQuery('pame') : 1;
        if (!empty($conditions['pame']))
        {
            switch ($conditions['pame']) {
                case 1:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
                    $conditions['endTime'] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;                   
                    break;
                case 2:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                    $conditions['endTime'] = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                    break;
                case 3:
                    $conditions['startTime'] = mktime(0, 0 , 0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y"));
                    $conditions['endTime'] = mktime(23,59,59,date("m"),date("d")-(date("w")==0?7:date("w"))+7,date("Y"));                    
                    break;
                case 4:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d')-(date("w")==0?7:date("w"))+1-7,date('Y'));
                    $conditions['endTime'] = mktime(23,59,59,date('m'),date('d')-(date("w")==0?7:date("w"))+7-7,date('Y'));
                    break;
                case 5:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),1,date('Y'));
                    $conditions['endTime'] = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    break;
                case 6:
                    $conditions['startTime'] = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                    $conditions['endTime'] = mktime(23,59,59,date("m") ,0,date("Y"));
                    break;
                default:
                    return $this->showMsg('没有数据', true, '/forms/total');
                    break;
            }
        }
        $v = $this->request->getQuery('v' ) ? :1;
        if ($v==1) {
                $res = $this->FormsLogic->getAllBetTotalList($page, $limit, $conditions, $issue);
                if(empty($res['list'][0]['bet_id']))
                {
                    $res['list'][0]['bet_id'] = $conditions['bet_id'];
                }
                $res['total'] = count($res['list']);
                $res['num'] = 0;
                $res['bet'] = 0;
                $res['bonus'] = 0;
                $res['back'] = 0;
                $res['earn'] = 0;
                for($i = 0; $i < $res['total']; $i++)
                {
                    $res['num'] = $res['num'] + $res['list'][$i]['bt_all_orders'];
                    $res['bet'] = $res['bet'] + $res['list'][$i]['bt_all_in'];
                    $res['bonus'] = $res['bonus'] + $res['list'][$i]['bt_all_out'];
                    $res['back'] = $res['back'] + $res['list'][$i]['bt_all_reback'];
                    $res['earn'] = $res['earn'] + $res['list'][$i]['bt_all_earn'];
                }
                $res['limit'] = ceil($res['total']/$limit);
        }
        if ($v==2) {
            $uid = !empty($this->request->getQuery('uid'))?$this->request->getQuery('uid'):0;
            $start = ($page - 1) * $limit;
            if(!empty($issue))
            {
                $info = $this->FormsLogic->getNewTimeTeamInfo($uid,$conditions['bet_id'],$issue,$start,$limit);
                $res['total'] = $this->FormsLogic->getNewTimeTeamInfocount($uid,$conditions['bet_id'],$issue)['total'];
            }
            else
            {
                $info = $this->FormsLogic->getNewAgentTeamInfo($conditions['bet_id'], $uid,$conditions['startTime'], $conditions['endTime'],$start,$limit);
                $res['total'] = $this->FormsLogic->getNewAgentTeamInfocount($conditions['bet_id'],$uid,$conditions['startTime'], $conditions['endTime'])['total'];
            }
            $res['list'] = $info;
            $res['limit'] = ceil($res['total']/$limit);
            $this->view->pick('forms/next');
        }

        if ($v==3) {//没用到
            $conditions['date'] = $this->request->getQuery('date');
            $res = $this->FormsLogic->getPerBetTotalList($page, $limit, $conditions);
            $res['limit'] = ceil($res['total']/$limit);

        }
        if ($v ==4) {
            $uid = intval($this->request->getQuery('uid'));
            $conditions['start'] = $conditions['startTime'];
            $conditions['end'] = $conditions['endTime'];
            $res = $this->FormsLogic->getOrdersInfo(intval($page), intval($limit),$uid, $conditions, $issue);
            $totalM = $this->FormsLogic->getOrdersInfoTotal($uid, $conditions,$issue);
            $totalE = $totalM['bo_bonus']-$totalM['bo_money']+$totalM['bo_back_money'];
            $res['limit'] = ceil($res['total']/$limit);
            $sub_bet = 0;
            $sub_bonus = 0;
            $sub_back = 0;
            $sub_earn = 0;
            foreach ($res['list'] as $key => $value) {
                if($value['bo_status']<>5)
                {
                    $sub_bet += $value['bo_money'];
                    $sub_bonus += $value['bo_bonus'];
                    $sub_back += $value['bo_back_money'];
                    $sub_earn += $value['bo_bonus']-$value['bo_money']+$value['bo_back_money'];
                }
            }
            
            $this->di['view']->setVars([
                'uid' => $uid,
                'sub_bet' => $sub_bet,
                'sub_bonus' => $sub_bonus,
                'sub_back' => $sub_back,
                'sub_earn' => $sub_earn,
                'totalM' => $totalM,
                'totalE' => $totalE
            ]);
            $this->view->pick('forms/detail');
        }
        $this->di['view']->setVars([
            'type' => $type,
            'date' => $bdate,
            'agent' => $allAgent,
            'info' => $res,
            'perpage' => $perpage,
            'game' => $this->di['config']['game'],
            'betId' => $conditions['bet_id'],
            'issue' => $issue,
            'start' => $conditions['startTime'],
            'end' => $conditions['endTime'],
        ]);
    }

    public function excelAction()
    {
        $objPHPExcel = $this->PHPExcel;

        $objPHPExcel->setActiveSheetIndex(0);
        //设置表格样式
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth('20');
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth('20');

        //设置默认行高
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        $issue =  $this->request->getQuery('issue')?$this->request->getQuery('issue'):'';

        $v = $this->request->getQuery('v' ) ? :1;
        //设置表头
        switch ($v) {
            case 1:
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A1','彩种')
                    ->setCellValue('B1','日期')
                    ->setCellValue('C1','期号')
                    ->setCellValue('D1','投注数')
                    ->setCellValue('E1','投注金额')
                    ->setCellValue('F1','派彩金额')
                    ->setCellValue('G1','返点金额')
                    ->setCellValue('H1','净利润');

                break;
            case 2:
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A1','用户名')
                    ->setCellValue('B1','类型')
                    ->setCellValue('C1','投注金额')
                    ->setCellValue('D1','派彩金额')
                    ->setCellValue('E1','回水金额')
                    ->setCellValue('F1','对平台贡献')
                    ->setCellValue('G1','代理返点金额')
                    ->setCellValue('H1','个人投注金额')
                    ->setCellValue('I1','个人派彩金额')
                    ->setCellValue('J1','个人回水金额')
                    ->setCellValue('K1','个人输赢金额');

                break;
            case 4:
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('A1','用户名')
                    ->setCellValue('B1','玩法')
                    ->setCellValue('C1','下注时间')
                    ->setCellValue('D1','订单号')
                    ->setCellValue('E1','状态')
                    ->setCellValue('F1','投注金额')
                    ->setCellValue('G1','回水金额')
                    ->setCellValue('H1','派彩金额')
                    ->setCellValue('I1','净盈利');

                break;
        }
        
        
        $conditions['bet_id'] = !empty($this->request->getQuery('type')) ? $this->request->getQuery('type') : 0;
        $conditions['pame'] =  !empty($this->request->getQuery('pame')) ? $this->request->getQuery('pame') : 1;
        $conditions['uid'] =  !empty($this->request->getQuery('uid')) ? $this->request->getQuery('uid') : 0;
        $time ='';
        if (!empty($conditions['pame']))
        {
            switch ($conditions['pame']) {
                case 1:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d'),date('Y'));
                    $conditions['endTime'] = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                    $time = date('Ymd',$conditions['startTime']);
                    break;
                case 2:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                    $conditions['endTime'] = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                    $time = date('Ymd',$conditions['startTime']);
                    break;
                case 3:
                    $conditions['startTime'] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
                    $conditions['endTime'] = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
                    $time = date('Ymd',$conditions['startTime']).'-'.date('Ymd',$conditions['endTime']);
                    break;
                case 4:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                    $conditions['endTime'] = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                    $time = date('Ymd',$conditions['startTime']).'-'.date('Ymd',$conditions['endTime']);
                    break;
                case 5:
                    $conditions['startTime'] = mktime(0,0,0,date('m'),1,date('Y'));
                    $conditions['endTime'] = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    $time = date('Ymd',$conditions['startTime']).'-'.date('Ymd',$conditions['endTime']);
                    break;
                case 6:
                    $conditions['startTime'] = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                    $conditions['endTime'] = mktime(23,59,59,date("m") ,0,date("Y"));
                    $time = date('Ymd',$conditions['startTime']).'-'.date('Ymd',$conditions['endTime']);
                    break;
                default:
                    return $this->showMsg('没有数据', true, '/forms/total');
                    break;
            }
        }
        $betType = $this->sysLogic->getBetsType();
        foreach ($betType as $key => $value)
            $type[$value['bet_id']] = $value['bet_name'];
        
        switch ($v) {
            case 1:
                $res[0] = $this->FormsLogic->excel($conditions, $v,0,$issue);
                if(empty($res[0]))
                    return $this->showMsg('没有数据', true, '/forms/total');
                $res['total'] = count($res[0]);
                $result[0]['bet_id'] = 6;//名称把采种改为总计
                $result[0]['bt_all_orders'] = 0;
                $result[0]['bt_all_in'] = 0;
                $result[0]['bt_all_out'] = 0;
                $result[0]['bt_all_reback'] = 0;
                $result[0]['bt_all_earn'] = 0;
                for($i = 0; $i < $res['total']; $i++)
                {
                    $result[0]['bt_all_orders'] = $result[0]['bt_all_orders'] + $res[0][$i]['bt_all_orders'];
                    $result[0]['bt_all_in'] = $result[0]['bt_all_in'] + $res[0][$i]['bt_all_in'];
                    $result[0]['bt_all_out'] = $result[0]['bt_all_out'] + $res[0][$i]['bt_all_out'];
                    $result[0]['bt_all_reback'] = $result[0]['bt_all_reback'] + $res[0][$i]['bt_all_reback'];
                    $result[0]['bt_all_earn'] = $result[0]['bt_all_earn'] + $res[0][$i]['bt_all_earn'];
                }
                $res[0][5] = $result[0];
                $title = '_总报表';
                break;
            case 2:               
                $uid = !empty($this->request->getQuery('uid'))?$this->request->getQuery('uid'):0;
                $allAgent = $this->userAgentLogic->getAllAgent($uid);
                $ids = array();
                $len = count($allAgent);
                for($i = 0;$i < $len ; $i++)
                {
                    if(!empty($allAgent[$i]['u_id'])){
                        $ids[$i] = intval($allAgent[$i]['u_id']);
                    }
                }
                if(empty($ids))
                {
                    $info = array(); 
                }
                else 
                {
                     if(!empty($issue))
                    {
                        $info = $this->FormsLogic->getTimeTeamInfo($ids,$conditions['bet_id'], $conditions['startTime'], $conditions['endTime'],$issue);
                    }
                    else
                    {
                        $info = $this->FormsLogic->getAgentTeamInfo($ids,$conditions['bet_id'], $conditions['startTime'], $conditions['endTime']);
                    }
                }
                $res[0] = $allAgent;
                $res[1] = $info;
                $title = $type[$conditions['bet_id']].'_团队报表';
                break;
            case 3:
                $title = $type[$res[0]['bet_id']].'_日报表';

                break;
            case 4:
                if($conditions['uid']!==0)
                {
                    $res[0] = $this->FormsLogic->excel($conditions, $v,$conditions['uid'],$issue);
                }
                else
                {
                    $res[0] = '';
                }
                $title = '_个人报表';                                                                        
                break;
        }

        if(empty($res))
            return $this->showMsg('没有数据', true, '/forms/total');

        $row = 2;
        foreach ($res[0] as $key => $value) {
            switch ($v) {
            case 1:
            $betName = $value['bet_id']<=5?$type[$value['bet_id']]:'总计';
            $betdate= !empty($value['bt_date']) ? date('Y-m-d',$value['bt_date']) : '-';
            $betPeriods = !empty($value['bt_periods']) ? $value['bt_periods'] : '-';
            $betNum = !empty($value['bt_all_orders']) ? $value['bt_all_orders'] : 1 ;
            $betIn = $value['bt_all_in'];
            $betOut = $value['bt_all_out'];
            $betReback = $value['bt_all_reback'];
            $betEarn = !empty($value['bt_all_earn']) ? $value['bt_all_earn'] : 0;
                 //写入数据
            $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $row, $betName)
                        ->setCellValue('B' . $row, $betdate)
                        ->setCellValue('C' . $row, $betPeriods)
                        ->setCellValue('D' . $row, $betNum)
                        ->setCellValue('E' . $row, $betIn)
                        ->setCellValue('F' . $row, $betOut)
                        ->setCellValue('G' . $row, $betReback)
                        ->setCellValue('H' . $row, $betEarn);
            $row++;

                break;
            case 2:
                $name = $res[0][$key]['u_name'];
                $type = $res[0][$key]['ua_type'] == 1? '会员':'代理';
                $betMoney = !empty($res[1][$key]['pfr_team_bet_money'])?$res[1][$key]['pfr_team_bet_money']:0;
                $bonuse = !empty($res[1][$key]['pfr_team_earn_money'])?$res[1][$key]['pfr_team_earn_money']:0;
                $reback = !empty($res[1][$key]['pfr_team_reback_money'])?$res[1][$key]['pfr_team_reback_money']:0;
                $plat = !empty($res[1][$key]['pfr_team_plat_money'])?$res[1][$key]['pfr_team_plat_money']:0;
                $back = !empty($res[1][$key]['pfr_team_back_money'])?$res[1][$key]['pfr_team_back_money']:0;
                $mybet = !empty($res[1][$key]['pfr_my_bet_money'])?$res[1][$key]['pfr_my_bet_money']:0;
                $mybonus = !empty($res[1][$key]['pfr_my_earn_money'])?$res[1][$key]['pfr_my_earn_money']:0;
                $myreback = !empty($res[1][$key]['pfr_my_reback_money'])?$res[1][$key]['pfr_my_reback_money']:0;
                $myearn = !empty($res[1][$key]['pfr_my_earn_money']-$res[1][$key]['pfr_my_bet_money']+$res[1][$key]['pfr_my_reback_money'])?$res[1][$key]['pfr_my_earn_money']-$res[1][$key]['pfr_my_bet_money']+$res[1][$key]['pfr_my_reback_money']:0;
//                $earn = !empty($res[1][$key]['pfr_team_plat_money'])?$res[1][$key]['pfr_team_plat_money']:0;
                 //写入数据
            $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $row, $name)
                        ->setCellValue('B' . $row, $type)
                        ->setCellValue('C' . $row, $betMoney)
                        ->setCellValue('D' . $row, $bonuse)
                        ->setCellValue('E' . $row, $reback)
                        ->setCellValue('F' . $row, $plat)
                        ->setCellValue('G' . $row, $back)
                        ->setCellValue('H' . $row, $mybet)
                        ->setCellValue('I' . $row, $mybonus)
                        ->setCellValue('J' . $row, $myreback)
                        ->setCellValue('K' . $row, $myearn);
            $row++;

                break;
            case 4:
                $name = $value['u_name'];
                $play = $value['bo_played_name'];
                $date = date('Y-m-d,H:i:s',$value['bo_created_time']);
                $orderNum = $value['bo_sn'];
                $status = $value['bo_draw_result'] == 1?'中奖':($value['bo_draw_result'] == 3?'未中奖':'待开奖');
                $bet = !empty($value['bo_money'])?$value['bo_money']:0;
                $bonuse = !empty($value['bo_bonus'])?$value['bo_bonus']:0;
                $back = !empty($value['bo_back_money'])?$value['bo_back_money']:0;
                $earn = $value['bo_bonus']-$value['bo_money']+$value['bo_back_money'];
                 //写入数据
            $objPHPExcel->getActiveSheet()
                        ->setCellValue('A' . $row, $name)
                        ->setCellValue('B' . $row, $play)
                        ->setCellValue('C' . $row, $date)
                        ->setCellValue('D' . $row, $orderNum)
                        ->setCellValue('E' . $row, $status)
                        ->setCellValue('F' . $row, $bet)
                        ->setCellValue('G' . $row, $back)
                        ->setCellValue('H' . $row, $bonuse)
                        ->setCellValue('I' . $row, $earn);  
            $row++;

                break;
        }
            
        }

        $this->view->disable();
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $time . $title.'.xlsx');

        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

}
