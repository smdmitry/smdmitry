<?php

namespace Shorten\Model;

use Phalcon\Mvc\Model;

class Url extends \Application\Model
{
    public function getId()
    {
        return $this->data['id'];
    }

    public function getHash()
    {
        return \Shorten\Service\Base::base_encode($this->getId());
    }

    public function getShortUrl($withScheme = true)
    {
        return ($withScheme ? 'http://' : '') . $this->getShortDomain() . '/' . $this->getHash();
    }

    public function getLongUrl($withScheme = true)
    {
        if (!$withScheme) {
            $url = $this->data['url'];
            $pos = strpos($url, '://');
            return substr($url, $pos + 3);
        }

        return $this->data['url'];
    }

    public function getInsertedAgo()
    {
        return \Shorten\Service\Base::agoText(strtotime($this->data['inserted'])) . ' ago';
    }

    public function getHitCount()
    {
        return $this->data['hit_count'];
    }

    public function getPreviewUrl()
    {
        $prefix = PRODUCTION ? 'http://smdmitry.com' : 'http://im4.me';
        return $prefix . '/url2png/shot.php?download=1&w=500&url=' . urlencode($this->getLongUrl());
        //return '/static/images/robot.png';
    }

    protected function getShortDomain()
    {
        return PRODUCTION ? 'smd.im' : 'im4.me';
    }
}