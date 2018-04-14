<?php
use Phalcon\Mvc\View;

class GameController extends ControllerBase
{

    public function initialize()
    {
        $this->logic = new BetsConfigLogic();
        $this->gameLogic = new GameLogic();
        $this->ruleLogic = new BetsRulesLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->userLogic = new UsersLogic();
        $this->walletLogic = new WalletLogic();
        $this->orderLogic = new BetOrdersLogic();
        $this->resultLogic = new BetsResultLogic();
        $this->syscfgLogic = new SystemLogic();
        $this->uId = $this->di['session']->get('uInfo')['u_id'];
        $this->uName = $this->di['session']->get('uInfo')['u_name'];
    }

    public function infoAction($betId)
    {
        if (!$betId)
            $this->response->redirect('/');

        // 获取游戏信息
        if (!$info = $this->logic->getInfoById($betId, 1))
            $this->response->redirect('/');

        // 获取游戏倍率设置
        if (!$rulesBase = $this->ruleLogic->getInfoById($betId))
            $this->response->redirect('/');

        // 获取当前用户返点
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'];
        $maxRate = $this->syscfgLogic->getRate();


        $rules = [];
        foreach ($rulesBase as $rule)
        {
            if (empty($rules[$rule['br_type']])) $rules[$rule['br_type']] = [];

            $baseBonus = ceil($rule['br_bonus']);
            $rule['br_bonus'] = sprintf('%.3f', $rule['br_bonus'] - ($baseBonus * ($maxRate - $rate) / 100));
            if (in_array($rule['br_base_type'], $this->di['config']['game']['rule_size_big']))
            {
                if (empty($rules[$rule['br_type']]['big'])) $rules[$rule['br_type']]['big'] = [];
                array_push($rules[$rule['br_type']]['big'], $rule);
            }
            elseif (in_array($rule['br_base_type'], $this->di['config']['game']['rule_size_shape']))
            {
                if (empty($rules[$rule['br_type']]['shape'])) $rules[$rule['br_type']]['shape'] = [];
                array_push($rules[$rule['br_type']]['shape'], $rule);
            }
            else
            {
                if (empty($rules[$rule['br_type']]['small'])) $rules[$rule['br_type']]['small'] = [];
                array_push($rules[$rule['br_type']]['small'], $rule);
            }
        }

        $key = $this->uId.$this->uName.'bet';
        $betRate = !empty($this->cookies->get($key)->getValue())?$this->cookies->get($key)->getValue(): '0';

        $this->di['view']->setVars([
            'title' => $info['bet_name'],
            'gameId' => $betId,
            'gameList' => $this->logic->getAll(1),
            'config' => $this->di['config']['game'],
            'rules' => $rules,
            'rate' => $rate,
            'trackMax' => $this->syscfgLogic->getTrackMax(),
            'betRate' => $betRate,
        ]);
    }

    /**
     * 订单下注
     * @return [type] [description]
     */
    public function lotteryAction()
    {
        if (!$gameId = intval($this->request->getPost('game_id')))
            return $this->di['helper']->resRet('', 500);

        if (!$total = trim($this->request->getPost('total')))
            return $this->di['helper']->resRet('', 500);

        if (!$price = trim($this->request->getPost('price')))
            return $this->di['helper']->resRet('', 500);

        if (!$rules = $this->request->getPost('rules'))
            return $this->di['helper']->resRet('', 500);
        $rules = json_decode($rules,true);

        $key = $this->uId.$this->uName.'bet';
        $this->di['cookie']->set($key, floatval($rules[0]['percent']),time()+5*365*24*60*60);

        if (count($rules) != $total)
            return $this->di['helper']->resRet('', 500);

        $track = $this->request->getPost('track') ?: [];
        if ($track && count($track['perids']) < 2)
            return $this->di['helper']->resRet('追号期数至少2期', 500);

        // 用户可用余额是否充足
        $walletInfo = $this->walletLogic->getInfo($this->uId);
        if ($walletInfo['w_status'] != 1)
            return $this->di['helper']->resRet('您的钱包已被冻结，禁止下注', 500);

        if ($walletInfo['w_money'] < $price)
            return $this->di['helper']->resRet('您的余额不足，请先充值', 500);

        // 该游戏是否关闭
        if (!$gameInfo = $this->logic->getInfoById($gameId,1))
            return $this->di['helper']->resRet('', 500);

        if ($gameInfo['bet_isenable'] != 1)
            return $this->di['helper']->resRet('当前彩种已关闭', 500);

        // 执行投注
        try {
            return $this->orderLogic->create($gameId, $this->uId, $this->uName, $rules, $this->request->getPost('track')) ? $this->di['helper']->resRet('', 200) : $this->di['helper']->resRet('下注失败，请重新尝试', 500);
        } catch (Exception $e) {
            return $this->di['helper']->resRet($e->getMessage(), 500);
        }
    }

