<?php

namespace Tempmail\Controller;

class IndexController extends \Application\Mvc\Controller
{
    public function indexAction()
    {
        $emails = \Tempmail\Dao\Email::i()->getAll();

        $this->view->emails = $emails;

        $this->view->setLayout('main');
    }
}
