<?php

namespace Application\Mvc;

class Controller extends \Phalcon\Mvc\Controller
{
    /*public function p($param, $default = null)
    {
        if ($param == '*') {
            $data = array_merge($_GET, $_POST, $this->dispatcher->getParams());
            return $data;
        }

        $data = $this->dispatcher->getParam($param);
        if (isset($_POST[$param])) $data = $_POST[$param];
        if (isset($_GET[$param])) $data = $_GET[$param];
        return $data;
    }*/

    protected function ajaxSuccess($data = null)
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);

        $result = array(
            'res' => 1,
            'data' => $data,
        );

        header('Content-Type: application/json');

        $response = new \Phalcon\Http\Response();
        $response->setJsonContent($result);
        $response->send();

        return true;
    }

    protected function ajaxError($data = null)
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);

        $result = array(
            'res' => 0,
            'data' => $data,
        );

        header('Content-Type: application/json');

        $response = new \Phalcon\Http\Response();
        $response->setJsonContent($result);
        $response->send();

        return false;
    }

    protected function norender()
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        return false;
    }

    protected function renderView($partialPath)
    {
        ob_start();
        $this->view->partial($partialPath);
        $html = ob_get_contents();
        ob_clean();

        return $html;
    }

    protected function p($param = '*', $default = null)
    {
        $params = $this->request->get();
        $dispatcherParams = $this->dispatcher->getParams();

        $fullparams = array_merge($params, $dispatcherParams);

        return $param == '*' ? $fullparams : (isset($fullparams[$param]) ? $fullparams[$param] : $default);
    }

    protected function redirect($url, $code = 303)
    {
        return $this->response->redirect($url, true, $code)->sendHeaders();
    }

    protected function checkDomainRedirect()
    {
        if (strpos($_SERVER['HTTP_HOST'], 'smdmitry.com') === false) {
            $isHTTPS = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
            $isHTTPS = $isHTTPS || !empty($_SERVER['HTTPS']);
            return $this->redirect(($isHTTPS ? 'https' : 'http') . '://smdmitry.com' . $_SERVER['REQUEST_URI']);
        }

        return false;
    }

    public function beforeExecuteRoute($dispatcher)
    {
        if ($dispatcher->getActionName() != 'go') {
            if ($this->checkDomainRedirect()) {
                return false;
            }
        }
    }
}
