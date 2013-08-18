<?php

namespace Application\Plugin\Permission;

use Zend\Permissions\Acl\Acl;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService as AuthenticationService;


use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Mvc as MvcPage;

class Permission extends AbstractPlugin
{
	protected $user;
	protected $role = NULL;
	protected $acl  = NULL;
	protected $menu = NULL;
	
	
	public function __construct()
	{
		$auth = new AuthenticationService();
		
		if($auth->hasIdentity()){
			$this->user = $auth->getIdentity();
		}
		
	}//function __construct() end
	
	public function menu($e){
		
		//第一部分：得到模块、控制器与方法的名称与路由，用于在下面调用
		$serviceManager = $e->getApplication()->getServiceManager();
		//得到服务管理者对象
		
		$router = $e->getRouter();
		//得到路由对象
		
		$configure = $serviceManager->get('config');
		//得到配置对象
		
		$RouteParamList = $this->getRouteParamList($e,$serviceManager,$router);
		$actionPath = $RouteParamList['action_path'];
		
		if(!in_array($actionPath,$configure['need_generate_menu'])){
		//如果当前方法不需要生成菜单,那么直接返回
		  	return;
		}
		
		$menuModel = $serviceManager->get('Application/Plugin/Permission/Model/MenuModel');
		
		$tempMenuItemList  = $menuModel->getMenuItemList();
		
		$menuItemList = array();
		
		foreach($tempMenuItemList as $menuItem){
			$menuItemList[$menuItem['id']] = $menuItem;
		}//foreach end
		
		$baseUrl = $e->getRequest()->getBaseUrl();
		
		foreach($menuItemList as $key=>$menuItem){
			
			$parentId = $menuItem['parent_id'];
			
			if($parentId==0){
				$menuItemList[$key]['uri'] = '#';
			}
            else{
            	
            	//通过菜单项中的模块名称、控制器名称、方法名称拼接得到资源名称
            	$resourceArray = array($menuItem['module_name'],$menuItem['controller_name'],$menuItem['action_name']);
            		
            	$menuItemList[$key]['resource'] = strtolower(  implode('.',array_filter($resourceArray))  );
            		
            	if($menuItem['level']==1){
            		
            		$menuItemList[$key]['uri']	= $baseUrl.'/index/sidebar/'.$menuItem['module_name'];
            		
            	}
            	else{
            		
            		$menuItemList[$key]['route']	  = $menuItem['module_name'];
            		
            		$menuItemList[$key]['module'] 	  = $menuItem['module_name'];
            		
            		$menuItemList[$key]['controller'] = $menuItem['controller_name'];
            		
            		$menuItemList[$key]['action'] 	  = $menuItem['action_name'];
            		
            		$menuItemList[$key]['target'] 	  = 'navTab';
            		
            		
            	}
            	
            	
            	if(!isset($menuItemList[$parentId]['pages'])){
            		$menuItemList[$parentId]['pages'] = array();
            	}
            	
            	$menuItemList[$parentId]['pages'] = array_merge(array($menuItemList[$key]),$menuItemList[$parentId]['pages']);
            	
            	unset($menuItemList[$key]);
            
            }
			
			
            
        }//foreach
		
        $container = new Navigation();
        
        $container->addPages($menuItemList);
        
        
        
        $this->injectRouter($container,$router);
        
        $renderer = $serviceManager->get ( 'Zend\View\Renderer\PhpRenderer' );
        
        $acl  = $this->getAcl();
        
        //print_r($menuItemList);//report.weeklyreporttemplate.showweeklyreporttemplatelist
        
//         print_r($acl);
        
        $role = $this->getRole();
        

       
        $renderer->navigation()->setContainer($container)->setAcl($acl)->setRole('total');
        
     }//function menu() end
	

	
	
