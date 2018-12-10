<?php

namespace app\common\model;

use think\Cache;
use think\Model;

class PrintCode extends Model
{
    public static function generalPrintCode($expire=300){
        $time_until = time()+intval($expire);
        return uniqid($time_until,true);
    }

    public static function checkPrintCode($code){
        $time_until = substr($code,0,10);
        $check_res = true;
        //是否过期
        if(time()>$time_until) $check_res=false;

        $code = self::destroy(['code'=>$code]);

        if(!$code) $check_res=false;
        return $check_res;
    }

}
