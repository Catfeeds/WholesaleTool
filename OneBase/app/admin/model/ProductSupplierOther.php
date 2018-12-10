<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/11/5
 * Time: 15:10
 */

namespace app\admin\model;


class ProductSupplierOther extends AdminBase
{
    protected $connection = 'db_con';
    protected $table = 'tb_product_supplier';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = false;

}