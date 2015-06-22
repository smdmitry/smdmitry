<?php

namespace Shorten\Service;

class Base
{
    const URL_PREG = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';

    /**
     * Converts a base 10 number to any other base.
     *
     * @param int $val   Decimal number
     * @param int $base  Base to convert to. If null, will use strlen($chars) as base.
     * @param string $chars Characters used in base, arranged lowest to highest. Must be at least $base characters long.
     *
     * @return string    Number converted to specified base
     */
    public static function base_encode($val, $base = 62, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if(!isset($base)) $base = strlen($chars);
        $str = '';

        do {
            $m = bcmod($val, $base);
            $str = $chars[$m] . $str;
            $val = bcdiv(bcsub($val, $m), $base);
        } while(bccomp($val,0) > 0);

        return $str;
    }

    /**
     * Convert a number from any base to base 10
     *
     * @param string $str   Number
     * @param int $base  Base of number. If null, will use strlen($chars) as base.
     * @param string $chars Characters use in base, arranged lowest to highest. Must be at least $base characters long.
     *
     * @return int    Number converted to base 10
     */
    public static function base_decode($str, $base = 62, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if (!isset($base)) $base = strlen($chars);
        $len = strlen($str);
        $val = 0;
        $arr = array_flip(str_split($chars));

        for ($i = 0; $i < $len; ++$i) {
            $val = bcadd($val, bcmul($arr[$str[$i]], bcpow($base, $len - $i - 1)));
        }

        return $val;
    }

    public static function agoText($tm, $rcs = 0) {
        $cur_tm = time();
        $dif = $cur_tm-$tm;
        $pds = array('second','minute','hour','day','week','month','year','decade');
        $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);

        for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
        $no = floor($no);
        if($no <> 1)
            $pds[$v] .='s';
        $x = sprintf("%d %s ",$no,$pds[$v]);
        if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0))
            $x .= time_ago($_tm);
        return $x;
    }

    public static function strEllipsis($str, $len, $ellipsis = '...')
    {
        if (strlen($str) > $len) {
            return substr($str, 0, $len - strlen($ellipsis)) . $ellipsis;
        }

        return $str;
    }
}