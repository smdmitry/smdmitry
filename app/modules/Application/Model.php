<?php

namespace Application;

class Model
{
    protected $data;

    public function __construct($data = array())
    {
        $this->data = $data;
    }
}
