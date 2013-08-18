<?php

namespace Help\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class MenuModel extends BaseModel
{
    protected $table = 'oa_menu';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    
}