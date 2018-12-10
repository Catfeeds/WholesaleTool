<?php

namespace app\common\model;

use think\Model;

class Supplier extends Model
{

    // 定义时间戳字段名
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
//    public function getIsFirstAttr($value)
//    {
//        $is_first = [
//            '是','否'
//        ];
//        return $is_first[$value];
//    }
}
