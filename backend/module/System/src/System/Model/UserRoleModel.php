<?php

namespace System\Model;

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

    public function getRoleListByUserId($userId,$columns=null){
   		
    	
    	$select = $this->getSql()->select();
    	
    	if($columns===null){
    	
    	}
    	else{
    		$select->columns($columns);
    	}
    	
    	
    	$where = new where();
    	
    	if(is_numeric($userId)){
    	
    		$where->equalTo('user_id', $userId);
    	
    		$select->where($where);
    	
    		$result = $this->selectWith($select)->toArray();
    	
    		
    		return $result;
    		
    	
    	}else{
    		return false;
    	}
    	
    	
    	
    	
    }//function getRoleListByUserId() end
    

    /**
     * 删除用户角色
     * @param int $userId
     */
    public function deleteUserRole($userId)
    {
    	$this->delete(array('user_id' => $userId));
    }
    
    /**
     * 插入用户角色
     * @param array $userData
     */
    public function saveUserRole($data)
    {
    	$userId = $data["userId"];
    	$roleIdArr = $data["roleIds"];
    	if (sizeof($roleIdArr) < 1){
    		throw new \Exception("角色Id为空！");
    	}
    	foreach($roleIdArr as $roleId){
    		if (!$this->insert(array("user_id"=>$userId,"role_id"=>$roleId)))
    		{
    			throw new \Exception("角色插入错误！");
    		}
    
    	}
    
    	 
    }
}