<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

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
		
		'percent_page_row_count'=>'15'
		
);


