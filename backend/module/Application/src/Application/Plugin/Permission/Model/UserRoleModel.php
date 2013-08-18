<?php

namespace Application\Plugin\Permission\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class UserRoleModel extends BaseModel
{
    protected $table = 'oa_user_role';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }


    /**
     * 根据用户ID得到该用户的角色ID数组，可能存在多角色
     * @param int $userId
     * @return array
     */
    public function getRoleIdListByUserId($userId)
    {
    	$select = $this->getSql()->select();
    	
    	$where = new where();
    	
    	$where->equalTo('user_id', $userId);
    	
    	$select->where($where);
    	
    	$result = $this->selectWith($select)->toArray();
    	
    	$roleIdList = array();
    	 
    	foreach($result as $value){
    		array_push($roleIdList,$value['role_id']);
    	}
    	return $roleIdList;
    }
}