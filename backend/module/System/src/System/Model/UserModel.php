<?php

namespace System\Model;

use Zend\Db\Sql\Expression;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Insert;


class UserModel extends BaseModel
{
    protected $table = 'oa_user';
    
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->initialize();
    }

    /**
     * 根据条件统计用户总数
     * @param array $param
     * @return int
     */
    public function countUser( $param = NULL )
    {
    	$select = $this->getSql()->select();

    	$where = new Where();
    	
    	//初始化查询条件


    	$where->equalTo('status', 'Y');
    	
    	if(!empty($param['userId'])){
    		$where->equalTo('id',$param['userId']);
    	}

    	if(!empty($param['nameStr'])){
    		$subWhere = new Where();
    		$subWhere->like('username', '%'.$param['nameStr'].'%');
    		$subWhere->or;
    		$subWhere->like('realname', '%'.$param['nameStr'].'%');
    		$where->addPredicate($subWhere);
    	}
    	
    	if(!empty($param['departmentId'])){
    		$where->in('department_id',$param['departmentId']);
    	}

    	$select->where($where);
    	
    	$count = $this->selectWith($select)->count();
    	
    	return $count;   	 	
    }
    


    /**
     * 根据条件得到用户列表
     * @param array $param
     * @return array
     */
    public function getUserList($param = NULL, $columns = NULL)
    {
    	
    	$select = $this->getSql()->select();	
    	
    	if ($columns != null){
    		$select->columns($columns);
    	}
    	$where = new Where();
    	
    	//初始化查询条件


    	$where->equalTo('status', 'Y');
    	
    	if(!empty($param['userId'])){
    		$where->equalTo('id',$param['userId']);
    	}

    	if(!empty($param['nameStr'])){
    		$subWhere = new Where();
    		$subWhere->like('username', '%'.$param['nameStr'].'%');
    		$subWhere->or;
    		$subWhere->like('realname', '%'.$param['nameStr'].'%');
    		$where->addPredicate($subWhere);
    	}
    	
    	if(!empty($param['departmentId'])){
    		$where->in('department_id',$param['departmentId']);
    	}
    	$select->where($where);
    	
    	
    	if(!empty($param['offset'])){
    		$select = $select->offset($param['offset']);
    	}
    	 
    	if(!empty($param['numPerPage'])){
    		$select = $select->limit($param['numPerPage']);
    	}
    		
    	$select->order('add_time DESC');
    	$result = $this->selectWith($select)->toArray();
    	
    	return $result;
    }


    /**
     * 保存用户
     * @param array $userData
     */
    public function userAdd($userData)
    {
    	if (sizeof($userData)<1){
    		throw new \Exception("没有数据！");
    	}
    	
		if (!$this->insert($userData)){
			throw new \Exception("用户插入错误！");
		}
    }

    /**
     * 更新用户
     * @param array $userData
     */
    public function updateUser($userData,$where)
    {
//     	print_r($where);
//     	print_r($userData);
    	
    	if (sizeof($userData)<1){
    		throw new \Exception("没有数据！");
    	}
    	
    	if ($this->update($userData,$where) === false){
    		throw new \Exception("更新用户错误！");
    	}
    	
    }

    /**
     * 删除用户
     * @param array $userData
     */
    public function deleteUser($userId)
    {
    	if(!is_numeric($userId)){
    		throw new \Exception("没有数据！");
    	}
    	
    	if ($this->update(array("status"=>'D'),array("id"=>$userId)) === false){
    		throw new \Exception("更新用户错误！");
    	}
    }
    
    /**
     * 根据用户ID返回用户信息
     * @param int $id
     * @return array
     */
    public function getUserByUserId($userId)
    {
    	if(!is_integer($userId)){
    		return false;
    	}
    	$select = $this->getSql()->select();
    	$select->where(array("id = ".$userId));
    	$select->where(array('status'=>"Y"));
    	
    	$result = $this->selectWith($select)->toArray();
    	return $result?$result[0]:null;
    }
    
    
    /**
     * 根据用户IDArray返回 id为键值的二维数组
     */
    public function getUserListByIds($IdAarray)
    {
    	if (sizeof($IdAarray)<1){
    		return false;
    	}
    	$select = $this->getSql()->select();
    	$select->where(array("id"=>$IdAarray));
    	
    	$result = $this->selectWith($select)->toArray();
    	
    	$UserList = array();
    	 
    	if($result){
    		foreach($result as $key=>$value){
    			$UserList[$value["id"]]["name"] = $value["realname"];
    		}
    	}
    	return $UserList;
    }
    
    /**
     * 根据部门ids 返回 所有人员
     * @param unknown_type $departmentIds
     * @return unknown
     */
    
    
    public function getUsersBydepartmentIds($departmentIds)
    {
    	if (sizeof($departmentIds)<1){
    		return false;
    	}
    	$select = $this->getSql()->select();
    	$where = new Where();
    	foreach ($departmentIds as $departmentId)
    	{
    		$where->equalTo('department_id', $departmentId);
    		$where->or;
    	}
    	$select->where($where);
    	$select->columns(array('id'));
    	$result = $this->selectWith($select)->toArray();
    	
    	return $result;
    }
    
//     /**
//      * 返回用户所有的下级人员  （包括自己）
//      */
//     public function getSubordinateByUserId($userId)
//     {
//     	$select = $this->getSql()->select();
//     	$select->where(array("id = ".$userId));
//     	$result = $this->selectWith($select)->toArray();
    	
//     	if($result == null)
//     	{
//     		return false;
//     	}
//     	$userInfo = $result[0];
    	
//     	$select = $this->getSql()->select();
//     	$select->columns(array('id'));
    	
    	
// //     	print_r($userInfo);exit;
    	 
//     	//根据左右值来判断
//     	$select->where(array('left_number >= '.$userInfo['left_number']));
//     	$select->where(array('left_number < '.$userInfo['right_number']));
//     	$result = $this->selectWith($select)->toArray();

//     	foreach ($result as $key=>$value){
//     		$ids[$key] = $value['id'];
//     	}
    	 
//     	return $ids;
//     }

    
//     /**
//      * 
//      * @param unknown_type $LeftNumber
//      * @param unknown_type $Type
//      * @return number
//      */
//     public function userLeftNumberUpdateByLeftNumber($LeftNumber,$Type)
//     {
    	
    	
//     	$AddLeftNumber = new Expression();
    	
//     	if($Type=='add'){
    	
//     		$AddLeftNumber->setExpression('left_number+2');
    		
//     	}
//     	else if($Type=='delete'){
    	
//     		$AddLeftNumber->setExpression('left_number-2');
//     	}
    	 
//     	$result = $this->update(array('left_number'=>$AddLeftNumber),'left_number >='.$LeftNumber);
    	 
//     	return $result;
    	
    	
//     }
    
//     public function userRightNumberUpdateByLeftNumber($LeftNumber,$Type)
//     {
//     	$AddRightNumber = new Expression();
    	 
//     	if($Type=='add'){
    		 
//     		$AddRightNumber->setExpression('right_number+2');
    	
//     	}
//     	else if($Type=='delete'){
    		 
//     		$AddRightNumber->setExpression('right_number-2');
//     	}
    	
//     	$result = $this->update(array('right_number'=>$AddRightNumber),'right_number >='.$LeftNumber);
    	
//     	return $result;
    	
//     }
    
    
}