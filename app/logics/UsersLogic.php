<?php
use Phalcon\Security;

class UsersLogic extends LogicBase
{

    public function __construct()
    {
        $this->usersModel = new UsersModel;
        $this->bankModel = new BankModel;
        $this->agentModel = new UserAgentModel;
        $this->walletModel = new WalletModel;
    }

    /**
     * 根据uid获取用户信息
     * @param type $uId
     * @param type $fields
     * @return type
     */
    public function getInfoByUid($uId, $fields = "*")
    {
        return $this->usersModel->getInfoByUid($uId, $fields);
    }

    /**
     * 检验登录状态
     * @return [type] [description]
     */
    public function checkLogin($uId,$uName)
    {
        if (!$rInfo = $this->di['redis']->get('check_'.$uId.'_login')) return false;

        $redisInfo = explode('_',$rInfo);

        if($redisInfo[0] !== session_id()) return false;

        return true;
    }

    public function login($uName, $pwd)
    {
        if (!$user = $this->usersModel->getInfoByName($uName, 'u_id, u_name, u_type, u_mobile, u_pass, u_status'))
            return false;

        if ($user['u_status'] != 1)
            return false;

        if (!$this->security->checkHash($pwd, $user['u_pass']))
            return false;

        return $user;
    }

    /**
     * 注册
     * @param  [type]  $type      会员类型  1-会员  3-代理
     * @param  [type]  $regType   注册渠道   1-网上   3-注册链接    5-代理添加
     * @param  [type]  $uNick     昵称
     * @param  [type]  $uName     用户名
     * @param  [type]  $pwd       密码
     * @param  integer $parentUid 上级uid
     * @param  float   $rate      返点
     * @return [type]             [description]
     */
    public function reg($type, $regType, $uNick, $uName, $pwd, $rate, $parentUid = 0, $unionid = 0)
    {
        return $this->usersModel->reg($type, $regType, $uNick, $uName, $pwd, $rate, $parentUid, $unionid);
    }

    public function getInfoByName($uName)
    {
        return $this->usersModel->getInfoByName($uName, 'u_name, u_id, u_type, u_pass, u_status');
    }

    public function getInfoByMobi($mobi)
    {
        return $this->usersModel->getInfoByMobi($mobi, 'u_name, u_id, u_mobile, u_email, u_wx_unionid, u_pass');
    }

    /**
     * 用户名是否存在
     * @return [type] [description]
     */
    public function unameExists($uName)
    {
        return $this->usersModel->getInfoByName($uName, 'u_id');
    }

    public function logout()
    {
        // $this->di['cookie']->get('auth')->delete();
        // $this->di['redis']->delete('user:' . $this->di['session']->get('uInfo')['u_id']);
        $this->di['session']->remove('uInfo');
    }

    public function saveLogin($uId,$uName,$type = 1)
    {
        $random = new Phalcon\Security\Random();
        $uuid = $random->uuid();

        $sessionKey = 'user_'.$uId.'_login';
        $this->di['cookie']->set($sessionKey,session_id(),time()+7*26*3600);
        $this->di['session']->set('uInfo', ['u_id' => $uId, 'u_name' => $uName, 'u_type' => $type] );
        $this->di['session']->set('sysac', 1);
        $checkKey = 'check_'.$uId.'_login';
        $this->di['redis']->set($checkKey,session_id().'_'.$uuid);
    }

    public function changePwd($uId, $newPwd)
    {
        return $this->usersModel->changePwd($uId, $newPwd);
    }

    public function changePwdByMobi($info, $newPwd)
    {
        return $this->usersModel->changePwdByMobi($info, $newPwd);
    }

    public function getType($uId)
    {
        return $this->usersModel->getType($uId);
    }

    public function getName($uId)
    {
        return $this->usersModel->getName($uId);
    }

    public function getAllUser($currentPage, $num, $condition, $value)
    {
        if($condition == 1)
        {
            $index = ($currentPage - 1) * $num;
            $user = $this->usersModel->getAllUser($index, $num, $condition, $value);
        }
        else
        {
            $index = ($currentPage - 1) * $num;
            $user = $this->usersModel->getAllUser($index, $num, $condition, $value);
        }

        $uIds = [];
        foreach ($user as $value) {
            $uIds[] = $value['u_id'];
        }

        $res = [];
        if ($uIds)
        {
            $wall = $this->walletModel->getWalletByUids($uIds);
            $rate = $this->agentModel->getAgentByUids($uIds);
        }


        foreach ($user as $key => $u) {
            $res[] = array_merge($u, $wall[$key], $rate[$key]);
        }

        return $res;
    }

