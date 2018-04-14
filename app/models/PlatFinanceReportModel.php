<?php

class PlatFinanceReportModel extends ModelBase
{

    protected $table = 'le_plat_finance_report';

    public function __construct()
    {
        parent::__construct();
    }

    public function getTimeTeamInfo($ids,$lotteryType,$startDay,$endDay)
    {
        if($lotteryType==0)
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and nper = 0 and (tsd_date>= ? and tsd_date< ?)', [$id,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
        else
        {
            $res = array();
            for($i = 0; $i <count($ids); $i++)
            {
                $id = $ids[$i];
                $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ' WHERE '
                        . 'u_id = ? and nper = 0 and bet_id = ? and (tsd_date>= ? and tsd_date< ?)', [$id,$lotteryType,$startDay,$endDay]);
                $res = array_merge($res,$this->getAll());
            }
            return $res;
        }
    }
    
    public function getTeamTime($ids,$startDay,$endDay)
    {
        $ids = implode(',', $ids);

        $this->query('SELECT if(SUM(tsd_withdraw)<>\'\',SUM(tsd_withdraw),0) tsd_withdraw,if(SUM(tsd_recharge)<>\'\',SUM(tsd_recharge),0.0000) tsd_recharge,'
                        . 'if(SUM(tsd_bet_money)<>\'\',SUM(tsd_bet_money),0.0000) tsd_bet_money,if(SUM(tsd_earn_money)<>\'\',SUM(tsd_earn_money),0.0000) tsd_earn_money ,'
                        . 'if(SUM(tsd_reback_money)<>\'\',SUM(tsd_reback_money),0.0000) tsd_reback_money ,if(SUM(tsd_pay_bonuses)<>\'\',SUM(tsd_pay_bonuses),0.0000) tsd_pay_bonuses FROM ' . $this->table . ''
                    . ' WHERE u_id in (' . $ids . ') and nper = 0 and (tsd_date>= ? and tsd_date< ?)', [$startDay,$endDay]);

        return $this->getAll();
    }
}