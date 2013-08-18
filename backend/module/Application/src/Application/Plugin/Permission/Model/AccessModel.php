<?php

namespace Application\Plugin\Permission\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class AccessModel extends BaseModel
{
    protected $table = 'oa_access';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
   
    public function getNodeIdListByRoleId($roleId)
    {
    	$select = $this->getSql()->select();
    	
    	$where = new where();
    	
    	$where->in('role_id', $roleId);
    	
    	$select->where($where);
    	
    	$result = $this->selectWith($select)->toArray();
    	
    	$nodeIdList = array();
    	 
    	foreach($result as $value){
    		array_push($nodeIdList,$value['node_id']);
    	}
    	return $nodeIdList;
    }
}