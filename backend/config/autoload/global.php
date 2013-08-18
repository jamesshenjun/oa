<?php
return array(
		
		'db' => array(
					'driver'   => 'mysqli',
					'hostname' => 'localhost',
					'database' => 'oa',
    				'username' => 'root',
    				'password' => '123456',
					'charset'  => 'utf8',
				    'port'	   => 3306,
		),
		
		'not_need_auth_module' => array('application'),
         //不需要验证的模块
									
		'not_need_auth_controller' => array('application.index'),
		//不需要验证的控制器
				
		'not_need_auth_action' => array(
	    //不需要验证的方法	
				'application.index.showuserlogin',
				'application.index.checkuserlogin',
				'application.index.captcha',
				'application.index.logout',
				
		),
		
		'need_generate_menu' =>array(
				//需要生成菜单的方法列表
		
			'application.index.admin',
				 
			'application.index.sidebar',
				 
		),
		
		'template_upload'=>array (
				'max_size' => '5MB', // 上传文件的最大尺寸
				'ext' =>'xls,xlsx', 
				'dir' => BASEPATH. '/public/ReportTemplate/' 
		),
		'daliy_report'=>array(
				'deadline' =>'23:59:59',
				'startline' =>'00:00:00',
				),
		'ignore_list' =>array(
				//检查未交周报的忽略名单
				'超级管理员',
				'张良全',
				'沈振冈'
				)
		
		
		
		
);
