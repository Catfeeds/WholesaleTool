<?php
// +---------------------------------------------------------------------+
// | OneBase    | [ WE CAN DO IT JUST THINK ]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | Bigotry <3162875@qq.com>                               |
// +---------------------------------------------------------------------+
// | Repository | https://gitee.com/Bigotry/OneBase                      |
// +---------------------------------------------------------------------+

namespace app\common\validate;

/**
 * 会员验证器
 */
class User extends ValidateBase
{
    
    // 验证规则
    protected $rule =   [
        'search_user_type'      => 'in:0,1,2,999',
        'search_nickname'      => 'min:1|max:50',
    ];
    
    // 验证提示
    protected $message  =   [
        'search_user_type.in'      => '用户类型错误',
        'search_nickname.min'       => '用户昵称错误',
        'search_nickname.max'       => '用户昵称过长',
    ];

    // 应用场景
    protected $scene = [
        
        'list'       =>  ['search_user_type','search_nickname'],//列表页面
    ];
}
