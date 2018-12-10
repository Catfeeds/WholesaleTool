<?php

namespace app\common\model;

use think\Model;

class Supplier extends Model
{
    //以下是订单小程序的方法
    public function user()
    {
        return $this->hasOne('User','supplier_id');
    }
}
