<?php

namespace Help;

use Zend\Mvc\ModuleRouteListener;
use Help\Model\ArticleInfoModel;
use Help\Model\ArticleContentModel;
use Help\Model\ArticleSortModel;
use Help\Model\RoleArticleSortModel;
use Help\Model\UserModel;
use Help\Model\UserRoleModel;
use Help\Model\MenuModel;



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
    					
    					'Help\Model\ArticleModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleModel($dbAdapter);
    					},
    					'Help\Model\ArticleSortModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleSortModel($dbAdapter);
    					},
    					'Help\Model\UserModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new UserModel($dbAdapter);
    					},
    					'Help\Model\UserRoleModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new UserRoleModel($dbAdapter);
    					},
    					'Help\Model\RoleArticleSortModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new RoleArticleSortModel($dbAdapter);
    					},
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
    					'Help\Model\MenuModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new MenuModel($dbAdapter);
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
