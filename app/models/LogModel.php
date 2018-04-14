<?php

class LogModel extends ModelBase
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getInfoLimit($index, $perRows, $uName, $roleId, $startTime, $endTime)
    {
        $where = "WHERE 1 = 1";
        $match = array();
        if ($uName)
        {
            $where .= ' AND `al_u_name` LIKE "%' . $uName . '%"';
        }
        if ($roleId)
        {
            $where .= ' AND `al_pg_id` = :al_pg_id';
            $match['al_pg_id'] = $roleId;
        }
        if ($startTime)
        {
            $where .= ' AND `al_logtime` >= :startTime';
            $match['startTime'] = $startTime;
        }
        if ($endTime)
        {
            $where .= ' AND `al_logtime` <= :endTime';
            $match['endTime'] = $endTime;
        }

        $sql = 'SELECT * FROM `adm_action_log` ' . $where . ' ORDER BY `al_id` DESC LIMIT ' . $index . ', ' . $perRows;

        $res = $this->db->query($sql, $match);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        return $res->fetchAll();
    }

    public function addInfo($adminId, $uName, $roleId, $controller, $action, $logip, $content)
    {
        $sql = 'INSERT INTO `adm_action_log` (`al_adm_id`, `al_u_name`, `al_pg_id`, `al_controller`, `al_action`, `al_content`, `al_logtime`, `al_logip`) VALUES
                (:al_adm_id, :al_u_name, :al_pg_id, :al_controller, :al_action, :al_content, :al_logtime, :al_logip)';
        $res = $this->db->execute( $sql,array (
            'al_adm_id'  => $adminId,
            'al_u_name'  => $uName,
            'al_pg_id' => $roleId,
            'al_controller' => $controller ,
            'al_action' => $action,
            'al_content' => $content,
            'al_logtime' => time(),
            'al_logip' => $logip,
        ));

        return $this->db->lastInsertId();
    }

    public function getTotalNum($uName, $roleId, $startTime, $endTime)
    {
        $where = "WHERE 1 = 1";
        $match = array();
        if ($uName)
        {
            $where .= ' AND `al_u_name` LIKE "%' . $uName . '%"';
        }
        if ($roleId)
        {
            $where .= ' AND `al_pg_id` = :al_pg_id';
            $match['al_pg_id'] = $roleId;
        }
        if ($startTime)
        {
            $where .= ' AND `al_logtime` >= :startTime';
            $match['startTime'] = $startTime;
        }
        if ($endTime)
        {
            $where .= ' AND `al_logtime` <= :endTime';
            $match['endTime'] = $endTime;
        }

        $sql = 'SELECT COUNT(al_id) AS total  FROM `adm_action_log` ' . $where;

        $res = $this->db->query($sql, $match);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        return $res->fetch();
    }

    public function delInfo($logIds)
    {
        $sql = 'DELETE FROM adm_action_log WHERE `al_id` in (' . $logIds . ')';
        $res = $this->db->execute($sql);

        return $this->db->affectedRows();
    }

}