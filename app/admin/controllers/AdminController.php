<?php
use Phalcon\Security;

class AdminController extends ControllerBase
{

    public function initialize()
    {
        $this->admin = new AdminLogic;
    }

    public function indexAction()
    {
        $admList = $this->admin->getListByPage();
        $groupList = $this->admin->groupLists();

        $this->di['view']->setVars([
            'list' => $admList,
            'glist' => $groupList,
        ]);
    }

    /**
     * 修改管理员组
     * @return [type] [description]
     */
    public function doeditAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));
        $groupid = intval($this->request->getPost('groupid'));

        //管理员无法修改本身
        if($uid == $this->di['session']->get('uInfo')['u_id']){
            return $this->di['helper']->resRet('管理员无法修改自己', 500);
        }
        //判断管理员是否存在
        if(!$this->admin->isAdmin($uid)){
            return $this->di['helper']->resRet('管理员不存在', 500);
        }

        //判断管理组是否存在
        if(!$this->admin->isGruop($uid)){
            return $this->di['helper']->resRet('管理员不在管理员组中', 500);
        }

        //超级管理员不添加
        if($groupid == 1)
            return $this->di['helper']->resRet('添加失败，只允许一名超级管理员', 500);

        //修改管理员所在组
        if(!$this->admin->edit($uid, $groupid)){
            return $this->di['helper']->resRet('管理员组未发生改变,请重新选择', 500);
        }

        $this->logContent = '修改管理员id:'. $uid .' 为权限组id:'.$groupid;
        return $this->di['helper']->resRet();
    }

    /**
     * 删除管理员
     * @return [type] [description]
     */
    public function delAction()
    {
        $this->view->disable();
        $uid = intval($this->request->getPost('uid'));

        //判断管理员是否存在
        if(!$this->admin->isAdmin($uid)){
            return $this->di['helper']->resRet('管理员不存在', 500);
        }

        //管理员无法删除自己
        if($uid == $this->di['session']->get('uInfo')['u_id']){
            return $this->di['helper']->resRet('管理员无法删除自己', 500);
        }
        //删除管理员
        if(!$this->admin->del($uid)){
            return $this->di['helper']->resRet('删除管理员失败', 500);
        }

        $this->logContent = '删除管理员id:'. $uid ;
        return $this->di['helper']->resRet();
    }

    public function detailAction()
    {
        $uid = intval($this->request->getQuery('uid'));
        //user is or not exist
        if(!$user = $this->admin->isAdmin($uid)){
            return $this->showMsg('管理员不存在', true, '/admin');
        }

        $this->di['view']->setVars([
            'user' => $user,
        ]);
    }

    public function doPassAction()
    {
        $this->view->disable();
        if (!$pwd = trim($this->request->getPost('pwd')))
            return $this->di['helper']->resRet('请输入新密码', 500);

        if (!$newPwd = trim($this->request->getPost('new_pwd')))
            return $this->di['helper']->resRet('请输入确认新密码', 500);

        if(strcmp($pwd,$newPwd) != 0){
            return $this->di['helper']->resRet('两次输入的密码不一致', 500);
        }

        if (!$this->admin->match($pwd, 8, 20))
            return $this->di['helper']->resRet('密码必须为8-20位的数字和字母的组合', 500);

        $uid = intval($this->request->getPost('uid'));
        if(!$this->admin->isAdmin($uid)){
            return $this->di['helper']->resRet('管理员不存在', 500);
        }

        // 执行修改
        if (!$this->admin->changePwd($uid, $this->security->hash($pwd)))
            return $this->di['helper']->resRet('密码修改失败', 500);

        $this->logContent = '修改密码管理员id:'. $uid ;

        //是否本身,退出登录界面
        if($uid == $this->di['session']->get('uInfo')['u_id']){
            return $this->di['helper']->resRet(['url' => '/index/logout']);
        }

        return $this->di['helper']->resRet('密码修改成功');
    }

    /**
     *添加管理员界面
     * @return [type] [description]
     */
    public function applyAction()
    {
        $groupList = $this->admin->groupLists();

        $this->di['view']->setVars([
            'glist' => $groupList,

        ]);
    }

    /**
     *添加管理员
     * @return [type] [description]
     */
    public function doaddAction()
    {
        $this->view->disable();

        if (!$pwd = trim($this->request->getPost('pwd')))
            return $this->di['helper']->resRet('请输入新密码', 500);

        if (!$newPwd = trim($this->request->getPost('new_pwd')))
            return $this->di['helper']->resRet('请输入确认新密码', 500);

        if(strcmp($pwd,$newPwd) != 0){
            return $this->di['helper']->resRet('两次输入的密码不一致', 500);
        }

        if (!$this->admin->match($pwd, 8, 20))
            return $this->di['helper']->resRet('密码必须为8-20位的数字和字母的组合', 500);

        $username = trim($this->request->getPost('u_name'));

        if (!preg_match('/^[_0-9a-zA-Z]{3,15}$/i',$username))
            return $this->di['helper']->resRet('帐号必须为3-15位的数字或字母', 500);

        //超级管理员不添加
        if(intval($this->request->getPost('group')) == 1)
            return $this->di['helper']->resRet('添加失败，只允许一名超级管理员', 500);

        // 检测管理员是否已存在
        if ($this->admin->getAdminByUser($username))
            return $this->di['helper']->resRet('添加失败，该管理员已存在', 500);

        //添加管理员并添加管理员组
        if (!$this->admin->doadd($username, $this->security->hash($pwd), intval($this->request->getPost('group'))))
            return $this->di['helper']->resRet('添加管理员失败', 500);

        $this->logContent = '添加管理员:'. $username ;
        return $this->di['helper']->resRet('添加管理员成功');
    }
}