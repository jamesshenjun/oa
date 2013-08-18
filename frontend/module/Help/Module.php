<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Help;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Help\Model\ArticleInfoModel;
use Help\Model\ArticleContentModel;
use Help\Model\ArticleSortModel;

class Module
{
    
	public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
    	return array(
    			 
    			'factories' => array(
    					
    					'Help\Model\ArticleInfoModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleInfoModel($dbAdapter);
    					},
    					'Help\Model\ArticleContentModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleContentModel($dbAdapter);
    					},
    					'Help\Model\ArticleSortModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleSortModel($dbAdapter);
    					},
    			),
    	);
    }
    

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
