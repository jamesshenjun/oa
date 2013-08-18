<?php

return array(
		
    /*****首页显示页面*****/
    'router' => array(
        'routes' => array(			
            'help' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/help[/:controller][/][:action][/][:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    	'id'=> '[0-9+]',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Help\Controller',
                        'controller'    => 'Article',
                        'action'        => 'showActicleList',
                    ),
                ),
            ),
			
        ),
    ),
	
	/*****添加新的控制器*****/
    'controllers' => array(
        'invokables' => array(
        	'Help\Controller\Article'    => 'Help\Controller\ArticleController',
        	'Help\Controller\ArticleSort'=> 'Help\Controller\ArticleSortController'
        ),
    ),
	/*****模版路径*****/
    'view_manager' => array(
        'template_path_stack' => array(
           'help'=>__DIR__ . '/../view',
        ),
    ),
);
