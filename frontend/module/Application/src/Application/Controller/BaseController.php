<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as Session;

use Zend\Authentication\Adapter\DbTable as dbTableAuthAdapter;
use Zend\Authentication\Result as Result;

use Zend\Mvc\MvcEvent;
use Application\Factory\ServiceLocatorFactory;
use Application\Filter\UserFilter;

class BaseController extends AbstractActionController
{
	protected $loginUser;
	
	public function __construct(){
		
				
	}
	
	/**
	 * 向控制器中的成员变量注册数据表模型
	 * @param object $serviceManager
	 * @param string $applicationName
	 * @param string $folderName
	 * @param string $ModelName
	 */
	
	final protected function getDbModel($serviceManager, $applicationName, $folderName, $ModelName) {
	
		$lcfirstModelName = lcfirst ( $ModelName );
	
		if (! $this->{$lcfirstModelName}) {
	
			$path = $applicationName . "/" . $folderName . "/" . $ModelName;
	
			$model = $serviceManager->get ( $path );
	
				
				
			$this->{$lcfirstModelName} = $model;
		}
	}
	
	
    
   /**
     * 返回登录用户
     */
    public function getLoginUser()
    {
    	if(!isset($this->loginUser)){
    		$auth = new AuthenticationService();
    		$this->loginUser = $auth->getIdentity();
    	}
    	return $this->loginUser;
    }
    
    
    final protected  function returnMessage($statusCode, $message) {
    	$return = array (
    			'statusCode' => $statusCode,
    			'message' => $message
    	);
    	exit ( json_encode ( $return ) );
    }
    
}
