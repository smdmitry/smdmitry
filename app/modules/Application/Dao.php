<?php

namespace Application;

class Dao
{
    protected $db;

    private function __construct()
    {
        $this->db = \Phalcon\DI::getDefault()->getDb();
    }
    public static function i() {static $i; $i = new static(); return $i;}
}
