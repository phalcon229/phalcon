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
        if (!$info = $this->logic->getInfoById($betId))
            $this->response->redirect('/');

        // 获取游戏倍率设置
        if (!$rulesBase = $this->ruleLogic->getInfoById($betId))
            $this->response->redirect('/');

        // 获取当前用户返点
        $rate = $this->userAgentLogic->getInfo($this->uId, 'ua_rate')['ua_rate'];
        $maxRate = $this->syscfgLogic->getRate();

        foreach ($rulesBase as $key => $rule)
        {
            $rulesBase[$key]['br_base_title'] = $this->di['config']['game']['rule_base_type'][$rule['br_base_type']];
        }

        $rules = [];

        foreach ($rulesBase as $rule)
        {
            if (empty($rules[$rule['br_type']]))  $rules[$rule['br_type']] = [];
            $rules[$rule['br_type']]['title'] = $this->di['config']['game']['rule_type'][$rule['br_type']];
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
        foreach ($rules as $key => $value) {
            $newrules[] = $value;
        }
        $key = $this->uId.$this->uName.'bet';
        $betRate = $this->cookies->get($key)->getValue()?$this->cookies->get($key)->getValue():0;

        $data = [
            'title' => $info['bet_name'],
            'gameId' => $betId,
            'gameList' => $this->logic->getAll(1),
            'rules' => $newrules,
            'rate' => $rate,
            'trackMax' => $this->syscfgLogic->getTrackMax(),
            'betRate' => $betRate,
        ];
        return $this->di['helper']->resRet($data, 200);

    }

    /**
     * 订单下注
     * @return [type] [description]
     */
    public function lotteryAction()
    {
        // $info = json_decode(file_get_contents('php://input'));
        
        // $gameId = $info->game_id;
        // $total = $info->total;
        // $price = $info->price;
        // $rules = $info->rules;
        // $track = $info->track;
        // return $this->di['helper']->resRet($info, 500);
        if (!$gameId = intval($this->request->getPost('game_id')))
        // if(!$gameId)
            return $this->di['helper']->resRet('', 500);

        if (!$total = trim($this->request->getPost('total')))
        // if(!$total)
            return $this->di['helper']->resRet('', 500);

        if (!$price = trim($this->request->getPost('price')))
        // if(!$price)
            return $this->di['helper']->resRet('', 500);

        if (!$rules = $this->request->getPost('rules'))
        // if(!$rules)
            return $this->di['helper']->resRet('', 500);
        $rules = json_decode($rules, true);
        // return $this->di['helper']->resRet($rules, 500);

        $key = $this->uId.$this->uName.'bet';
        $this->di['cookie']->set($key, $rules[0]['percent'],time()+5*365*24*60*60);

        if (count($rules) != $total)
            return $this->di['helper']->resRet('', 500);

        // $track = $this->request->getPost('track') ?: [];
        $track = $track ?: [];
        if ($track && count($track['perids']) < 2)
            return $this->di['helper']->resRet('追号期数至少2期', 500);

        // 用户可用余额是否充足
        $walletInfo = $this->walletLogic->getInfo($this->uId);
        if ($walletInfo['w_status'] != 1)
            return $this->di['helper']->resRet('您的钱包已被冻结，禁止下注', 500);

        if ($walletInfo['w_money'] < $price)
            return $this->di['helper']->resRet('您的余额不足，请先充值', 500);

        // 该游戏是否关闭
        if (!$gameInfo = $this->logic->getInfoById($gameId))
            return $this->di['helper']->resRet('', 500);

        if ($gameInfo['bet_isenable'] != 1)
            return $this->di['helper']->resRet('当前彩种已关闭', 500);

        // 执行投注
        try {
            return $this->orderLogic->create($gameId, $this->uId, $this->uName, $rules, $track) ? $this->di['helper']->resRet('', 200) : $this->di['helper']->resRet('下注失败，请重新尝试', 500);
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

    /**
     * 获取追号期数列表
     * @return [type] [description]
     */
    public function nxtperidsAction()
    {
        if (!$betId = intval($this->request->getQuery('betid')))
            return $this->di['helper']->resRet('', 500);

        $nums = $this->request->getQuery('nums') ?: 2;
        if (!$res = $this->resultLogic->getNextPerids($betId, $nums))
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
        }
        $ballColor = ['sbg-yellow', 'sbg-dblue', 'sbg-dgray', 'sbg-orange', 'sbg-blue', 'sbg-purple', 'sbg-gray', 'sbg-red', 'sbg-dred', 'sbg-green','sbg-default'];
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
        $data = [
            'lists' => $list,
            'ruleCfg' => $this->di['config']['game'],
        ];
        return $this->di['helper']->resRet($data, 200);

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

        return $this->di['helper']->resRet($res, 200);

        // switch ($gameId) {
        //     case '1':   // 重庆时时彩
        //         $this->view->pick('game/trend/cqssc');
        //         break;
        //     case '2':   // pc蛋蛋
        //         $this->view->pick('game/trend/pcdd');
        //         break;
        //     case '3':   // 幸运飞艇
        //         $this->view->pick('game/trend/xyft');
        //         break;
        //     case '4':   // 广东快乐十分
        //         $this->view->pick('game/trend/gdkl10');
        //         break;
        //     case '5':   // 北京赛车PK10
        //         // 球颜色
        //         $this->view->pick('game/trend/bjpk10');
        //         break;
        //     case '6':   // 北京赛车PK10
        //         // 球颜色
        //         $this->view->pick('game/trend/cakeno');
        //         break;
        // }
    }

    public function recordAction()
    {
        if (!$gameId = intval($this->request->getQuery('id')))
            return $this->di['helper']->resRet('', 500);

        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $list['normal'] = $this->orderLogic->getMyList($this->uId, $gameId, false);
        $len = count($list['normal']);
        for($i = 0; $i < count($len); $i++)
        {
            $list['normal'][$i]['bo_content'] = explode('-', $list['normal'][$i]['bo_content']);
            $list['normal'][$i]['bo_content'] = $this->di['config']['game']['rule_type'][$list['normal'][$i]['bo_content'][0]].':'
            .$this->di['config']['game']['rule_base_type'][$list['normal'][$i]['bo_content'][1]];
        }
        $list['track'] = $this->orderLogic->getMyList($this->uId, $gameId, true);
        $len = count($list['track']);
        for($i = 0; $i < count($len); $i++)
        {
            $list['track'][$i]['bo_content'] = explode('-', $list['track'][$i]['bo_content']);
            $list['track'][$i]['bo_content'] = $this->di['config']['game']['rule_type'][$list['track'][$i]['bo_content'][0]].':'
            .$this->di['config']['game']['rule_base_type'][$list['track'][$i]['bo_content'][1]];
        }
        return $this->di['helper']->resRet($list, 200);
    }

    public function helpAction()
    {
        $this->view->setVars(['title' => '新手帮助']);
        return $this->di['helper']->resRet('新手帮助', 200);

    }
}
