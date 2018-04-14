<?php
use Phalcon\Mvc\View;

class UserController extends ControllerBase
{

    public function initialize()
    {
        $this->logic = new UsersLogic();
        $this->userAgentLogic = new UserAgentLogic();
        $this->walletLogic = new WalletLogic();
        $this->teamLogic = new TeamLogic();
        $this->betsConfigLogic = new BetsConfigLogic;
        $this->betRuleLogic = new BetsRulesLogic;
        $this->dealflowLogic = new DealflowLogic;
        $this->userAgentModel = new UserAgentModel();
        $this->captchaLogic = new CaptchaLogic();
        $this->uInfo = $this->di['session']->get('uInfo');
        $this->uName = $this->uInfo['u_name'];
        $this->uId = $this->di['session']->get('uInfo')['u_id'];
    }

    public function indexAction()
    {
        $uInfo = $this->logic->getInfoByUid($this->uId, 'u_nick');
        $this->di['view']->setVars([
            'title' => '账户中心',
            'uNick' => $uInfo['u_nick'] ?: '',
            'today' => $this->teamLogic->getTodayCount($this->uId),
            'yestoday' => $this->teamLogic->getYestodayCount($this->uId),
            'hideBack' => true
        ]);
    }

    public function pwdAction()
    {
        $this->di['view']->setVar('title', '修改密码');
        $this->di['view']->disableLevel([
            View::LEVEL_LAYOUT => false,
        ]);
    }

    public function pwdloginAction()
    {
        if (!$this->request->isPost())
        {
            $this->di['view']->setVar('title', '修改登录密码');
            $this->di['view']->disableLevel([View::LEVEL_LAYOUT => false]);
            return true;
        }

        if (!$oldPwd = trim($this->request->getPost('old_pwd')))
            return $this->di['helper']->resRet('请输入旧密码', 500);

        $pwd = trim($this->request->getPost('new_pwd'));
        if ($err = $this->di['check']->pwd($pwd))
            return $this->di['helper']->resRet($err, 500);

        // 获取用户信息
        if (!$userInfo = $this->logic->getInfoByUid($this->uId, 'u_pass'))
            return $this->di['helper']->resRet('用户状态有误', 500);

        // 校验旧密码
        if (!$this->security->checkHash($oldPwd, $userInfo['u_pass']))
            return $this->di['helper']->resRet('原密码错误', 500);

        // 执行修改
        if (!$this->logic->changePwd($this->uId, $this->security->hash($pwd)))
            return $this->di['helper']->resRet('密码修改失败', 500);

        $this->logic->logout();
        return $this->di['helper']->resRet(['url' => '/auth/login']);
    }

    public function editinfoAction()
    {
        $type = $this->request->getQuery('type');
        $url = $this->request->getQuery('url');
        if(!$key =$this->redis->get('change:'.$type.'user:'.$this->uId))
            header("Location:/user/base");
        if($type ==2)
            $title = '修改资金密码';
        else if($type==4)
            $title = '修改手机号';
        else
            $title ='修改微信账号';

        $this->di['view']->setVars([
                'title' => $title,
                'url' => $url,
                'type' => $type
        ]);
        $this->di['view']->disableLevel([View::LEVEL_LAYOUT => false]);
    }

    public function agentAction()
    {
        $this->di['view']->setVar('title', '下线管理');
        $this->di['view']->setVar('hideBack', true);
        $this->di['view']->disableLevel([View::LEVEL_LAYOUT => false]);
    }

    public function bankAction()
    {
        $this->di['view']->setVar('title', '银行卡管理');
    }

    public function msgAction()
    {
        $this->di['view']->setVar('title', '站内信息');
        $this->di['view']->setVar('hideBack', true);
    }

