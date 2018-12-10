<?php

namespace app\common\model;

use think\Model;

class ProductSupplier extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;

    public function product(){
        return $this->hasOne('Product','id','product_id');
    }
}
