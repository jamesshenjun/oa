<?php

namespace Report;
use Report\Model\ReporttemplateInfoModel;
use Report\Model\ReporttemplateContentModel;
use Report\Model\ReportContentModel;
use Report\Model\ReportInfoModel;
use Report\Model\UserReportModel;

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
    					'Report\Model\ReportTemplateInfoModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ReportTemplateInfoModel($dbAdapter);
    					},
    					'Report\Model\ReportTemplateContentModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ReportTemplateContentModel($dbAdapter);
    					},
    					'Report\Model\ReportContentModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ReportContentModel($dbAdapter);
    					},
    					'Report\Model\ReportInfoModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new ReportInfoModel($dbAdapter);
    					},
    					'Report\Model\UserReportModel' =>  function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						return new UserReportModel($dbAdapter);
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
