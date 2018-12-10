<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/19
 * Time: 13:48
 */

namespace app\common\library;

use think\Cache;

class PrintCode
{
    //生成code
    public static function generalPrintCode($expire){
        $time_until = time()+intval($expire);
        return uniqid($time_until,true);
    }

    //检查code并删除
    public static function checkPrintCode($code){
        $code = Cache::pull($code);
        if(!$code) return false;
        return true;
    }

    //生成并存储code
    public static function savePrintCode($expire=300){
        $code = self::generalPrintCode($expire);
        if(Cache::set($code,$code,$expire)){
            return $code;
        }
        return false;
    }
}