    /**
     * 获取下期投注
     * @return [type] [description]
     */
    public function nextAction()
    {
        // 获取id
        if (!$betId = intval($this->request->getQuery('betid')))
            return $this->di['helper']->resRet('', 500);

        $perid = $this->request->getQuery('perid');

        if (!$res = $this->resultLogic->getNextPerids($betId, 1, $perid))
            return $this->di['helper']->resRet('已经停止开奖', 500);

        $interval = $this->syscfgLogic->getClosetime(); // 封单时间
        $data = [
            'expect' => $res[0]['bres_periods'],
            'openTime' => $res[0]['bres_open_time'],
            'interval' => $interval, // 封单时间
            'closeTime' => $res[0]['bres_open_time'] - $interval,
            'time' => time()
        ];

        return $this->di['helper']->resRet($data, 200);
    }

    public function nextallAction()
    {
        $interval = $this->syscfgLogic->getClosetime(); // 封单时间
        // 获取id
        $gameId = intval($this->request->getPost('betid'));
        $perid = 0;
        if($gameId == 0)
        {
            $all = $this->logic->getLotteryType();
            foreach ($all as $key=>$value)
            {
                $allId[$key] = intval($value['bet_id']);
            }
            $betId = $allId;
            $len = count($betId);
            $res = [];
            for($i = 0; $i < $len; $i++)
            {
               $result = $this->resultLogic->getNextAllPerids($betId[$i], 1, $perid,$interval);
               //加拿大，有可能为空
               if(empty($result))
               {
                    array_push($res, ['bet_id'=>$i+1,'bres_periods'=>0,'bres_open_time'=>-1]);
               }
               else{
                    array_push($res, $result[0]);
               }
    
            }
        }
        else
        {
            $res = $this->resultLogic->getNextAllPerids($gameId, 1, $perid,$interval);
        }

        $data = [
            'interval' => $interval, // 封单时间
            'res' => $res,
            'time' => time()
        ];

        return $this->di['helper']->resRet($data, 200);
    }

    /**
     * 获取追号期数列表
     * @return [type] [description]
     */
    public function nxtperidsAction()
    {
        if (!$betId = intval($this->request->getQuery('betid')))
            return $this->di['helper']->resRet('', 500);

        $nums = $this->request->getQuery('nums') ?: 2;
        if (!$res = $this->resultLogic->getNextTrackPerids($betId, $nums))
            return $this->di['helper']->resRet('追号方案生成失败', 500);

        $data = array_map(function($v) {
            return $v['bres_periods'];
        }, $res);

        return $this->di['helper']->resRet($data, 200);
    }

