<?php

namespace System\Controller;

use Zend\Authentication\Storage\Chain;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;


use System\Form\RoleForm;
use System\Form\RolePermissionForm;


use Application\Factory\ServiceLocatorFactory;

use Application\Filter\RoleFilter;

use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Element\Checkbox;
use Zend\Db\Sql\Where;

class RoleController extends BaseController
{
	protected $userModel;
	protected $roleModel;
	protected $nodeModel;
	protected $accessModel;
	
	
	function __construct(){
		
		$serviceManager = ServiceLocatorFactory::getInstance ();
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'RoleModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'NodeModel' );
		$this->getDbModel ( $serviceManager, 'System', 'Model', 'AccessModel' );
		
	}//function __construct() end
	
	public function rebuildAction(){
		
		$this->nodeModel->rebuildStructureTree(1,1);
		
	}
	
	public function initAction(){
    
		//首先删除网站管理员的所有权限
		$this->accessModel->delete(array('role_id'=>1));
		
		//然后查询出节点表中所有的节点
		$nodeList = $this->nodeModel->getNodeById();
		
	    //最后循环插入权限表
	    foreach($nodeList as $node){
	    	
	    	$array = array('role_id'=>1,'node_id'=>$node['id']);
	    	
	    	$this->accessModel->insert($array);
	    }
		
	    die('插入成功');
		
	}//function initAdminAction() end
	
	
	
