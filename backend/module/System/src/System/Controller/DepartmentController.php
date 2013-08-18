<?php

namespace System\Controller;

use Zend\Validator\Explode;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Application\Factory\ServiceLocatorFactory;
use Application\Filter\DepartmentFilter;
// 加载对于部门表专门的过滤验证器
use Application\Filter\DepartmentLeaderFilter;
// 加载用于部门领导表的过滤验证器
use System\Form\DepartmentForm;
use System\Model\ReportTemplateInfoModel;

class DepartmentController extends BaseController {
	protected $userRoleModel;
	protected $departmentModel;
	protected $userModel;
	protected $roleModel;
	protected $reportTemplateInfoModel;
	protected $departmentLeaderModel;
	
	function __construct() {
		$serviceManager = ServiceLocatorFactory::getInstance ();
		
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'RoleModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserRoleModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'ReportTemplateInfoModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentLeaderModel' );
	}
	
	/**
	 * 显示部门树状列表
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showDepartmentListAction() {
		
		$departmentList = $this->departmentModel->getDepartmentStructure ();
		
		$departmentList = $this->FormatdepartmentList ( $departmentList );
		
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'departmentList', $departmentList );
		
		return $viewModel;
	}
	
	/**
	 * 添加部门
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showDepartmentAddAction() {
		$DepartmentForm = new DepartmentForm ();
		// 使用快速生成的表单来生成部门添加的所使用的表单
		
		// 第一部分：用来生成周报下拉列表的内容
		$tempWeeklyTemplateInfoList = $this->reportTemplateInfoModel->getWeeklyTemplateInfoList ();
		
		$weeklyTemplateInfoList = array (
				'0' => '请选择' 
		);
		
		foreach ( $tempWeeklyTemplateInfoList as $TemplateInfo ) {
			$weeklyTemplateInfoList [$TemplateInfo ['id']] = $TemplateInfo ['name'];
		}
		$DepartmentForm->get ( 'weekly_report_template_id' )->setValueOptions ( $weeklyTemplateInfoList );
		
		// 第二部分：用来生成日报下拉列表的内容
		$tempDailyTemplateInfoList = $this->reportTemplateInfoModel->getDailyTemplateInfoList ();
		
		$dailyTemplateInfoList = array (
				'0' => '请选择' 
		);
		
		foreach ( $tempDailyTemplateInfoList as $TemplateInfo ) {
			$dailyTemplateInfoList [$TemplateInfo ['id']] = $TemplateInfo ['name'];
		}
		$DepartmentForm->get ( 'daily_report_template_id' )->setValueOptions ( $dailyTemplateInfoList );
		
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'DepartmentForm', $DepartmentForm );
		
		return $viewModel;
	}
	
	/**
	 * 显示部门编辑的页面
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showDepartmentEditAction() {
		
		$request = $this->getRequest ();
		
		if (!$request->isXmlHttpRequest ()) {
			$this->returnMessage('300', '传入ID异常');
		}
			
		$id = $request->getQuery('id');
		
		//第一部分：查询部门的相关信息
		$department = $this->departmentModel->getRowById($id);
		
		
		//第二部分：找到上级部门的名称，用于显示
		if ($department['parent_id'] > 0 ){
			$parentDepartment 	  = $this->departmentModel->getRowById($department['parent_id']);
		}
		
		
		//第三部分：找到领导的信息，先找到id，再找到名称，用','连接
		$departmentLeader = $this->departmentLeaderModel->getLeaderByDepartmentId($department['id']);
		
		$LeaderIdArray   	 = array();
		
		$LeaderRealnameArray = array();
		
		foreach ($departmentLeader as $key=>$leader){
			$user = $this->userModel->getRowById($leader['user_id']);
			if (sizeof($user) < 1){
				continue;
			}
			array_push($LeaderIdArray		, $user['id']);
			array_push($LeaderRealnameArray , $user['realname']);
		}
		
		
		$LeaderIdString   	  = implode(',', $LeaderIdArray);
		$LeaderRealnameString = implode(',', $LeaderRealnameArray);
		
		
		$departmentForm = new DepartmentForm ();
		
		
		// 第四部分：用来生成周报下拉列表的内容
		$tempWeeklyTemplateInfoList = $this->reportTemplateInfoModel->getWeeklyTemplateInfoList ();
		
		$weeklyTemplateInfoList = array ( '0' => '请选择' );
										   
		foreach ( $tempWeeklyTemplateInfoList as $TemplateInfo ) {
			$weeklyTemplateInfoList [$TemplateInfo ['id']] = $TemplateInfo ['name'];
		}
		$departmentForm->get ( 'weekly_report_template_id' )->setValueOptions ( $weeklyTemplateInfoList );
		
		// 第五部分：用来生成日报下拉列表的内容
		$tempDailyTemplateInfoList = $this->reportTemplateInfoModel->getDailyTemplateInfoList ();
		
		$dailyTemplateInfoList = array (	'0' => '请选择' );
				
		foreach ( $tempDailyTemplateInfoList as $TemplateInfo ) {
			$dailyTemplateInfoList [$TemplateInfo ['id']] = $TemplateInfo ['name'];
		}
		$departmentForm->get ( 'daily_report_template_id' )->setValueOptions ( $dailyTemplateInfoList );
		
		
		//第六部分：设置数据
		$departmentForm->get('id')->setValue($id);
		
		if($department['parent_id']>0){
			$departmentForm->get('org.parent_id')->setValue($parentDepartment['id']);
			$departmentForm->get('org.parent_name')->setValue($parentDepartment['name']);
		}else{
			$departmentForm->remove('org.parent_id');
			$departmentForm->remove('org.parent_name');
		}		
		$departmentForm->get('leader.user_id')->setValue($LeaderIdString);
		$departmentForm->get('leader.user_name')->setValue($LeaderRealnameString);
		$departmentForm->get('report_type')->setValue($department['report_type']);
		$departmentForm->get('daily_report_template_id')->setValue($department['daily_report_template_id']);
		$departmentForm->get('weekly_report_template_id')->setValue($department['weekly_report_template_id']);
		$departmentForm->get('name')->setValue($department['name']);
		$departmentForm->get('description')->setValue($department['description']);
		
		$viewModel = new ViewModel ();
		
		$viewModel->setVariable ( 'departmentForm', $departmentForm );
		
		
		
		return $viewModel;
		
	}//function showDepartmentEditAction() end
	
	/**
	 * 后台中用来检测部门添加的函数
	 */
	public function checkDepartmentAddAction() {
		$request = $this->getRequest ();
		
		if (!$request->isXmlHttpRequest ()) {
			$this->returnMessage ( '300', '传入数据异常' );
		}
			
		$postData = $request->getPost ();
		

		// 添加客户提交数据
		
		//删除查找带回带回中返回的键值问题
		$postData['parent_id'] = $postData ['org_parent_id'];
		unset ( $postData ['org_parent_id'] );
		$postData['user_id']   = $postData ['leader_user_id'];
		unset ( $postData ['leader_user_id'] );
		
		//开始部门信息的过滤问题
		$departmentFilter = new DepartmentFilter ();
		
		$departmentFilter = $departmentFilter->getInputFilter ();
		
		$departmentFilter->setValidationGroup(array(	'name',
														'description',
														'parent_id',
														'report_type',
														'daily_report_template_id',
														'weekly_report_template_id'
													)
										     );
		
		$departmentFilter->setData ( $postData );
		
		if (!$departmentFilter->isValid ()) {
			
			$dataError = $departmentFilter->getMessages ();
			foreach ( $dataError as $key => $error ) {
				$this->returnMessage ( '300', array_pop ( $error ) );
			}
		} 
		
		$departmentData = $departmentFilter->getValues ();
		
		
		//开始部门领导信息的过滤问题
		$departmentLeaderFilter = new DepartmentLeaderFilter ();
		$departmentLeaderFilter = $departmentLeaderFilter->getInputFilter ();
		$departmentLeaderFilter->setData ( $postData );
		
		if (!$departmentLeaderFilter->isValid ()) {
			
			$dataError = $departmentLeaderFilter->getMessages ();
				
			foreach ( $dataError as $key => $error ) {
			
				$this->returnMessage ( '300', array_pop ( $error ) );
			
			}
		} 
		
		$departmentLeaderData = $departmentLeaderFilter->getValues ();
		//验证所有数据，验证完了开始对数据库操作
		
		// 此处开启事物
		$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();
		try{
			
			// 1.得到父节点信息
			$ParentDepartmentData = $this->departmentModel->getRowById($departmentData['parent_id']);
			
			// 2.得到新节点的左右值 和级别
			
			$departmentData['left_number']  = $ParentDepartmentData ['right_number'] ;
			
			$departmentData['right_number'] = $departmentData['left_number'] + 1;
			
			$departmentData['level'] = $ParentDepartmentData['level'] + 1;
			// 得到新节点的右值
			
			// 3. 更新所有的相关的节点的左右值
			$this->departmentModel->updateLeftNumberAndRightNumber($departmentData['left_number'], 'insert' );

			// 4.插入新节点的数据
			$this->departmentModel->saveDepartment($departmentData);
			
			// 部门表插入完毕后 开始对部门领导表进行操作
			$departmentId = $this->departmentModel->lastInsertValue;
			
			
			$leaderUserIdList = explode(',',$departmentLeaderData['user_id']);
				
			$departmentLeaderArray = array();
				
			foreach($leaderUserIdList as $user_id){
			
				$relationship =  array('department_id'=>$departmentId,'user_id'=>$user_id);
			
				array_push($departmentLeaderArray,$relationship);
			}
				
			$this->departmentLeaderModel->insertAll($departmentLeaderArray);
		
		}catch (\Exception $e )
		{
			$dbConnection->rollback ();
			$this->returnMessage ( 300, $e->getMessage () );
		}
		
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '添加部门成功' );
			
	}

	
	
	/**
	 * 后台中用来检测部门编辑的函数
	 */
	public function checkDepartmentEditAction() {
		
		
		$request = $this->getRequest ();
	
		if (!$request->isXmlHttpRequest ()) {
			$this->returnMessage ( '300', '传入数据异常' );
		}
				
		$postData = $request->getPost ();
		// 添加客户提交数据
		
		//删除查找带回带回中返回的键值问题
		if(isset($postData ['org_parent_id'])){
			$postData['parent_id'] = $postData ['org_parent_id'];
			unset ( $postData ['org_parent_id'] );
		}
		else{
			$postData['parent_id'] = 0;
		}
		$postData['user_id']   = $postData ['leader_user_id'];
		unset ( $postData ['leader_user_id'] );
		

		//开始过滤的验证
		$departmentFilter = new DepartmentFilter ();
		$departmentFilter = $departmentFilter->getInputFilter ();
		$departmentFilter->setData ( $postData );
			
		if (!$departmentFilter->isValid ()) {
			
			$dataError = $departmentFilter->getMessages ();
			foreach ( $dataError as $key => $error ) {
				$this->returnMessage ( '300', array_pop ( $error ) );
			}
		} 
		
		$departmentPostData = $departmentFilter->getValues ();
		//从表单接受到的信息
		
// 		print_r($departmentPostData);exit;
		
		$departmentLeaderFilter = new DepartmentLeaderFilter ();
		$departmentLeaderFilter = $departmentLeaderFilter->getInputFilter ();
		$departmentFilter->setValidationGroup(
												array(	'name',
														'description',
														'parent_id',
														'report_type',
														'daily_report_template_id',
														'weekly_report_template_id'
												)
											 );
		
		$departmentLeaderFilter->setData ( $postData );
			
		if (!$departmentLeaderFilter->isValid ()) {
			
			$dataError = $departmentLeaderFilter->getMessages ();
			foreach ( $dataError as $key => $error ) {
				$this->returnMessage ( '300', array_pop ( $error ) );
			}
		} 
		
		$departmentLeaderPostData = $departmentLeaderFilter->getValues ();
		
		$departmentDatabaseData = $this->departmentModel->getRowById($departmentPostData['id'],array('parent_id'));
		//从数据库中查询到信息
		
		//验证所有数据，验证完了开始对数据库操作
		$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();
		
		try{
			
			//第一步：查询父分类的信息
			$parentDepartmentData = $this->departmentModel->getRowById($departmentPostData['parent_id']);
			if (isset($parentDepartmentData['level'])){
				
				$departmentPostData['level'] = $parentDepartmentData['level'] + 1;
				
				
				
			}else{
				$departmentPostData['level'] = 1;
			}
			//第二步：把收上来的数据更新到数据库
			$this->departmentModel->updateRowById($departmentPostData['id'],$departmentPostData);
				
			//第三步：更新文章分类的左右值，就是移动分类
			if($departmentPostData['parent_id']!=0 && $departmentDatabaseData['parent_id']!=$departmentPostData['parent_id']){ 
				

				//如果parent_id为0，那么就是根节点，那么就不要移动
				$this->departmentModel->rebuildStructureTree(1,1);
			}
			
			//第四步：删除该部门领导的相关数据
			$this->departmentLeaderModel->deleteByDepartmentId($departmentPostData['id']);
			
			//第五步：添加部门与部门领导之间的关系数据
			$leaderUserIdList = explode(',',$departmentLeaderPostData['user_id']); 
			
			$departmentLeaderArray = array();
			
			foreach($leaderUserIdList as $user_id){
				
				$relationship =  array('department_id'=>$departmentPostData['id'],'user_id'=>$user_id);
				
				array_push($departmentLeaderArray,$relationship);
			}
			
			$this->departmentLeaderModel->insertAll($departmentLeaderArray);
			
		}
		catch ( \Exception $e ){
		
			$dbConnection->rollback ();
			$this->returnMessage ( 300, $e->getMessage () );
		}
		
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '修改部门成功' );
				
	}	
	
	
	/**
	 * 显示一个部门中的成员
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showDepartmentMemberAction() {
		$viewModel = new ViewModel ();
		
		return $viewModel;
	}
	
	/**
	 * 用于删除部门的后台回调函数
	 */
	public function checkDepartmentDeleteAction() {
		
		$request = $this->getRequest ();
		
		if (!$request->isXmlHttpRequest ()) {
			$this->returnMessage ( '300', '传入数据异常' );
		}
			
		$id = $request->getQuery('id');
		
		$departmentInfo = $this->departmentModel->getRowById($id);
		
		//开启事物
		$dbConnection = $this->getServiceLocator()->get ( 'Zend\Db\Adapter\Connection' );
		$dbConnection->beginTransaction ();
		try{
			//1. 检查本节点是否是叶子节点，如果不是则拒绝修改
			if(($departmentInfo['right_number'] - $departmentInfo['left_number']) != 1)
			{
				throw new \Exception ( "有其他的部门是该部门的下级，如果要修改，请先修改下级部门" );
			}
			
			//更新受影响节点的左右值
			$this->departmentModel->updateLeftNumberAndRightNumber($departmentInfo['left_number'],'delete');
				
			// 清空删除
			$this->departmentModel->deleteDepartment($departmentInfo['id']);
			
		}
		catch ( \Exception $e ){
			
			$dbConnection->rollback ();
			$this->returnMessage ( 300, $e->getMessage () );
			
		}
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '删除部门成功' );
		
	}//function 
	

	
	/**
	 * 用于Ajax返回部门列表的函数
	 */
	public function setParentOrgAction() {
		
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
		if(isset($queryData['id'])){
			
			$departmentFilter = new DepartmentFilter();
			$departmentFilter = $departmentFilter->getInputFilter();
			$departmentFilter->setData($queryData);
				
			$departmentFilter->setValidationGroup(array('id'));
				
			if(!$departmentFilter->isValid()){
					
				$errorMessages = $departmentFilter->getMessages();
					
				foreach($errorMessages as $errorMessage){
					$this->returnMessage('300', array_pop($errorMessage));
				}
					
			}
			
			$departmentChildren = $this->departmentModel->getLowerLevelDepartment($queryData['id']);
			$departmentChildrenIdList = array ();
			
			
			foreach ($departmentChildren as $children){
				array_push($departmentChildrenIdList, $children['id']);
			}
			array_push($departmentChildrenIdList,$queryData['id']);
		}else{
			
			$departmentChildrenIdList = array ();
		}
		$viewModel = new ViewModel();
		$departmentList = $this->departmentModel->getDepartmentStructure ();
		$departmentList = $this->FormatDepartmentList ( $departmentList );
		$viewModel->setVariable ( 'departmentList', $departmentList );
		$viewModel->setVariable ( 'departmentChildrenIdList', $departmentChildrenIdList );
		
		return $viewModel;
	}
	
	
	
	private function FormatDepartmentList($departmentList) {
		
		foreach ( $departmentList as $key => $department ) {
			
			if ($department ['status'] == 'Y') {
				
				$departmentList [$key] ['status'] = '已启用';
				
			} 
			else if ($department ['status'] == 'N') {
				
				$departmentList [$key] ['status'] = '已禁用';
			}
			$departmentList [$key] ['margin_left'] = 20*$department['level'];
			$departmentList [$key] ['children_count'] = ($department ['right_number'] - $department ['left_number'] - 1) / 2;
		} // foreach end
		
		return $departmentList;
	} // function FormatDepartmentList() end
	  
}//class DepartmentController end
