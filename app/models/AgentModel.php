<?php

class AgentModel extends ModelBase
{

    protected $table = 'le_agent';

    public function __construct()
    {
        parent::__construct();
    }

    public function getNumByUserId($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id = ?', [$uId]);

        return $this->getAll();
    }

    public function getAgentTableInfo($uId, $fields = '*')
    {
        $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE u_id=? or a_parents = ?', [$uId,$uId]);

        return $this->getAll();
    }

}
