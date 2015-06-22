<?php

namespace Autosms\Controller;

class IndexController extends \Application\Mvc\Controller
{
    protected $user;

    protected function checkHTTPSRedirect()
    {
        $isHTTPS = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
        $isHTTPS = $isHTTPS || !empty($_SERVER['HTTPS']);

        if (!$isHTTPS) {
            return $this->redirect('https://smdmitry.com' . $_SERVER['REQUEST_URI']);
        }

        return false;
    }

    public function beforeExecuteRoute($dispatcher)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        if ($this->checkHTTPSRedirect()) {
            return false;
        }

        if ($this->cookies->has('hw')) {
            $hw = $this->cookies->get('hw');
            if ($hw == $this->getHWHash()) {
                $this->view->user = $this->user = \Autosms\Dao\User::i()->getById(1);
                $this->view->car = $this->car = \Autosms\Dao\Car::i()->getById(2);
            }
        }

        parent::beforeExecuteRoute($dispatcher);
    }

    protected function getHWHash() {
        return md5(\Phalcon\DI::getDefault()->config->smdmitry->hwhash);
    }

    protected function checkLogin() {
        if (empty($this->user) || empty($this->car)) {
            if ($this->dispatcher->getActionName() != 'login') {
                $this->redirect('/autosms/index/login/');
                return false;
            }
        }
    }

    public function loginAction()
    {
        if (!empty($this->user) && !empty($this->car)) {
            return $this->redirect('/autosms/');
        }

        $login = strtolower($this->p('login'));
        $password = $this->p('password');

        if ($login == 'user' || $login == 'smdmitry') {
            if ($password == \Phalcon\DI::getDefault()->config->smdmitry->hwpassword) {
                setcookie("hw", $this->getHWHash(), time() + 365 * 86400, '/autosms/', null, true, true);
                return $this->redirect('/autosms/');
            }
        }

        $this->view->setLayout('autosms');
    }


    public function indexAction()
    {
        $this->checkLogin();

        $this->view->types = \Autosms\Service\Command::i()->getTypes();
        $this->view->queue = \Autosms\Dao\Command::i()->getCommandsQueue($this->car->getId());
        $this->view->setLayout('autosms');

        $models = array();
        foreach ($this->view->types as $typeId => $typeData) {
            $models[$typeId] = \Autosms\Service\Command::i()->getModel($this->user, $this->car, $typeId);
        }

        $this->view->models = $models;
    }

    public function getQueueAction()
    {
        $this->checkLogin();

        $this->view->queue = \Autosms\Dao\Command::i()->getCommandsQueue($this->car->getId());

        return $this->ajaxSuccess(array(
            'html' => $this->renderView('index/queue'),
        ));
    }

    public function cmdAction()
    {
        $this->checkLogin();

        $cmd = $this->p('cmd');
        $param = $this->p('param');

        $waitingResponse = \Autosms\Dao\Command::i()->getWaitingResponseCommandsQueue($this->car->getId(), 10, 0, 'ASC');
        if (!empty($waitingResponse)) {
            return $this->ajaxError(array(
                'text' => 'В очереди есть каманда, ожидающая завершения',
            ));
        }

        $command = \Autosms\Service\Command::i()->getModel($this->user, $this->car, $cmd, $param);

        if (!$command) {
            return $this->ajaxError(array(
                'text' => 'Неверная команда',
            ));
        }

        if ($this->user->getBalance() < $command->getPrice()) {
            return $this->ajaxError(array(
                'text' => 'Не хватает кредитов',
            ));
        }

        $command = \Autosms\Dao\Command::i()->add($command);
        if (!$command) {
            return $this->ajaxError(array(
                'text' => 'Ошибка добавления команды',
            ));
        }

        $result = $this->sendSms($command->getPhoneNumber(), $command->getText(), $command->getId());
        if (!empty($result)) {
            \Autosms\Dao\SMS::i()->add($command);
            \Autosms\Dao\User::i()->addBalance($this->user->getId(), -$command->getPrice());
            \Autosms\Dao\Command::i()->updateStatus($command->getId(), \Autosms\Service\Command::STATUS_SENT_SMS);
        } else {
            \Autosms\Dao\Command::i()->updateStatus($command->getId(), \Autosms\Service\Command::STATUS_ERROR_SENDING_SMS);
        }

        return $this->ajaxSuccess(array(
            'result' => $result,
        ));
    }

    public function receivedSms($from, $text)
    {
        $phone = '+' . $from;

        $car = \Autosms\Dao\Car::i()->getByPhone($phone);
        if (!empty($car)) {
            $command = \Autosms\Dao\Command::i()->getWaitingResponseCommandsQueue($car->getId(), 5, 0, 'ASC');
            $command = reset($command);

            if ($command) {
                \Autosms\Service\Command::i()->received($command, $text);
            }
        }
    }

    protected function sendSms($to, $text, $id)
    {
        $hash = md5(\Phalcon\DI::getDefault()->config->smdmitry->sendsmssalt . $to . $text);
        $url = "https://sms.smdmitry.com/send/?to=" . urlencode($to)."&text=".urlencode($text)."&hash={$hash}&id={$id}";
        $json = file_get_contents($url);
        $response = json_decode($json);
        return $response ? $response : $json;
    }

    public function testreceiveAction()
    {
        $logfile = \Phalcon\DI::getDefault()->config->smdmitry->smslogfile;
        if ($this->p('clean')) {
            file_put_contents($logfile . '_tmp.txt', '');
        }
        $data = file_get_contents($logfile . '_tmp.txt');
        echo '<pre>';
        echo($data);
        echo '</pre>';
        $this->norender();
    }

    public function receiveAction()
    {
        $logfile = \Phalcon\DI::getDefault()->config->smdmitry->smslogfile;
        $type = $this->p('type');

        $logdata = $this->p('*');
        unset($logdata['text']);
        unset($logdata['type']);
        $logtext = str_replace("\n", ' ', $this->p('text'));
        file_put_contents($logfile . '_tmp.txt', date('Y-m-d H:i:s') . ' - ' .json_encode($logdata) . ': ' . $logtext . "\n", FILE_APPEND);

        $from = $this->p('from');
        $phone = \Phalcon\DI::getDefault()->config->smdmitry->receivesmsphone;
        if ($from != $phone && $from != ('+' . $phone)) {
            $status = $this->p('status');
            $from = $this->p('from');
            $text = $this->p('text');

            if ($status == 'RECEIVED') {
                $message = "{$from}\n{$text}";
            } else {
                $message = "{$status}\n{$from}\n{$text}";
            }
            \Autosms\Service\SlackNotifier::send(\Autosms\Service\SlackNotifier::CH_SLACK_TESTGROUP, $message);

            return $this->ajaxSuccess(array(
                'data' => 'forwarded',
            ));
        }

        if ($type == $logfile) {
            $status = $this->p('status');
            $from = $this->p('from');
            $to = $this->p('to');
            $text = $this->p('text');
            $hash = $this->p('hash');
            $messageId = $this->p('id');

            if ($status == 'RECEIVED' || $status == 'REPORT') {
                $myHash = md5(\Phalcon\DI::getDefault()->config->smdmitry->receivesmssalt . $status . $from . $text);

                if ($myHash != $hash) {
                    return $this->ajaxSuccess(array(
                        'error' => 'wrong hash',
                    ));
                }
            } else if ($status == 'SENT') {
                $myHash = md5(\Phalcon\DI::getDefault()->config->smdmitry->receivesmssalt . $status . $to . $messageId . $text);

                if ($myHash != $hash) {
                    return $this->ajaxSuccess(array(
                        'error' => 'wrong hash',
                    ));
                }
            }

            if ($status == 'RECEIVED') {
                $this->receivedSms($from, $text);
                \Autosms\Dao\SMS::i()->received($from, $text);
            }
            file_put_contents($logfile . '.txt', $status . ':' . $from . ':' . $text . "\n", FILE_APPEND);

            return $this->ajaxSuccess(array(
                'data' => $this->p('*'),
            ));
        }

        return $this->ajaxSuccess(array(
            'error' => 'wrong request',
        ));
    }
}
