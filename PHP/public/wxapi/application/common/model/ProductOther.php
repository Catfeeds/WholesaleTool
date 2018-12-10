<?php

namespace app\common\model;

use think\Model;

class ProductOther extends Model
{
    protected $connection = 'db_con';
    protected $table = 'tb_product';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    public function getBySupplierAttr($value)
    {
        $status = [0=>'终端用户',1=>'供应商'];
        return $status[$value];
    }
}
