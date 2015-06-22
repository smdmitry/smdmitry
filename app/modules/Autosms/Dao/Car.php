<?php

namespace Autosms\Dao;

class Car extends \Application\Dao
{
    public function getById($id)
    {
        $sql = "SELECT * FROM autosms_car WHERE car_id = {$id};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\Car($data);
    }

    public function getByOwner($userId)
    {
        $sql = "SELECT * FROM autosms_car WHERE user_id = {$userId};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\Car($data);
    }

    public function getByPhone($phone)
    {
        $sql = "SELECT * FROM autosms_car WHERE phone = {$phone};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\Car($data);
    }
}
