<?php

return array(
            /*****首页显示页面*****/
    'router' => array(
        'routes' => array(			
                'report' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/report[/:controller[/:action]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Report\Controller',
                        'controller'    => 'weeklyreport',
                        'action'        => 'showWeeklyReportList',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
			
        ),
    ),
	
	/*****添加新的控制器*****/
    'controllers' => array(
        'invokables' => array(
            'Report\Controller\WeeklyReport' => 'Report\Controller\WeeklyReportController',
        	'Report\Controller\WeeklyReporttemplate' => 'Report\Controller\WeeklyReportTemplateController',
        	'Report\Controller\DailyReport' => 'Report\Controller\DailyReportController',
        	'Report\Controller\DailyReporttemplate'  => 'Report\Controller\DailyReportTemplateController',
       ),
    ),
	/*****模版路径*****/
    'view_manager' => array(
        'template_path_stack' => array(
           'report'=>__DIR__ . '/../view',
        ),
    ),
);
