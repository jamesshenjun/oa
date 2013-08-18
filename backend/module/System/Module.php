<?php

namespace System;

use System\Model\UserModel;
use System\Model\UserRoleModel;
use System\Model\DepartmentModel;
use System\Model\RoleModel;
use System\Model\NodeModel;
use System\Model\AccessModel;
use System\Model\ArticleSortModel;
use System\Model\RoleArticleSortModel;
use System\Model\DepartmentLeaderModel;
use System\Model\ReportTemplateInfoModel;


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
    					'System\Model\UserModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new UserModel($dbAdapter);
    					},
    					'System\Model\UserRoleModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new UserRoleModel($dbAdapter);
    					},
    					'System\Model\DepartmentModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new DepartmentModel($dbAdapter);
    					},
    					'System\Model\RoleModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new RoleModel($dbAdapter);
    					},
    					'System\Model\AccessModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new AccessModel($dbAdapter);
    					},
    					'System\Model\NodeModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new NodeModel($dbAdapter);
    					},
    					'System\Model\ArticleSortModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ArticleSortModel($dbAdapter);
    					},
    					'System\Model\RoleArticleSortModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new RoleArticleSortModel($dbAdapter);
    					},
    					'System\Model\DepartmentLeaderModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new DepartmentLeaderModel($dbAdapter);
    					},
    					'System\Model\ReportTemplateInfoModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ReportTemplateInfoModel($dbAdapter);
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