    public function resultAction()
    {
        if (!$gameId = intval($this->request->getQuery('game_id')))
            return $this->di['helper']->resRet('', 500);

        $issue = $this->request->getQuery('issue');

        switch ($gameId) {
            case '1':   // 重庆时时彩
                $this->view->pick('game/result/cqssc');
                break;
            case '2':   // pc蛋蛋
                $this->view->pick('game/result/pcdd');
                break;
            case '3':   // 幸运飞艇
                $this->view->pick('game/result/xyft');
                break;
            case '4':   // 广东快乐十分
                $this->view->pick('game/result/gdkl10');
                break;
            case '5':   // 北京赛车PK10
                // 球颜色
                $this->view->pick('game/result/bjsc');
                break;
            case '6':   // 北京赛车PK10
                // 球颜色
                $this->view->pick('game/result/cakeno');
                break;
            case '7':   // 福建快3
                $this->view->pick('game/result/fjks');
                break;
            case '8':   // 北京快3
                $this->view->pick('game/result/bjks');
                break;
            case '9':   // 江苏快3
                $this->view->pick('game/result/jsks');
                break;
            case '10':   // 广东11选5
                $this->view->pick('game/result/gd11x5');
                break;
            case '11':   // 浙江11选5
                // 球颜色
                $this->view->pick('game/result/zj11x5');
                break;
            case '12':   // 山东11选5
                // 球颜色
                $this->view->pick('game/result/sd11x5');
                break;
            case '13':   // 辽宁11选5
                // 球颜色
                $this->view->pick('game/result/ln11x5');
                break;
            case '14':   // 辽宁11选5
            case '15':
            case '16':
                // 球颜色
                $this->view->pick('game/result/kuai3');
                break;
            case '17':   // 辽宁11选5
            case '18':
            case '19':
            case '20':
                // 球颜色
                $this->view->pick('game/result/11x5');
                break;
            case '21':
                // 球颜色
                $this->view->pick('game/result/txffc');
                break;
        }
        $ballColor = ['mbg-yellow', 'mbg-dblue', 'mbg-dgray', 'mbg-orange', 'mbg-blue', 'mbg-purple', 'mbg-gray', 'mbg-red', 'mbg-dred', 'mbg-green','mbg-default'];
        $this->view->setVars(['ballColor' => $ballColor]);
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();
        $interval = $this->syscfgLogic->getClosetime(); // 封单时间
        if($issue == 0)
        {
            $list = $this->resultLogic->lists($gameId, 10);
        }
        else
        {
            $bresid = $this->resultLogic->getBresId($gameId, $issue)[0]['bres_id'];
            $list = $this->resultLogic->tolists($gameId, $bresid,10);
        }

        $this->view->setVars([
            'lists' => $list,
            'ruleCfg' => $this->di['config']['game'],
        ]);
    }

    public function openAction()
    {
        $issue = $this->request->getPost('issue');
        $betId = $this->request->getPost('game_id');
        $uId = $this->uId;

        $isOrder = $this->orderLogic->getOrder($uId,$betId,$issue);

        if(empty($isOrder))
        {
            return $this->di['helper']->resRet('', 201);
        }
        $isOpen = $this->orderLogic->getOpen($uId,$betId,$issue);

        if(!empty($isOpen))
        {
            return $this->di['helper']->resRet('', 200);
        }
        else
        {
            return $this->di['helper']->resRet('', 500);
        }
    }

