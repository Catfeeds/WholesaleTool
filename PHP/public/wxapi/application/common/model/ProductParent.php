<?php

namespace app\common\model;

use think\Model;

class ProductParent extends Model
{
    //
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function products(){
        return $this->hasMany('Product','pid','id')->field('id,pid,product_pic,sku_unit,spec_id,status');
    }
}
