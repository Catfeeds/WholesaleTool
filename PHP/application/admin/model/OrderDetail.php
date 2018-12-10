<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class OrderDetail extends Model
{

    protected static function init()
    {
    }
    // 追加属性
    protected $append = [
        'unlieve_text'
    ];


    public function getIsUnlieveList()
    {
        return ['0' => __('unlieveb'), '1' => __('unlievea')];
    }
    public function getUnlieveTextAttr($value,$data)
    {
        $value = $value ? $value : $data['is_unlieve'];
        $list = $this->getIsUnlieveList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
