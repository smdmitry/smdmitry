<?php

namespace Autosms;

class Module
{
    public function registerAutoloaders()
    {

    }

    public function registerServices($di)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace('Autosms\Controller');
        $di->set('dispatcher', $dispatcher);

        $view = $di->get('view');
        $view->setViewsDir(__DIR__ . '/views/');
    }
}