<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class Order extends Model
{

    protected static function init()
    {
    }
    // 追加属性
    protected $append = [
        'order_status_text',
        'order_type_text',
        'supplier',
        'client',
    ];

    public function getCreatedAtAttr($value, $data)
    {
        $value = $value ? $value : $data['created_at'];
        $list = '-';
        if(!empty($value)){
            $list = date('Y-m-d H:i:s',$value);
        }
        return $list;
    }

    public function getUpdatedAtAttr($value, $data)
    {
        $value = $value ? $value : $data['updated_at'];
        $list = '-';
        if(!empty($value)){
            $list = date('Y-m-d H:i:s',$value);
        }
        return $list;
    }
    public function getOrderTypeList()
    {
        return ['0' => __('typea'), '1' => __('typeb')];
    }
    public function getOrderTypeTextAttr($value,$data)
    {
        $value = $value ? $value : $data['order_type'];
        $list = $this->getOrderTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getOrderStatusList()
    {
        return ['1' => __('statusa'), '2' => __('statusb'), '3' => __('statusc')];
    }
    public function getOrderStatusTextAttr($value,$data)
    {
        $value = $value ? $value : $data['order_status'];
        $list = $this->getOrderStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    /**
     * @desc 绑定供货商id
     */
    public function getSupplierAttr($value,$data)
    {
        $value = $value ? $value : $data['supplier_id'];
        $info = Db::name('supplier')->where(['id'=>$value])->find();
        return $info['name'].'-'.$info['mobile'];
    }

    /**
     * @desc 绑定终端用户id
     */
    public function getClientAttr($value,$data)
    {
        $value = $value ? $value : $data['client_id'];
        $info = Db::name('user')->where(['id'=>$value])->find();
        return $info['nickname'].'-'.$info['mobile'];
    }

}
