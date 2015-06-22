<?php

error_reporting(E_ALL);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 5);

$settings = array(
    'database' => array(
        'host' => 'localhost',
        'username' => 'smdmitry',
        'password' => 'smdmitry',
        'dbname' => 'smdmitry',
        'charset' => 'utf8',
        'options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            PDO::ATTR_CASE => PDO::CASE_LOWER,
        ),
    ),
    'loader' => array(
        'namespaces' => array(
            'Zend' => APPLICATION_PATH . '/../vendor/zendframework/library/Zend',
            'Application' => APPLICATION_PATH . '/modules/Application',
            'Index' => APPLICATION_PATH . '/modules/Index',
            'Shorten' => APPLICATION_PATH . '/modules/Shorten',
            'Tempmail' => APPLICATION_PATH . '/modules/Tempmail',
            'Autosms' => APPLICATION_PATH . '/modules/Autosms',
        ),
    ),
    'modules' => array(
        'index' => array(
            'className' => 'Index\Module',
            'path' => APPLICATION_PATH . '/modules/Index/Module.php'
        ),
        'shorten' => array(
            'className' => 'Shorten\Module',
            'path' => APPLICATION_PATH . '/modules/Shorten/Module.php'
        ),
        'tempmail' => array(
            'className' => 'Tempmail\Module',
            'path' => APPLICATION_PATH . '/modules/Tempmail/Module.php'
        ),
        'autosms' => array(
            'className' => 'Autosms\Module',
            'path' => APPLICATION_PATH . '/modules/Autosms/Module.php'
        ),
    ),
    'slack' => array(
        'token' => 'token',
    ),
    'smdmitry' => array(
        'hwhash' => 'hwhash',
        'hwpassword' => 'hwpassword',
        'sendsmssalt' => 'sendsmssalt',
        'receivesmsphone' => '79215555555',
        'smslogfile' => 'smslogfile',
    ),
    'profiler' => true,
);

@define('PRODUCTION', (
    strpos(@$_SERVER['HTTP_HOST'], '.lc') !== false ||
    strpos(@$_SERVER['APPDATA'], 'Documents and Settings') !== false ||
    strpos(@$_SERVER['APPDATA'], 'Roaming') !== false
? false : true));

$configPrefix = PRODUCTION ? 'prod' : 'local';
if (file_exists(APPLICATION_PATH . '/config/' . $configPrefix . '.override.php')) {
    include APPLICATION_PATH . '/config/' . $configPrefix . '.override.php';
}

return new \Phalcon\Config($settings);
