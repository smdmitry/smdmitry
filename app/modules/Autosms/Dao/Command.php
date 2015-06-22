<?php

namespace Autosms\Dao;

class Command extends \Application\Dao
{
    public function add($model)
    {
        $this->_add($model->getUserId(), $model->getCarId(), $model->getType());
        $id = $this->db->lastInsertId();
        $res = $this->getById($id);
        return $res;
    }

    /*public function add($userId, $command)
    {
        $this->_add($userId, $command['type']);
        $id = $this->db->lastInsertId();
        $res = $this->getById($id);
        return $res;
    }*/

    protected function _add($userId, $carId, $type)
    {
        $inserted = date('Y-m-d H:i:s');
        $sql = "INSERT INTO autosms_commands(user_id, car_id, type, status, inserted, updated)
                VALUES ({$userId}, {$carId}, {$type}, 0, '{$inserted}', '{$inserted}');";
        return $this->db->query($sql);
    }

    public function updateStatus($id, $status, $data = false)
    {
        if (!empty($data)) {
            $data = serialize($data);
        } else {
            $data = '';
        }
        $sql = "UPDATE autosms_commands SET data = '{$data}', status = {$status} WHERE id = {$id};";
        return $this->db->query($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM autosms_commands WHERE id = {$id};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Autosms\Model\Command($data);
    }

    public function getCommandsQueue($carId, $limit = 10, $offset = 0, $sort = 'DESC')
    {
        $sql = "SELECT * FROM autosms_commands WHERE car_id = {$carId} ORDER BY id ". $sort ." LIMIT {$limit} OFFSET {$offset};";
        $data = $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);

        $models = array();
        foreach ($data as $record) {
            $models[$record['id']] = new \Autosms\Model\Command($record);
        }

        return $models;
    }

    public function getWaitingResponseCommandsQueue($carId, $limit = 10, $offset = 0, $sort = 'DESC')
    {
        $sql = "SELECT * FROM autosms_commands WHERE car_id = {$carId} AND status = ". \Autosms\Service\Command::STATUS_SENT_SMS ." ORDER BY id ". $sort ." LIMIT {$limit} OFFSET {$offset};";
        $data = $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);

        $models = array();
        foreach ($data as $record) {
            $models[$record['id']] = new \Autosms\Model\Command($record);
        }

        return $models;
    }

    public function getExpiredQueue($limit = 10, $offset = 0, $sort = 'DESC')
    {
        $expirationDate = date('Y-m-d H:i:s', time() - 3*60);
        $sql = "SELECT * FROM autosms_commands WHERE status IN (". \Autosms\Service\Command::STATUS_SENT_SMS .", ". \Autosms\Service\Command::STATUS_NEW .", ". \Autosms\Service\Command::STATUS_ERROR_SENDING_SMS .") AND updated < '{$expirationDate}' ORDER BY updated ". $sort ." LIMIT {$limit} OFFSET {$offset};";
        $data = $this->db->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);

        $models = array();
        foreach ($data as $record) {
            $models[$record['id']] = new \Autosms\Model\Command($record);
        }

        return $models;
    }
}
