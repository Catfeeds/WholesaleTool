<?php

namespace app\admin\model;

use think\Db;
use think\Model;

class User extends Model
{

    // 表名
    protected $name = 'user';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'gender_text',
        'user_type_text',
        'supplier'
    ];

    protected static function init()
    {
        self::beforeUpdate(function ($row) {
            $changed = $row->getChangedData();
            //如果有修改密码
            if (isset($changed['password'])) {
                if ($changed['password']) {
                    $salt = \fast\Random::alnum();
                    $row->password = \app\common\library\Auth::instance()->getEncryptPassword($changed['password'], $salt);
                    $row->salt = $salt;
                } else {
                    unset($row->password);
                }
            }
        });
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getGenderList()
    {
        return ['1' => __('Male'), '0' => __('unknown'),'2'=>'FeMale'];
    }

    public function getGenderTextAttr($value, $data)
    {
        $value = $value ? $value : $data['gender'];
        $list = $this->getGenderList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getUserTypeList()
    {
        return ['1' => __('supplier'), '0' => __('unknown'),'2'=>__('client')];
    }

    public function getUserTypeTextAttr($value,$data)
    {
        $value = $value ? $value : $data['user_type'];
        $list = $this->getUserTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    
    /**
     * @desc 绑定供货商id
     * @author: yangsy
     * @time: 2018-08-18 21:25:29
     */
    public function getSupplierAttr($value,$data)
    {
        $value = $value ? $value : $data['supplier_id'];
        $info = Db::name('supplier')->where(['id'=>$value])->find();
        return $info['name'].'-'.$info['mobile'];
    }

}
