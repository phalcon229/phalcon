<?php
use Phalcon\Security;

class AdminLogic extends LogicBase
{
    public function __construct()
    {
        $this->obj = new AdminModel;
    }

    /**
     * 获取管理员列表
     * @return [type] [description]
     */
    public function getListByPage()
    {
        return $this->obj->getInfoLimit();
    }

    /**
     * 所有管理员组列表
     */
    public function groupLists()
    {
        return $this->obj->getList();
    }

    /**
     * 获取管理员列表
     * @return [type] [description]
     */
    public function isAdmin($uid)
    {
        return $this->obj->isAdmin($uid);
    }

    /**
     * 所有管理员组列表
     */
    public function isGruop($uid)
    {
        return $this->obj->isGruop($uid);
    }

    public function getMenu($action)
    {
        return $this->obj->getMenu($action);
    }

    public function getperm($super)
    {
        return $this->obj->getperm($super);
    }
    /**
     * 修改管理员组
     */
    public function edit($uid, $groupid)
    {
        return $this->obj->edit($uid, $groupid);
    }

    /**
     * 删除管理员
     */
    public function del($uid)
    {
        return $this->obj->del($uid);
    }

    /**
     * 修改管理员密码
     * @param  [type] $uId    [description]
     * @param  [type] $newPwd [description]
     * @return [type]         [description]
     */
    public function changePwd($uId, $newPwd)
    {
        return $this->obj->changePwd($uId, $newPwd);
    }

    /**
     * 添加管理员
     * @param  [type] $uId    [description]
     * @param  [type] $newPwd [description]
     * @return [type]         [description]
     */
    public function doadd($uname, $newPwd, $groupid)
    {
        return $this->obj->doadd($uname, $newPwd, $groupid);
    }

    /**
     * 管理员名字是否存在
     */
    public function getAdminByUser($uname)
    {
        return $this->obj->getAdminByUser($uname);
    }

    /**
     * 修改登录信息
     * @param  [type] $uId   [description]
     * @param  [type] $uName [description]
     * @param  [type] $uPass [description]
     * @return [type]        [description]
     */
    public function updateLoginInfo($adminId, $lastLoginTime)
    {
        $upData = array(
            'u_lastlogintime' => $lastLoginTime,
            'u_lastloginip' => Components\Utils\Helper::getIP()
        );
        return $this->obj->updateInfo($adminId, $upData);
    }

    public function logout()
    {
        $this->di['session']->destroy();
        $this->di['cookie']->get('auth')->delete();
        // $this->di['redis']->delete('user:' . $this->di['session']->get('uInfo')['u_id']);
    }

    /**
     * 根据菜单详情获取菜单
     * @param  [type] $siteId     [description]
     * @param  [type] $controller [description]
     * @param  [type] $action     [description]
     * @return [type]             [description]
     */
    public function getMenuByDetail($controller, $action)
    {
        return $this->obj->getInfoByDetail($controller, $action);
    }

    public function getPermByGidSid($groupId)
    {
        $groupId = intval($groupId);

        return $this->obj->getPermByGid($groupId);
    }

    /**
     * 根据管理员组id获取其所拥有的权限菜单
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public function getMenuByGid($groupId)
    {
        if (!$groupPerm = $this->getPermByGidSid($groupId))
            return [];

        $permConf = json_decode($groupPerm['perm_config'], true);
        $allMenus = $this->getMenus();

        foreach ($allMenus as $key => $menus)
        {
            if (!in_array($menus['m_id'], $permConf))
                unset($allMenus[$key]);

            if(!empty($allMenus[$key]))
            {
                foreach ($allMenus[$key]['sub'] as $skey => $value)
                {
                    if (!in_array($value['m_id'], $permConf))
                        unset($allMenus[$key]['sub'][$skey]);
                }
            }
        }

        return $allMenus;
    }

    /**
     * 获取所有站点菜单信息
     * @param  [type] $siteCfg [description]
     * @return [type]          [description]
     */
    public function getMenus()
    {
        $menus = [];
        $subMenu = [];
        $menus = $this->obj->getSubsByDetail(0);
        foreach ($menus as $key => $pMenu)
        {
            $subMenu = $this->obj->getSubsByDetail($pMenu['m_id']);
            $menus[$key]['sub'] = $subMenu;
        }

        return $menus;
    }

    public function match($str, $start, $end){
        $plen = strlen($str);
        if(!preg_match("/^(([a-z]+[0-9]+)|([0-9]+[a-z]+))[a-z0-9]*$/i",$str)||$plen < $start||$plen > $end)
            return false;

        return true;
    }
}