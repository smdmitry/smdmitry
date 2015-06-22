<?php

namespace Index;

class Module
{
    public function registerAutoloaders()
    {

    }

    public function registerServices($di)
    {
        $dispatcher = $di->get('dispatcher');
        $dispatcher->setDefaultNamespace("Index\Controller");
        $di->set('dispatcher', $dispatcher);

        $view = $di->get('view');
        $view->setViewsDir(__DIR__ . '/views/');
    }
}