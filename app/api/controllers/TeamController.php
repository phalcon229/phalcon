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
    }

    public function tableAction()
    {
        $uId = intval($this->request->getQuery('uId')) ?: $this->uId;
        $startTime = strtotime($this->request->getQuery('startTime'));
        $endTime = strtotime($this->request->getQuery('endTime'));

        if (!$startTime)
        {
            $now = $_SERVER['REQUEST_TIME'];
            $startTime = date('w') == 1 ? strtotime('Monday', $now) : strtotime('last Monday', $now);
            $endTime = strtotime('Sunday', $startTime);
        }

        //查出uId的所有下级
        $baseInfo = $this->userAgentLogic->getAllAgent($uId);
        if (!$baseInfo)
            return $this->di['helper']->resRet();

        $ids = [];
        for($i = 0;$i < count($baseInfo) ; $i++)
        {
            $ids[$i] = intval($baseInfo[$i]['u_id']);
        }

        $reports = $this->agentReportLogic->getTimeTeamInfofront($ids, 0, $startTime, $endTime+86399);

        $total['myback'] = 0; //我的佣金 =  ar_team_back_money - ar_my_back_money （显示上一级得到的）
        $total['myearn'] = 0; //此级盈亏 = ar_my_earn_money - ar_my_bet_money + ar_my_reback_money
        $total['mybet'] = 0; //此级投注 = ar_my_bet_money
        $total['bet'] = 0; //投注额 = ar_team_bet_money + ar_my_bet_money (团队加上自己)
        $total['earn'] = 0; //输赢 = ar_team_earn_money - ar_team_bet_money + ar_team_back_money + ar_team_reback_money + ar_my_earn_money - ar_my_bet_money + ar_my_reback_money
        $total['withdraw'] = 0;
        $total['recharge'] = 0;
        $total['reg_nums'] = 0;
        $reback = $earn = $totalBack = 0;

        $lists = [];
        foreach ($reports as $k => $v) {
            $my['myback'] = $my['myearn'] = $my['mybet'] = $my['bet'] = $my['earn'] = $my['withdraw'] = $my['recharge'] = 0;
            $my['myback'] = $v['ar_team_back_money'] - $v['ar_my_back_money'];
            $my['myearn'] = $v['ar_my_earn_money'] - $v['ar_my_bet_money'] + $v['ar_my_reback_money'];
            $my['mybet'] = $v['ar_my_bet_money'];
            $my['bet'] = $v['ar_team_bet_money'] + $v['ar_my_bet_money'];
            $my['earn'] = $v['ar_team_earn_money'] - $v['ar_team_bet_money'] + $v['ar_team_back_money'] + $v['ar_team_reback_money'] + $v['ar_my_earn_money'] - $v['ar_my_bet_money'] + $v['ar_my_reback_money'];
            $my['withdraw'] = $v['ar_team_withdraw_money'];
            $my['recharge'] = $v['ar_team_recharge_money'];

            $total['myback'] += $my['myback'];
            $total['myearn'] += $my['myearn'];
            $total['mybet'] += $v['ar_my_bet_money'];
            $total['bet'] += $my['bet'];
            $total['earn'] += $my['earn'];
            $total['withdraw'] += $v['ar_team_withdraw_money'];
            $total['recharge'] += $v['ar_team_recharge_money'];
            $my['ua_reg_nums'] = $baseInfo[$k]['ua_reg_nums'];
            $my['memo'] = $baseInfo[$k]['ua_memo'];
            $my['ua_type'] = $baseInfo[$k]['ua_type'];
            $my['u_name'] = $baseInfo[$k]['u_name'];
            $my['u_id'] = $baseInfo[$k]['u_id'];
            $total['reg_nums'] += $baseInfo[$k]['ua_reg_nums'];
            $data['base'][] = $my;
        }

        $data['total'] = $total;
        return $this->di['helper']->resRet($data, 200);
    }

    public function  showAction()
    {
        $uId = intval($this->request->getQuery('uId')) ?: $this->uId;
        $startTime = strtotime(trim($this->request->getQuery('startTime')));
        $endTime = strtotime(trim($this->request->getQuery('endTime')));

        if (!$startTime)
        {
            $now = $_SERVER['REQUEST_TIME'];
            $startTime = date('w') == 1 ? strtotime('Monday', $now) : strtotime('last Monday', $now);
            $endTime = strtotime('Sunday', $startTime);
        }

        if (!$info = $this->userAgentLogic->getTeamNum($uId))
            return $this->di['helper']->resRet('用户信息不存在!', 500);
        $ids = $this->userAgentLogic->getDown($uId);
        $balance = $online = 0;
        if ($ids)
        {
            $balance = sprintf("%.1f", $this->walletLogic->moneyRest($ids)['w_money']);
            foreach (explode(',', $ids) as $id) {
                if ($this->di['redis']->get($id . ':count'))
                    $online ++;
            }
        }

        //需要查所有人的团队统计日表信息
        $team = $this->agentReportLogic->getTeamTime($uId, $startTime, $endTime+86399);

        $data = [
            'online' => $online,
            'balance' => $balance,
            'ua_team_num' => $info['ua_team_num'],
            'ua_reg_num' => $info['ua_reg_nums'],
            'ar_team_withdraw_money' => $team[0]['ar_team_withdraw_money'],
            'ar_team_recharge_money' => $team[0]['ar_team_recharge_money'],
            'ar_team_bet_money' => $team[0]['ar_team_bet_money'],
            'ar_my_back_money' => $team[0]['ar_my_back_money'],
            'agent_back' => $team[0]['ar_team_back_money'] - $team[0]['ar_my_back_money'],
            'agent_earn' => $team[0]['ar_team_earn_money'] - $team[0]['ar_team_bet_money'] + $team[0]['ar_team_back_money'] + $team[0]['ar_team_reback_money']
        ];

        return $this->di['helper']->resRet($data, 200);
    }
}