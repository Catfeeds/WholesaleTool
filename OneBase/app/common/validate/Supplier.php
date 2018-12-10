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
 * 供应商验证器
 */
class Supplier extends ValidateBase
{

    // 验证规则
    protected $rule = [
        'name'     => 'require|chsDash|max:32',
        'mobile'   => [ 'require','unique:supplier' ],//通过构造方法(补充)
        'password' => 'require',
        'address'  => 'require|chsDash',
        'desc'     => 'require',
        'desc2'    => 'require',
        'id'       => 'require|number',
    ];

    // 验证提示
    protected $message = [
        'name.require'     => '用户名不能为空',
        'name.chsDash'     => '用户名只能是汉字、字母、数字和下划线_及破折号-',
        'name.max'         => '用户名最大长度为32位',
        'mobile.require'   => '手机号必须',
        'mobile.regex'     => '手机号不合法',
        'mobile.unique'    => '手机号已存在:1',
        'password.require' => '密码必填',
        'address.require'  => '地址必填',
        'address.chsDash'  => '地址只能是汉字、字母、数字和下划线_及破折号-',
        'desc.require'     => '优惠信息必填',
        'desc2.require'    => '优惠信息2必填',
        'id.require'       => 'ID必填',
        'id.number'        => 'ID必须为数字',
    ];

    // 应用场景
    protected $scene = [
        'add'    => [ 'name','mobile','password','address','desc','desc2' ],//新增
        'update' => [ 'id','name','mobile' => [ 'require','checkUpdateMobile' ],'address','desc','desc2' ],//更新
        'index'  => [ 'name' => 'max:32|chsDash' ],//列表
    ];

    public function __construct( array $rules = [],array $message = [],array $field = [] )
    {
        parent::__construct( $rules,$message,$field );
        $this->rule[ 'mobile' ][ 'regex' ]              = \think\Config::get( 'regex_mobile' );
        $this->scene[ 'update' ][ 'mobile' ][ 'regex' ] = \think\Config::get( 'regex_mobile' );
    }

    public function checkUpdateMobile( $mobile,$rule,$data )
    {
        $Supplier = \app\common\model\Supplier::where( 'mobile',$mobile )->find();

        if( !$Supplier || $Supplier->id === (int)$data[ 'id' ] ){
            return true;
        }
        return '手机号码已存在:2';
    }


}
