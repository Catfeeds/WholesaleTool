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
namespace app\admin\validate;
use think\Env;


/**
 * 登录验证器
 */
class Login extends AdminBase
{
    // 验证规则
    protected $rule = [];

    // 验证提示
    protected $message = [

        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
        'verify.require'   => '验证码不能为空',
        'verify.captcha'   => '验证码不正确',
    ];

    // 应用场景
    protected $scene = [

        'admin' => [ 'username','password','verify' ],
    ];


    public function __construct( array $rules = [],array $message = [],array $field = [] )
    {
        parent::__construct( $rules,$message,$field );
        $this->rule = [
            'username' => 'require',
            'password' => 'require',
            'verify'   => Env::get( 'admin.verify','require|captcha' )
        ];

    }
}
