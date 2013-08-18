<?php

namespace System\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class DepartmentModel extends BaseModel {
	protected $table = 'oa_department';
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	
	/**
	 * 根据一个部门的id得到一个部门之下的所有节点的部分(包括节点本身)
	 * 筛选的条件为：
	 * 一个节点的下面的所有子节点，通过左右值的范围来判断
	 * 
	 *
	 * @param int $departmentId        	
	 * @return array $LeafDepartmentList or false
	 */
	public function getLeafDepartmentList($departmentId, $columns = null) {
		if (! is_integer ( $departmentId )) {
			// 如果传捡来的是整型的话
			return false;
		}
		
		// 第一步：查询自身节点的信息
		$select = $this->getSql ()->select ();
		// 得到select对象
		
		$select->columns ( array (
				'id',
				'left_number',
				'right_number' 
		) );
		
		$select->where ( array (
				'id' => $departmentId 
		) );
		
		$result = $this->selectWith ( $select )->toArray ();
		
		if (sizeof ( $result ) == 0) {
			// 如果查询不到数据库的话
			return false;
		}
		
		$ancestorInfo = $result [0];
		// 得到祖先节点的信息
		
		// 第二步：查询叶子节点的信息
		$select = $this->getSql ()->select ();
		// 得到select对象
		
		if ($columns !== null) {
			// 如果设置了字段,那么就以设置字段为准，否则选取所有的字段
			$select->columns ( $columns );
		}
		
// 		$selectExpression = new Expression ();
		
// 		$selectExpression->setExpression ( "`right_number`-1" );
		
		$where = new Where ();
		
		$where->equalTo ( 'status', 'Y' );
		// 叶子节点的状态必须为启用状态
		
// 		$where->equalTo ( 'left_number', $selectExpression );
		// 左值等于右值减去1
		
		$where->greaterThanOrEqualTo ( 'left_number', $ancestorInfo ['left_number'] );
		// 叶子节点的左值大于祖先节点的左值
		
		$where->lessThanOrEqualTo ( 'right_number', $ancestorInfo ['right_number'] );
		// 叶子节点的右值小于祖先节点的右值
		
		$select->where ( $where );
		// 把查询条件添加到select对象中
		
		$LeafDepartmentList = $this->selectWith ( $select )->toArray ();
		
		if (sizeof ( $result ) == 0) {
			return false;
		}
		
		return $LeafDepartmentList;
	} // function getLeafDepartmentList
	
	/**
	 * 根据级别取得部门列表
	 *
	 * @param int $level        	
	 * @return array
	 */
	public function getAllDepartment($level = NULL) {
		$select = $this->getSql ()->select ();
		if ($level) {
			$select->where ( array (
					'level' => $level 
			) );
		}
		$select->where(array('status'=>'Y'));
		$select->order ( "id ASC" );
		
		$result = $this->selectWith ( $select )->toArray ();
		return $result;
	}
	
	/**
	 * 根据部门ID返回以部门ID为键值的二维数组
	 *
	 * @param int|array $departmentId        	
	 * @return array
	 */
	public function getDepartmentListByDepartmentId($departmentId = NULL) {
		$select = $this->getSql ()->select ();
		if ($departmentId) {
			$select->where ( array (
					"id" => $departmentId 
			) );
		}
		$select->order ( "id ASC" );
		$result = $this->selectWith ( $select )->toArray ();
		$departmentArr = array ();
		
		if ($result) {
			foreach ( $result as $key => $value ) {
				$departmentArr [$value ["id"]] = $value;
			}
		}
		return $departmentArr;
	}
	
	/**
	 * 返回格式化的部门列表，主要用于select和部门列表展示
	 *
	 * @return unknown
	 */
	public function getDepartmentStructure() {
		$select = $this->getSql ()->select ();
		// 新建select对象
		$select->where(array('status'=>'Y'));
		$select->order ( "left_number ASC" );
		// 根据左值进行排序，可以达到上下级的关系
		
		$result = $this->selectWith ( $select )->toArray ();
		// 把返回结果组成数组
		
		return $result;
	}
	/**
	 * 得到下级部门
	 *
	 * @param unknown_type $department        	
	 * @return unknown
	 */
	public function getLowerLevelDepartment($department) {
		if (! is_array ( $department )) {
			$select = $this->getSql ()->select ();
			
			$select->where ( array (
					'id' => $department 
			) );
			
			$result = $this->selectWith ( $select )->toArray ();
			
			$department = $result [0];
		}
		
		$select = $this->getSql ()->select ();
		$select->where(array('status'=>'Y'));
		$select->where ( array (
				'left_number >' . $department ['left_number'] 
		) );
		$select->where ( array (
				'left_number < ' . $department ['right_number'] 
		) );
		$result = $this->selectWith ( $select )->toArray ();
		return $result;
	}
	/**
	 * 返回该部门的二级部门
	 *
	 * @param unknown_type $departmentId        	
	 * @return unknown
	 */
	public function getDepartmentLevelTwo($departmentId) {
		
		$select = $this->getSql ()->select ();
		
		$select->where ( array (
				"id" => $departmentId 
		) );
		$result = $this->selectWith ( $select )->toArray ();
		$department = $result [0];
		while ( $department ['level'] > 2 ) {
			$select = $this->getSql ()->select ();
			$select->where(array('status'=>'Y'));
			$select->where ( array (
					'left_number < ' . $department ['left_number'] 
			) );
			$select->where ( array (
					'right_number > ' . $department ['right_number'] 
			) );
			$select->order ( 'left_number DESC' );
			$result = $this->selectWith ( $select )->toArray ();
			$department = $result [0];
		}
		return $department;
	}
	
	/**
	 * 保存部门
	 * @param unknown_type $departmentData
	 * @throws \Exception
	 */
	public function saveDepartment($departmentData) {
		if (sizeof ( $departmentData ) < 1) {
			throw new \Exception ( "没有数据！" );
		}
		
		if (! $this->insert ( $departmentData )) {
			throw new \Exception ( "部门插入错误！" );
		}
	}
	
	public function updateDepartment($departmentData, $where) {
		if (sizeof ( $departmentData ) < 1) {
			throw new \Exception ( "没有数据！" );
		}
		if (sizeof($where) <1 ){
			throw new \Exception ( "没有条件！！" );
		}
		
		if (($this->update ( $departmentData, $where )) === false) {
			throw new \Exception ( "更新部门错误！" );
		}
	}
	
	public function deleteDepartment($DepartmentId) {
		if (!is_integer ( $DepartmentId )) {
			throw new \Exception ( "传入的部门ID异常" );
		}
		
		if (! $this->update ( array (
				'status' => 'D',
				'left_number'=>'0',
				'right_number'=>'0'
		), array (
				'id' => $DepartmentId 
		) )) {
			throw new \Exception ( "删除部门错误！" );
		}
	}
	

}