<?php

class BannerModel extends ModelBase
{

    protected $table = 'le_index_banner';

    public function __construct()
    {
        parent::__construct();
    }

    public function getBannerInfo( $fields = '*')
      {
          $this->query('SELECT ' . $fields . ' FROM ' . $this->table . ' WHERE ib_status = 1 order by ib_created_time desc');
            
          return $this->getAll();
      }

}