	public function auth($e)
	{	
		
		//第一部分：得到模块、控制器与方法的名称与路由，用于在下面调用
		$serviceManager = $e->getApplication()->getServiceManager();
		//得到服务管理者对象
		
		$router = $e->getRouter();
		//得到路由对象
		
		$configure = $serviceManager->get('config');
		//得到配置对象
		
		$RouteParamList = $this->getRouteParamList($e,$serviceManager,$router);
		$modulePath 	= $RouteParamList['module_path'];
		$controllerPath = $RouteParamList['controller_path'];
		$actionPath 	= $RouteParamList['action_path'];
		
		
		//第二步:判断有没有权限来执行路由
		
		//1.判断方法路径在不在白名单中,如果再就什么都不用做，直接返回
		//不能跳转到登录页，因为可能形成死循环；而且形成验证码也不需要跳转到登录页
		if(in_array($actionPath,$configure['not_need_auth_action'])){
			return;
		}
		
		
		
		//2.当前访问的方法不在白名单中，就去判断存在不存在session,如果不存在，就跳转到登录页要求用户进行登录
		if($this->user==null){
			
			$userLoginUrl = $router->assemble(array(), array('name'=>'application'));
			
			$response = $e->getResponse();
				
			$response->setStatusCode(301);
				
			$response->getHeaders()->addHeaderLine('location',$userLoginUrl);
			
			return;
		}
		
		
		//3.如果通过了第二步的认证，现在进行超级管理员的认证,超级管理员不受权限的约束
		if($this->user->username=='admin'){
			return;
		}
		
		//---------------------如果通过了第二步的认证，又不是超级管理员，就判断为受权限列表约束的角色用户-------------//
		
		//第三步：在受权限约束的体系中检验有没有权限执行路由
		//1.得到相关的模型
		$userRoleModel = $serviceManager->get('Application/Plugin/Permission/Model/UserRoleModel');
		$accessModel   = $serviceManager->get('Application/Plugin/Permission/Model/AccessModel');
		$nodeModel     = $serviceManager->get('Application/Plugin/Permission/Model/NodeModel');
		
		
		//2.根据用户查询用户所拥有的角色
		$roleIdList = $userRoleModel->getRoleIdListByUserId($this->user->id);
		
		if (sizeof($roleIdList)==0){
			die('该用户没有角色，请联系网站管理员！');
		}
		
		//3.新建权限判断的容器 和 角色对象
		//这部分的内容将会用来形成
		$acl = new Acl();
		
		$role = new Role('total');
		
		$acl->addRole($role);
		
		//4.查询用户所拥有的角色，既而得到多个角色所拥有的权限
		$accessList = $accessModel->getNodeIdListByRoleId($roleIdList);
		$accessList = array_unique($accessList);
		
		if (sizeof($accessList) < 1 ){
			die('该用户未被授权任何权限，请联系网站管理员！');
		}
		
		//5.根据权限列表给该用户的角色授权。
		foreach ($accessList as $nodeId){
			
			$nodeAncestorColumns = array('id','name','level');
			$nodeAncestor = $nodeModel->getAncestorById($nodeId,$nodeAncestorColumns,2) ;

			if(sizeof($nodeAncestor)==0){
				continue;
			}
			
			$nodeAncestorNameArray = array();
			
			foreach ($nodeAncestor as $key=>$node){
				array_push($nodeAncestorNameArray,$node['name']);
			}
			
			
			$resource = implode($nodeAncestorNameArray, '.');
			$resource = strtolower($resource);
			
		    
			$acl->addResource(new Resource($resource));
			
			$acl->allow('total',$resource);
		}
			
		
		$modulePath 	= $RouteParamList['module_path'];
		$controllerPath = $RouteParamList['controller_path'];
		$actionPath 	= $RouteParamList['action_path'];
		
		//echo $controllerPath;
// 		echo $actionPath;
		//第五步，检查当前用户所访问的url，组合成node的name，检测是否被授权
		if(!$acl->hasResource($modulePath)){
        	die('当前模块不存在，请联系网站管理员！');
        }
        
        if(!$acl->hasResource($controllerPath)){
        	die('当前控制器不存在，请联系网站管理员！');
        }
        
        if(!$acl->hasResource($actionPath)){
        	die('当前方法不存在，请联系网站管理员！');
        }
        
		if (!$acl->isAllowed('total',$modulePath)){
			die('你没有当前模块的权限，请联系网站管理员！');
        }
         
        if (!$acl->isAllowed('total',$controllerPath)){
         	die('你没有当前控制器的权限，请联系网站管理员！');
        }
         
        if (!$acl->isAllowed('total',$actionPath)){
        	die('你没有当前方法的权限，请联系网站管理员！');
        }
       
        
        $this->setRole($role);
        $this->setAcl($acl);

    }
    
    public function getRole()
    {
    	return $this->role;
    }
    
    public function setRole($role){
    	
    	$this->role = $role;
    	
    }
    
    public function getAcl(){
    	 
    	return $this->acl;
    	 
    }
    
    public function setAcl($acl){
    	$this->acl = $acl;
    }
    
    public function setMenu($menu){
    	$this->menu = $menu;
    }
    public function getMenu(){
    	return $this->menu;
    }

    
    private function getRouteParamList($e,$serviceManager,$router){
    
    	$routeMatch = $e->getRouteMatch();
    	//得到路由匹配对象
    
    	$routeMatchParam = $routeMatch->getParams();
    	//得到路由配置对象的参数
    
    	$module 	= strtolower(substr($routeMatchParam['__NAMESPACE__'], 0,strpos($routeMatchParam['__NAMESPACE__'], '\\')));
    	//得到模块的名称
    
    	$controller = $routeMatchParam['__CONTROLLER__'];
    	//得到控制器的名称
    
    	$action		= $routeMatchParam['action'];
    	//得到方法的名称
    
    	$param = array();
    
    	$param['module_path'] = strtolower($module);
    
    	$param['controller_path'] = strtolower($param['module_path'].'.'.$controller);
    
    	$param['action_path'] = strtolower($param['controller_path'].'.'.$action);
    
    	return $param;
    
    }//function getRouteParamList() end
    
    private function injectRouter($container, $router) {
    		
    	foreach ($container->getPages() as $page) {
    		
    		if ($page instanceof MvcPage) {
    			
    			$page->setRouter($router);
    			
    		}
    
    		if ($page->hasPages()) {
    			
    			$this->injectRouter($page, $router);
    			
    		}
    	}
    	
    }
	

	

}