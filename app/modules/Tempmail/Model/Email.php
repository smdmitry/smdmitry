<?php

namespace Tempmail\Model;

use Phalcon\Mvc\Model;

class Email extends \Application\Model
{
    public function getBody()
    {
        return gzuncompress($this->data['mail']);
    }
}