    public function listAction()
    {
        $uName = trim($this->request->getQuery('username'));
        $log = trim($this->request->getQuery('log'))?trim($this->request->getQuery('log')):0;
        $uId = trim($this->request->getQuery('uId'));
        if(empty($uId))
        {
            $uId = $this->uId;
        }
        $startTime = trim($this->request->getQuery('starttime'));
        $endTime = trim($this->request->getQuery('endtime'));

        $endTime = $endTime ? strtotime($endTime) + 86399 : 0;

        $this->di['view']->disableLevel([
            View::LEVEL_LAYOUT => false,
        ])->setVars([
            'title' => '下线列表',
            'log' => $log,
            'list' => $this->userAgentLogic->lists($uId, $uName, strtotime($startTime), $endTime)
        ]);
    }

    public function editAction()
    {
        $info = trim($this->request->getPost('info'));
        $uId = intval(trim($this->request->getPost('uId')));
        $res = $this->userAgentLogic->editMemo($info, $uId);
        if($res)
        {
            $res = $this->userAgentLogic->getMemo($uId);
            return $this->di['helper']->resRet($res, 200);
        }
        else
        {
             return $this->di['helper']->resRet('修改失败', 500);
        }

    }

    public function ajaxinfoAction()
    {
        if (!$uId = trim($this->request->getQuery('uid')))
            return $this->di['helper']->resRet('参数错误', 500);

        if (!$detail = $this->userAgentLogic->getInfo($uId, 'ua_type, u_id, u_name'))
            return $this->di['helper']->resRet('获取失败', 500);

        $detail['type'] = $this->di['config']['agent'][$detail['ua_type']] ?: '';
        $lastTime = $this->logic->getInfoByUid($detail['u_id'], 'u_last_time');
        $detail['lastTime'] = $lastTime ? date('Y-m-d H:i:s', $lastTime['u_last_time']) : '';
        // 获取可用金额
        $detail['money'] = $this->walletLogic->money($detail['u_id']);

        return $this->di['helper']->resRet($detail);
    }

    /**
     * 返点设置
     * @return [type] [description]
     */
    public function rateAction()
    {
        if (!$this->request->isPost())
        {
            if (!$uId = trim($this->request->getQuery('uid')))
                $this->response->redirect('/user/list');

            // 获取用户基本信息
            if (!$user = $this->logic->getInfoByUid($uId))
                $this->response->redirect('/user/list');

            // 获取用户返点信息
            if (!$agent = $this->userAgentLogic->getInfo($uId))
                $this->response->redirect('/user/list');

            // 获取当前登录用户返点信息
            if (!$currentUser = $this->userAgentLogic->getInfo($this->uId, 'ua_rate'))
                $this->response->redirect('/user/list');

            $bonus = !empty($this->redis->get('nomalBonus')) ? $this->redis->get('nomalBonus') : 1.970 ;
            $this->di['view']->setVars([
                'title' => '返点设置',
                'user' => $user,
                'agent' => $agent,
                'rateMoney' => $this->di['helper']->rateMoney($bonus, $agent['ua_rate']),
                'currentUser' => $currentUser
            ]);
            $this->di['view']->disableLevel([
                View::LEVEL_LAYOUT => false,
            ]);
            return;
        }

        // 执行修改
        if (!$uId = trim($this->request->getPost('uid')))
            return $this->di['helper']->resRet('参数错误', 500);

        $upUid = intval($this->userAgentLogic->getInfo($uId)['ua_u_id']);
        if($upUid !== intval($this->uId))
        {
            return $this->di['helper']->resRet('只能修改直属下级的返点', 500);
        }

        $rate = floatval(trim($this->request->getPost('rate'))) ?: 0;

        $max = intval($this->userAgentLogic->getMaxRate($uId));
        if($rate<$max)
        {
            return $this->di['helper']->resRet('本级返点不能比本级的下级低', 500);
        }

        $this->userAgentLogic->updateRate($uId, $rate);
        return $this->di['helper']->resRet(['url' => '/user/list']);
    }

