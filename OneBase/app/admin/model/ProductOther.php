<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/11/5
 * Time: 15:10
 */

namespace app\admin\model;

class ProductOther extends AdminBase
{
    protected $connection = 'db_con';
    protected $table = 'tb_product';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

}