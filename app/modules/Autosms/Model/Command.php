<?php

namespace Autosms\Model;

use Phalcon\Mvc\Model;

class Command extends \Application\Model
{
    public function getCar()
    {
        return \Autosms\Dao\Car::i()->getById($this->getCarId());
    }

    public function getUser()
    {
        return \Autosms\Dao\User::i()->getById($this->getUserId());
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function getCarId()
    {
        return $this->data['car_id'];
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getType()
    {
        return $this->data['type'];
    }

    public function getData()
    {
        $data = $this->data['data'];
        if (!empty($data)) return unserialize($data);
        return false;
    }

    public function getTitle()
    {
        $text = 'Неизвестная команда';

        $type = $this->data['type'];
        $types = \Autosms\Service\Command::i()->getTypes();

        $text = isset($types[$type]) ? $types[$type]['title'] : '';
        return $text;
    }

    public function getStatusText()
    {
        $text = 'Неизвестное состояние';

        $status = $this->data['status'];
        if ($status == \Autosms\Service\Command::STATUS_NEW) {
            $text = 'Принята';
        } else if ($status == \Autosms\Service\Command::STATUS_ERROR_SENDING_SMS) {
            $text = 'Ошибка отправки команды';
        } else if ($status == \Autosms\Service\Command::STATUS_FINISHED) {
            $text = 'Выполнена успешно';
        } else if ($status == \Autosms\Service\Command::STATUS_SENT_SMS) {
            $text = 'Ожидание ответа ('.  (time() - strtotime($this->getUpdated())) .' сек)';
        } else if ($status == \Autosms\Service\Command::STATUS_EXPIRED) {
            $text = 'Ответ не получен';
        } else if ($status == \Autosms\Service\Command::STATUS_UNEXPECTED_ANSWER) {
            $text = 'Неверный формат ответа';
        } else if ($status == \Autosms\Service\Command::STATUS_WRONG_CODE) {
            $text = 'Неверный код';
        }

        return $text;
    }

    public function getInserted()
    {
        return $this->data['inserted'];
    }

    public function getUpdated()
    {
        return $this->data['updated'];
    }

    public function getPhoneNumber()
    {
        return $this->getCar()->getPhone();
    }

    public function getText()
    {
        $config = \Autosms\Service\Command::i()->getConfigFromType($this->getType());
        $text = $config['text'];

        if (!empty($config['need_code'])) {
            $text = $text . " " . $this->getCar()->getCode();
        }

        /*if ($this->getCar()->getPin()) {
            $text = $text . " " . $this->getCar()->getPin();
        } else if ($this->getCar()->getCode()) {
            $text = $text . " " . $this->getCar()->getCode();
        }*/

        return $text;
    }

    public function getPrice()
    {
        return 100;
    }
}