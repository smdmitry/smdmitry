<?php

namespace Autosms\Dao;

class SMS extends \Application\Dao
{
    const TYPE_SENT = 1;
    const TYPE_RECEIVED = 2;

    public function add($command)
    {
        $commandId = $command->getId();
        $carId = $command->getCarId();
        $text = $command->getText();
        $phone = $command->getPhoneNumber();
        $type = self::TYPE_SENT;

        $inserted = date('Y-m-d H:i:s');
        $sql = "INSERT INTO autosms_sms(command_id, car_id, phone, `type`, `text`, inserted, updated)
                VALUES ({$commandId}, {$carId}, '{$phone}', '{$type}' , '{$text}', '{$inserted}', '{$inserted}');";
        return $this->db->query($sql);
    }

    public function received($phone, $text)
    {
        $type = self::TYPE_RECEIVED;
        $inserted = date('Y-m-d H:i:s');
        $sql = "INSERT INTO autosms_sms(phone, `type`, `text`, inserted, updated)
                VALUES ('{$phone}', '{$type}' , '{$text}', '{$inserted}', '{$inserted}');";
        return $this->db->query($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM autosms_sms WHERE id = {$id};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\SMS($data);
    }
}
