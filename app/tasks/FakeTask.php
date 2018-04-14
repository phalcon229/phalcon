<?php

use \Components\Bets;

class FakeTask extends TaskBase
{
    public function lotteryAction($param = [])
    {
        $uId = !empty($param[0]) ? $param[0] : 1;
        $betId = !empty($param[1]) ? $param[1] : 1;

        // 获取未开奖期
        $resultLogic = new BetsResultLogic();
        if (!$perids = $resultLogic->getNextPerids($betId, 120))
        {
            echo '获取未开奖期数失败';
            return;
        }

        // 获取规则
        $ruleLogic = new BetsRulesLogic();
        if (!$rules = $ruleLogic->getInfoById($betId))
        {
            echo '获取规则失败';
            return;
        }

        // 获取用户赔率
        $userAgentLogic = new UserAgentLogic();
        $rate = $userAgentLogic->getInfo($uId, 'ua_rate')['ua_rate'] ?: 0;

        // 循环投注
        $orderLogic = new BetOrdersLogic();
        foreach ($perids as $perid)
        {
            $r = array_rand($rules, 1);
            $rule = [
                [
                    'brid' => $rules[$r]['br_id'],
                    'unit_price' => rand(2, 10),
                    'percent' => ceil(rand(0, $rate * 10)) / 10,
                    'perid' => $perid['bres_periods']
                ]
            ];

            try {
                $res = $orderLogic->create($betId, $uId, '', $rule, []);
                echo '[', date('Y-m-d H:i:s') ,'][' . $rule[0]['perid'] . ']自动下注成功，data:', json_encode(compact("betId", "uId", "rule")), "\n";
            } catch (Exception $e) {
                echo '[', date('Y-m-d H:i:s') ,'][' . $rule[0]['perid'] . ']自动下注失败，data:', json_encode(compact("betId", "uId", "rule")) . '，msg:' . $e->getMessage(), "\n";
            }
        }
    }
}

