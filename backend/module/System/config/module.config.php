<?php

return array(
            /*****首页显示页面*****/
    'router' => array(
        'routes' => array(			
            'system' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/system[/:controller][/][:action]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'System\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
            ),
			
        ),
    ),
	
	/*****添加新的控制器*****/
    'controllers' => array(
        'invokables' => array(
            'System\Controller\User' => 'System\Controller\UserController',
        	'System\Controller\Role' => 'System\Controller\RoleController',
        	'System\Controller\Department' => 'System\Controller\DepartmentController',
        ),
    ),
	/*****模版路径*****/
    'view_manager' => array(
        'template_path_stack' => array(
           'system'=>__DIR__ . '/../view',
        ),
    ),
);
