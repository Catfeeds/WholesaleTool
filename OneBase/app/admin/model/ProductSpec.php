<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/11/2
 * Time: 15:10
 */

namespace app\admin\model;

class ProductSpec extends AdminBase
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}