    public function getTotal($condition, $value)
    {
       return $this->usersModel->getTotalNum($condition, $value)['total'];
    }

    public function isUser($uid)
    {
       return $this->usersModel->isUser($uid);
    }

    public function stop($uid)
    {
       return $this->usersModel->stop($uid);
    }

    public function pass($uid)
    {
       return $this->usersModel->pass($uid);

    }
    public function stopMoney($uid)
    {
       return $this->usersModel->stopMoney($uid);
    }

    public function passMoney($uid)
    {
       return $this->usersModel->passMoney($uid);
    }

    /**
     * 根据uid获取昵称
     * @param type $uId
     * @return type
     */
    public function getUnick($uId)
    {
        return $this->usersModel->getUnick($uId);
    }

    /**
     * 根据名称获取uid
     * @param type $name
     * @return type
     */
    public function getUid($name)
    {
         return $this->usersModel->getUid($name);
    }

    /**
     * 获取对应会员类型的人数
     * @param  [type] $startDay [description]
     * @param  [type] $endDay   [description]
     * @return [type]           [description]
     */
    public function getUserTypeNums($startDay, $endDay)
    {
        $res['user'] = $this->usersModel->getUserNums(1, $startDay, $endDay);
        $res['agent'] = $this->usersModel->getUserNums(3, $startDay, $endDay);

        return $res;
    }

    /**
     * 获取用户银行账户
     * @param  [type] $uId [description]
     * @return [type]      [description]
     */
    public function getUserAccount($uId)
    {
        //获取用户昵称
        $res['name'] = $this->usersModel->getUserName($uId)['u_name'];
        //获取银行账户列表
        $res['list'] = $this->bankModel->getInfoByUserId($uId);

        return $res;
    }

    public function getUserBankAccount($uId)
    {
        //获取用户昵称
        $res['name'] = $this->usersModel->getUserName($uId)['u_name'];
        //获取银行账户列表
        $res['list'] = $this->bankModel->getBankInfoByUserId($uId);

        return $res;
    }

    public function  getAllids()
    {
        return $this->usersModel->getAllids();
    }

    public function doBase($uId, $fields)
    {
        return $this->usersModel->doBase($uId, $fields);
    }

    public function doBases($uId, $fields, $pass)
    {
        return $this->usersModel->doBases($uId, $fields, $pass);
    }

    public function getUserbyUnionid($unionid)
    {
        return $this->usersModel->getUserbyUnionid($unionid);
    }

    public function getUserbyMobi($mobi)
    {
        return $this->usersModel->getUserbyMobi($mobi);
    }

    /**
     * 密码校验
     * @param  [type] $uid  [description]
     * @param  [type] $pwd  [用户输入的密码]
     * @param  [type] $wPwd [系统存储的密码]
     * @return [type]       [description]
     */
     public function checkPass($uid, $pwd, $wPwd) {
        $num = $this->redis->get('pass:'.$uid.':');
        $num = $num ? $num : 0;
        if($num == 3) //连续输错三次密码
            return -1;
        if(!$this->security->checkHash($pwd, $wPwd)) {
            $time = strtotime(date('Ymd')) + 86400 - time();
            $this->redis->setex('pass:'.$uid.':', $time, $num+1);//每输错密码次数加1
            return -2;   //密码错
        }

        //密码正确则删除输错次数记录
        if($num > 0)
            $this->redis->del('pass:'.$uid.':');

        return 1;
    }

    /**
     * 根据用户ids批量获取用户信息
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function getUserByIds($ids)
    {
        $fields = 'u_id, u_name';
        return $this->usersModel->getUserByIds($ids, $fields);
    }

    public function getManeyUser($ids)
    {
        $user = $this->usersModel->getManeyUser($ids);
        $uIds = [];
        foreach ($user as $value) {
            $uIds[] = $value['u_id'];
        }

        $res = [];
        if ($uIds)
        {
            $wall = $this->walletModel->getWalletByUids($uIds);
            $rate = $this->agentModel->getAgentByUids($uIds);
        }

        foreach ($user as $key => $u) {
            $res[] = array_merge($u, $wall[$key], $rate[$key]);
        }
        return $res;
    }

    public function editMemo($info, $uId)
    {
        return $this->usersModel->editMemo($info, $uId);
    }

    public function updateName($name, $uid)
    {
        return $this->usersModel->updateName($name,$uid);  
    }
}