    public function freshByTypeAction()
    {
        $betId = trim($this->request->get('type'));

        $limit = $this->betsConfigLogic->getLimit($betId);
        switch ($limit[0]['bet_id'])
        {
            case 1: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 2: $play =['总和大','总和大','总和单','总和单','总和810','总大单','总大单','总小单','总小单','第五名虎','冠亚和3','冠亚和4','冠亚和5','冠亚和6','冠亚和7',
                            '冠亚和8','冠亚和9','冠亚和10','冠亚和11','冠亚和12','冠亚和13','冠亚和14','冠亚和15','冠亚和16','冠亚和17','冠亚和18','冠亚和19','冠亚和值单','冠亚和值双',
                            '冠亚和值大','冠亚和值小','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1',
                            '冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1'];break;
            case 3: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 4: $play =['单','双','大','小','总单','总双','总大','总小'];break;
            case 5: $play =['冠军龙','冠军虎','亚军龙','亚军虎','第三名龙','第三名虎','第四名龙','第四名虎','第五名龙','第五名虎','冠亚和3','冠亚和4','冠亚和5','冠亚和6','冠亚和7',
                            '冠亚和8','冠亚和9','冠亚和10','冠亚和11','冠亚和12','冠亚和13','冠亚和14','冠亚和15','冠亚和16','冠亚和17','冠亚和18','冠亚和19','冠亚和值单','冠亚和值双',
                            '冠亚和值大','冠亚和值小','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1',
                            '冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1','冠亚和1'];break;
        }
        $data = ['limit' => $limit, 'play' => $play];
        return $this->di['helper']->resRet($data, 200);
    }

    public function changeAction()
    {
        $this->di['view']->disableLevel([
            View::LEVEL_LAYOUT => false,
        ]);

        //获取交易类型
        $congif = $this->di['config'];
        $recordType = $congif['type'];

        //点击进入的用户id
        $uId = $this->request->get('uid');

        if(!empty($uId))
        {
            $uId = $uId;
            //获取所有下级id
            $downId = $this->userAgentModel->getDown($this->uId);
            $downId = explode(',',$downId);
            if(!in_array($uId,$downId))
            {
                $uId = $this->uId;
            }
        }
        else
        {
            $uId = $this->uId;
        }


        //mun=1即刚开始加载页面的时候去10条数据
        $num = 1;

        $todayBegin = '';//一开始进入账变没有时间限制
        $todayEnd = '';

        //默认查10条
        $subInfo = $this->dealflowLogic->getRecordInfo($uId, $todayBegin, $todayEnd, 0, $num);

        //查出所有数据，统计总得条数，金额
        $totalInfo = $this->dealflowLogic->getTotalInfo($uId, $todayBegin, $todayEnd, 0);

        $subTotal = count($subInfo);
        $total = count($totalInfo);

        $subTotalIn = 0;
        $subTotalOut = 0;
        $totalIn = 0;
        $totalOut = 0;

        //统计小计支出与收入
        for($i = 0; $i < count($subInfo); $i++ )
        {
            if($subInfo[$i]['uwr_money'] >= 0)
            {
                $subTotalIn += $subInfo[$i]['uwr_money'];
            }
            else
            {
                $subTotalOut += $subInfo[$i]['uwr_money'];
            }
        }

        //统计总的支出与收入
        for($i = 0; $i < count($totalInfo); $i++ )
        {
            if($totalInfo[$i]['uwr_money'] >= 0)
            {
                $totalIn += $totalInfo[$i]['uwr_money'];
            }
            else
            {
                $totalOut += $totalInfo[$i]['uwr_money'];
            }
        }

        $this->view->setVars(['subInfo' => $subInfo, 'title' => '账变记录', 'subTotal' => $subTotal, 'subTotalIn' =>$subTotalIn , 'subTotalOut' =>$subTotalOut,
                              'total' => $total, 'totalIn' => $totalIn, 'totalOut' => $totalOut, 'recordType' => $recordType, 'uId' =>$uId ]);
    }

