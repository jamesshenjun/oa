<?php

header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('PRC');

chdir(dirname(__DIR__));

define('BASEPATH', dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();