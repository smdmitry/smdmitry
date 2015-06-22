<?php

namespace Autosms\Controller;

class CronController extends \Application\Mvc\Controller
{
    public function indexAction()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);

        $queue = \Autosms\Dao\Command::i()->getExpiredQueue(10);

        foreach ($queue as $command) {
            \Autosms\Dao\Command::i()->updateStatus($command->getId(), \Autosms\Service\Command::STATUS_EXPIRED);

            // TODO: send push
        }

        echo 'Expired '. count($queue) . ' commands';

        return true;
    }
}
