<?php

class IndexController extends ControllerBase
{

    public function initialize()
    {
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uId = $this->uInfo['u_id'];
        $this->uName = $this->uInfo['u_name'];

        $this->noticeLogic = new NoticeLogic;
        $this->bannerLogic = new BannerLogic;
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->resultLogic = new BetsResultLogic;
        $this->sysConfigLogic = new SysConfigLogic;
    }

    public function bannerAction()
    {
        //首页banner活动
        return $this->di['helper']->resRet($this->bannerLogic->getBannerInfo(), 200);
    }

    //首页公告
    public function noticeAction()
    {
        return $this->di['helper']->resRet($this->noticeLogic->getNotice(), 200);
    }

    //在线客服
    public function serviceAction()
    {
        $userId = $this->uId;
        $loginname = $this->uName;
        $name =$this->uName;
        $mtime=number_format(microtime(true),3,'','');
        $hashCode=md5(strtoupper(urlencode($userId.$loginname.$name.$mtime.'dazhongcai99889988')));
        $infoValue= urlencode('userId='.$userId.'&loginname='.$loginname.'&name='.$name.'&timestamp='.$mtime.'&hashCode='.$hashCode);
        $enterurl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        $url = 'https://vp8.livechatvalue.com/chat/chatClient/chatbox.jsp?companyID=80000802&configID=2371&k=1&s=1&info=' . $infoValue . '&enterurl=' . $enterurl;

        return $this->di['helper']->resRet(['url' => $url]);
    }

    public function indexAction()
    {
        $bets = $this->betsConfigLogic->getOpenBets('bet_id, bet_name, bet_play_type');
        $haveId = $this->di['redis']->smembers($this->uId.'have');
        $haveGId = $this->di['redis']->smembers($this->uId.'haveG');

        //获取所有彩种的logo数组
        $logos = $this->di['config']['game']['gameImg'];

        //获取网站url
        $url = $this->sysConfigLogic->getSysConfig(10, 'sc_value')['sc_value'] ?: 'https://dazhongcai.cn';

        $data = $tmp = [];
        foreach ($bets as $key => $bet) {
            $tmp = $bet;
            $tmp['img'] = array_key_exists($bet['bet_id'], $logos) ? $logos[$bet['bet_id']] : '';

            switch ($bet['bet_id']) {
                case '3':
                    $tmp['type'] = 2;//方形
                    break;
                case '5':
                    $tmp['type'] = 2;//方形
                    break;
                default:
                    $tmp['type'] = 1;//圆形
                    break;
            }

            //判断是否用户选择显示的彩种
            if ($bet['bet_play_type'] == 1)
            {
                //第一次进入默认显示所有信用玩法彩种
                if (!$haveId)
                {
                    $tmp['sel'] = 1;
                    $this->di['redis']->sadd($this->uId . 'have', intval($bet['bet_id']));
                    $this->di['redis']->sadd($this->uId . 'del', intval($bet['bet_id']));
                }
                else
                    $tmp['sel'] = in_array($bet['bet_id'], $haveId) ? 1 : 0;

            }
            else if($bet['bet_play_type'] == 3)
            {
                //第一次进入默认显示所有官方玩法彩种
                if (!$haveGId)
                {
                    $tmp['sel'] = 1;
                    $this->di['redis']->sadd($this->uId . 'haveG', intval($bet['bet_id']));
                    $this->di['redis']->sadd($this->uId . 'delG', intval($bet['bet_id']));
                }
                else
                    $tmp['sel'] = in_array($bet['bet_id'], $haveGId) ? 1 : 0;
            }

            //拼接game地址
            $gameUrl = $bet['bet_play_type'] == 1 ? 'game/info/' : 'game/official/';
            $tmp['url'] = $url . $gameUrl . $bet['bet_id'];

            //彩种说明
            $tmp['explain'] = '';
            if (array_key_exists($bet['bet_id'], $this->di['config']['tips']))
                $tmp['explain'] = $this->di['config']['tips'][$bet['bet_id']];

            $data[] = $tmp;
        }

        return $this->di['helper']->resRet($data);
    }

    //设置彩种是否显示
    public function setBetAction()
    {
        $betId = intval($this->request->getPost('betId'));
        $type = intval($this->request->getPost('type'));
        if (!$betId || !$type)
            return $This->di['helper']->resRet('Invalid Data!', 500);

        if (!in_array($type, [1, 3]))
            return $this->di['helper']->resRet('参数错误!', 500);

        if (!$info = $this->betsConfigLogic->getInfoById($betId))
            return $this->di['helper']->resRet('彩种不存在', 500);

        $key = $info['bet_play_type'] == 1 ? 'have' : 'haveG';
        $delKey = $info['bet_play_type'] == 1 ? 'del' : 'delG';
        $addKey = $info['bet_play_type'] == 1 ? 'add' : 'addG';
        if ($type == 1)//添加
        {
            if (in_array($betId, $this->di['redis']->smembers($this->uId . $key)))
                return $this->di['helper']->resRet('已经是显示彩种了，不能重复添加', 500);
            $this->di['redis']->sadd($this->uId . $key, $betId);
            $this->di['redis']->sadd($this->uId . $delKey, $betId);
            $this->di['redis']->srem($this->uId . $addKey, $betId);
        }
        else if ($type == 3)//删除
        {
            $len = count($this->di['redis']->smembers($this->uId . $key));
            if ($len <= 3)
                return $this->di['helper']->resRet('至少保留三种彩种', 500);

            if (in_array($betId, $this->di['redis']->smembers($this->uId . $addKey)))
                return $this->di['helper']->resRet('已经隐藏彩种了，不能重复删除', 500);

            $this->di['redis']->srem($this->uId . $key, $betId);
            $this->di['redis']->srem($this->uId . $delKey, $betId);
            $this->di['redis']->sadd($this->uId . $addKey, $betId);
        }

        return $this->di['helper']->resRet();
    }

    //获取各彩种开奖倒计时
    public function opentimeAction()
    {
        //获取所有开放彩种
        $bets = $this->betsConfigLogic->getOpenBets('bet_id, bet_name, bet_play_type');

        if (!$bets)
            return $this->di['helper']->resRet('没有开放彩种', 500);

        $systemLogic = new SystemLogic();
        $interval = $systemLogic->getClosetime(); // 封单时间

        $ids = [];
        foreach ($bets as $bet) {
            $ids[] = $bet['bet_id'];
        }

        //获取彩种最近一期未开奖信息
        $fields = 'bet_id, bres_open_time';
        $lists = $this->resultLogic->getBetsNext($ids, $interval, $fields);

        if (!$lists)
            return $this->di['helper']->resRet();

        $tmp = [];
        foreach ($lists as $list) {
            $tmp[$list['bet_id']] = $list;
        }

        $res = $data = [];
        foreach ($bets as $bet) {
            if (array_key_exists($bet['bet_id'], $tmp))
            {
                $res['bet_id'] = $bet['bet_id'];
                $res['bres_open_time'] = $tmp[$bet['bet_id']]['bres_open_time'];
            }
            else
            {
                $res['bet_id'] = $bet['bet_id'];
                $res['bres_open_time'] = 0;
            }
            $data['list'][] = $res;
        }
        $data['interval'] = $interval;
        $data['time'] = $_SERVER['REQUEST_TIME'];
        return $this->di['helper']->resRet($data);
    }

}
