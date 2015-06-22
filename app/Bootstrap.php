<?php

class Bootstrap
{
    public function run()
    {
        $di = new \Phalcon\DI\FactoryDefault();

        $config = include APPLICATION_PATH . '/config/application.php';
        $di->set('config', $config);

        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces($config->loader->namespaces->toArray());
        $loader->register();

        $db = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname,
            "charset" => $config->database->charset,
        ));
        $di->set('db', $db);

        $di->set('cookies', function() {
            $cookies = new Phalcon\Http\Response\Cookies();
            $cookies->useEncryption(false);
            return $cookies;
        });

        $view = new \Phalcon\Mvc\View();

        define('MAIN_VIEW_PATH', '../../../views/');
        $view->setLayoutsDir(MAIN_VIEW_PATH . '/layouts/');
        $view->setLayout('smdmitry');

        $phtml = new \Phalcon\Mvc\View\Engine\Php($view, $di);
        $view->registerEngines(array(".phtml" => $phtml));

        $di->set('view', $view);

        $application = new \Phalcon\Mvc\Application();
        $application->registerModules($config->modules->toArray());

        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $di->set('dispatcher', $dispatcher);

        $router = new \Application\Mvc\DefaultRouter();
        $router->setDi($di);
        foreach ($application->getModules() as $module) {
            $routesClassName = str_replace('Module', 'Routes', $module['className']);
            if (class_exists($routesClassName)) {
                $routesClass = new $routesClassName();
                $router = $routesClass->init($router);
            }
            $initClassName = str_replace('Module', 'Init', $module['className']);
            if (class_exists($initClassName)) {
                $initClass = new $initClassName();
                $initClass->init($di);
            }
        }

        $di->set('router', $router);

        $application->setDI($di);

        $this->dispatch($di);

        $this->backgroundProcess();
    }

    protected function dispatch($di)
    {
        $router = $di['router']; /** @var $router \Application\Mvc\DefaultRouter **/
        $dispatcher = $di['dispatcher']; /** @var $dispatcher \Phalcon\Mvc\Dispatcher **/
        $response = $di['response'];
        $view = $di['view'];

        $router->handle();

        $dispatcher->setModuleName($router->getModuleName());
        $dispatcher->setControllerName($router->getControllerName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->setParams($router->getParams());

        $tmpModuleNameArr = explode('-', $router->getModuleName());
        $moduleName = '';
        foreach ($tmpModuleNameArr as $part) {
            $moduleName .= \Phalcon\Text::camelize($part);
        }

        $ModuleClassName = $moduleName . '\Module';
        if (class_exists($ModuleClassName)) {
            $module = new $ModuleClassName;
            $module->registerAutoloaders();
            $module->registerServices($di);
        }

        $view->start();

        try {
            $dispatcher->dispatch();
        } catch (\Phalcon\Exception $e) {
            if ($e instanceof Phalcon\Mvc\Dispatcher\Exception) {
                $module = new \Shorten\Module();
                $module->registerAutoloaders();
                $module->registerServices($di);

                $dispatcher->setModuleName('shorten');
                $dispatcher->setControllerName('index');
                $dispatcher->setActionName('go');
                $dispatcher->setParam('e', $e);
                $dispatcher->setParam('hash', $router->getModuleName());

                $dispatcher->dispatch();
            } else {
                $view->setViewsDir(MAIN_VIEW_PATH . '/layouts/');
                $response->setHeader(503, 'Service Unavailable');
                $view->partial('smdmitry');
                $response->sendHeaders();

                echo $response->getContent();
                return;
            }
        }

        $view->render(
            $dispatcher->getControllerName(),
            $dispatcher->getActionName(),
            $dispatcher->getParams()
        );

        $view->finish();

        $response->setContent($view->getContent());
        $response->sendHeaders();

        echo $response->getContent();
    }

    protected function backgroundProcess()
    {
        $worker = \Application\BackgroundWorker::i();
        if ($worker->hasJob()) {
            $worker->doJob();
        }
    }
}