	public function showRolePermissionEditAction(){
		
		$request = $this->getRequest();
		
		$queryData = $request->getQuery();
		
		$id = $queryData['id'];
		//得到角色id
		
		//第一步：根据角色id，查询角色的相关信息
		$roleColumns = array('id','left_number','right_number','parent_id','level');
		$role = $this->roleModel->getRoleById($id,$roleColumns);
		
	    if(!$role){
	    	$this->returnMessage(300,'没有查询到任何相关角色，请联系网站管理员！');
	    }
	    
	    //第二步：取得权限列表的容器
	    if($role['parent_id']==0&&$role['level']==1){
	    //如果parent_id等于0并且level等于1，那么判断为网站管理员	
	    //权限列表的容器就是全部的可以显示出来的节点
	        $nodeColumns = array('node_id'=>'id');
	    	$nodeList = $this->nodeModel->getNodeById(null,$nodeColumns);
	    }
	    else{
        //如果不是网站管理员的话，就查询父节点所拥有的权限来形成容器
	    	$nodeList = $this->accessModel->getNodeIdListByRoleId($role['parent_id']);
	    }
	    
	   	$nodeIdList = array();
	    
	    foreach($nodeList as $node){
	    	array_push($nodeIdList,$node['node_id']);
	    }
	    //取得这个角色所能拥有的权限最大集的节点列表
	    
	    if(sizeof($nodeIdList)==0){
	    	$this->returnMessage('300','该角色父角色没有任何权限,请先为父角色设置权限！');
	    }
	    
	    
	    //第三步：取得权限最大集的节点的相关信息，节点名称、左右值等等
	    $container = array();
	    //声明一个数组，用来存储节点的相关信息
	    
	    $nodeColumns = array('id','title','left_number','right_number','description','level','status');
	    //选择节点字段
	    
	    $nodeWhere = new Where();
	    $nodeWhere->equalTo('master_id',0);
	    //只选择主方法，过滤辅助方法
	    
	    $container = $this->nodeModel->getNodeById($nodeIdList,$nodeColumns,$nodeWhere);
	    
	    $container = $this->formatNodeList($container);
	    
	    //第四步:取得该角色所拥有的全部权限，用来设置选中状态
	    $permission = $this->accessModel->getNodeIdListByRoleId($role['id']);
	    
	    $permissionNodeIdList = array();
	    
	    foreach($permission as $permissionNode){
	    	array_push($permissionNodeIdList,$permissionNode['node_id']);
	    }
	    
	    
	    //第五步：循环权限容器，然后装载checkbox对象，然后根据角色所拥有的节点来设置状态
	    foreach($container as $key=>$node){
	    	
	    	$checkbox = new Checkbox();
	    	$checkbox->setName('node_id_list[]');
	    	
	    	$checkbox->setCheckedValue($node['id']);
	    	
	    	if(in_array($node['id'],$permissionNodeIdList)){
	    		$checkbox->setAttribute('checked','checked');
	    		
	    	}
	    	
	    	$checkbox->setUseHiddenElement(false);
	    	$container[$key]['checkbox'] = $checkbox;
	    	
	    }
	    
	    $viewModel = new ViewModel();
		
		$viewModel->setVariable('container',$container);
		
		$rolePermissionForm = new RolePermissionForm();
		//新建关于角色权限编辑的form
		
		$rolePermissionForm->get('id')->setValue($role['id']);
		
		$viewModel->setVariable('rolePermissionForm', $rolePermissionForm);
		
		return $viewModel;
		
	}//function showRolePermissionEditAction() end
	
	
	public function checkRolePermissionEditAction(){
		
		$request  = $this->getRequest();
		
		$postData = $request->getPost();
		
		$id = $postData['id'];
		
		$nodeIdList = $postData['node_id_list'];
		
		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
	    //开启数据事务的操作，用来保证数据的一致性
	    
		$dbConnection->beginTransaction();
		
		try{
			
			//第一步：声明一个数组用来装载权限
			$accessData = array();
			
			//第二步：删除该角色在权限表的所有权限
		    $this->accessModel->deleteByRoleId($id);	
			
		    //第三步：向权限表中添加所拥有的权限
		    foreach($nodeIdList as $nodeId){
		    	$element = array('role_id'=>$id,'node_id'=>$nodeId);
		    	array_push($accessData,$element);
		    }
		    
		   	//第四步：查询辅助方法id的列表
		    $assistantNodeList = $this->nodeModel->getAssistantNodeList($nodeIdList);
		    
		    foreach($assistantNodeList as $node){
		    	$element = array('role_id'=>$id,'node_id'=>$node['id']);
		    	array_push($accessData,$element);
		    }
		    
		    //第五步：向权限表中添加主方法和辅助方法的id列表
		    $this->accessModel->insertAll($accessData);
		    
		    
		    //第六步：得到该角色的所有子孙角色节点id的列表,限定权限的裁切范围
		    $descendantRoleList = $this->roleModel->getDescendantById($id,array('id'),false);
		    
		    $descendantRoleIdList = array();
		    
		    foreach($descendantRoleList as $role){
		    	array_push($descendantRoleIdList,$role['id']);
		    }
		    
		    if(sizeof($descendantRoleIdList)>0){
		    	
		    	
		    //第七步：裁切子孙节点的权限
		    	$accessNodeId = array();
		    	
		    	foreach($accessData as $element){
		    		array_push($accessNodeId,$element['node_id']);
		    	}
		    	
		    	$where = new where();
		    	
		    	$where->in('role_id',$descendantRoleIdList);
		    	//只裁切该节点的子孙节点的权限
		    	
		    	$where->notIn('node_id',$accessNodeId);
		    	//把子孙角色中不属于该角色权限集合的节点都删除掉
		    	
		    	$this->accessModel->delete($where);
		    	
		    }
		    
		   
		    
		}
		catch(\Exception $e){
		
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
		
		}
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，编辑角色成功' );
		
		
	}//function checkRolePermissionEditAction() end
	
	
	
	
	/**
	 * 显示角色列表
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showRoleListAction()
	{
		
		$roleList = $this->roleModel->getRoleList();
		
		$roleList = $this->formatRoleList($roleList);
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('roleList', $roleList);
		
		return $viewModel;
		
	}//function showRoleListAction() end
	
	/**
	 * 显示父级角色列表，用在角色添加和角色编辑页面
	 * @return \Zend\View\Model\ViewModel
	 */
	
	public function showParentRoleListAction(){
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
		if(isset($queryData['id'])){
				
			$roleFilterObject = new RoleFilter();
				
			$roleFilter = $roleFilterObject->getInputFilter();
				
			$roleFilter->setData($queryData);
				
			$roleFilter->setValidationGroup(array('id'));
				
			if(!$roleFilter->isValid()){
					
				$errorMessages = $roleFilter->getMessages();
					
				foreach($errorMessages as $errorMessage){
					$this->returnMessage('300', array_pop($errorMessage));
				}
					
			}
		
		}//if end
		
		$roleId = $queryData['id'];
		//根据这个接受的角色id查询子孙分类的列表
		
		$roleChildren = $this->roleModel->getChildrenById($roleId);
		
		$roleChildrenChildrenIdList = array();
		
		foreach($roleChildren as $child){
			array_push($roleChildrenChildrenIdList,$child['id']);
		}
		
		array_push($roleChildrenChildrenIdList,$roleId);
		
		$roleList = $this->roleModel->getRoleList();
		
		$roleList = $this->formatRoleList($roleList);
		
		$viewModel = new viewModel();
		
		$viewModel->setVariable('roleList', $roleList);
		
		$viewModel->setVariable('roleChildrenChildrenIdList', $roleChildrenChildrenIdList);
		
		return  $viewModel;
		
	}//function showParentRoleListAction() end
	
	
	
	

