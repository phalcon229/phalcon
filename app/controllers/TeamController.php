<?php
class TeamController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->logic = new  TeamLogic();
        $this->agentReportLogic = new AgentReportLogic();
        $this->platFinanceReportLogic = new PlatFinanceReportLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->walletLogic = new WalletLogic();
        $this->usersLogic = new UsersLogic();
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->userAgentModel = new UserAgentModel();

    }
    public function reportAction()
    {
        $startTime = !empty($this->request->getQuery('startTime')) ? strtotime($this->request->getQuery('startTime')) : mktime(0,0,0,date('m'),date('d'),date('Y'));;
        $endTime = !empty($this->request->getQuery('endTime'))? strtotime($this->request->getQuery('endTime')) + 86399: mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $pame = $this->request->getQuery('pame') ? $this->request->getQuery('pame') : 0;
        if (!empty($pame))
        {
            switch ($pame) {
                case 1:
                    $startTime = mktime(0,0,0,date('m'),date('d'),date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                    break;
                case 2:
                    $startTime = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                    $endTime = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
                    break;
                case 3:
                    $startTime = mktime(0, 0 , 0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y"));
                    $endTime = mktime(23,59,59,date("m"),date("d")-(date("w")==0?7:date("w"))+7,date("Y"));                    
                    break;
                case 4:
                    $startTime = mktime(0,0,0,date('m'),date('d')-(date("w")==0?7:date("w"))+1-7,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('d')-(date("w")==0?7:date("w"))+7-7,date('Y'));
                    break;
                case 5:
                    $startTime = mktime(0,0,0,date('m'),1,date('Y'));
                    $endTime = mktime(23,59,59,date('m'),date('t'),date('Y'));
                    break;
                case 6:
                    $startTime = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
                    $endTime = mktime(23,59,59,date("m") ,0,date("Y"));
                    break;
                case 0:
                    $startTime = $startTime;
                    $endTime = $endTime;
                    break;
            }
        }  

        $uId=trim($this->request->get('uId'));
        if(!empty($uId))
        {
            $downId = $this->userAgentModel->getDown($this->uId);
            $downId = explode(',',$downId);
            if(!in_array($uId,$downId))
            {
                $uId = $this->uId;
            }
            $this->uId = $uId;
        }
        //查出uId的所有下级
        $baseInfo = $this->userAgentLogic->getAllAgent($this->uId);
        for($i = 0; $i < count($baseInfo); $i++)
        {
            switch ($baseInfo[$i]['ua_type'])
            {
                case 1: $baseInfo[$i]['ua_type'] = '会员';break;
                case 3: $baseInfo[$i]['ua_type'] = '代理';break;
            }
        }
        
        $ids = array();
        $totalReg = 0;
        for($i = 0;$i < count($baseInfo) ; $i++)
        {
            $totalReg += $baseInfo[$i]['ua_reg_nums'];
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }

        if(empty($ids))
        {
            $othorInfo = array();
        }
        else 
        {
            $othorInfo = $this->agentReportLogic->getTimeTeamInfofront($ids,0,$startTime,$endTime);
        }

        for($i = 0;$i<count($othorInfo);$i++)
        {
            $othorInfo[$i]['ar_team_earn'] = $othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_earn_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money'];
        }
        
        $total['ar_team_withdraw_money'] = 0;
        $total['ar_team_recharge_money'] = 0;
        $total['ar_team_bet_money'] = 0;
        $total['ar_team_earn_money'] = 0;
        $total['ar_team_back_money'] = 0;
        $total['ar_team_reback_money'] = 0;
        $total['ar_team_earn'] = 0;
        $total['ar_my_back_money'] = 0;
        $total['ar_my_bet_money'] = 0;
        $total['ar_team_my_earn'] = 0;
        $total['ar_team_upback_money'] = 0;//金额变动总计
        for($i = 0;$i < count($othorInfo) ; $i++){
            
            $total['ar_team_withdraw_money'] += $othorInfo[$i]['ar_team_withdraw_money'];
            $total['ar_team_recharge_money'] += $othorInfo[$i]['ar_team_recharge_money'];
            $total['ar_team_bet_money'] += $othorInfo[$i]['ar_team_bet_money'];
            $total['ar_team_earn_money'] += $othorInfo[$i]['ar_team_earn_money'];
            $total['ar_team_back_money'] += $othorInfo[$i]['ar_team_back_money']-$othorInfo[$i]['ar_my_back_money'];
            $total['ar_team_reback_money'] += $othorInfo[$i]['ar_team_reback_money'];
            $total['ar_my_back_money'] += $othorInfo[$i]['ar_my_back_money'];
            $total['ar_team_earn'] += $othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money'] + $othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']  ;
            $total['ar_my_bet_money'] += $othorInfo[$i]['ar_my_bet_money'];
            $total['ar_team_upback_money'] += $othorInfo[$i]['ar_up_back_money'];
        }

        $this->view->setVars([
            'title' => '下线列表' ,
            'totalReg' => $totalReg,
            'total' => $total ,
            'base' => $baseInfo ,
            'selectUid' => $this->uId,
            'othorInfo' => $othorInfo, 
            'pame' => $pame,
            'start' => date('Y-m-d',$startTime),
            'end' => date('Y-m-d',$endTime)
        ]);
    }
    public function  showAction()
    {
        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $times);

        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast)+86400;
        //获取url上的uId参数，若存在，查询此参数的团队总览信息，若不存在则查询登入账号的团队总览信息
        $uId=trim($this->request->get('uId'));
        if(!empty($uId))
        {
            //查询团队总人数，为固定值，不随时间变化
            $info = $this->userAgentLogic->getTeamNum($uId);
            $num = $info['ua_team_num'];
            $regNum = $info['ua_reg_nums'];
            
            //ajax查询需要用的参数
            $selectUid = $uId;
        }
        else
        {
            $uId=$this->uId;
            $info = $this->userAgentLogic->getTeamNum($uId);
            $num = $info['ua_team_num'];
            $regNum = $info['ua_reg_nums'];
        }
        
        //在代理报表没有数据，默认只有登入账号的信息
        if(empty($num))
        {
            $num['ua_team_num'] = 1;
        }

        $baseInfo = $this->userAgentLogic->getAllAgent($uId);
        $nextIds = [];
        for($i = 0;$i < count($baseInfo) ; $i++)
        {
            $nextIds[$i] = intval($baseInfo[$i]['u_id']);
        }
        $online = 0;
        $len = count($nextIds);
        for($i = 0;$i < $len ; $i++)
        {
            $key = $nextIds[$i].':count';
            $res = $this->di['redis']->get($key);
            if(!empty($res))
            {
                $online ++;
            }
        }
        
        $ids = $this->userAgentLogic->getDown($uId);
        if(empty($ids))
        {
            $balance = 0;
        }
        else
        {
            
            $balance = sprintf("%.1f", $this->walletLogic->moneyRest($ids)['w_money']);
        }
        //$regNum = count($baseInfo);

        //求代理和下级所有的uId
