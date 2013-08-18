<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class DepartmentLeaderModel extends BaseModel {
	
	protected $table = 'oa_department_leader';
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	/**
	 * 用于获取用户是某部门的领导
	 */
	public function getLeadedDepartment($userId)
	{
		if (!is_integer($userId)){
			return false;
		}
    	$select = $this->getSql()->select();
    	$where = new Where();
    	$where->equalTo('user_id', $userId);
    	$select->where($where);
    	$result = $this->selectWith($select)->toArray();
    	if (is_null($result)){
    		return false;
    	}
    	$departmentIds = array();
    	foreach ($result as $key=>$value)
    	{
    		$departmentIds[$key] = $value['department_id'];
    	}
    	return $departmentIds;
	}
	

	public function saveDepartmentLeader($userIds, $departmentId ) {
		
		$userid = explode(",",$userIds);
		if (sizeof($userid) <1 ){
			throw new \Exception ( "数据传入错误" );
		}
		foreach ($userid as $key=>$id){
			if (! $this->insert ( array('department_id'=>$departmentId,'user_id'=>$id) )) {
				throw new \Exception ( "领导插入错误！" );
			}
		}
	}
	
	
	
	/**
	 * @param int $departmentId;
	 * 根据部门ID删除所有相关的记录，删除部门领导的信息
	 */
	public function deleteByDepartmentId($departmentId)
	{
		$delete = $this->getSql()->delete();
		
		$where = new where();
		
		if(is_numeric($departmentId)){
			$where->equalTo('department_id', $departmentId);
		}
		else{
			throw new \Exception ('传入的部门ID参数错误，请联系网站管理员');
		}
		
		$delete->where($where);
		
		$deleteState = $this->deleteWith($delete);
		
		if( $deleteState=== false){
			throw new \Exception ("部门领导记录删除失败!");
		}
		
	}//function deleteByDepartmentId();
	
	/**
	 * 通过部门ID查找领导信息
	 * @param unknown_type $departmentId
	 * @return boolean|unknown
	 */
	
	public function getLeaderByDepartmentId($departmentId)
	{
		if (!is_integer($departmentId)){
			return false;
		}
		$select = $this->getSql()->select();
		$where = new Where();
		$where->equalTo('department_id', $departmentId);
		$select->where($where);
		$result = $this->selectWith($select)->toArray();
		if (is_null($result)){
			return false;
		}
		
		return $result;
		
	}
	
	
}