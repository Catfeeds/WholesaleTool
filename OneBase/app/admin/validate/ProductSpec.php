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

/**
 * 产品验证器
 */
class ProductSpec extends AdminBase
{

    // 验证规则
    protected $rule = [
        'name' => 'require|unique:product_spec|max:10|chsDash',
    ];

    // 验证提示
    protected $message = [
        'name.require' => '单位名称不能为空',
        'name.unique'  => '单位名称已存在',
        'name.max'     => '单位名称不多于10个字符',
        'name.chsDash' => '单位名称只能是汉字、字母、数字和下划线及破折号',
    ];

    // 应用场景
    protected $scene = [
        'edit' => ['name'],
    ];
}