    public function changeFreshAction()
    {
        //mun=1即刚开始根据条件查询的时候去10条数据
        $num = 1;
        $name = $this->request->get('name');

        //判断如果输入用户名查询，则查出相应用户的id，没有此用户直接返回无数据   如果$name为空，即用户没有输入用户名，则查询登入用户
        if($name != '')
        {
            //判断是否在下级范围内
            $downId = $this->userAgentModel->getDown($this->uId);
            $downId = explode(',',$downId);
            $uId = $this->logic->getUid($name);
            $uId = intval($uId['u_id']);
            if(empty($uId))
            {
               return $this->di['helper']->resRet('没有任何资料', 500);
            }

            if(!in_array($uId, $downId))
            {
               return $this->di['helper']->resRet('没有任何资料', 500);
            }
        }
        else
        {
            $uId = (int)$this->request->getPost('uId');
            if(!empty($uId))
            {
              $uId = $uId;
            }
            else
            {
                $uId = $this->uId;
            }
        }

        $next = $this->request->get('next');
        $type = (int)$this->request->get('type');
        $startDay = strtotime($this->request->get('startDay'));
        $endDay = strtotime($this->request->get('endDay'))+86400;
        if($startDay > $endDay)
        {
            return $this->di['helper']->resRet('开始日期不能晚于结束日期', 501);
        }

        $subTotalIn = 0;
        $subTotalOut = 0;
        $totalIn = 0;
        $totalOut = 0;
        //是否有选择包括下级
        if($next == 'true')
        {
            $ids[0] = $uId;

            $idInfo = $this->userAgentLogic->getAllAgent($uId);
            for($i = 1; $i <= count($idInfo); $i++)
            {
                $ids[$i] = $idInfo[$i-1]['u_id'];
            }
            $subInfo = $this->dealflowLogic->getRecordNext($ids, $startDay, $endDay, $type, $num);
            //如果没有数据，直接返回
            if(empty($subInfo))
            {
                return $this->di['helper']->resRet('没有任何资料', 500);
            }
            $totalInfo = $this->dealflowLogic->getRecordNextTotal($ids, $startDay, $endDay, $type);

            //统计小计支出，收入
            for($i = 0; $i < count($subInfo); $i++ )
            {
                if($subInfo[$i]['uwr_money'] >= 0)
                {
                    $subTotalIn += $subInfo[$i]['uwr_money'];
                }
                else
                {
                    $subTotalOut += $subInfo[$i]['uwr_money'];
                }
            }

            //统计总得支出，收入
            for($i = 0; $i < count($totalInfo); $i++ )
            {
                if($totalInfo[$i]['uwr_money'] >= 0)
                {
                    $totalIn += $totalInfo[$i]['uwr_money'];
                }
                else
                {
                    $totalOut += $totalInfo[$i]['uwr_money'];
                }
            }
            $subTotal = count($subInfo);
            $total = count($totalInfo);
            $data = ['subInfo' => $subInfo, 'subTotal' => $subTotal, 'total' => $total, 'subTotalIn' => $subTotalIn, 'subTotalOut' => $subTotalOut,
                    'totalIn' => $totalIn, 'totalOut' => $totalOut];

            return $this->di['helper']->resRet($data, 200);
        }
        else
        {
            $subTotalIn = 0;
            $subTotalOut = 0;
            $totalIn = 0;
            $totalOut = 0;
            $subInfo = $this->dealflowLogic->getRecordInfo($uId, $startDay, $endDay, $type, $num);
            $totalInfo = $this->dealflowLogic->getTotalInfo($uId, $startDay, $endDay, $type);
            if(empty($subInfo))
            {
                return $this->di['helper']->resRet('没有任何资料', 500);
            }
            $subTotal = count($subInfo);
            $total = count($totalInfo);

            //统计小计支出，收入
            for($i = 0; $i < count($subInfo); $i++ )
            {
                if($subInfo[$i]['uwr_money'] >= 0)
                {
                    $subTotalIn += $subInfo[$i]['uwr_money'];
                }
                else
                {
                    $subTotalOut += $subInfo[$i]['uwr_money'];
                }
            }

            //统计总得支出，收入
            for($i = 0; $i < count($totalInfo); $i++ )
            {
                if($totalInfo[$i]['uwr_money'] >= 0)
                {
                    $totalIn += $totalInfo[$i]['uwr_money'];
                }
                else
                {
                    $totalOut += $totalInfo[$i]['uwr_money'];
                }
            }
            $data = ['subInfo' => $subInfo, 'subTotal' => $subTotal, 'total' => $total, 'subTotalIn' => $subTotalIn, 'subTotalOut' => $subTotalOut,
                    'totalIn' => $totalIn, 'totalOut' => $totalOut, 'uId' => $uId];

            return $this->di['helper']->resRet($data, 200);

        }

    }

