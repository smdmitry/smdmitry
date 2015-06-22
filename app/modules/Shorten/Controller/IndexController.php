<?php

namespace Shorten\Controller;

class IndexController extends \Application\Mvc\Controller
{
    const LIST_LIMIT = 10;

    public function indexAction()
    {
        $limit = self::LIST_LIMIT;
        $offset = (int)$this->p('offset', 0);
        $offset = $offset > 0 ? $offset : 0;

        $urls = \Shorten\Dao\Url::i()->getByUserId(0, $limit, $offset);

        $this->view->urls = $urls;
        $this->view->urlsCount = \Shorten\Dao\Url::i()->getCountByUserId(0);
        $this->view->offset = $offset;
        $this->view->limit = $limit;

        $urlInfo = $this->p('info');
        if ($urlInfo) {
            $this->view->urlInfo = \Shorten\Dao\Url::i()->getByHash($urlInfo);
        }

        $this->view->setLayout('main');
    }

    public function infoAction()
    {
        $hash = $this->p('hash');
        $this->view->urlInfo = \Shorten\Dao\Url::i()->getByHash($hash);

        return $this->ajaxSuccess(array(
            'html' => $this->renderView('index/url_info'),
        ));
    }

    public function listAction()
    {
        $limit = self::LIST_LIMIT;
        $offset = (int)$this->p('offset', 0);
        $offset = $offset > 0 ? $offset : 0;

        $urls = \Shorten\Dao\Url::i()->getByUserId(0, $limit, $offset);

        $this->view->urls = $urls;
        $this->view->urlsCount = \Shorten\Dao\Url::i()->getCountByUserId(0);
        $this->view->offset = $offset;
        $this->view->limit = $limit;

        return $this->ajaxSuccess(array(
            'html' => $this->renderView('index/url_list'),
        ));
    }

    public function addAction()
    {
        $url = $this->p('url');
        $url = trim($url);

        if (!strpos($url, '://')) {
            $url = 'http://' . $url;
        }

        if (!preg_match(\Shorten\Service\Base::URL_PREG, $url)) {
            return $this->ajaxError(array(
                'error' => 'The url you entered is not valid. Please fix the url and try again.',
            ));
        }

        $validation = new \Phalcon\Validation();
        $validation->add('url', new \Phalcon\Validation\Validator\Url((array(
            'message' => 'The url you entered is not valid. Please fix the url and try again.'
        ))));

        if (empty($url) || count($validation->validate(array('url' => $url)))) {
            return $this->ajaxError(array(
                'error' => 'The url you entered is not valid. Please fix the url and try again.',
            ));
        }

        $urlModel = \Shorten\Dao\Url::i()->addUrl($url);
        if (!$urlModel) {
            return $this->ajaxError(array(
                'error' => 'Database error occurred. Please try again.',
            ));
        }

        return $this->ajaxSuccess(array(
            'hash' => $urlModel->getHash(),
        ));
    }

    public function addahkAction()
    {
        $url = $this->p('url');
        $url = trim($url);

        if (!strpos($url, '://')) {
            $url = 'http://' . $url;
        }

        if (!preg_match(\Shorten\Service\Base::URL_PREG, $url)) {
	    die('The url you entered is not valid. Please fix the url and try again.');
        }

        $validation = new \Phalcon\Validation();
        $validation->add('url', new \Phalcon\Validation\Validator\Url((array(
            'message' => 'The url you entered is not valid. Please fix the url and try again.'
        ))));

        if (empty($url) || count($validation->validate(array('url' => $url)))) {
 	    die('The url you entered is not valid. Please fix the url and try again.');
        }

        $urlModel = \Shorten\Dao\Url::i()->addUrl($url);
        if (!$urlModel) {
            die('Database error occurred. Please try again.');
        }

	    die($urlModel->getShortUrl());
    }

    public function goAction()
    {
        $hash = $this->dispatcher->getParam('hash');
        $id = \Shorten\Service\Base::base_decode($hash);

        $urlModel = \Shorten\Dao\Url::i()->getById($id);

        if (empty($urlModel)) {
            return $this->checkDomainRedirect();
        }

        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        \Shorten\Dao\UrlHit::i()->addHit($urlModel->getId(), $ip, $referer, $ua);
        \Shorten\Dao\Url::i()->addHit($urlModel->getId());

        return $this->redirect($urlModel->getLongUrl());
    }
}
