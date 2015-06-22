<?php

namespace Application\Utils;

class Hash
{
    public static function genRandHash($len)
    {
        $result = '';
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strlen = strlen($chars);

        for ($i = 1; $i <= $len; ++$i) {
            $index = mt_rand(0, $strlen - 1);
            $result .= $chars[$index];
        }

        return $result;
    }
}
