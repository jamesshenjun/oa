<?php

return array(
		
	'router' => array(
			
        'routes' => array(	
        	'help' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/help[/:controller][/:action][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    	'id'		 => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Help\Controller',
                        'controller'    => 'Article',
                        'action'        => 'index',
                    ),
                	'showSearchList' => array(
                			'__NAMESPACE__' => 'Help\Controller',
                			'controller'    => 'Article',
                			'action'        => 'showSearchList',
                	),
                		
                		
                		
                ),
            ),
			
        ),
    ),
    
    'controllers' => array(
        'invokables' => array(
        	'Help\Controller\Index' => 'Help\Controller\IndexController',
            'Help\Controller\Article' => 'Help\Controller\ArticleController'
        ),
     ),
	'view_manager' => array(
			'template_path_stack' => array(
					'help'=>__DIR__ . '/../view',
			),
	),
   
);