    public function trendAction()
    {
        if (!$gameId = intval($this->request->getQuery('id')))
            return $this->di['helper']->resRet('', 500);

        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $res['dx'] = $this->di['redis']->get('analyze:' . $gameId . ':dx') ?: [];
        $res['ds'] = $this->di['redis']->get('analyze:' . $gameId . ':ds') ?: [];
        $res['lr'] = $this->di['redis']->get('analyze:' . $gameId . ':lr') ?: [];
        $list = $this->resultLogic->lists($gameId, 10);

        $this->view->setVars($res);

        switch ($gameId) {
            case '1':   // 重庆时时彩
                $this->view->pick('game/trend/cqssc');
                break;
            case '21':   // 重庆时时彩
                $this->view->pick('game/trend/txffc');
                break;
            case '2':   // pc蛋蛋
                $this->view->pick('game/trend/pcdd');
                break;
            case '3':   // 幸运飞艇
                $this->view->pick('game/trend/xyft');
                break;
            case '4':   // 广东快乐十分
                $this->view->pick('game/trend/gdkl10');
                break;
            case '5':   // 北京赛车PK10
                // 球颜色
                $this->view->pick('game/trend/bjpk10');
                break;
            case '6':   // 北京赛车PK10
                // 球颜色
                $this->view->pick('game/trend/cakeno');
                break;
            case '7':   // 福建快3
                $this->view->pick('game/trend/ks');
                break;
            case '8':   // 北京快3
                $this->view->pick('game/trend/ks');
                break;
            case '9':   // 江苏快3
                $this->view->pick('game/trend/ks');
                break;
            case '10':   // 广东11选5
                $this->view->pick('game/trend/11x5');
                break;
            case '11':   // 浙江11选5
                // 球颜色
                $this->view->pick('game/trend/11x5');
                break;
            case '12':   // 山东11选5
                // 球颜色
                $this->view->pick('game/trend/11x5');
                break;
            case '13':   // 辽宁11选5
                // 球颜色
                $this->view->pick('game/trend/11x5');
                break;
            case '14':
            case '15':
            case '16':
                $analyzeZstKey = sprintf('analyze:%s:zst', $gameId);
                $zst = (array)$this->di['redis']->get($analyzeZstKey);
                if (!$type = intval($this->request->getQuery('type')))
                   $type = 0;
                if (!$brid = intval($this->request->getQuery('brid')))
                    return $this->di['helper']->resRet('', 500);
                $color = ['c-green', 'c-purple', 'c-theme', 'c-blue', 'c-orange'];
                $this->view->setVars(['zst'=>$zst,'nkey'=>$type, 'color'=>$color,'brid'=>$brid]);
                $this->view->pick('game/trend/kuai3G');
                break;

            case '17':   // 11选5
            case '18';
            case '19';
            case '20';
                // 球颜色
                $analyzeZstKey = sprintf('analyze:%s:zst', $gameId);
                $zst = (array)$this->di['redis']->get($analyzeZstKey);
                if (!$type = intval($this->request->getQuery('type')))
                   $type = 0;
                if (!$brid = intval($this->request->getQuery('brid')))
                    return $this->di['helper']->resRet('', 500);
                $color = ['c-green', 'c-purple', 'c-theme', 'c-blue', 'c-orange'];
                $this->view->setVars(['zst'=>$zst,'nkey'=>$type, 'color'=>$color,'brid'=>$brid]);
                $this->view->pick('game/trend/11x5G');
                break;
        }
    }

    public function recordAction()
    {
        if (!$gameId = intval($this->request->getQuery('id')))
            return $this->di['helper']->resRet('', 500);

        if (!$type = intval($this->request->getQuery('type')))
            return $this->di['helper']->resRet('', 500);


        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $list['normal'] = $this->orderLogic->getMyList($this->uId, $gameId, false);
        $list['track'] = $this->orderLogic->getMyList($this->uId, $gameId, true);

        $this->view->setVars($list);
        $this->view->setVars(['type'=>$type]);
        $this->view->pick('game/layrecord');
    }

    public function helpAction()
    {
        $this->view->setVars(['title' => '新手帮助']);
    }

    public function officialAction($betId)
    {
        if (!$betId)
            $this->response->redirect('/');

        // 获取游戏信息
        if (!$info = $this->logic->getInfoById($betId, 3))
            $this->response->redirect('/');

        $type = 3;
        // 获取游戏倍率设置
        if (!$rulesBase = $this->ruleLogic->getInfoById($betId))
            $this->response->redirect('/');


        // 获取当前用户返点
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'];
        $maxRate = $this->syscfgLogic->getRate();

        $rules = [];
        foreach ($rulesBase as $key=>$rule)
        {
            $baseBonus = ceil($rule['br_bonus']);
            $rule['br_bonus'] = sprintf('%.3f', $rule['br_bonus'] - ($baseBonus * ($maxRate - $rate) / 100));
            array_push($rules, $rule);
        }
        $temp = [];
        foreach ($rules as $key => $value) {
            if($value['br_type'] == 38) {
                $temp = $rules[$key];
                unset($rules[$key]);
            }
        }
        if(!empty($temp))
            array_unshift($rules, $temp);

        $key = $this->uId.$this->uName.'bet';
        $betRate = !empty($this->cookies->get($key)->getValue())?$this->cookies->get($key)->getValue(): '0';

        $this->di['view']->setVars([
            'title' => $info['bet_name'],
            'gameId' => $betId,
            'info' => $info,
            'gameList' => $this->logic->getAll(1),
            'config' => $this->di['config']['game'],
            'rules' => $rules,
            'rate' => $rate,
            'trackMax' => $this->syscfgLogic->getTrackMax(),
            'betRate' => $betRate,
        ]);
    }

