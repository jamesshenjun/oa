<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array (
		'router' => array (
				'routes' => array (
						'home' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'controller' => 'Help\Controller\Index',
												'action' => 'index' 
										) 
								) 
						) 
				) 
		),
		'service_manager' => array (
				
				'factories' => array (
						
						'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
						
						'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
						
						'Zend\Db\Adapter\Connection' => function ($serviceManager) {
							
							$dbAdapter = $serviceManager->get ( 'Zend\Db\Adapter\Adapter' );
							// 首先得到数据库适配器对象
							
							$dbConnection = $dbAdapter->getDriver ()->getConnection ();
							// 通过得到数据库驱动从而得到数据库的连接
							
							return $dbConnection;
							// 返回数据库的连接
						}
						
						
    					
				)
				 
		),
		'translator' => array (
				'locale' => 'zh_CN',
				'translation_file_patterns' => array (
						array (
								'type' => 'gettext',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.mo' 
						) 
				) 
		),
		'controllers' => array (
				'invokables' => array (
						'Application\Controller\Index' => 'Application\Controller\IndexController' 
				) 
		),
		'view_manager' => array (
				'display_not_found_reason' => true,
				'display_exceptions' => true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'template_map' => array (
						'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
						'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
						'error/404' => __DIR__ . '/../view/error/404.phtml',
						'error/index' => __DIR__ . '/../view/error/index.phtml' 
				),
				'template_path_stack' => array (
						__DIR__ . '/../view' 
				) 
		),
		
		
		
);//array end

