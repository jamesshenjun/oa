<?php

namespace System\Controller;


use Application\Filter\UserFilter;
use Application\Filter\RoleUserFilter;
use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use System\Form\UserForm;
use Application\Factory\ServiceLocatorFactory;



class UserController extends BaseController {
	
	protected $userModel;
	protected $roleModel;
	protected $userRoleModel;
	protected $departmentModel;
	
	function __construct() {
		$serviceManager = ServiceLocatorFactory::getInstance ();
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'RoleModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserRoleModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'UserModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'DepartmentModel' );
	}
	
	
	/**
	 * 显示用户列表
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showUserListAction() {
		

		
		// 用户列表查询、翻页动作时，提交的数据
		$postData = $this->getPostDataOfUserList ();
		
		
		$viewModel = new ViewModel ();
		
		$Action = $this->getRequest ()->getQuery('Action');
		
		if ($Action == 'selectLeader'){
			$postData['numPerPage'] = 200;
			$viewModel->setTemplate('system/user/set-leader-user');
		}
		
		// 查询数据
		$queryData = $postData;
		
		// 初始化查询栏部门列表
		$departmentModel = $this->departmentModel;
		$departmentList = $departmentModel->getDepartmentStructure();
		$departmentList = $this->FormatDepartment ( $departmentList );
		
		//对部门进行处理，找到部门下所有子部门
		if (!empty($queryData['departmentId'])){
			$departmentId = $queryData['departmentId'];
			$departmentIds = $departmentModel->getLeafDepartmentList($departmentId, array('id'));
			foreach ($departmentIds as $key=>$value){
				$temp[$key] = $value['id'];
			}
			$departmentIds= $temp;
			$queryData['departmentId'] = $departmentIds;
		}
		
		$userModel = $this->userModel;


		
		
		// 统计用户总数
		$totalCount =  $userModel->countUser($queryData);
		$pageNumShow = ceil ( $totalCount / $postData ['numPerPage'] );
		
		// 用户列表数据
		$queryData ['offset'] = ($postData ['pageNum'] - 1) * $postData ['numPerPage'];
		
		$userList = $userModel->getUserList ( $queryData );
		$userList = $this->processUserList ( $userList );
		
		
		
		
		// 模版变量
		$tplVar = $postData;
		$tplVar ["departmentList"] = $departmentList;
		$tplVar ["totalCount"] = $totalCount;
		$tplVar ["pageNumShow"] = $pageNumShow;
		$tplVar ["userList"] = $userList;
		
		$viewModel->setVariables ( $tplVar );


		return $viewModel;
	}
	
	/**
	 * 取得用户列表在翻页、查询动作时提交的数据
	 *
	 * @return array
	 */
	public function getPostDataOfUserList() {
		$numPerPage = 20; // 每页显示多少条记录
		$pageNum = 1; // 当前在第几页
		
		$userId = 0;
		$nameStr = '';
		$departmentId = 0;
		
		$request = $this->getRequest ();
		
		if ($request->isXmlHttpRequest()) {
			$post = $request->getPost ();
			
			if (! empty ( $post->numPerPage )) {
				$numPerPage = ( int ) $post->numPerPage;
			}
			
			if (! empty ( $post->pageNum )) {
				$pageNum = ( int ) $post->pageNum;
			}
			
			if (! empty ( $post->userId )) {
				$userId = ( int ) $post->userId;
			}
			
			if (! empty ( $post->nameStr )) {
				$nameStr = trim ( $post->nameStr );
			}
			
			if (! empty ( $post->departmentId )) {
				$departmentId = ( int ) $post->departmentId;
			}
		}
		
		$postData = array (
				'numPerPage' => $numPerPage,
				'pageNum' => $pageNum,
				'userId' => $userId,
				'nameStr' => $nameStr,
				'departmentId' => $departmentId 
		);
		
		return $postData;
	}
	
	/**
	 * 对用户列表的数据进行处理，主要与部门表的关联，得到部门名称
	 *
	 * @param unknown_type $userList        	
	 * @return string
	 */
	public function processUserList($userList) {
		if ($userList) {
			// 取得部门ID
			$departmentIdArr = array ();
			foreach ( $userList as $key => $value ) {
				$departmentIdArr [] = $value ["department_id"];
			}
			
			// 取得部门列表
			$departmentList = array ();
			if ($departmentIdArr) {
				// 去重复值
				$departmentIdArr = array_unique ( $departmentIdArr );
				
				// 取得部门名称
				$departmentModel = $this->departmentModel;
				$departmentList = $departmentModel->getDepartmentListByDepartmentId ( $departmentIdArr );
			}
			
			foreach ( $userList as $key => $value ) {
				// 关联部门名称
				if (array_key_exists ( $value ["department_id"], $departmentList )) {
					$userList [$key] ["department_name"] = $departmentList [$value ["department_id"]] ["name"];
				} else {
					$userList [$key] ["department_name"] = "-";
				}
				unset ( $userList [$key] ["department_id"] );
			}
		}
		
		return $userList;
	}
	
	/**
	 * 显示添加用户界面
	 */
	public function showUserAddAction() {
		// 部门列表
		$departmentModel = $this->departmentModel;
		$departmentList = $departmentModel->getDepartmentStructure ();
		$department = $this->FormatDepartment ( $departmentList );
		
		$userForm = new UserForm ();
		$userForm->get ( 'department_id' )->setValueOptions ( $department );
		
		$roleModel = $this->roleModel;
		$RoleList = $roleModel->getRoleList ();
		$RoleList = $this->FormatRoleList ( $RoleList );
		$userForm->get ( 'roleIds' )->setValueOptions ( $RoleList );
		
		$viewModel = new ViewModel ();
		$viewModel->setVariable ( 'userform', $userForm );
		
		return $viewModel;
	}
	private function FormatRoleList($RoleList) {
		$temp = array ();
		foreach ( $RoleList as $role ) {
			$temp [$role ['id']] = $role ['name'];
		}
		$RoleList = $temp;
		return $RoleList;
	}
	
	/**
	 * 添加用户监测
	 */
	public function checkUserAddAction() {
		
		$request = $this->getRequest ();
		
		if ($request->isPost ()) {
			// 添加客户提交数据
			$post = $request->getPost ();
			
			// 检测添加客户时提交的数据
			$dbAdapter = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Adapter' );
			$useFilter = new UserFilter ( $dbAdapter );
			$useinputFilter = $useFilter->getInputFilter ();
			$useinputFilter->setData ( $post );
			
			if ($useinputFilter->isValid ()) {
				$userData = $useinputFilter->getValues ();
				$userData = $this->FormatUserData ( $userData );
			} else {
				$dataError = $useinputFilter->getMessages ();
				foreach ( $dataError as $key => $error ) {
					$this->returnMessage ( '300', array_pop ( $error ) );
				}
			}
			
			$roleUserFilter = new RoleUserFilter ();
			$roleUserinputFilter = $roleUserFilter->getInputFilter ();
			$roleUserinputFilter->setData ( $post );
			$roleUserinputFilter->setValidationGroup ( 'roleIds' );
			
			if ($roleUserinputFilter->isValid ()) {
				$roleUserData = $roleUserinputFilter->getValues ();
			} else {
				$dataError = $roleUserinputFilter->getMessages ();
				foreach ( $dataError as $key => $error ) {
					$this->returnMessage ( '300', array_pop ( $error ) );
				}
			}
			
			// 事务操作
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
			
			try {
				// 添加用户
				$userModel = $this->userModel;
				$userModel->userAdd ( $userData );
				
				// 最后插入的ID
				$userId = $userModel->LastInsertValue;
				// 添加用户角色
				$userRoleModel = $this->userRoleModel;
				$roleUserData ['userId'] = $userId;
				$userRoleModel->saveUserRole ( $roleUserData );
			} catch ( \Exception $e ) {
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( 200, "添加用户成功！" );
		}
		
		$this->returnMessage ( 300, "添加用户失败！" );
	}
	private function FormatUserData($userData) {
		$data = array ();
		if ($userData ['password'] != $userData ['confirmPassword']) {
			$this->returnMessage ( '300', '两次输入的密码不一致' );
		}
		$data ['username'] = $userData ['username'];
		$data ['realname'] = $userData ['realname'];
		$data ['password'] = md5($userData ['password']);
		$data ['department_id'] = $userData ['department_id'];
		$data ['id_card_number'] = $userData ['id_card_number'];
		$data ['address'] = $userData ['address'];
		$data ['telephone'] = $userData ['telephone'];
		$data ['email'] = $userData ['email'];
		$data ["status"] = 'Y';
		$data ["add_time"] = date ( "Y-m-d H:i:s" );
		return $data;
	}
	
	/**
	 * 编辑用户界面
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showUserEditAction() {
		$request = $this->getRequest ();
		
		if ($request->isGet ()) {
			// 用户ID
			$userId = ( int ) $request->getQuery ( 'id' );
			$userModel = $this->userModel;
			$user = $userModel->getUserByUserId ( $userId );
			if ($user) {
				$viewModel = new ViewModel ();
				
				// 部门列表
				$departmentModel = $this->departmentModel;
				$departmentList = $departmentModel->getDepartmentStructure ();
				$department = $this->FormatDepartment ( $departmentList );
				
				$roleModel = $this->roleModel;
				$roleList = $roleModel->getRoleList ();
				$roleList = $this->FormatRoleList ( $roleList );
				
				$userEditForm = new UserForm ();
				
				$userEditForm->get ( 'userId' )->setValue ( $userId );
				$userEditForm->get ( 'username' )->setValue ( $user ['username'] );
				$userEditForm->get ( 'username' )->setAttribute ( 'readonly', 'readonly' );
				$userEditForm->get ( 'realname' )->setValue ( $user ['realname'] );
				$userEditForm->get ( 'password' )->setAttribute ( 'class', 'textInput' );
				$userEditForm->get ( 'confirmPassword' )->setAttribute ( 'class', 'textInput' );
				$userEditForm->get ( 'department_id' )->setValueOptions ( $department );
				$userEditForm->get ( 'department_id' )->setValue ( $user ['department_id'] );
				$userEditForm->get ( 'id_card_number' )->setValue ( $user ['id_card_number'] );
				$userEditForm->get ( 'address' )->setValue ( $user ['address'] );
				$userEditForm->get ( 'telephone' )->setValue ( $user ['telephone'] );
				$userEditForm->get ( 'email' )->setValue ( $user ['email'] );
				
				// 取得用户的角色信息
				$userRoleModel = $this->userRoleModel;
				
				$userRoleList = $userRoleModel->getRoleListByUserId ( $userId ,array('role_id'));
				$roleIdArr = array();
				foreach ($userRoleList as $key=>$userRole){
					$roleIdArr[$key] = $userRole['role_id'];
				}
				
				
				$userEditForm->get ( 'roleIds' )->setValueOptions ( $roleList );
				$userEditForm->get ( 'roleIds' )->setValue ( $roleIdArr );
			} else {
				$this->returnMessage ( 300, "用户不存在或已被删除！" );
			}
			$viewModel->setVariable ( 'userform', $userEditForm );
			return $viewModel;
		}
		
		$this->returnMessage ( 300, "用户不存在或已被删除！" );
	}
	public function checkUserEditAction() {
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			// 取得表单数据
			$post = $request->getPost ();
			
			// 检测添加客户时提交的数据
			
			$useFilter = new UserFilter ();
			$useinputFilter = $useFilter->getInputFilter ();
			$useinputFilter->setData ( $post );
			$useinputFilter->remove ( 'username' );
			$useinputFilter->get ( 'userId' )->setAllowEmpty ( false );
			$useinputFilter->get ( 'password' )->setAllowEmpty ( true );
			$useinputFilter->get ( 'confirmPassword' )->setAllowEmpty ( true );
			if ($useinputFilter->isValid ()) {
				$userData = $useinputFilter->getValues ();
				$userId = $userData['userId'];
				$userData = $this->FormatEdituserData ( $userData );
			} else {
				$dataError = $useinputFilter->getMessages ();
				foreach ( $dataError as $key => $error ) {
					$this->returnMessage ( '300', array_pop ( $error ) );
				}
			}
			$roleUserFilter = new RoleUserFilter ();
			$roleUserinputFilter = $roleUserFilter->getInputFilter ();
			$roleUserinputFilter->setData ( $post );
			$roleUserinputFilter->setValidationGroup ( 'roleIds', 'userId' );
			if ($roleUserinputFilter->isValid ()) {
				$roleUserData = $roleUserinputFilter->getValues ();
			} else {
				$dataError = $roleUserinputFilter->getMessages ();
				foreach ( $dataError as $key => $error ) {
					$this->returnMessage ( '300', array_pop ( $error ) );
				}
			}
			
			// 事务操作
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
			
			try {
				// 更新用户信息
				$userModel = $this->userModel;
				
				$userModel->updateUser ( $userData , array (
						"id" => $userId, 
				) );
				// 删除用户原有角色
				$userRoleModel = $this->userRoleModel;
				$userRoleModel->deleteUserRole ( $userId );
				
				// 保存新角色
				$userRoleModel->saveUserRole ( $roleUserData );
				
			} catch ( \Exception $e ) {
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			
			$dbConnection->commit ();
			$this->returnMessage ( 200, "编辑用户成功！" );
		} else {
			$this->returnMessage ( 300, "编辑用户失败！" );
		}
	}
	private function FormatEdituserData($userData) {
		if ($userData ['password'] != $userData ['confirmPassword']) {
			$this->returnMessage ( '300', '两次输入的密码不一致' );
		}
		$data = array ();
		$data ['realname'] = $userData ['realname'];
		if (!empty($userData ['password'])){
			$data ['password'] = md5($userData ['password']);
		}
		$data ['department_id'] = $userData ['department_id'];
		$data ['id_card_number'] = $userData ['id_card_number'];
		$data ['address'] = $userData ['address'];
		$data ['telephone'] = $userData ['telephone'];
		$data ['email'] = $userData ['email'];
		$data ["status"] = 'Y';
		$data ["update_time"] = date ( "Y-m-d H:i:s" );
		
		
		return $data;
	}
	
	/**
	 * 删除用户
	 */
	public function checkUserDeleteAction() {
		$request = $this->getRequest ();
		
		if (!$request->isPost ()) {
			$this->returnMessage ( 300, "数据传入错误，请勿非法操作" );
		}
		if (property_exists ( $request->getQuery (), 'id' )) {
			// 用户ID
			$userId = $request->getQuery ()->id;
			
			
			$user = $this->userModel->getRowById ( $userId );
			
			
			if (! $user) {
				$this->returnMessage ( 300, "用户不存在或已被删除！" );
			}
			// 事务操作
			$dbConnection = $this->getServiceLocator ()->get ( 'Zend\Db\Adapter\Connection' );
			$dbConnection->beginTransaction ();
				
			try {
				// 删除用户
				$this->userModel->deleteUser ( $userId );
				
				// 删除用户角色
				$userRoleModel = $this->userRoleModel;
				$userRoleModel->deleteUserRole ( $userId );
			} catch ( \Exception $e ) {
				$dbConnection->rollback ();
				$this->returnMessage ( 300, $e->getMessage () );
			}
			$dbConnection->commit ();
			$this->returnMessage ( 200, "删除用户成功！" );

		
		}
		
		$this->returnMessage ( 300, "删除失败！" );
	}
	
	public function AjaxGetDepartmentComplexChildrenListAction() {
		$DepartmentId = $_POST ['id'];
		
		$DepartmentModel = $this->departmentModel;
		
		$DepartmentChildrenList = $DepartmentModel->getLowerLevelDepartment ( $DepartmentId );
		
		if (sizeof ( $DepartmentChildrenList ) > 0) {
			
			$this->returnMessage ( '200', $DepartmentChildrenList );
		} else {
			$this->returnMessage ( '300', '没有子部门' );
		}
	}
	
	
	private function FormatDepartment($departments) {
		
		$departmentArray = array (
				
				'0' => '请选择'
				
		);
		foreach ( $departments as $value ) {
			$department = '';
			while (--$value['level']){
				$department .= "　　";
			}
			if ($value['left_number'] != $value['right_number'] - 1 ){
				$department .= '+'.$value ['name'];
			}else{
				$department .= '--'.$value ['name'];
			}
				
			$departmentArray [$value ['id']] = $department;
		}
		return $departmentArray;
	}
}
