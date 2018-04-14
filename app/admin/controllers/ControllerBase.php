<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class ControllerBase extends Controller
{
    protected $safeRequest;
    protected $logContent = '';  //
    protected $ctrlName;    // 当前访问控制器名
    protected $actName;    // 当前访问方法名
    protected $uInfo = array(); // 用户信息
    protected $validRes = true;
    protected $validMsg = array();
    protected $params;
    protected $_sanReq = [];
    protected $validFlag = true;
    protected $warnMsg = '';
    protected $isSuper = false; // 是否超级管理员
    /**
     * set before execute route
     */
    public function beforeExecuteRoute($dispatcher)
    {

        $this->ctrlName = $this->dispatcher->getControllerName();
        $this->actName = $this->dispatcher->getActionName();

        //验证登录
        if(!$this->checkAuth() && $this->ctrlName != 'index' )
        {
            if(!$this->request->isAjax())
                return $this->showMsg('没有权限访问', true, '/index');

            if (!in_array($this->ctrlName, ['agent', 'system', 'order'])){
                $this->di['helper']->resRet('没有权限访问', 500);
                exit;
            }

                $this->jsonMsg(0, '没有权限访问');exit;
        }

        if($this->uInfo){

            $this->di['view']->setVars(array(
                 'uInfo' => $this->uInfo,
                'menus' => $this->_getPermMenu($this->uInfo['u_id']),
                'controller' => $this->ctrlName,
                'action' => $this->actName,
                'isSuper' => $this->isSuper,
                'icon' => $this->di['config']['menu'],
            ));
        }
        // 获取校验规则
        $rulesFile = __DIR__ . '/rules/' . ucfirst($this->ctrlName) . 'Rules.php';

        $this->_sanReq = $this->request->get();
        if (file_exists($rulesFile))
        {
            $rules = include $rulesFile;
            $actionRules = $rules && isset($rules[$this->actName]) ? $rules[$this->actName] : false;

            if ($actionRules)
            {
                $utils = new \Components\Utils\RulesParse($actionRules);
                $utils->parse();
                if (!$utils->resFlag)
                {
                    $this->validFlag = false;
                    $this->warnMsg = !empty(current($utils->warnMsg)['msg']) ? current($utils->warnMsg)['msg'] : 'Invalid Data';
                }
                else
                    $this->_sanReq = $utils->_sanReq;
            }
        }

    }

    /**
     * set after execute route
     *
     *
     */
    public function afterExecuteRoute($dispatcher)
    {
        if ($this->logContent)
        {
            $logLogic = new LogLogic();

            $uInfo = $this->di['session']->get('uInfo');
            $adminId = $uInfo['u_id'];//登录用户id
            $uname = $uInfo['u_name'];
            $roleId = $uInfo['groupid'];

            $controller = $this->ctrlName;
            $action = $this->actName;
            $logip = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));

            return $logLogic->addLog($adminId, $uname, $roleId, $controller, $action, $logip, $this->logContent);
        }
    }

    /**
     * 跳转提示页
     * @param  [type]  $msg      [description]
     * @param  boolean $error    [description]
     * @param  string  $back_url [description]
     * @param  string  $sec      [description]
     * @return [type]            [description]
     */
    protected function showMsg($msg, $error =false, $back_url='', $sec = '2')
    {
        $this->view->setVars(array(
            'back_url' => $back_url ?: (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'),
            'msg' => $msg,
            'error'=>$error,
            'sec' => $sec
          ));
        $this->view->pick("common/showMsg");

        $this->view->disableLevel(
            [
                View::LEVEL_LAYOUT      => true,
                View::LEVEL_MAIN_LAYOUT => true,
            ]
        );
        return true;
    }

     protected function jsonMsg($code, $data)
    {

        echo json_encode(['ret' => $code, 'msg' => $data]);
        return false;

    }

    /**
     * 校验身份
     * @return [type] [description]
     */
    public function checkAuth()
    {
        if (!$auth = $this->di['cookie']->get('auth')->getValue())
            return false;

        list($username, $password, $lastLoginTime) = explode('_', $auth);

        // 校验用户名和密码
        $admininfo = new AdminLogic();
        $this->uInfo = $admininfo->getAdminByUser($username);

        if ($this->uInfo['u_status'] != 1)
            return false;

        if (!$this->security->checkHash($password, $this->uInfo['u_pass']))
            return false;

        //获取对定的权限组id
        if(!$gruop = $admininfo->isGruop($this->uInfo['u_id']))
            return false;
        $uId = $this->session->get('uInfo')['u_id'];
        $adkey = 'admin:login:'.$uId;
        $sessionId = $this->di['redis']->get($adkey);
        if ($sessionId != session_id())
        {
            $admininfo->logout();
            return false;
        } 

        if($gruop['ugr_rel_id'] != 1){
            // 验证访问模块权限
            $visiMenu = $admininfo->getMenuByDetail($this->ctrlName, $this->actName);
            if (!$groupPerms = $admininfo->getPermByGidSid($gruop['ugr_rel_id']))
                return false;
            $perms = json_decode($groupPerms['perm_config'], true);

            if (!in_array($visiMenu['m_id'], $perms)){
                return false;
            }
        }

        $isSuper = 1;
        $this->di['session']->set('uInfo', ['u_id' => $this->uInfo['u_id'], 'u_name' => $this->uInfo['u_name'], 'groupid'=> $gruop['ugr_rel_id']] );
        return true;
    }

    protected function _getPermMenu($userId)
    {
        $admininfo = new AdminLogic();
        $menu = [];

        //超级管理员
        if ($this->di['session']->get('uInfo')['groupid'] ==1)
        {
            $menu = $admininfo->getMenus();

        }
        else
        {
            $group = $admininfo->isGruop($userId);
            $menu = $admininfo->getMenuByGid($group['ugr_rel_id']);
        }

        return $menu;
    }
}
