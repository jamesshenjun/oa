<?php

namespace Help\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class UserModel extends BaseModel
{
    protected $table = 'oa_user';
    
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
    public function getRoleIdArrByUserId($userId)
    {
    	$select = $this->getSql()->select();
    	$select->where(array('user_id'=>$userId));
    	 
    	$result = $this->selectWith($select)->toArray();
    	$roleIdArr = array();
    	 
    	if($result){
    		foreach($result as $value){
    			$roleIdArr[] = $value['role_id'];
    		}
    	}
    	 
    	return $roleIdArr;
    }
}