	/**
	 * 角色编辑界面
	 * @return \Zend\View\Model\ViewModel
	 */
	public function showRoleEditAction()
	{
		
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			//die('请不要尝试非法操作');
		}
		
		$queryData = $request->getQuery();
		
		$roleFilterObject = new RoleFilter();
		
		$roleFilter = $roleFilterObject->getInputFilter();
		
		$roleFilter->setData($queryData);
		
		$roleFilter->setValidationGroup(array('id'));
		
		if(!$roleFilter->isValid()){
		
			$errorMessages = $roleFilter->getMessages();
		
			foreach($errorMessages as $errorMessage){
				$this->returnMessage('300', array_pop($errorMessage));
			}
		
		}
		
		$roleId = $queryData['id'];
		
		$roleEditForm = new RoleForm();
		
		//根据文章分类id查询文章分类的相关信息
		$roleColumns = array('id','parent_id','name','description');
		
		$role = $this->roleModel->getRoleById($roleId,$roleColumns);
			
		$roleEditForm->get('id')->setValue($role['id']);
		
		$roleEditForm->get('name')->setValue($role['name']);
		
		$roleEditForm->get('description')->setValue($role['description']);
		
		if($role['parent_id']==0){
			//如果是根节点的话，那么就要考虑把选择父分类的地方删除掉
		
			$roleEditForm->remove('role.parent_id');
				
			$roleEditForm->remove('role.parent_name');
				
		}
		else{
			//如果不是根节点的话，那么就要考虑把选择父分类的地方填充数值
				
			//根据文章分类的parent_id查询文章分类的父分类的信息
			$parentRoleColumns = array('id','name');
				
			$parentRole = $this->roleModel->getRoleById($role['parent_id'],$parentRoleColumns);
				
			$roleEditForm->get('role.parent_id')->setValue($parentRole['id']);
				
			$roleEditForm->get('role.parent_name')->setValue($parentRole['name']);
				
		}
		
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('roleEditForm', $roleEditForm);
			
