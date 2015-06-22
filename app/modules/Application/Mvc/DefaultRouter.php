<?php

namespace Application\Mvc;

use Phalcon\Mvc\Router;

class DefaultRouter extends Router
{
    public function __construct()
    {
        parent::__construct();

        $this->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);
        $this->removeExtraSlashes(true);

        $this->setDefaultModule('index');
        $this->setDefaultController('index');
        $this->setDefaultAction('index');

        $this->add('/:module/:controller/:action/:params', array(
            'module' => 1,
            'controller' => 2,
            'action' => 3,
            'params' => 4,
        ))->setName('default');

        $this->add('/:module/:controller', array(
            'module' => 1,
            'controller' => 2,
            'action' => 'index',
        ))->setName('default_action');

        $this->add('/:module', array(
            'module' => 1,
            'controller' => 'index',
            'action' => 'index',
        ))->setName('default_controller');
    }
}
