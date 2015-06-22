<?php

namespace Shorten;

class Module
{
    public function registerAutoloaders()
    {

    }

    public function registerServices($di)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace('Shorten\Controller');
        $di->set('dispatcher', $dispatcher);

        $view = $di->get('view');
        $view->setViewsDir(__DIR__ . '/views/');
    }
}