//        $ids[0] = $uId;
//        for($i = 0;$i < count($baseInfo) ; $i++)
//        {
//            $ids[$i] = intval($baseInfo[$i]['u_id']);
//        }
//        
        //需要查所有人的团队统计日表信息
        $info = $this->agentReportLogic->getTeamTime($uId,$tWeekF,$tWeekL);

        
        $this->view->setVars([
            'info' => $info , 
            'online' => $online,
            'num' => $num , 
            'balance' => $balance ,
            'todayBegin' => $todayBegin ,
            'todayEnd' => $todayEnd , 
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd ,
            'lWeekLast' => $lWeekLast ,
            'lWeekFirst' => $lWeekFirst , 
            'tWeekFirst' => $tWeekFirst , 
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast , 
            'tMonthFirst' => $tMonthFirst ,
            'tMonthFirst' => $tMonthFirst , 
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast  ,
            'title' => '下线总账' ,
            'selectUid' => $selectUid ,
            'regNum' => $regNum]);

    }
    
    //ajax刷新团队总览信息
    public function showFreshAction()
    {
        $now = time();
        $todayBegin = date('Y-m-d ', $now);//今天
        
        $startDay = strtotime($this->request->getPost('startDay'));
        $endDay = strtotime($this->request->getPost('endDay'))+86400;
        
        //selectUid为链接跳转进来的id，非登入者id
        $uId = trim($this->request->getPost('selectUid'));

        //判断是更新url上的uId的团队总览信息还是登入账号的团队总览信息
        if(!empty($uId))
        {
            $selectUid=$uId;
            $baseInfo = $this->userAgentLogic->getAllAgent($uId);
            
//            $ids[0] = $uId;
            $numInfo = $this->userAgentLogic->getTeamNum($uId);
            $num = $numInfo['ua_team_num'];
            //统计注册人数
            $regNum = $numInfo['ua_reg_nums'];
        }
        else
        {
            $uId = $this->uId;
            //设置为空，即进来的时候会选为登入者id
            $selectUid=null;
            $baseInfo = $this->userAgentLogic->getAllAgent($this->uId);
            
            
//            $ids[0] = $this->uId;
            $numInfo = $this->userAgentLogic->getTeamNum($this->uId);
            $num = $numInfo['ua_team_num'];
            //统计注册人数
            $regNum = $numInfo['ua_reg_nums'];
        }
        
        //求代理和子级所有的uId
//         for($i = 0;$i < count($baseInfo) ; $i++)
//         {
//             $ids[$i] = intval($baseInfo[$i]['u_id']);
//         }
        
        if($startDay == null)
            return $this->di['helper']->resRet('请选择开始日期', 501);
        
        if($endDay == null)
            return $this->di['helper']->resRet('请选择截止日期', 502);
        
        if($startDay > $endDay)
            return $this->di['helper']->resRet('请选择截止日期', 503);

        if(empty($num))
        {
            $num['ua_team_num'] = 1;
        }
        $info = $this->agentReportLogic->getTeamTime($uId,$startDay,$endDay);
        $info[0]['ar_team_earn'] = $info[0]['ar_team_earn_money']-$info[0]['ar_team_bet_money']+$info[0]['ar_team_reback_money']+$info[0]['ar_team_back_money'];
        $info[0]['ar_next_back_money'] = $info[0]['ar_team_back_money'] - $info[0]['ar_my_back_money'];
        $data = array('info' => $info, 'num' => $num, 'selectUid' => $selectUid, 'regNum' => $regNum);

        return $this->di['helper']->resRet($data, 200);
    }

    public function tableAction()
    {
        $lottery = $this->betsConfigLogic->getAll();

        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $times);

        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast)+84600;
        
        $uId=trim($this->request->get('uId'));

        if(!empty($uId))
        {
            $this->uId = $uId;
        }
        //查出uId的所有下级
        $baseInfo = $this->userAgentLogic->getAllAgent($this->uId);
        for($i = 0; $i < count($baseInfo); $i++)
        {
            switch ($baseInfo[$i]['ua_type'])
            {
                case 1: $baseInfo[$i]['ua_type'] = '会员';break;
                case 3: $baseInfo[$i]['ua_type'] = '代理';break;
            }
            switch ($baseInfo[$i]['ua_reg_type'])
            {
                case 1: $baseInfo[$i]['ua_reg_type'] = '网上注册';break;
                case 3: $baseInfo[$i]['ua_reg_type'] = '分销链接注册';break;
                case 5: $baseInfo[$i]['ua_reg_type'] = '代理驻村';break;
            }
        }
        
        $ids = array();
        $totalReg = 0;
        for($i = 0;$i < count($baseInfo) ; $i++)
        {
            $totalReg += $baseInfo[$i]['ua_reg_nums'];
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }
        //格式化0
        $sNum = 0.0000;
        $sprintfNum = sprintf("%.4f", $sNum);
        if(empty($ids))
        {
            $othorInfo = array();
        }
        else 
        {
            $othorInfo = $this->agentReportLogic->getTimeTeamInfofront($ids,0,$tWeekF,$tWeekL);
        }

        //如果有些id在日统计表没有数据，即查出来的条数会比基础信息base条数少，那么用0填充加上少的数据
        $addNum = count($baseInfo)-count($othorInfo);
        $addArray = array();
        if($addNum > 0)
        {
            for($i = 0;$i < $addNum ; $i++)
            {
                $addArray[$i] = ['tsd_withdraw'=>0,'tsd_recharge'=>$sprintfNum,'tsd_bet_money'=>$sprintfNum,'tsd_earn_money'=>$sprintfNum,'tsd_reback_money'=>$sprintfNum,'tsd_pay_bonuses'=>$sprintfNum];
            }
        }
        $othorInfo = array_merge($othorInfo,$addArray);
        for($i = 0;$i<count($othorInfo);$i++)
        {
            $othorInfo[$i]['ar_team_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_earn_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money']);
        }
        
        $total['ar_team_withdraw_money'] = 0;
        $total['ar_team_recharge_money'] = 0;
        $total['ar_team_bet_money'] = 0;
        $total['ar_team_earn_money'] = 0;
        $total['ar_team_back_money'] = 0;
        $total['ar_team_reback_money'] = 0;
        $total['ar_team_earn'] = 0;
        $total['ar_my_back_money'] = 0;
        $total['ar_my_bet_money'] = 0;
        $total['ar_team_my_earn'] = 0;//金额变动总计
        for($i = 0;$i < count($othorInfo) ; $i++){
            
            $total['ar_team_withdraw_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_withdraw_money']);
            $total['ar_team_recharge_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_recharge_money']);
            $total['ar_team_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money']);
            $total['ar_team_earn_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']);
            $total['ar_team_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money']-$othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_reback_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_reback_money']);
            $total['ar_my_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_earn_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money'])  ;
            $total['ar_my_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_my_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
            $total['ar_oneteam_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']-$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_team_back_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
        }

        $this->view->setVars([
            'title' => '下线列表' ,
            'totalReg' => $totalReg,
            'total' => $total ,
            'base' => $baseInfo ,
            'selectUid' => $this->uId,
            'othorInfo' => $othorInfo , 
            'lottery' => $lottery ,
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd ,
            'lWeekLast' => $lWeekLast ,
            'lWeekFirst' => $lWeekFirst ,
            'tWeekFirst' => $tWeekFirst , 
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast ,
            'tMonthFirst' => $tMonthFirst , 
            'tMonthFirst' => $tMonthFirst ,
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast , 
            'todayBegin' => $todayBegin ,
            'todayEnd' => $todayEnd ]);

    }

    public function tableFreshAction()
    {
        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d ', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $now);

        $uId = trim($this->request->get('selectUid'));
        $lotteryType = $this->request->get('lotteryType');
        $startDay = strtotime($this->request->get('startDay'));
        $endDay = strtotime($this->request->get('endDay'))+86400;
        if(!empty($uId))
        {
            $this->uId = $uId;
        }
        
        $baseInfo = $this->userAgentLogic->getAllAgent($this->uId);
        for($i = 0; $i < count($baseInfo); $i++)
        {          
            switch ($baseInfo[$i]['ua_type'])
            {
                case 1: $baseInfo[$i]['ua_type'] = '会员';break;
                case 3: $baseInfo[$i]['ua_type'] = '代理';break;
            }
            switch ($baseInfo[$i]['ua_reg_type'])
            {
                case 1: $baseInfo[$i]['ua_reg_type'] = '网上注册';break;
                case 3: $baseInfo[$i]['ua_reg_type'] = '分销链接注册';break;
                case 5: $baseInfo[$i]['ua_reg_type'] = '代理驻村';break;
            }
        }
        $ids = array();
        $totalReg = 0;
        for($i = 0;$i < count($baseInfo);$i++)
        {
            $totalReg += $baseInfo[$i]['ua_reg_nums'];
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }
        $sNum = 0.0000;
        $sprintfNum = sprintf("%.4f", $sNum);
        if(empty($ids))
        {
            $othorInfo = array();
        }
        else
        {
            $othorInfo = $this->agentReportLogic->getTimeTeamInfofront($ids,$lotteryType,$startDay,$endDay);
        }
        
        $addNum = count($baseInfo)-count($othorInfo);
        $addArray = array();
        if($addNum > 0)
        {
            for($i = 0;$i < $addNum ; $i++)
            {
                $addArray[$i] = ['tsd_withdraw'=>0,'tsd_recharge'=>$sprintfNum,'tsd_bet_money'=>$sprintfNum,'tsd_earn_money'=>$sprintfNum,'tsd_reback_money'=>$sprintfNum,'tsd_pay_bonuses'=>$sprintfNum];
             }
        }
        $othorInfo = array_merge($othorInfo,$addArray);
        for($i = 0;$i<count($othorInfo);$i++)
        {
            $othorInfo[$i]['ar_team_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']) ;
            $othorInfo[$i]['ar_next_back_money'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
            $othorInfo[$i]['my_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money']+$othorInfo[$i]['ar_my_reback_money']);
            $othorInfo[$i]['ar_t_bet_money'] = sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_my_bet_money']);
            $othorInfo[$i]['ar_t_my_back'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
        }
        
        $total['ar_team_withdraw_money'] = 0;
        $total['ar_team_recharge_money'] = 0;
        $total['ar_team_bet_money'] = 0;
        $total['ar_team_earn_money'] = 0;
        $total['ar_team_back_money'] = 0;
        $total['ar_team_reback_money'] = 0;
        $total['ar_team_earn'] = 0;
        $total['ar_my_back_money'] = 0;
        $total['ar_next_back_money'] = 0;
        $total['ar_my_bet_money'] = 0;
        $total['ar_team_my_earn'] = 0;
        $total['ar_oneteam_earn'] = 0;//金额变动总计
        for($i = 0;$i < count($othorInfo) ; $i++){
            
            $total['ar_team_withdraw_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_withdraw_money']);
            $total['ar_team_recharge_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_recharge_money']);
            $total['ar_team_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_my_bet_money']) ;
            $total['ar_team_earn_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']);
            $total['ar_team_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_reback_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_reback_money']);
            $total['ar_my_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money'])  ;
            $total['ar_my_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_my_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
            $total['ar_oneteam_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']-$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_team_back_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
        }
        $total['ar_next_back_money'] = sprintf("%.1f",$total['ar_team_back_money'] - $total['ar_my_back_money']);
        for($i = 0;$i < count($othorInfo) ; $i++)
        {
            $baseInfo[$i] = array_merge($baseInfo[$i],$othorInfo[$i]);
        }
        $data = array('total' => $total, 'base' => $baseInfo, 'totalReg' => $totalReg,'status'=>3);
        $json = json_encode($data, true);
        echo $json;
        exit;
    }

    public function nextAction()
    {
        $lottery = $this->betsConfigLogic->getAll();

        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $times);

        $uaUid = $this->request->get('uId');
        $tWeekF = strtotime($tWeekFirst);
        $tWeekL = strtotime($tWeekLast)+86400;
        $baseInfo = $this->userAgentLogic->getAllAgent($uaUid);
        for($i = 0; $i < count($baseInfo); $i++)
        {
            switch ($baseInfo[$i]['ua_type'])
            {
                case 1: $baseInfo[$i]['ua_type'] = '会员';break;
                case 3: $baseInfo[$i]['ua_type'] = '代理';break;
            }
            switch ($baseInfo[$i]['ua_reg_type'])
            {
                case 1: $baseInfo[$i]['ua_reg_type'] = '网上注册';break;
                case 3: $baseInfo[$i]['ua_reg_type'] = '分销链接注册';break;
                case 5: $baseInfo[$i]['ua_reg_type'] = '代理驻村';break;
            }
        }
        $ids = array();
        $totalReg = 0;
        for($i = 0;$i < count($baseInfo) ; $i++)
        {
            $totalReg += $baseInfo[$i]['ua_reg_nums'];
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }
        $sNum = 0.0000;
        $sprintfNum = sprintf("%.4f", $sNum);
        if(empty($ids))
        {
            $othorInfo = array();
        }
        else
        {
            $othorInfo = $this->agentReportLogic->getTimeTeamInfofront($ids,0,$tWeekF,$tWeekL);
        }
        
        $addNum = count($baseInfo)-count($othorInfo);
        $addArray = array();
        if($addNum > 0)
        {
            for($i = 0;$i < $addNum ; $i++)
            {
                $addArray[$i] = ['tsd_withdraw'=>0,'tsd_recharge'=>$sprintfNum,'tsd_bet_money'=>$sprintfNum,'tsd_earn_money'=>$sprintfNum,'tsd_reback_money'=>$sprintfNum,'tsd_pay_bonuses'=>$sprintfNum];
            }
        }
        $othorInfo = array_merge($othorInfo,$addArray);
        for($i = 0;$i<count($othorInfo);$i++)
        {
            $othorInfo[$i]['ar_team_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_reback_money']);
            $othorInfo[$i]['ar_next_back_money'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']); 
            $othorInfo[$i]['ar_t_my_back'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);   
        }
        $total['ar_team_withdraw_money'] = 0;
        $total['ar_team_recharge_money'] = 0;
        $total['ar_team_bet_money'] = 0;
        $total['ar_team_earn_money'] = 0;
        $total['ar_team_back_money'] = 0;
        $total['ar_team_reback_money'] = 0;
        $total['ar_team_earn'] = 0;
        $total['ar_my_back_money'] = 0;
        $total['ar_next_back_money'] = 0;
        $total['ar_my_bet_money'] = 0;
        $total['ar_team_my_earn'] = 0;//金额变动总计
        for($i = 0;$i < count($othorInfo) ; $i++){
            
            $total['ar_team_withdraw_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_withdraw_money']);
            $total['ar_team_recharge_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_recharge_money']);
            $total['ar_team_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_earn_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']);
            $total['ar_team_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money']);
            $total['ar_team_reback_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_reback_money']);
            $total['ar_my_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money']) ;
            $total['ar_my_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_my_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
            $total['ar_oneteam_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']-$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_team_back_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
        }
        $total['ar_next_back_money'] = sprintf("%.1f",$total['ar_team_back_money'] - $total['ar_my_back_money']);
        $this->view->setVars([
            'title' => '下线报表' ,
            'totalReg' => $totalReg,
            'total' => $total , 
            'base' => $baseInfo ,
            'othorInfo' => $othorInfo ,
            'lottery' => $lottery ,
            'yBegin' => $yBegin ,
            'yEnd' => $yEnd , 
            'selectUid' => $uaUid,
            'lWeekLast' => $lWeekLast , 
            'lWeekFirst' => $lWeekFirst , 
            'tWeekFirst' => $tWeekFirst ,
            'tWeekLast' => $tWeekLast ,
            'tMonthLast' => $tMonthLast , 
            'tMonthFirst' => $tMonthFirst , 
            'tMonthFirst' => $tMonthFirst , 
            'lMonthFirst' => $lMonthFirst ,
            'lMonthLast' => $lMonthLast ,
            'todayBegin' => $todayBegin , 
            'todayEnd' => $todayEnd ]);
    }

    public function nextAjaxAction()
    {

        $date = date('Y-m-d',time());
        $now = time();

        $timestamp = strtotime($date);//上月
        $lMonthFirst = date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
        $lMonthLast = date('Y-m-d',strtotime("$lMonthFirst +1 month -1 day"));

        $tMonthFirst = date("Y-m-01",strtotime($date));//本月
        $tMonthLast = date("Y-m-d",strtotime("$tMonthFirst +1 month -1 day"));

        $time = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//本周
        $tWeekFirst = date('Y-m-d', $time);
        $tWeekLast = date('Y-m-d', strtotime('Sunday', $now));

        $thisMonday = '1' == date('w') ? strtotime('Monday', $now) : strtotime('last Monday', $now);//上周
        $lastMonday = strtotime('-7 days', $thisMonday);  // 上周一
        $lWeekFirst = date('Y-m-d', $lastMonday);
        $lWeekLast = date('Y-m-d', strtotime('last sunday', $now));

        $todayBegin = date('Y-m-d ', $now);//今天
        $todayEnd = date('Y-m-d', $now);

        $times = strtotime('-1 day', $now);//昨天
        $yBegin = date('Y-m-d', $times);
        $yEnd = date('Y-m-d', $now);

        $lotteryType = $this->request->get('lotteryType');
        $startDay = strtotime($this->request->get('startDay'));
        $endDay = strtotime($this->request->get('endDay'))+86400;
        
        $uaUid=$this->request->get('selectUid');
        $baseInfo = $this->userAgentLogic->getAllAgent($uaUid);
        for($i = 0; $i < count($baseInfo); $i++)
        {
            switch ($baseInfo[$i]['ua_type'])
            {
                case 1: $baseInfo[$i]['ua_type'] = '会员';break;
                case 3: $baseInfo[$i]['ua_type'] = '代理';break;
            }
            switch ($baseInfo[$i]['ua_reg_type'])
            {
                case 1: $baseInfo[$i]['ua_reg_type'] = '网上注册';break;
                case 3: $baseInfo[$i]['ua_reg_type'] = '分销链接注册';break;
                case 5: $baseInfo[$i]['ua_reg_type'] = '代理驻村';break;
            }
        }
        $ids = array();
        $totalReg = 0;
        for($i = 0;$i < count($baseInfo);$i++)
        {
            $totalReg += $baseInfo[$i]['ua_reg_nums'];
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }
        $sNum = 0.0000;
        $sprintfNum = sprintf("%.4f", $sNum);
        if(empty($ids))
        {
            $othorInfo = array();
        }
        else
        {
            $othorInfo = $this->agentReportLogic->getTimeTeamInfofront($ids,$lotteryType,$startDay,$endDay);
        }
        
        $addNum = count($baseInfo)-count($othorInfo);
        $addArray = array();
        if($addNum > 0)
        {
            for($i = 0;$i < $addNum ; $i++)
            {
                $addArray[$i] = ['tsd_withdraw'=>0,'tsd_recharge'=>$sprintfNum,'tsd_bet_money'=>$sprintfNum,'tsd_earn_money'=>$sprintfNum,'tsd_reback_money'=>$sprintfNum,'tsd_pay_bonuses'=>$sprintfNum];
             }
        }
        $othorInfo = array_merge($othorInfo,$addArray);
        for($i = 0;$i<count($othorInfo);$i++)
        {
            $othorInfo[$i]['ar_next_back_money'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
            $othorInfo[$i]['ar_team_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] +$othorInfo[$i]['ar_team_back_money']+ $othorInfo[$i]['ar_my_reback_money'] + $othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money']);
            $othorInfo[$i]['my_earn'] = sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money']+$othorInfo[$i]['ar_my_reback_money']);  
            $othorInfo[$i]['ar_t_bet_money'] = sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_my_bet_money']);
            $othorInfo[$i]['ar_t_my_back'] = sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);                    
        }
        $total['ar_team_withdraw_money'] = 0;
        $total['ar_team_recharge_money'] = 0;
        $total['ar_team_bet_money'] = 0;
        $total['ar_team_earn_money'] = 0;
        $total['ar_team_back_money'] = 0;
        $total['ar_team_reback_money'] = 0;
        $total['ar_team_earn'] = 0;
        $total['ar_my_back_money'] = 0;
        $total['ar_next_back_money'] = 0;
        $total['ar_my_bet_money'] = 0;
        $total['ar_team_my_earn'] = 0;
        $total['ar_oneteam_earn'] = 0;//金额变动总计
        for($i = 0;$i < count($othorInfo) ; $i++){
            
            $total['ar_team_withdraw_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_withdraw_money']);
            $total['ar_team_recharge_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_recharge_money']);
            $total['ar_team_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_earn_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']);
            $total['ar_team_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money']);
            $total['ar_team_reback_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_reback_money']);
            $total['ar_my_back_money'] += sprintf("%.1f",$othorInfo[$i]['ar_team_back_money'] - $othorInfo[$i]['ar_my_back_money']);
            $total['ar_team_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money'] - $othorInfo[$i]['ar_team_bet_money'] + $othorInfo[$i]['ar_team_back_money'] + $othorInfo[$i]['ar_team_reback_money']) ;
            $total['ar_my_bet_money'] += sprintf("%.1f",$othorInfo[$i]['ar_my_bet_money']);
            $total['ar_team_my_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']) ;
            $total['ar_oneteam_earn'] += sprintf("%.1f",$othorInfo[$i]['ar_team_earn_money']-$othorInfo[$i]['ar_team_bet_money']+$othorInfo[$i]['ar_team_reback_money']+$othorInfo[$i]['ar_team_back_money']+$othorInfo[$i]['ar_my_earn_money'] - $othorInfo[$i]['ar_my_bet_money'] + $othorInfo[$i]['ar_my_reback_money']);
        }
        $total['ar_next_back_money'] = sprintf("%.1f",$total['ar_team_back_money'] - $total['ar_my_back_money']);
        for($i = 0;$i < count($othorInfo) ; $i++)
        {
            $baseInfo[$i] = array_merge($baseInfo[$i],$othorInfo[$i]);
        }

        $data = array('total' => $total, 'base' => $baseInfo,'totalReg' => $totalReg,'status'=>3, 'uId' =>$uaUid);
        $json = json_encode($data, true);
        echo $json;
        exit;
    }
}
