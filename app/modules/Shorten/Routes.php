<?php

namespace Shorten;

class Routes
{
    public function init($router)
    {
        /*$router->add('/url', array(
            'module' => 'url',
            'controller' => 'index',
            'action' => 'index',
        ))->setName('url');

        $router->add('/url/{id:\d+}', array(
            'module' => 'url',
            'controller' => 'index',
            'action' => 'index',
        ))->setName('url_id');*/

        return $router;
    }
}