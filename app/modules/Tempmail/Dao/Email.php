<?php

namespace Tempmail\Dao;

class Email extends \Application\Dao
{
    public function getAll()
    {
        $sql = "SELECT * FROM new_mail ORDER BY mail_id DESC LIMIT 10;";
        $data = $this->db->fetchAll($sql);

        $models = array();
        foreach ($data as $record) {
            $models[] = new \Tempmail\Model\Email($record);
        }

        return $models;
    }
}
