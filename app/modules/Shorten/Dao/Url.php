<?php

namespace Shorten\Dao;

class Url extends \Application\Dao
{
    public function addUrl($url)
    {
        $count = 0; $len = 3; // len 8 = 2^48, 9 = 2^54, 10 = 2^60
        do {
            $hash = \Application\Utils\Hash::genRandHash($len);
            $id = \Shorten\Service\Base::base_decode($hash);
            $record = $this->getById($id);

            if ($count++ > 5) {
                $len++;
            }
        } while (!empty($record) && $len <= 10);

        $this->_addUrl($id, $url);

        $res = $this->getById($id);

        if ($res) {
            \Application\BackgroundWorker::i()->addJob(function() use ($res) {
                $data = file_get_contents($res->getPreviewUrl());
                var_dump($res->getPreviewUrl());
                var_dump($data);
            });
        }

        return $res;
    }

    protected function _addUrl($id, $url)
    {
        $inserted = date('Y-m-d H:i:s');
        $sql = "INSERT INTO url(id, url, inserted) VALUES ({$id}, '{$url}', '{$inserted}');";
        return $this->db->query($sql);
    }

    public function addHit($id, $count = 1)
    {
        $count = (int)$count;
        $sql = "UPDATE url SET hit_count = hit_count + {$count} WHERE id = {$id};";
        return $this->db->query($sql);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM url WHERE id = {$id};";
        $data = $this->db->fetchOne($sql);

        if (empty($data)) return false;

        return new \Shorten\Model\Url($data);
    }

    public function getByHash($hash)
    {
        $id = \Shorten\Service\Base::base_decode($hash);
        return $this->getById($id);
    }

    public function getByUserId($userId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM url WHERE user_id = {$userId} ORDER BY inserted DESC LIMIT {$limit} OFFSET {$offset};";
        $data = $this->db->fetchAll($sql);

        $models = array();
        foreach ($data as $record) {
            $models[$record['id']] = new \Shorten\Model\Url($record);
        }

        return $models;
    }

    public function getCountByUserId($userId)
    {
        $sql = "SELECT COUNT(*) FROM url WHERE user_id = {$userId};";
        $data = $this->db->fetchOne($sql);
        return (int)$data['COUNT(*)'];
    }
}
