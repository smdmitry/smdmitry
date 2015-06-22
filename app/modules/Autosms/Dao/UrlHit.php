<?php

namespace Shorten\Dao;

class UrlHit extends \Application\Dao
{
    public function addHit($urlId, $ip, $referer, $ua, $userId = 0)
    {
        $ua = htmlentities($ua);
        $ip = htmlentities($ip);
        $referer = htmlentities($referer);

        $sql = "INSERT INTO url_hit(url_id, ip, referer, user_agent, user_id) VALUES ({$urlId}, '{$ip}', '{$referer}', '{$ua}', {$userId});";
        return $this->db->query($sql);
    }
}