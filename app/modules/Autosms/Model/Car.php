<?php

namespace Autosms\Model;

use Phalcon\Mvc\Model;

class Car extends \Application\Model
{
    public function getId()
    {
        return $this->data['car_id'];
    }

    public function getPhone()
    {
        return $this->data['phone'];
    }

    public function getUserId()
    {
        return $this->data['user_id'];
    }

    public function getCode()
    {
        if (empty($this->data['code'])) {
            return '15*69*28';
        }
        return $this->data['code'];
    }

    public function getPin()
    {
        return $this->data['pin'];
    }
}