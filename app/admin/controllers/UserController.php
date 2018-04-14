<?php
use Phalcon\Security;
use Phalcon\Mvc\View;

class UserController extends ControllerBase
{
    const NUM_PAGE = 20;
    public function initialize()
    {
        $this->logic = new UsersLogic();
        $this->userAgentLogic = new UserAgentLogic;
        $this->walletLogic = new WalletLogic();
        $this->perPage = $this->di['config']['admin']['perPage'];
    }

    public function indexAction()
    {
        $perpage = $this->di['config']['admin']['perPage'];
        $limit = intval($this->request->getQuery('limit')) ?: current($perpage);
        empty($perpage[$limit]) AND $limit = current($perpage);
        if (!in_array($limit, $perpage))
            $currentPage = 1;
        else
            $currentPage = intval($this->request->getQuery('page')) ?: 1;

        $condition = intval($this->request->getQuery('condition'))?intval($this->request->getQuery('condition')):1;
        $value = trim($this->request->getQuery('value'));
        
        if($condition == 1 || $condition == 5)
        {
            $total = $this->logic->getTotal($condition, $value);
            $res = $this->logic->getAllUser($currentPage, $limit, $condition, $value);
        }
        else if($condition == 3)
        {
            $total = $this->userAgentLogic->getCountNext($value)['total'];
            $ids = $this->userAgentLogic->getNextUid($currentPage, $limit, $value);
            foreach ($ids as $key => $value) {
                $id [] = $value['u_id'];
            }
            if(empty($id))
            {
                $res = [];
            }
            else
            {
                $res = $this->logic->getManeyUser(implode(',', $id));
            }

        }
        else if($condition == 9)
        {
            $total = $this->walletLogic->getWalletTotal()['total'];
            $res = $this->walletLogic->getUserList($currentPage, $limit);
        }
        else if($condition == 11)
        {
            $total = $this->walletLogic->getWalletTotal()['total'];
            $res = $this->walletLogic->getUserdownList($currentPage, $limit);
        }
        else
        {
            $total = 1;
            $res = $this->logic->getAllUser($currentPage, $limit, $condition, $value);
        }
        //获取最高返点配置
        $sysConfigLogic = new SysConfigLogic;
        $sysRate = $sysConfigLogic->getSysConfig(3)['sc_value'];

        $this->di['view']->setVars([
            'list' => $res,
            'total' => $total,
            'limit' => ceil($total/$limit),
            'perpage' => $perpage,
            'maxrate' => $sysRate,
            'condition' => intval($condition)
        ]);
    }

    public function detailAction()
    {
        $uid = intval($this->request->getQuery('uid'));
        //user is or not exist
        if(!$user = $this->logic->isUser($uid)){
            return $this->showMsg('用户不存在', true, '/user');
        }

        $this->di['view']->setVars([
            'user' => $user,
        ]);
    }

