<?php

namespace Report\Model;

use Application\Model\BaseModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Where;

class ReportInfoModel extends BaseModel {
	
	protected $table = 'oa_report_info';
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
		$this->initialize ();
	}
	
	/**
	 * 统计报告数
	 */
	public function countReport($param, $idsAllowed, $rid) {
		if ( sizeof($idsAllowed) < 1){
			return null;
		}
		//如果允许列表里面一个值都没有，那么不允许查回任何值
		
		
		$select = $this->getSql ()->select ();
		$where = new Where ();
		
		// 初始化查询条件
		if (! empty ( $param ['type'] )) {
			$where->equalTo ( 'type', $param ['type'] );
		}

		$where->equalTo ( 'status', "Y" );
		
		// 数据权限控制
		$where->in ( 'add_user_id', $idsAllowed );
		
		
		// 关键字检索
		
		$where2 = new Where();
		
		if (sizeof($rid) > 0) {
			$where2->in ( 'id', $rid );
			$where->andPredicate($where2);
		}
		
		if (! empty ($param ['keywords'])) {
			$where2->or;
			$where2->like ( 'title', '%' . $param ['keywords'] . '%' );
			$where->andPredicate($where2);
		}
		
		
		
		$select->where ( $where );
		$count = $this->selectWith ( $select )->count ();
		
		return $count;
	}
	
	/**
	 * 获取报告信息列表
	 */
	public function getReportList($param, $idsAllowed, $rid) {
		
		if ( sizeof($idsAllowed) < 1){
			return null;
		}
		//如果允许列表里面一个值都没有，那么不允许查回任何值
		
		$select = $this->getSql ()->select ();
		$where = new Where ();
		
		// 初始化查询条件
		if (! empty ( $param ['type'] )) {
			$where->equalTo ( 'type', $param ['type'] );
		}

		$where->equalTo ( 'status', "Y" );
		
		// 数据权限控制
		$where->in ( 'add_user_id', $idsAllowed );
		
		
		// 关键字检索
		
		$where2 = new Where();
		
		if (sizeof($rid) > 0) {
			$where2->in ( 'id', $rid );
			$where->andPredicate($where2);
		}
		
		if (! empty ($param ['keywords'])) {
			$where2->or;
			$where2->like ( 'title', '%' . $param ['keywords'] . '%' );
			$where->andPredicate($where2);
		}
		
		
		
		$select->where ( $where );
		
		if (! empty ( $param ['offset'] )) {
			$select = $select->offset ( $param ['offset'] );
		}
		
		if (! empty ( $param ['numPerPage'] )) {
			$select = $select->limit ( $param ['numPerPage'] );
		}
		
		
		$select->order ( 'add_time DESC' );
		$result = $this->selectWith ( $select )->toArray ();
		
		return $result;
	}
	
	/**
	 * 根据id返回报告信息
	 * 
	 * @param unknown_type $id        	
	 * @return unknown
	 */
	public function getReprotInfo($id) {
		
		if (!is_numeric($id)){
			return false;
		}
		$select = $this->getSql ()->select ();
		$where = new Where ();
		// 初始化查询条件
		$where->equalTo ( 'id', $id );
		$where->equalTo ( 'status', "Y" );
		$select->where ( $where );
		
		$ReportInfo = $this->selectWith ( $select )->toArray ();
		$ReportInfo = $ReportInfo[0];
		
		return $ReportInfo;
		
		
	}
	
	/**
	 * 保存报告信息
	 * 
	 * @param array $ReportData        	
	 */
	public function saveReportInfo($ReportData) {
		if (sizeof ( $ReportData ) < 1) {
			throw new \Exception ( "没有数据！" );
		}
		if (! $this->insert ( $ReportData )) {
			throw new \Exception ( "保存报告信息出错！" );
		}
	}
	
	/**
	 * 更新报告
	 * 
	 * @param array $userData        	
	 */
	public function updateReportInfo($ReportData, $where) {
		if (sizeof ( $ReportData ) < 1) {
			throw new \Exception ( "没有数据！" );
		}
		if (! $this->update ( $ReportData, $where )) {
			throw new \Exception ( "保存报告信息出错！" );
		}
	}
	
	/**
	 * 删除模板
	 * 
	 * @param int $tid        	
	 */
	public function deleteReporatInfo($rid) {
		return $this->update ( array (
				"status"=>"D"),array("id"=>$rid));
    }
    
    
}