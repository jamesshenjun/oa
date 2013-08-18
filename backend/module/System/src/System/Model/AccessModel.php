<?php

namespace System\Model;


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
    
    
    
    /**
     * 根据角色id删除在权限表的所有的权限
     * @param int $roleId|| array $roleId
     * @throws Exception
     */
    public function deleteByRoleId($roleId){
    	
    	$delete = $this->getSql()->delete();
    	
    	$where = new where();
    	
    	if(is_numeric($roleId)){
    		$where->equalTo('role_id',$roleId);
    	}
    	else if(is_array($roleId)){
    		$where->in('role_id',$roleId);
    	}
    	else{
    		throw new \Exception('角色id的参数类型不正确，请不要尝试非法操作');
    	}
    	
    	$delete->where($where);
    	
    	$deleteState = $this->deleteWith($delete);
    	
    	if($deleteState===null){
    		throw new \Exception('删除角色权限失败，请联系网站管理员');
    	}
    	
    }//function deleteByRoleId() end
    
    

    /**
     * 根据传进来的参数来来选择查询条件in 还是  equal
     * 但是返回结果都是一个二维数组,因为role_id不是主键
     * @param int $roleId || array $roleId
     * @return array
     */
    public function getNodeIdListByRoleId($roleId){
    	
    	$where = new Where();
    	
    	if(is_numeric($roleId)){
    		$where->equalTo('role_id', $roleId);
    	}
    	else if(is_array($roleId)){
    		$where->in('role_id', $roleId);
    	}
    	
    	$select = $this->getSql()->select();
    	
    	$select->where($where);
    	
    	$select->columns(array('node_id'));
    	
    	return $this->selectWith($select)->toArray();
    	
    }//function getNodeIdListByRoleId() end
    
    
    
}