    public function doPassAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));
        if(!$this->logic->isUser($uid))
            return $this->di['helper']->resRet('用户不存在', 500);

        $pwd = trim($this->request->getPost('pwd'));
        $newPwd = trim($this->request->getPost('new_pwd'));

        $zpwd = trim($this->request->getPost('zpwd'));
        $newzPwd = trim($this->request->getPost('new_zpwd'));

        if(!empty($pwd))
        {
            if(strcmp($pwd,$newPwd) != 0)
                return $this->di['helper']->resRet('两次输入的密码不一致', 500);

             // 执行修改
            if (!$this->logic->changePwd($uid, $this->security->hash($pwd)))
                return $this->di['helper']->resRet('登录密码改失败', 500);

            $this->logContent = '修改登录密码用户id:'. $uid ;
            return $this->di['helper']->resRet('登录密码修改成功');
        }

        if(!empty($zpwd)) {
            if(strcmp($zpwd,$newzPwd) != 0)
                return $this->di['helper']->resRet('两次输入的密码不一致', 500);

             // 执行修改
            if (!$this->walletLogic->changePwd($uid, $this->security->hash($zpwd)))
                return $this->di['helper']->resRet('资金密码修改失败', 500);

            $this->logContent = '修改资金密码用户id:'. $uid ;
            return $this->di['helper']->resRet('资金密码修改成功');
        }
    }

    public function stopAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));

        if(!$this->logic->isUser($uid)){
            return $this->di['helper']->resRet('用户不存在', 500);
        }

        //禁止登录
        if(!$this->logic->stop($uid)){
            return $this->di['helper']->resRet('禁止登录失败', 500);
        }

        $this->logContent = '禁止用户id:'. $uid.'登录' ;
        return $this->di['helper']->resRet();
    }

    public function passesAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));

        if(!$this->logic->isUser($uid)){
            return $this->di['helper']->resRet('用户不存在', 500);
        }

        //解除禁止登录
        if(!$this->logic->pass($uid)){
            return $this->di['helper']->resRet('解除禁止登录失败', 500);
        }

        $this->logContent = '解除禁止用户id:'. $uid.'登录' ;
        return $this->di['helper']->resRet();
    }

    public function stopMoneyAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));

        if(!$this->logic->isUser($uid)){
            return $this->di['helper']->resRet('用户不存在', 500);
        }

        //禁止登录
        if(!$this->logic->stopMoney($uid)){
            return $this->di['helper']->resRet('冻结钱包失败', 500);
        }

        $this->logContent = '冻结用户id:'. $uid.'钱包' ;
        return $this->di['helper']->resRet();
    }

    public function passMoneyAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));

        if(!$this->logic->isUser($uid)){
            return $this->di['helper']->resRet('用户不存在', 500);
        }

        //禁止登录
        if(!$this->logic->passMoney($uid)){
            return $this->di['helper']->resRet('解冻钱包失败', 500);
        }

        $this->logContent = '解冻用户id:'. $uid.'钱包' ;
        return $this->di['helper']->resRet();
    }

    /**
     * 设置用户返点率
     * @return [type] [description]
     */
    public function setRateAction()
    {
        $this->view->disable();
        $uId = intval($this->request->getPost('id'));
        $rate = floatval($this->request->getPost('rate'));

        if(!$uId || !$rate)
            return $this->jsonMsg(0, 'Invalid Data!');

        //判断用户是否存在
        if (!$info = $this->userAgentLogic->getInfo($uId))
            return $this->jsonMsg(0, '用户记录不存在');

        if ($rate == $info['ua_rate'])
            return $this->jsonMsg(1, '修改成功');

        //获取上级代理或系统最高返点率
        if ($info['ua_u_id'] == 0)
        {
            $sysConfigLogic = new SysConfigLogic;
            $sysRate = $sysConfigLogic->getSysConfig(3)['sc_value'];
            if ($rate > $sysRate)
                return $this->jsonMsg(0, '返点率不得超过' . $sysRate . '%');
        }
        else
        {
            $uaRate = $this->userAgentLogic->getInfo($info['ua_u_id'])['ua_rate'];
            if ($rate > $uaRate)
                return $this->jsonMsg(0, '返点率不得超过' . $uaRate . '%');
        }

        //获取代理下级最高返点率
        $agentRate = $this->userAgentLogic->getAgentMaxRate($uId);
        if ($rate < $agentRate)
            return $this->jsonMsg(0, '返点率不得低于' . $agentRate . '%');

        if (!$this->userAgentLogic->setAgentRate($uId, $rate))
            return $this->jsonMsg(0, '修改失败');

        $this->logContent = '修改用户(ID: ' . $uId . ')返点为' . $rate . '%';
        $this->jsonMsg(1, '修改成功');
    }

    //获取用户银行帐号列表
    public function bankAccountAction()
    {
        $uId = intval($this->request->getPost('id'));

        if (!$uId)
            return $this->showMsg('Invalid Data!');

        $res = $this->logic->getUserBankAccount($uId);
        if (!$res['list'])
            return $this->showMsg('未设置银行帐号或记录不存在!');

        $this->view->pick('user/result/bankaccount');
        $config = $this->di['config'];

        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        )->enable();

        $this->view->setVars([
            'list' => $res['list'],
            'name' => $res['name'],
            'bank' => $config['bank']
        ]);
    }

    public function updateMoneyAction()
    {
        $this->view->disable();
        if (!$this->validFlag)
            return $this->di['helper']->resRet($this->warnMsg, 500);

        $uId = intval($this->request->getPost('id'));
        $type = intval($this->request->getPost('state'));
        $money = sprintf("%.4f", $this->request->getPost('money'));
        $reason = htmlspecialchars(trim($this->request->getPost('reason')));

        if (!$uId || !$money || !$reason || !$type)
            return $this->showMsg('Invalid Data!');

        if (!$this->logic->isUser($uId))
            return $this->di['helper']->resRet('用户不存在', 500);

        if (!in_array($type, [1, 3]))
            return $this->di['helper']->resRet('操作类型错误', 500);

        if ($type == 3)
        {
            if ($money > $this->walletLogic->money($uId))
                return $this->di['helper']->resRet('提现金额超过可用余额', 500);
        }
        //执行操作
        if (!$res = $this->walletLogic->updateUserMoney($uId, $type, $money, $reason))
            return $this->di['helper']->resRet('操作失败', 500);

        $this->logContent = '为用户（ID：' . $uId . ') ' . ($type == 1 ? '充值' : '提现') . ' 金额：' . $money . ' 元';

        return $this->di['helper']->resRet('操作成功', 200);
    }

    //出入账记录
    public function recordAction()
    {
        $uId = intval($this->request->getQuery('uId'));
        if (!$uId)
            $this->showMsg('Invalid Data!');
        $type = intval($this->request->getQuery('type'));
        if ($type && !in_array($type, [1, 3, 5, 7, 9, 11, 13, 15]))
            $this->showMsg('Invalid Data!');

        $startTime = $this->request->getQuery('startTime') ? strtotime($this->request->getQuery('startTime')) : '';
        $endTime = $this->request->getQuery('endTime') ? strtotime($this->request->getQuery('endTime')) : '';
        $page = intval($this->request->getQuery('page')) ?: 1;
        $nums = intval($this->request->getQuery('limit')) ?: current($this->perPage);

        $wallRecordLogic = new WalletRecordLogic();
        $res = $wallRecordLogic->userRecordLists($uId, $type, $startTime, $endTime, $page, $nums);
        $out = 0;
        $in = 0;
        foreach ($res['lists'] as $key => $value) {
            in_array($value['uwr_type'],['5','7'])?$out += $value['uwr_money']: $in += $value['uwr_money'];
        }

        $usersLogic = new UsersLogic();
        $this->di['view']->setVars([
            'name' => $usersLogic->getName($uId)['u_name'],
            'lists' => $res['lists'],
            'total' => $res['total'],
            'limit' => ceil($res['total']/$nums),
            'nums' => $nums,
            'numsPage' => $this->perPage,
            'out' => $out,
            'in' => $in,
            'uwrType' => [
                1 => '充值',
                3 => '开奖结算',
                5 => '提现',
                7 => '投注',
                9 => '系统赠送',
                11 => '返点',
                13 => '系统回水',
                15 => '退款',
            ],
        ]);
    }

    public function editmemoAction()
    {
        $uId = intval($this->request->getPost('uId'));
        $res = $this->logic->getName($uId)['u_name'];
        $data = ['u_name'=>$res,'uId'=>$uId];
        if($res)
            return $this->di['helper']->resRet($data, 200);
        else
            return $this->di['helper']->resRet($data, 500);
    }

    public function doeditmemoAction()
    {
        $info = $this->request->getPost('info');
        $uId = intval($this->request->getPost('uId'));
        $res = $this->logic->editMemo($info, $uId);
        if($res)
        {
            return $this->di['helper']->resRet('操作成功', 200);
        }
        else
        {
            return $this->di['helper']->resRet('操作失败', 500);
        }
    }
}
