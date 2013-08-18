<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class RoleModel extends BaseModel
{
    protected $table = 'oa_role';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    
    public function getRoleList($columns=null){
    	
        $select = $this->getSql()->select();
        
        if($columns===null){
        	$columns = array('id','name','description','left_number','right_number','level','parent_id','status');
        }
        
        $select->columns($columns);
        
        $select->order(array('left_number'=>'asc'));
        
        $roleList = $this->selectWith($select)->toArray();
        
        return $roleList;
        
    }//function getRoleList() end
    
    
	 /**
     * 根据角色的id得到角色的相关信息
     * 因为角色id是主键，
     * 如果传进来的参数是整数，那么返回一个一维数组
     * 如果传进来的参数是数组，那么返回一个二维数组
     * @param int $id | array $id
     * @return array
     */
    
    public function getRoleById($id,$columns=null){
    
    	$select = $this->getSql()->select();
    
    	if($columns===null){
    
    		$select->columns(array('id','name','level','left_number','right_number'));
    
    	}
    	else{
    		$select->columns($columns);
    	}
    
    	
    	$where = new where();
    
    	if(is_numeric($id)){
    
    		$where->equalTo('id', $id);
    		
    		$select->where($where);
    
    		$result = $this->selectWith($select)->toArray();
    		
    		$role = $result[0];
    		
    	}
    	else if(is_array($id)){
    
    		$where->in('id', $id);
    		
    		$select->where($where);
    
    		$role = $this->selectWith($select)->toArray();
    		
    	}
    	
    	return $role;
    	 
    }//function getRoleById() end
    
    
    
    /**
     * 在角色表中插入新的节点
     * @param array $role
     * @throws \Exception
     */
    public function insertRole($role){
    	 
    	if(!$this->insert($role)){
    		throw new \Exception('在角色中插入新节点失败');
    	}
    	 
    }//function insertArticleSort() end
    
    
    
    /**
     * 根据角色id返回该角色所有的子孙角色
     * @param int $id
     * @return array
     */
    public function getChildrenById($id){
    //根据角色id得到子孙分类的列表
    
    	//根据角色id得到文章分类信息
    	$select = $this->getSql()->select();
    	 
    	$where = new where();
    	 
    	$where->equalTo('id',$id);
    	 
    	$select->where($where);
    	 
    	$select->columns(array('id','left_number','right_number'));
    	 
    	$roleInfo = $this->selectWith($select)->toArray();
    	 
    	//根据父分类的名称得到子分类的列表
    	$parentRoleInfo  = array_pop($roleInfo);
    	 
    	$select = $this->getSql()->select();
    	 
    	$where  = new where();
    	 
    	$where->greaterThan('left_number', $parentRoleInfo['left_number']);
    	 
    	$where->lessThan('right_number', $parentRoleInfo['right_number']);
    	 
    	$select->where($where);
    	 
    	$select->columns(array('id','left_number','right_number'));
    	 
    	$roleChildren = $this->selectWith($select)->toArray();
    	 
    	return $roleChildren;
    	 
    }//function getArticleSortInfoByArticleSortId() end
    
    
    /**
     * @param int $id
     * @param array $data
     */
    public function updateRoleById($id,$data){
    	 
    	$update = $this->getSql()->update();
    	 
    	$where = new Where();
    	 
    	$where->equalTo('id', $id);
    	 
    	$update->where($where);
    	 
    	$update->set($data);
    	 
    	$this->updateWith($update);
    	 
    }//function updateRoleById() end
    
    
   
    	 
    
    
    
    
    

    
   
   

   
    
   

    
}