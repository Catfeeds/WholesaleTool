<?php

namespace app\common\model;

use think\Model;

class ProductParentOther extends Model
{
    protected $connection = 'db_con';
    protected $table = 'tb_product_parent';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function products(){
        return $this->hasMany('ProductOther','pid','id')->field('id,pid,product_pic,sku_unit,spec_id,status');
    }


    public function getBySupplierAttr($value)
    {
        $status = [0=>'终端用户',1=>'供应商'];
        return $status[$value];
    }
}