    public function addMoreAction()
    {
        $now = time();
        $today = date('Y-m-d ', $now);
        $num = $this->request->get('num');
        $name = $this->request->get('name');

        $subTo = $this->request->get('subTo');
        $subMonOut = $this->request->get('subMonOut');
        $subMonIn = $this->request->get('subMonIn');

        if(!empty($name))
        {
            $uId = $this->logic->getUid($name);
            $uId = $uId['u_id'];
            if(empty($uId))
            {
               return $this->di['helper']->resRet('没有任何资料', 500);
            }
        }
        else
        {
            $uId = $this->request->getPost('uId');
            if(!empty($uId))
            {
              $uId = $uId;          }
            else
            {
                $uId = $this->uId;
            }
        }

        $next = $this->request->get('next');
        $type = $this->request->get('type');
        $startDay = strtotime($this->request->get('startDay'));
        $endDay = strtotime($this->request->get('endDay'))+86400;
        if($startDay > $endDay)
        {
            return $this->di['helper']->resRet('开始日期不能晚于结束日期', 501);
        }

        $subTotalIn = $subMonIn;
        $subTotalOut = $subMonOut;
        $totalIn = 0;
        $totalOut = 0;
        if($next == 'true')
        {
            $ids[0] = $uId;

            $idInfo = $this->userAgentLogic->getAllAgent($uId);
            for($i = 1; $i <= count($idInfo); $i++)
            {
                $ids[$i] = $idInfo[$i-1]['u_id'];
            }
            $subInfo = $this->dealflowLogic->getRecordNext($ids, $startDay, $endDay, $type, $num);
            if(empty($subInfo))
            {
                return $this->di['helper']->resRet('没有任何资料', 500);
            }
            $totalInfo = $this->dealflowLogic->getRecordNextTotal($ids, $startDay, $endDay, $type);

            //统计小计支出，收入
            for($i = 0; $i < count($subInfo); $i++ )
            {
                if($subInfo[$i]['uwr_money'] >= 0)
                {
                    $subTotalIn += $subInfo[$i]['uwr_money'];
                }
                else
                {
                    $subTotalOut += $subInfo[$i]['uwr_money'];
                }
            }

            //统计总得支出，收入
            for($i = 0; $i < count($totalInfo); $i++ )
            {
                if($totalInfo[$i]['uwr_money'] >= 0)
                {
                    $totalIn += $totalInfo[$i]['uwr_money'];
                }
                else
                {
                    $totalOut += $totalInfo[$i]['uwr_money'];
                }
            }
            $subTotal = count($subInfo)+$subTo;
            $total = count($totalInfo);
            $data = ['subInfo' => $subInfo, 'subTotal' => $subTotal, 'total' => $total, 'subTotalIn' => $subTotalIn, 'subTotalOut' => $subTotalOut,
                    'totalIn' => $totalIn, 'totalOut' => $totalOut];

            return $this->di['helper']->resRet($data, 200);
        }
        else
        {
            $subTotalIn = $subMonIn;
            $subTotalOut = $subMonOut;
            $totalIn = 0;
            $totalOut = 0;
            $subInfo = $this->dealflowLogic->getRecordInfo($uId, $startDay, $endDay, $type, $num);
            $totalInfo = $this->dealflowLogic->getTotalInfo($uId, $startDay, $endDay, $type);
            if(empty($subInfo))
            {
                return $this->di['helper']->resRet('没有任何资料', 500);
            }
            $subTotal = count($subInfo)+$subTo;
            $total = count($totalInfo);

            //统计小计支出，收入
            for($i = 0; $i < count($subInfo); $i++ )
            {
                if($subInfo[$i]['uwr_money'] >= 0)
                {
                    $subTotalIn += $subInfo[$i]['uwr_money'];
                }
                else
                {
                    $subTotalOut += $subInfo[$i]['uwr_money'];
                }
            }

            //统计总的支出，收入
            for($i = 0; $i < count($totalInfo); $i++ )
            {
                if($totalInfo[$i]['uwr_money'] >= 0)
                {
                    $totalIn += $totalInfo[$i]['uwr_money'];
                }
                else
                {
                    $totalOut += $totalInfo[$i]['uwr_money'];
                }
            }
            $data = ['subInfo' => $subInfo, 'subTotal' => $subTotal, 'total' => $total, 'subTotalIn' => $subTotalIn, 'subTotalOut' => $subTotalOut,
                    'totalIn' => $totalIn, 'totalOut' => $totalOut];

            return $this->di['helper']->resRet($data, 200);

        }

    }

