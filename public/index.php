<?php

define('APPLICATION_PATH',  __DIR__ . '/../app');

require_once APPLICATION_PATH . '/Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->run();
