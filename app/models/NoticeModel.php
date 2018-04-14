<?php

class NoticeModel extends ModelBase
{

    protected $table = 'le_notice';

    public function __construct()
    {
        parent::__construct();
    }

    public function getNotice()
    {
        $this->query("SELECT n_content FROM {$this->table} WHERE n_is_display = 1 order by n_id desc");
        return $this->getAll();
    }
}