    /**
     * 切换是否显示余额
     * @return [type] [description]
     */
    public function tglmoneyAction()
    {
        $key = 'money:hide';
        $money = -1;
        if ($this->di['redis']->sIsMember($key, $this->uId))
        {
            $this->di['redis']->sRem($key, $this->uId);
            $money = (new WalletLogic())->money($this->uId) ?: 0.00;
        }
        else
            $this->di['redis']->sAdd($key, $this->uId);

        return $this->di['helper']->resRet($money, 200);
    }

    public function moneyAction()
    {
        $money = -1;
        if (!$this->di['redis']->sIsMember('money:hide', $this->uId))
            $money = (new WalletLogic())->money($this->uId) ?: 0.00;

        return $this->di['helper']->resRet($money, 200);
    }

    public function baseAction()
    {
        $uInfo = $this->logic->getInfoByUid($this->uId, 'u_mobile, u_email');
        $uPass = $this->walletLogic->pass($this->uId);
        $pd = 1;
        if ($uPass == '')
            $pd = 0;
        if(empty($uPass))
            $title = '完善资料';
        else
            $title = '个人信息';
        $this->assets
            ->addJs('js/distpicker.data.js')
            ->addJs('js/distpicker.js');
        $this->view->setVars([
                'title' => $title,
                'mobi' => !empty($uInfo['u_mobile']) ? substr_replace($uInfo['u_mobile'], '*****',3,-2) : '',
                'email' => !empty($uInfo['u_email']) ? $uInfo['u_email'] : '',
                'uPass' => !empty($uPass) ? $uPass : '',
                'pd' => $pd
            ]);
        $this->view->disableLevel([
            View::LEVEL_LAYOUT => false,
        ]);
    }

