<?php

namespace Autosms\Dao;

class User extends \Application\Dao
{
    public function getById($id)
    {
        $sql = "SELECT * FROM autosms_user WHERE user_id = {$id};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\User($data);
    }

    public function addBalance($userId, $value = 0)
    {
        $sign = $value > 0 ? '+' : '-';
        $avalue = abs($value);

        $sql = "UPDATE autosms_user SET balance = balance {$sign} {$avalue} WHERE user_id = {$userId};";
        return $this->db->query($sql);
    }
}
