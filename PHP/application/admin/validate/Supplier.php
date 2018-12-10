<?php

namespace app\admin\validate;

use think\Validate;

class Supplier extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name' => 'require',
        'mobile' => 'require,length:11',
        'password' => 'require',

    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name' => '请输入正确用户名',
        'mobile' => '请输入11位手机号',
        'password' => '请输入用户密码',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name','mobile','password'],
        'edit' => ['name','mobile'],
    ];
    
}
