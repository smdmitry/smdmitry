<?php

namespace Autosms\Model;

use Phalcon\Mvc\Model;

class User extends \Application\Model
{
    public function getId()
    {
        return $this->data['user_id'];
    }

    public function getPhone()
    {
        return $this->data['phone'];
    }

    public function getBalance()
    {
        return $this->data['balance'];
    }
}