    public function showAction()
    {
        if (!$gameId = intval($this->request->getQuery('id')))
            return $this->di['helper']->resRet('', 500);
        if (!$type = intval($this->request->getQuery('br_type')))
            return $this->di['helper']->resRet('', 500);

        if($type == 38)
        {
            $r = $br=[];
            $ruleBase = $this->ruleLogic->getInfo($gameId, $type);
            $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'];
            $maxRate = $this->syscfgLogic->getRate();
            $ball = $this->di['config']['game']['rule_base_type'];

            foreach ($ruleBase as $key => $value) {
                $baseBonus = ceil($value['br_bonus']);
                $r[$ball[$value['br_base_type']]] = sprintf('%.3f', $value['br_bonus'] - ($baseBonus * ($maxRate - $rate) / 100));
                $br[$ball[$value['br_base_type']]] = $value['br_id'];
            }
        }

        $dt = !empty($this->request->getQuery('dt')) ? intval($this->request->getQuery('dt')) : 0;

        if (in_array($gameId, $this->di['config']['game']['11xuan5'])) {
            if ($type ==37) {

                $html = '<div class="game-title" >万位号码</div>';
                $html .= '<ul class="data-show new-play">';
                for($i=1;$i<=11;$i++){
                    $j = $i < 10 ? '0'.$i : $i;
                    $x = $i*10000;
                    $html .= <<<EOP
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball = {$x}>{$j}</i>
                            </span>
                        </li>
EOP;
                }
                $html .= '</ul>';
                $html .= '<div class="game-title" >千位号码</div>';
                $html .= '<ul class="data-show new-play">';
                for($i=1;$i<=11;$i++){
                    $j = $i < 10 ? '0'.$i : $i;
                    $x = $i*100;
                    $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                    }
                $html .= '</ul>';
                $html .= '<div class="game-title" >百位号码</div>';
                $html .= '<ul class="data-show new-play">';
                for($i=1;$i<=11;$i++){
                    $x = $i*1;
                    $j = $i < 10 ? '0'.$i : $i;
                    $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                    }
                $html .= '</ul>';
        } else if ($type == 35) {
            $html = '<div class="game-title" >万位号码</div>';
            $html .= '<ul class="data-show new-play">';
            for($i=1;$i<=11;$i++){
                $x = $i*10000;
                $j = $i < 10 ? '0'.$i : $i;
                $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                }
            $html .= '</ul>';
            $html .= '<div class="game-title" >千位号码</div>';
            $html .= '<ul class="data-show new-play">';
            for($i=1;$i<=11;$i++){
                $x = $i*100;
                $j = $i < 10 ? '0'.$i : $i;
                $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                }
            $html .= '</ul>';
        } else if($type == 26) {

                $html = '<div class="game-title" >选号</div>';
                $html .= <<<EOP
                <div class="fast-btn">
                         <label class="fast-tit">快捷</label>
                         <span class="it" data-id=1>全</span>
                         <span class="it" data-id=2>大</span>
                         <span class="it" data-id=3>小</span>
                         <span class="it" data-id=4>奇</span>
                         <span class="it" data-id=5>偶</span>
                         <span class="it" data-id=6>清</span>
                </div>
EOP;
            $html .= '<ul class="data-show new-play">';
            for ($i = 1;$i <= 11;$i++){
                $x = $i*10000;
                $j = $i < 10 ? '0'.$i : $i;
                $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                }
            $html .= '</ul>';
        } else {
            if($dt==1) {
            $html ='<div class="standard-bar flex"><div class="item flex1 " data-id=0 ><span>普通</span></div><div class="item flex1 on" data-id=1><span>胆拖</span></div></div>';
            $html .= '<div class="game-title" >胆码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 11 ; $i++) {
                    $x = $i*10000;
                    $j = $i < 10 ? '0'.$i : $i;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$j.'</i>
                            </span>
                        </li>';
                }
                $html .= '</ul>';
                $html .= '<div class="game-title" >拖码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 11 ; $i++) {
                    $x = $i*100;
                    $j = $i < 10 ? '0'.$i : $i;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$j.'</i>
                            </span>
                        </li>';
                }
                 $html .= '</ul><input type="hidden" id="dt" value=1>';
            } else {
                $html ='<div class="standard-bar flex"><div class="item flex1 on" data-id=0 ><span>普通</span></div><div class="item flex1" data-id=1><span>胆拖</span></div></div>';
                $html .= '<div class="game-title" >选号</div>';
                $html .= <<<EOP
                <div class="fast-btn">
                         <label class="fast-tit">快捷</label>
                         <span class="it" data-id=1>全</span>
                         <span class="it" data-id=2>大</span>
                         <span class="it" data-id=3>小</span>
                         <span class="it" data-id=4>奇</span>
                         <span class="it" data-id=5>偶</span>
                         <span class="it" data-id=6>清</span>
                </div>
EOP;
            $html .= '<ul class="data-show new-play">';
            for ($i = 1;$i <= 11;$i++){
                $x = $i*10000;
                $j = $i < 10 ? '0'.$i : $i;
                $html .= <<<EOP
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball = {$x}>{$j}</i>
                        </span>
                    </li>
EOP;
                }
            $html .= '</ul>';
            }
        }
    }

    if (in_array($gameId, $this->di['config']['game']['kuai3'])) {
        if ($type == 38)
        {
            $html = '<div class="game-title" >选号</div>';
            $html .= <<<EOP
                <div class="fast-btn">
                         <label class="fast-tit">快捷</label>
                         <span class="it" data-id=1>全</span>
                         <span class="it" data-id=2>大</span>
                         <span class="it" data-id=3>小</span>
                         <span class="it" data-id=4>奇</span>
                         <span class="it" data-id=5>偶</span>
                         <span class="it" data-id=6>清</span>
                </div>
EOP;

            $html .= '<ul class="data-show small">';
            for ($i = 3;$i <= 18;$i++){
                $x = $i*10000;
                $html .= '<li><span class="cms"><i class="tit" data-bonus='.$r[$i].' data-br='.$br[$i].' data-odd='.$r[$i].' data-ball ='.$x.'>'.$i.'</i><em class="srate">'.$r[$i].'</em></span></li>';
                }
            $html .= '</ul>';
        }
        else if ($type == 39)
        {
            $html = '<div class="game-title" >选号</div>';
            $html .= <<<EOP
            <ul class="data-show new-k3">
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball=1110000>三同号通选</i>
                        </span>

                    </li>
                </ul>
EOP;
        }
        else if ($type == 40)
        {
            $html = '<div class="game-title" >选号</div>';
            $html .= '<ul class="data-show new-play">';
            for ( $i=1; $i <= 6 ; $i++) {
                $j = $i.$i.$i;
                $x = $j * 10000;
                $html .= '
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball ='.$x.'>'.$j.'</i>
                        </span>
                    </li>';
            }
            $html .= '</ul>';
        }
        else if ($type == 41)
        {
            if($dt==1) {
                 $html ='<div class="standard-bar flex"><div class="item flex1" data-id=0 ><span>普通</span></div><div class="item flex1 on" data-id=1><span>胆拖</span></div></div>';
                $html .= '<div class="game-title" >胆码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 6 ; $i++) {
                    $x = $i * 10000;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                            </span>
                        </li>';
                }
                $html .= '</ul>';
                $html .= '<div class="game-title" >拖码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 6 ; $i++) {
                    $x = $i * 100;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                            </span>
                        </li>';
                }
            $html .= '</ul><input type="hidden" id="dt" value=1>';
            }
            else{
                $html ='<div class="standard-bar flex"><div class="item flex1 on" data-id=0 ><span>普通</span></div><div class="item flex1" data-id=1><span>胆拖</span></div></div>';
                $html .= '<div class="game-title" >选号</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 6 ; $i++) {
                    $x = $i*10000;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                            </span>
                        </li>';
                }
                $html .= '</ul>';
            }
        }
        else if ($type == 42)
        {
            $html = '<div class="game-title" >选号</div>';
            $html .= <<<EOP
            <ul class="data-show new-k3">
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball=1230000>三连号通选</i>
                        </span>

                    </li>
                </ul>
EOP;
        }
        else if ($type == 43)
        {
            $html = '<div class="game-title" >选号</div>';
            $html .= '<ul class="data-show new-play">';
            for ( $i=1; $i <= 6 ; $i++) {
                $j = $i.$i;
                $x = $j * 10000;
                $html .= '
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball ='.$x.'>'.$j.'</i>
                        </span>
                    </li>';
            }
            $html .= '</ul>';
        }
        else if ($type == 44)
        {
            $html = '<div class="game-title" >同号</div>';
            $html .= '<ul class="data-show new-play">';
            for ( $i=1; $i <= 6 ; $i++) {
                $j = $i.$i;
                $x = $i * 10000;
                $html .= '
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball ='.$x.'>'.$j.'</i>
                        </span>
                    </li>';
            }
            $html .= '</ul>';
            $html .= '<div class="game-title" >不同号</div>';
            $html .= '<ul class="data-show new-play">';
            for ( $i=1; $i <= 6 ; $i++) {
                $x = $i * 100;
                $html .= '
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                        </span>
                    </li>';
            }
            $html .= '</ul>';
        }
        else if ($type == 45)
        {
            if($dt==1) {
                $html ='<div class="standard-bar flex"><div class="item flex1" data-id=0 ><span>普通</span></div><div class="item flex1 on" data-id=1><span>胆拖</span></div></div>';
                $html .= '<div class="game-title" >胆码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 6 ; $i++) {
                    $x = $i * 10000;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                            </span>
                        </li>';
                }
                $html .= '</ul>';
                $html .= '<div class="game-title" >拖码</div>';
                $html .= '<ul class="data-show new-play">';
                for ( $i=1; $i <= 6 ; $i++) {
                    $x = $i * 100;
                    $html .= '
                        <li>
                            <span class="cms">
                                <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                            </span>
                        </li>';
                }
            $html .= '</ul><input type="hidden" id="dt" value=1>';
            }
            else {
            $html ='<div class="standard-bar flex"><div class="item flex1 on" data-id=0 ><span>普通</span></div><div class="item flex1" data-id=1><span>胆拖</span></div></div>';
            $html .= '<div class="game-title" >选号</div>';
            $html .= '<ul class="data-show new-play">';
            for ( $i=1; $i <= 6 ; $i++) {
                $x = $i*10000;
                $html .= '
                    <li>
                        <span class="cms">
                            <i class="tit" data-ball ='.$x.'>'.$i.'</i>
                        </span>
                    </li>';
            }
            $html .= '</ul>';
            }
        }
    }
        echo $html;
    }

    public function newLotteryAction()
    {

        if (!$total = trim($this->request->getPost('total')))
            return $this->di['helper']->resRet('', 500);

        if (!$price = trim($this->request->getPost('price')))
            return $this->di['helper']->resRet('', 500);

        if (!$rules = $this->request->getPost('rules'))
            return $this->di['helper']->resRet('', 500);

        if (!$gameId = intval($this->request->getPost('game_id')))
            return $this->di['helper']->resRet('', 500);


        $rules = json_decode($rules,true);

        $key = $this->uId.$this->uName.'bet';
        $this->di['cookie']->set($key, floatval(end($rules)['percent']),time()+5*365*24*60*60);

        // if (count($rules) != $total)
        //     return $this->di['helper']->resRet('', 500);

        $track = $this->request->getPost('track') ?: [];
        if ($track && count($track['perids']) < 2)
            return $this->di['helper']->resRet('追号期数至少2期', 500);

        // 用户可用余额是否充足
        $walletInfo = $this->walletLogic->getInfo($this->uId);
        if ($walletInfo['w_status'] != 1)
            return $this->di['helper']->resRet('您的钱包已被冻结，禁止下注', 500);

        if ($walletInfo['w_money'] < $price)
            return $this->di['helper']->resRet('您的余额不足，请先充值', 500);

        // 该游戏是否关闭
        if (!$gameInfo = $this->logic->getInfoById($gameId, 3))
            return $this->di['helper']->resRet('', 500);

        if ($gameInfo['bet_isenable'] != 1)
            return $this->di['helper']->resRet('当前彩种已关闭', 500);

        // 执行投注
        try {
            return $this->orderLogic->newCreate($gameId, $this->uId, $this->uName, $rules, $this->request->getPost('track')) ? $this->di['helper']->resRet('', 200) : $this->di['helper']->resRet('下注失败，请重新尝试', 500);
        } catch (Exception $e) {
            return $this->di['helper']->resRet($e->getMessage(), 500);
        }
    }
}