		return $viewModel;
		
	}//function showRoleEditAction() end
	
	
	
	
	/**
	 * 服务器相应角色编辑的函数
	 * @return array
	 */
	public function checkRoleEditAction()
	{
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$postData = $request->getPost();
		
		if(isset($postData['role_parent_id'])){
			$postData['parent_id'] =  $postData['role_parent_id'];
			unset($postData['role_parent_id']);
		}
		else{
			$postData['parent_id'] = 0;
		}
		
		$roleFilterObject = new RoleFilter();
		
		$roleFilter = $roleFilterObject->getInputFilter();
		
		$roleFilter->setData($postData);
		
		$roleFilter->setValidationGroup(array('id','name','description','parent_id'));
		
		
		if(!$roleFilter->isValid()){
		
			$errorMessages = $roleFilter->getMessages();
		
			foreach($errorMessages as $errorMessage){
				$this->returnMessage('300', array_pop($errorMessage));
			}
		
		}
		
		$role = $roleFilter->getValues();
		
		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
			
		$dbConnection->beginTransaction();
		
		try{
		
			//第一步：查询父分类的信息，然后根据父分类的信息得到子分类的level
			$parentRole = $this->roleModel->getRoleById($role['parent_id']);
		
			$role['level'] = $parentRole['level'] + 1;
				
			//第二步：把收上来的数据更新到数据库
			$this->roleModel->updateRoleById($role['id'],$role);
				
			//第三步：更新文章分类的左右值，就是移动分类
			if($role['parent_id']!=0){
				//如果parent_id为0，那么就是根节点，那么就不要移动
				$this->roleModel->rebuildStructureTree(1,1);
			}
		
		}
		catch(\Exception $e){
				
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
				
		}
		
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，编辑角色成功' );
		
	}//function checkRoleEditAction() end
	
	
	
	
	
	/**
	 * 角色添加界面
	 * @return \Zend\View\Model\ViewModel
	 */	
	public function showRoleAddAction()
	{
		$roleForm = new RoleForm();
		
		
		$viewModel = new ViewModel();
		
		$viewModel->setVariable('roleForm', $roleForm);
		
		return $viewModel;
		
	}//function showRoleAddAction() end
	
	
	
	/**
	 * 服务器响应角色删除的函数
	 */
	public function checkRoleDeleteAction()
	{

		
		
		
		
	}//function checkRoleDeleteAction() end
	
	
	
	
	/**
	 * 服务器响应角色添加的函数
	 * @return array()
	 */
	public function checkRoleAddAction()
	{
		$request = $this->getRequest();
		
		if(!$request->isXmlHttpRequest()){
			die('请不要尝试非法操作');
		}
		
		$postData = $request->getPost();
		$postData['parent_id'] = $postData['role_parent_id'];
		unset($postData['role_parent_id']);
		
		
		$roleFilterObject = new RoleFilter();
		$roleFilter = $roleFilterObject->getInputFilter();
		
		$roleFilter->setValidationGroup(array('parent_id','name','description'));
		
		$roleFilter->setData($postData);
		
		if(!$roleFilter->isValid()){
				
			$errorMessages = $roleFilter->getMessages();
				
			foreach($errorMessages as $errorMessage){
				$this->returnMessage('300', array_pop($errorMessage));
			}
				
		}
		
		$role = $roleFilter->getValues();
		//得到经过过滤和验证的分类信息
		
		
		$dbConnection = $this->getServiceLocator()->get('Zend\Db\Adapter\Connection');
			
		$dbConnection->beginTransaction();
			
		try{
				
			//第二步：查询父角色的信息，然后根据父角色的信息得到角色的level、left_number、right_number
			$parentRole = $this->roleModel->getRoleById($role['parent_id']);
				
			$role['left_number']  = $parentRole['right_number'];
				
			$role['right_number'] = $role['left_number'] + 1;
				
			$role['level'] = $parentRole['level'] + 1;
				
				
			//第三步：为新加入的节点申请空间，挪一个位置出来，更新左值和右值
			//更新的规则，就是把左值大于新节点的左值的节点的左值都加2且就是把左值大于新节点的左值的节点的左值都加2
			$this->roleModel->updateLeftNumberAndRightNumber($role['left_number'],'insert');
				
			//第四步：申请空间之后，根据条件插入新节点
				
			$this->roleModel->insertRole($role);
				
		}
		catch(\Exception $e ){
				
			$dbConnection->rollback();
			$this->returnMessage ('300', $e->getMessage () );
				
		}
			
		$dbConnection->commit ();
		$this->returnMessage ( '200', '恭喜您，添加角色成功' );
		
		
	}//function checkRoleAddAction() end
	
	private function formatNodeList($nodeList){
		
		if(sizeof($nodeList)==0||$nodeList==null){
			return array();
		}
		
		foreach($nodeList as $key=>$node){
				
			if($node['status']=='Y'){
				$nodeList[$key]['status'] = '已启用';
			}
			else if($node['status']=='N'){
				$nodeList[$key]['status'] = '已禁用';
			}
				
			$nodeList[$key]['children_count'] = ($node['right_number'] - $node['left_number']-1)/2;
				
			$nodeList[$key]['margin_left'] = 20 * $node['level'];
				
				
		}
		
		return $nodeList;
	}//function formatNodeList() end
	
	
	
	
	
	private function formatRoleList($roleList){
		
		if(sizeof($roleList)==0||$roleList==null){
			return array();
		}
		
		foreach($roleList as $key=>$role){
			
			if($role['status']=='Y'){
				$roleList[$key]['status'] = '已启用';
			}
			else if($role['status']=='N'){
				$roleList[$key]['status'] = '已禁用';
			}
			
			$roleList[$key]['children_count'] = ($role['right_number'] - $role['left_number']-1)/2;
			
			$roleList[$key]['margin_left'] = 20 * $role['level'];
			
			$roleList[$key]['user_count']  = 0;
		}
		
		return $roleList;
		
	}//function formatRoleList() end
	
	
}//class  RoleController() end

