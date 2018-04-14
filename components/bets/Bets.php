<?php
namespace Components\Bets;

use Phalcon\Mvc\User\Component;

class Bets extends Component
{

    public static function isShut($id)
    {
        $betsConfig = new \BetsConfigLogic;
        $nowTime = time();
        switch($id) {
        case 1:
            $betsInfo = $betsConfig->getInfoById(1);
            if(!$betsInfo || $betsInfo['bet_isenable'] != 1)
                return false;

            $startTime = strtotime(date('Y-m-d 02:00:00'));
            $endTime = strtotime(date('Y-m-d 10:00:00'));
            if($nowTime > $startTime && $nowTime < $endTime)
                return false;
            else
                return true;
        }
    }

    public function getOpenBetById($id)
    {
        switch($id) {
        case 1:
            $res = $this->getCqsscBet();
            // todo 异常如何解决
            if(count($res['errors']) > 0)
            {
                sleep(3);
                for($i = 0; $i < 5; $i++)
                {
                    $res = $this->getCqsscBet();
                    if(count($res['errors']) <= 0)
                    {
                        break;
                    }
                    else
                        sleep(3);
                }

                if(count($res['errors']) > 1)
                {
                    // 记录错误日志，标记异常订单，通知平台
                    return false;
                }
            }

            // 比对结果和数据，整合出开奖结果和下期数据
            $data = $res['data']['openCai'];
            $infos = [
                'next' => ['expect' => $data['next']['expect'], 'openTime' => strtotime($data['next']['opentime'])],
                'open' => ['expect' => $data['open']['expect'], 'openCode' => $data['open']['opencode'], 'openTime' => $data['open']['opentime']],
            ];

            // 丢入计算走势的队列
            $this->di['redis']->rpush('open:analyze', ['type' => 1, 'info' => $infos['open']]);

            // 设置下期时间，设置开奖标志
            $curExpect = $this->di['redis']->get('bets:next:1');
            // 直接设置下期开奖的期号和时间
            if(!$curExpect || time() - $curExpect['openTime'] > 7200)
            {
                return $this->di['redis']->set('bets:next:1', $infos['next']);
            }
            else
            {
                if($curExpect['expect'] == $infos['open']['expect'])
                {
                    // 设置开奖信号
                    $this->di['redis']->rpush('bets:open:1', $infos['open']);

                    // 设置下期信息
                    return $this->di['redis']->set('bets:next:1', $infos['next']);
                }
                else if($curExpect['expect'] != $infos['next']['expect'])
                {
                    return $this->di['redis']->set('bets:next:1', $infos['next']);
                }

                return true;
            }
            break;
        case 2:
        case 3:
        case 4:
        case 5:
            break;
        }
    }

