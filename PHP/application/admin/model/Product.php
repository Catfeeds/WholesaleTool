<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class Product extends Model
{
    protected static function init()
    {
    }

    public function getCreatedAtAttr($value, $data)
    {
        $value = $value ? $value : $data['created_at'];
        $list = '-';
        if(!empty($value)){
            $list = date('Y-m-d H:i:s',$value);
        }
        return $list;
    }

    public function getUpdatedAtAttr($value, $data)
    {
        $value = $value ? $value : $data['updated_at'];
        $list = '-';
        if(!empty($value)){
            $list = date('Y-m-d H:i:s',$value);
        }
        return $list;
    }
}
