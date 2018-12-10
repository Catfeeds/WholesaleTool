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
class Product extends AdminBase
{

    // 验证规则
    protected $rule = [
        'product_name' => 'require|max:255|chsDash',
        //'supplier_ids'   => 'require',
        'product_desc' => 'require|max:512|chsDash',
        'product_sort' => 'require|number',
    ];

    // 验证提示
    protected $message = [
        'product_name.require' => '产品标题不能为空',
        'product_name.chsDash' => '产品标题只能是汉字、字母、数字和下划线及破折号',
        'product_name.max' => '产品标题最多255个字符',
        //'supplier_ids.require'         => '供应商不能为空',
        'product_desc.require' => '产品描述不能为空',
        'product_desc.max' => '产品描述最多512个字符',
        'product_desc.chsDash' => '产品描述只能是汉字、字母、数字和下划线及破折号',
        'product_sort.require' => '产品排序不能为空',
        'product_sort.number'  => '产品排序必须为数字',
    ];

    // 应用场景
    protected $scene = [
        'edit' => ['product_name', 'product_desc', 'product_sort'],
    ];
}