    public function getCqsscBet()
    {
        $openCai = $this->di['config']['game']['openConf']['openCai'];
        $anotherCai = $this->di['config']['game']['openConf']['anotherCai'];

        $urls = [
            'openCai' => $openCai['mainDomain'] . '/newly.do?token=' . $openCai['token'] . '&code=cqssc&format=' . $openCai['format'],
            //'anotherCai' => $anotherCai['mainDomain'] . '/newly.do?token=' . $anotherCai['token'] . '&code=cqssc&rows=1&format=' . $anotherCai['format'] . '&extend=true',
        ];

        $mh = curl_multi_init();
        $aCurlHandles = [];

        foreach($urls as $key => $url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_multi_add_handle($mh, $ch);
            $aCurlHandles[$key] = $ch;
        }

        $active = null;

        do
        {
            $mrc = curl_multi_exec($mh, $active);
        }
        while($mrc == CURLM_CALL_MULTI_PERFORM);

        while($active && $mrc == CURLM_OK)
        {
            if(curl_multi_select($mh) != -1)
            {
                usleep(100);
            }

            do
            {
                $mrc = curl_multi_exec($mh, $active);
            }
            while($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        $data = $errors = [];
        foreach($aCurlHandles as $key => $ch)
        {
            $res = json_decode(curl_multi_getcontent($ch), true);
            if($key == 'openCai')
            {
                $data[$key]['next'] = $res['next'][0];
                $data[$key]['open'] = $res['open'][0];
            }
            else if($key == 'anotherCai')
            {
                $data[$key]['next'] = $res['next'][0];
                $data[$key]['open'] = $res['open'][0];
            }

            $err = curl_error($ch);
            if($err)
            {
                // 记录错误日志
                error_log('[' . date('Y-m-d H:i:s') . '][getCqsscBet][' . $key . '][fail]' . json_encode($err) . "\n", 3, $this->di['config']['taskConf']['betResLog']);

                $errors[$key] = $err;
            }

            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        return ['data' => $data, 'errors' => $errors];
    }

    public function openCqssc($expect, $openCode)
    {
        $betsResultLogic = new \BetsResultLogic;

        // 插入开奖结果表，状态为开奖中
        if(($bres_id = $betsResultLogic->addBetResultByStatus(1, 5, $expect, $openCode)) <= 0)
        {
            // 记录错误日志
            error_log('[' . date('Y-m-d H:i:s') . '][openCqssc][fail][addBetResultByStatus]-' . json_encode([1, 5, $expect, $openCode]) . "\n", 3, $this->di['config']['taskConf']['errorLog']);
            return;
        }

        // 查找对应期号的订单进行开奖处理
        $betsOrdersLogic = new \BetOrdersLogic;
        $totalNums = $betsOrdersLogic->getWaittingOpenNumsByBetid(1, $expect);
        $eachNums = 50;
        $page = intval(ceil($totalNums / $eachNums));
        $betUtils = new Utils;
        $betRes = $betUtils->analyzeFromResByType(1, explode(',', $openCode));

        $betsResultStatus = 1;

        for($i = 0; $i < $page; $i++)
        {
            $orders = $betsOrdersLogic->getWaittingOpenOrderByBetidAndPage(1, $expect, $i, $eachNums);
            $nums = count($orders);
            if($nums > 0)
            {
                for($j = 0; $j < $nums; $j++)
                {
                    try
                    {
                        $betsOrdersLogic->openBets(1, $expect, $openCode, $betRes['detail'], $orders[$j]);
                    }
                    catch(Exception $e)
                    {
                        // 记录期号异常
                        $betsResultStatus = 7;
                        // 记录订单异常
                        $betsOrdersLogic->setExceptionToOrder($orders[$j]['bo_id']);
                        // 记录错误日志
                        error_log('[' . date('Y-m-d H:i:s') . '][openCqssc][fail][openBets]-' . $e->getMessage() . "\n", 3, $this->di['config']['taskConf']['errorLog']);
                        continue;
                    }
                }
            }
        }

        // 修改开奖结果表，状态为开奖结束
        if($betsResultLogic->setBetResultStatus(1, $expect, $betsResultStatus) <= 0)
        {
            // 记录错误日志
            error_log('[' . date('Y-m-d H:i:s') . '][openCqssc][fail][setBetResultStatus]-' . json_encode([1, $expect, $betsResultStatus]) . "\n", 3, $this->di['config']['taskConf']['errorLog']);
            return;
        }

        return true;
    }

    public function agentEarn($order)
    {
        try
        {
            $res = [];
            $earnMoney = 0.0000;
            // 查找上级代理的信息
            if($order['bo_u_id'] != 0)
            {
                $userAgentModel = new \UserAgentModel;
                $agentInfo = $userAgentModel->getAgentInfoByUid($order['bo_u_id']);
                $size = count($agentInfo);
                if($size == 0)
                {
                    return true;
                }
                else
                {
                    // 计算上级代理的佣金
                    $lastRsTax = $order['bo_rs_tax'];
                    $tempBackMoney = 0;
                    for($i = 0; $i < $size; $i++)
                    {
                        $upRate = !empty($agentInfo[$i+1]['ua_rate']) ? $agentInfo[$i+1]['ua_rate'] : $agentInfo[$i]['ua_rate'];
                        $taxMoney = (($agentInfo[$i]['ua_rate'] - $lastRsTax) * $order['bo_money']) / 100;
                        $myEarn = (($upRate - $agentInfo[$i]['ua_rate']) * $order['bo_money']) / 100;//我自己的返点乘投注
                        if($order['bo_bonus'] == null)
                        {
                            $order['bo_bonus'] = 0;
                        }
                        if($order['bo_back_money'] == null)
                        {
                            $order['bo_back_money'] = 0;
                        }
                        $res[] = [
                            'u_id' => $agentInfo[$i]['u_id'],
                            'bo_id' => $order['bo_id'],
                            'bet_id' => $order['bet_id'],
                            'bo_issue' => $order['bo_issue'],
                            'back_money' => $taxMoney,//返点
                            'u_name' => $agentInfo[$i]['u_name'],
                            'ua_type' => $agentInfo[$i]['ua_type'],
                            'bo_money' => $order['bo_money'],
                            'bo_bonus' => $order['bo_bonus'],
                            'bo_back_money' => $order['bo_back_money'],//回水
                            'bo_u_id' => $agentInfo[$i]['ua_u_id'],
                            'last_agent_back_money' => $tempBackMoney,
                            'myEarn' => $myEarn,
                            'bo_created_time' => $order['bo_created_time']
                        ];
                        
                        $tempBackMoney += $taxMoney;

                        $earnMoney += $taxMoney;

                        $lastRsTax = $agentInfo[$i]['ua_rate'];
                    }

                    // 更新该彩种的当日总盈亏
                    $this->di['redis']->incrByFloat('earn:' . $order['bet_id'] . ':' . date('Y-m-d'), -$earnMoney);
                    //更新今日平台盈亏
                    $this->di['redis']->incrByFloat('finance:' . date('Y-m-d').':planTotal', -$earnMoney);
                }
            }

            // 更新上级代理钱包
            if(count($res) > 0)
            {
                $userWalletModel = new \UserWalletModel;
                if($userWalletModel->agentEarn($res) !== true)
                {
                    throw new \Exception('更新代理金额失败，info: ' . json_encode($res));
                }
            }

            return true;
        }
        catch(Exception $e)
        {
            error_log('[' . date('Y-m-d H:i:s') . '][agentEarn][fail]data: ' . json_encode($info) . ' errMsg: ' . $e->getMessage() . "\n", 3, $this->di['config']['taskConf']['errorLog']);
            return false;
        }
    }

    public function getNextExpectByBetId($betId, $nums)
    {
        $expects = [];
        switch($betId)
        {
        case 1:// 重庆时时彩
            // 直接获取当前redis里面的期数
            $nextExpect = $this->di['redis']->get('bets:next:1');
            if($nextExpect)
            {
                $nextExpect = $nextExpect['expect'];
                $expects[] = $nextExpect;
                $shortExpect = intval(substr($nextExpect, -3));
                $dateExpect = substr($nextExpect, 0, -3);
                for($i = 1; $i < $nums; $i++)
                {
                    $next = $shortExpect + $i;
                    if($next <= 120)
                    {
                        $expects[] = $dateExpect . sprintf('%03d', $next);
                    }
                    else
                        break;
                }
            }

            break;

        case 2:
            break;

        case 3:
            break;

        case 4:
            break;

        case 5:
            break;
        }

        return $expects;
    }

    public function compareResult($betId, $expect)
    {
        $code = '';
        switch($betId)
        {
        case 1:
            $code = 'cqssc';
            break;
        case 2:
            $code = 'bjkl8';
            break;
        case 3:
            $code = 'mlaft';
            break;
        case 4:
            $code = 'gdklsf';
            break;
        case 5:
            $code = 'bjpk10';
            break;
        case 6:
            $code = 'cakeno';
            break;
        case 7:
            $code = 'fjk3';
            break;
        case 8:
            $code = 'bjk3';
            break;
        case 9:
            $code = 'jsk3';
            break;
        case 10:
            $code = 'gd11x5';
            break;
        case 11:
            $code = 'zj11x5';
            break;
        case 12:
            $code = 'sd11x5';
            break;
        case 13:
            $code = 'ln11x5';
            break;
        default:
            return false;
        }

        $openCai = $this->di['config']['game']['openConf']['openCai'];
        $anotherCai = $this->di['config']['game']['openConf']['anotherCai'];

        $openCaiUrl = $openCai['mainDomain'] . '/newly.do?token=' . $openCai['token'] . '&code=' . $code . '&format=' . $openCai['format'];
        $anotherCaiUrl = $anotherCai['mainDomain'] . '/newly.do?token=' . $anotherCai['token'] . '&code=' . $code . '&format=' . $anotherCai['format'];

        $row = 5;
        $times = 11;
        $utilsObj = new Utils;

        $openCaiRs = '';
        $anotherCaiRs = '';

        for($i = 0; $i < $times; $i++)
        {
            try
            {
                if(!$openCaiRs)
                {
                    $row = $row + $i;
                    // 通过openCai获取结果
                    $ocRs = $utilsObj->fetchBetRes($openCaiUrl . '&rows=' . $row);
                    
                    if($ocRs)
                    {
                        $ocRs = json_decode($ocRs, true);

                        foreach($ocRs['data'] as $val)
                        {
                            if($val['expect'] == $expect)
                            {
                                $openCaiRs = $val['opencode'];
                                break;
                            }
                        }
                    }
                }


                // 临时判断
                if($openCaiRs)
                {
                    return $openCaiRs;
                } else {
                    error_log('openCai--'. $code.'-'.$i . '-'.date('Y-m-d H:i:s'));   
                }
            }
            catch(\Exception $e)
            {
                // 记录错误日志
                error_log('[' . date('Y-m-d H:i:s') . '][compareResult][error]-' . json_encode($e->getMessage()) . "\n", 3, $this->di['config']['taskConf']['errorLog']);
            }

            sleep(6);
        }

        return false;
    }

    public function cakenoResult($betId, $expect, $date)
    {
        $code = '';
        switch($betId)
        {
        case 1:
            $code = 'cqssc';
            break;
        case 2:
            $code = 'bjkl8';
            break;
        case 3:
            $code = 'mlaft';
            break;
        case 4:
            $code = 'gdklsf';
            break;
        case 5:
            $code = 'bjpk10';
            break;
        case 6:
            $code = 'cakeno';
            break;
        default:
            return false;
        }

        $openCai = $this->di['config']['game']['openConf']['openCai'];
        $anotherCai = $this->di['config']['game']['openConf']['anotherCai'];

        $openCaiUrl = $openCai['mainDomain'] . '/daily.do?token=' . $openCai['token'] . '&code=' . $code . '&format=' . $openCai['format'];
        // $anotherCaiUrl = $anotherCai['mainDomain'] . '/newly.do?token=' . $anotherCai['token'] . '&code=' . $code . '&format=' . $anotherCai['format'];

        $row = 5;
        $times = 5;
        $utilsObj = new Utils;

        $openCaiRs = '';
        $anotherCaiRs = '';

        for($i = 0; $i < $times; $i++)
        {
            try
            {
                if(!$openCaiRs)
                {
                    $row = $row + $i;
                    // 通过openCai获取结果
                    $ocRs = $utilsObj->fetchBetRes($openCaiUrl . '&date=' . $date);
                    if($ocRs)
                    {
                        $ocRs = json_decode($ocRs, true);
                        foreach($ocRs['data'] as $val)
                        {
                            if($val['expect'] == $expect)
                            {
                                $openCaiRs[] = $val['opencode'];
                                $openCaiRs[] = $val['opentimestamp'];
                                break;
                            }
                        }
                    }
                }

                // 通过anotherCai获得结果
                //if(!$anotherCaiRs)
                //{
                //    $anotherRs = $utilsObj->fetchBetRes($anotherCaiUrl . '&row=' . $row);
                //}

                // 判断是否一致
                // if($openCaiRs == $anotherCaiRs)
                // {
                //     return $openCaiRs;
                //}

                // 临时判断
                if($openCaiRs)
                {
                    return $openCaiRs;
                }
            }
            catch(\Exception $e)
            {
                // 记录错误日志
                error_log('[' . date('Y-m-d H:i:s') . '][compareResult][error]-' . $e->getMessage() . "\n", 3, $this->di['config']['taskConf']['errorLog']);
                continue;
            }

            sleep(6);
        }

        return false;
    }
    
    public function getTxffcResult()
    {
        $url = 'https://cgi.im.qq.com/data/1min_city.dat';
        $data = file_get_contents($url);
        $data = json_decode($data,true);
        $res = array();
        $len = strlen((string)$data['minute'][59]);
        $total = 0;
        for($i=0; $i<$len; $i++)
        {
            $temp = (int)substr((string)$data['minute'][59] , -($i+1),1);
            $res[] = $temp;
            $total += $temp;
        }

        $result = array();
        $result[0] = $total%10;
        for($i=0; $i<4; $i++) {
            $result[$i+1] = $res[3-$i];
        }
        $result = implode(',', $result);
        return $result;
    }

    public function getLastExpectById($betId)
    {
        $betsResultModel = new \BetsResultModel;
        return $betsResultModel->getUnopenExpect($betId);
    }
    
    public function mendResult($betId, $expect, $date)
    {
        $code = '';
        switch($betId)
        {
        case 1:
            $code = 'cqssc';
            break;
        case 2:
            $code = 'bjkl8';
            break;
        case 3:
            $code = 'mlaft';
            break;
        case 4:
            $code = 'gdklsf';
            break;
        case 5:
            $code = 'bjpk10';
            break;
        case 6:
            $code = 'cakeno';
            break;
        case 7:
            $code = 'fjk3';
            break;
        case 8:
            $code = 'bjk3';
            break;
        case 9:
            $code = 'jsk3';
            break;
        case 10:
            $code = 'gd11x5';
            break;
        case 11:
            $code = 'zj11x5';
            break;
        case 12:
            $code = 'sd11x5';
            break;
        case 13:
            $code = 'ln11x5';
            break;
        default:
            return false;
        }

        $openCai = $this->di['config']['game']['openConf']['openCai'];
        $anotherCai = $this->di['config']['game']['openConf']['anotherCai'];

        $openCaiUrl = $openCai['mainDomain'] . '/daily.do?token=' . $openCai['token'] . '&code=' . $code . '&format=' . $openCai['format'];
        $anotherCaiUrl = $anotherCai['mainDomain'] . '/newly.do?token=' . $anotherCai['token'] . '&code=' . $code . '&format=' . $anotherCai['format'];

        $times = 3;
        $utilsObj = new Utils;

        $openCaiRs = '';
        $anotherCaiRs = '';
        if ($betId == 6) {
            $time = $date;
            $date = date('Y-m-d',$date);
        }
        for($i = 0; $i < $times; $i++)
        {
            try
            {
                if(!$openCaiRs)
                {
                    // 通过openCai获取结果
                    $ocRs = $utilsObj->fetchBetRes($openCaiUrl . '&date=' . $date);
                    $log = $ocRs;
                    if($ocRs)
                    {
                        $ocRs = json_decode($ocRs, true);
                        if (!empty($ocRs['data'])) {
                            foreach($ocRs['data'] as $val)
                            {
                                if($val['expect'] == $expect)
                                {
                                    $openCaiRs = $val['opencode'];
                                    break;
                                }
                            }
                        } else {
                            error_log($log);
                        }
                    }

                    if ($betId == 6) {
                        if (intval(date('H',$time)) == 23 && intval(date('is',$time)) >= 5630) {
                            $ocRs = $utilsObj->fetchBetRes($openCaiUrl . '&date=' . date('Y-m-d',strtotime('+1 day',$time)));
                            if($ocRs)
                            {
                                $ocRs = json_decode($ocRs, true);
                                foreach($ocRs['data'] as $val)
                                {
                                    if($val['expect'] == $expect)
                                    {
                                        $openCaiRs = $val['opencode'];
                                        break;
                                    }
                                }
                            }  
                        }
                        if (intval(date('H',$time)) == 0 && intval(date('is'),$time) <= 330){
                            $ocRs = $utilsObj->fetchBetRes($openCaiUrl . '&date=' . date('Y-m-d',strtotime('-1 day',$time)));
                            if($ocRs)
                            {
                                $ocRs = json_decode($ocRs, true);
                                foreach($ocRs['data'] as $val)
                                {
                                    if($val['expect'] == $expect)
                                    {
                                        $openCaiRs = $val['opencode'];
                                        break;
                                    }
                                }
                            }  
                        }
                    }

                }

                // 临时判断
                if($openCaiRs)
                {
                    return $openCaiRs;
                }
            }
            catch(\Exception $e)
            {
                // 记录错误日志
                error_log('[' . date('Y-m-d H:i:s') . '][compareResult][error]-' . $e->getMessage() . "\n", 3, $this->di['config']['taskConf']['errorLog']);
            }

            sleep(6);
        }

        return false;
    }
}

