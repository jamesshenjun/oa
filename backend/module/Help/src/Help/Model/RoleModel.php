<?php

namespace Help\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;

class RoleModel extends BaseModel
{
    protected $table = 'oa_role';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    public function getRoleByRoleId($roleId)
    {
    	$select = $this->getSql()->select();
    	$select->where(array("id"=>$roleId));
    	
    	$result = $this->selectWith($select)->toArray();  	
    	if($result){
    		$result = is_array($roleId)?$result:$result[0];
    	}
    	
    	return $result;    	
    }
}