    public function doUserBaseAction()
    {
        $pass = trim($this->request->getPost('new_pwd'));
        if(empty($pass))
            return $this->di['helper']->resRet('密码不能为空', 500);
        if($pass != trim($this->request->getPost('c_pwd')))
            return $this->di['helper']->resRet('两次密码不一致', 500);

        $mobi = trim($this->request->getPost('mobi'));
        if(empty($mobi))
            return $this->di['helper']->resRet('手机号不能为空', 500);

        if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$mobi))
            return $this->di['helper']->resRet('请输入正确的手机号', 500);

        $email = trim($this->request->getPost('email'));
        // if(empty($mobi))
        //     return $this->di['helper']->resRet('微信号不能为空', 500);

        $code = trim($this->request->getPost('code'));
        if(empty($pass))
            return $this->di['helper']->resRet('验证码不能为空', 500);

        $res = $this->captchaLogic->checkCaptcha($mobi, 3, $code);
        if ($res['code'] > 0)
            return $this->di['helper']->resRet($res['msg'], 500);

        $files = ['u_mobile' => $mobi, 'u_email' => $email];
        if(!$this->logic->doBases($this->uId, $files,$this->security->hash($pass)))
            return $this->di['helper']->resRet('提交失败', 500);
        return $this->di['helper']->resRet('提交成功', 200);
    }

    public function mobiCapAction()
    {
        $type = $this->request->getPost('type');
        if (empty($type))
                return $this->di['helper']->resRet('参数错误', 500);
        if (!in_array($type, [2, 4, 5])) {
            if($type != 3) {
                if (!$captcha = trim($this->request->getPost('imgcap')))
                    return $this->di['helper']->resRet('请输入验证码', 500);

                if (strtolower($captcha) != strtolower($this->di['session']->get('captcha')))
                    return $this->di['helper']->resRet('图片验证码输入错误', 501);
            }
            $mobi = $this->request->getPost('mobi');
            if (!preg_match("/^1(3|4|5|7|8)\d{9}$/",$mobi))
                return $this->di['helper']->resRet('请输入正确的手机号', 500);

            if ($this->logic->getInfoByMobi($mobi))
                return $this->di['helper']->resRet('手机号已存在', 500);
        } else {
            $uInfo = $this->logic->getInfoByUid($this->uId, 'u_mobile, u_email');
            if(empty($uInfo['u_mobile']))
                return $this->di['helper']->resRet('参数错误', 500);
            $mobi = $uInfo['u_mobile'];
        }

        $info = $this->captchaLogic->getCaptcha($type, $mobi);
        if (!empty($info)) {
            if ($_SERVER['REQUEST_TIME'] <= $info['expire']) {
                return $this->di['helper']->resRet(['time'=>$info['expire'] - $_SERVER['REQUEST_TIME']],503);
            }
        }
        $code = rand(100000, 999999);

        $n = $this->redis->get('check:captcha:'.$mobi);
        if($n < 12 ) {
            if(!$this->captchaLogic->send($type, $mobi, $code))
                return $this->di['helper']->resRet('短信发送失败', 500);
            $time = strtotime(date('Ymd')) + 86400 - time();
            $this->redis->setex('captcha:'.$mobi, $time, $n+1);
             return $this->di['helper']->resRet('验证码已发送致手机', 200);
        } else {
            return $this->di['helper']->resRet('您的操作太过频繁拉，请明天再试。如有需要，请联系客服。', 500);
        }
    }

    public function checkcapAction()
    {
        $type = intval($this->request->getPost('type'));
        $code = trim($this->request->getPost('msgcode'));

        if (empty($type) || empty($code))
            return $this->di['helper']->resRet('参数错误', 500);

        $uInfo = $this->logic->getInfoByUid($this->uId, 'u_mobile, u_email');
        $mobi = $uInfo['u_mobile'];

         $res = $this->captchaLogic->checkCaptcha($mobi, $type, $code);
        if ($res['code'] > 0)
            return $this->di['helper']->resRet($res['msg'], 500);

        $this->redis->setex('change:'.$type.'user:'.$this->uId, 300, 1);
        return $this->di['helper']->resRet('ok', 200);
    }

    public function doEditInfoAction()
    {
        $data = $this->request->getPost('data');
        if(empty($data))
            return $this->di['helper']->resRet('参数错误', 500);
        if(!$this->redis->get('change:'.$data['type'].'user:'.$this->uId))
            return $this->di['helper']->resRet('页面已失效，请重新操作', 501);

        if($data['type'] == 2) {
            if(empty($data['pass']) && ($data['pass'] != $data['cpass']))
                return $this->di['helper']->resRet('密码格式不正确', 500);

            if(!$this->walletLogic->changePwd($this->uId, $this->security->hash($data['pass'])))
                return $this->di['helper']->resRet('修改失败', 500);
        } else if ($data['type'] == 4) {
            if(empty($data['mobi']) || empty($data['mobicap']))
                return $this->di['helper']->resRet('参数错误', 500);
             $res = $this->captchaLogic->checkCaptcha($data['mobi'], $data['type'], $data['mobicap']);
            if ($res['code'] > 0)
                return $this->di['helper']->resRet($res['msg'], 500);
             $files = ['u_mobile' => $data['mobi']];
            if(!$this->logic->doBase($this->uId, $files))
                return $this->di['helper']->resRet('修改失败', 500);
        } else {
            if(empty($data['wx']))
                return $this->di['helper']->resRet('请输入微信账号', 500);
            $files = ['u_email' => $data['wx']];
            if(!$this->logic->doBase($this->uId, $files))
                return $this->di['helper']->resRet('修改失败', 500);
        }
        $this->redis->del('change:'.$data['type'].'user:'.$this->uId);
        return $this->di['helper']->resRet('修改成功', 200);
    }
}
