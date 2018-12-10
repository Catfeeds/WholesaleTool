<?php

namespace app\common\model;

use think\Model;

class ProductEnduserOther extends Model
{
    protected $connection = 'db_con';
    protected $table = 'tb_product_enduser';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;
}
