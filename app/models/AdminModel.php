<?php

class AdminModel extends ModelBase
{
    protected $table = 'adm_users';

    public function __construct()
    {
        parent::__construct();
    }

    public function getInfoLimit( $fields = '*')
    {
        $this->query('SELECT u.*,g.ugr_name, g.ugr_rel_id FROM `adm_users` as u LEFT JOIN `adm_ug_rel` as g ON u.u_id = g.u_id where u_status = 1 ORDER BY `u_id` DESC ');

        return $this->getAll();
    }

    public function getList( $fields = '*')
    {
        $this->table = 'adm_perm_groups';
        $this->query('SELECT '. $fields .' FROM '.$this->table.'  ORDER BY `pg_id` DESC ');

        return $this->getAll();
    }

    public function isAdmin( $uid)
    {
        $this->query('SELECT u_id, u_name FROM `adm_users` where u_status = 1 and u_id = ? limit 1 ', [$uid]);

        return $this->getRow();
    }

    public function isGruop($uid)
    {
        $this->query('SELECT u_id, ugr_rel_id FROM `adm_ug_rel` where u_id = ? limit 1 ', [$uid]);

        return $this->getRow();
    }

    public function getMenu($action)
    {
        $this->query('SELECT m_parent_id FROM `adm_menu` where m_controller = ? AND m_action = ?', [$action[0],$action[1]]);
        $data['index'] = $this->getRow();
        $this->query('SELECT m_parent_id FROM `adm_menu` where m_controller = ? AND m_action = ?', [$action[0],$action[2]]);
        $data['recharge'] = $this->getRow();
        return $data;
    }

    public function getperm($super)
    {
        $this->query('SELECT perm_config FROM `adm_permissions` where perm_id = ?', [$super]);

        return $this->getRow();
    }

    public function edit($uid, $groupid)
    {
        $name = $this->getGroupsById($groupid)['pg_name'];
        $this->table = 'adm_ug_rel';
        return $this->update(['ugr_rel_id' => $groupid, 'ugr_name' => $name], ['condition' => 'u_id = ?', 'values' => [$uid]]);
    }

    public function getGroupsById($groupid)
    {
        $this->table = 'adm_perm_groups';
        $this->query('SELECT pg_name FROM '.$this->table.' where pg_id = ? limit 1 ', [$groupid]);

        return $this->getRow();
    }

    public function del($uId)
    {

        return $this->update(['u_status' => 0], ['condition' => 'u_id = ?', 'values' => [$uId]]);
    }

    public function getAdminByUser($uname)
    {
        $this->query('SELECT * FROM '.$this->table.' WHERE `u_name` = ? and u_status = 1 LIMIT 1 ', [$uname]);

        return $this->getRow();
    }

    public function changePwd($uId, $newPwd)
    {
        return $this->update(['u_pass' => $newPwd], ['condition' => 'u_id = ?', 'values' => [$uId]]);
    }

    public function doadd($uname, $newPwd, $groupid)
    {
        $this->begin();
        // 1、插入管理员表
        $this->insert([
            'u_name'  => $uname,
            'u_pass' => $newPwd,
            'u_status' => 1,
            'u_addtime' => $_SERVER['REQUEST_TIME'],
            'u_super' => 0,
            'u_real_name' => '',
        ]);

        $uId = $this->lastInsertId();

        if(!$uId)
        {
            $this->rollback();
            throw new ModelException('Insert into ' . $this->table . ' failed. sql:' . $this->sql);
        }

        $name = $this->getGroupsById($groupid)['pg_name'];
        // 插入管理员组
        $this->table = 'adm_ug_rel';
        $this->insert([
            'u_id' => $uId,
            'ugr_rel_id' => $groupid,
            'ugr_name' => $name,
            'ugr_type' => 1,
        ]);
        $this->lastInsertId();

        $this->commit();
        return true;
    }

    public function updateInfo($adminId, $upData)
    {
        return $this->update($upData, ['condition' => 'u_id = ?', 'values' => [$adminId]]);
    }

    public function getInfoByDetail($controller, $action)
    {
        $this->table = 'adm_menu';
        $this->query('SELECT * FROM '.$this->table.' WHERE `m_controller` = ? AND `m_action` = ? ORDER BY m_id desc ', [$controller, $action]);

        return $this->getRow();
    }

    public function getPermByGid($groupid)
    {
        $this->table = 'adm_permissions';
        $this->query('SELECT * FROM adm_permissions WHERE `pg_id` = ? LIMIT 1', [$groupid]);

        return $this->getRow();
    }

    public function getSubsByDetail($parentId )
    {
        $this->query('SELECT * FROM adm_menu WHERE `m_parent_id` = ?  AND `m_dis` = 1 ORDER BY `m_id` asc', [$parentId ]);

        return $this->getAll();
    }
}
