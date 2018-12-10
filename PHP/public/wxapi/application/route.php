<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__'                     => [
        'name' => '\w+',
    ],
    '[hello]'                         => [
        ':id'   => [ 'index/hello',[ 'method' => 'get' ],[ 'id' => '\d+' ] ],
        ':name' => [ 'index/hello',[ 'method' => 'post' ] ],
    ],
    // api 接口路由规则
    // 供货商登录接口
    // 'ufs_supplier_login/index' => ['wxapi/login/login',['method'=>'post']],
    // 终端用户进入页面,获取open_id接口
    'ufs_register/index'              => [ 'wxapi/login/eu_login',[ 'method' => 'post' ] ],
    'ufs_register/index1'             => [ 'wxapi/login/eu_login1',[ 'method' => 'post' ] ],
    // 注册并验证手机验证码
    'ufs_login/index'                 => [ 'wxapi/login/eu_register',[ 'method' => 'post' ] ],
    // 供货商用户登录
    'ufs_supplier_login/index'        => [ 'wxapi/login/supplierLogin',[ 'method' => 'post' ] ],

    //获取天御js验证码url
    'ufs_verification/index'          => [ 'wxapi/sms/get_url',[ 'method' => 'post' ] ],
    //验证天御ticket并发送验证码
    'ufs_verification/captcha_verify' => [ 'wxapi/sms/check_captcha',[ 'method' => 'post' ] ],
    //获取附近餐厅列表
    'ufs_dining/index'                => [ 'wxapi/dining/index',[ 'method' => 'post' ] ],
    //获取地区列表
    'ufs_area/index'                  => [ 'wxapi/dining/get_area_list',[ 'method' => 'post' ] ],

    /** ==  订单，订单接口  == **/
    //新建订单
    'ufs_order/insert'                => [ 'wxapi/order/create_order',[ 'method' => 'post' ] ],
    //上传图片
    'ufs_order/upload_img'            => [ 'wxapi/order/upload_img',[ 'method' => 'post' ] ],
    //获取买家订单列表
    'ufs_order/index'                 => [ 'wxapi/order/get_list_client',[ 'method' => 'post' ] ],
    //获取卖家订单列表
    'ufs_order/supplier_order'        => [ 'wxapi/order/get_list_supplier',[ 'method' => 'post' ] ],
    //编辑订单
    'ufs_order/submit'                => [ 'wxapi/order/edit_order',[ 'method' => 'post' ] ],
    //取消订单
    'ufs_order/cancel'                => [ 'wxapi/order/cancel_order',[ 'method' => 'post' ] ],
    //确认订单
    'ufs_order/agree_order'           => [ 'wxapi/order/agree_order',[ 'method' => 'post' ] ],
    //订单详情
    'ufs_order/detail'                => [ 'wxapi/order/order_detail',[ 'method' => 'post' ] ],
    //删除订单产品
    'ufs_order/del_product'           => [ 'wxapi/order/del_order',[ 'method' => 'post' ] ],

    /** ==  商品，产品接口  == **/
    // 获取产品规格列表
    'ufs_product/spec'                => [ 'wxapi/goods/specList',[ 'method' => 'post' ] ],
    // 查询供货商所有商品
    'ufs_product/index'               => [ 'wxapi/goods/lists',[ 'method' => 'post' ] ],
    // 查询历史商品
    'ufs_product/history'             => [ 'wxapi/goods/myList',[ 'method' => 'post' ] ],

    //获取餐厅列表
    'ufs_supplier/dining'             => [ 'wxapi/dining/get_restaurant_list',[ 'method' => 'post' ] ],
    //终端获取供应商信息
    'ufs_supplier/info'               => [ 'wxapi/dining/get_supplier',[ 'method' => 'post' ] ],
    //店员获取供应商信息
    'ufs_supplier/get_info'           => [ 'wxapi/dining/get_supplier_info',[ 'method' => 'post' ] ],
    'ufs_supplier/get_dining'         => [ 'wxapi/dining/get_keyword_dining',[ 'method' => 'post' ] ],
    'ufs_supplier/get_basic_info'     => [ 'wxapi/dining/get_supplier_info1',[ 'method' => 'post' ] ],
    // 获取token
    'ufs_wxapi/get_token'             => [ 'wxapi/index/getToken',[ 'method' => 'post' ] ],
    'ufs_wxapi/get_qrcode'            => [ 'wxapi/index/index',[ 'method' => 'post' ] ],

    // 找回密码等接口
    'ufs_password/reset'              => [ 'wxapi/login/resetPassword',[ 'method' => 'post' ] ], // 重置
    'ufs_password/retrieve'           => [ 'wxapi/login/retrievePassword',[ 'method' => 'post' ] ], // 找回
    'ufs_password/sms'                => [ 'wxapi/login/smsSend',[ 'method' => 'post' ] ], // 验证码

    'ufs_password/supplier' => [ 'wxapi/login/supplier',[ 'method' => 'post' ] ], // 获取供货商信息

    //设置不显示引导层
    'set_not_show_layer'    => [ 'wxapi/User/setNotShowLayer',[ 'method' => 'post' ] ],

    //测试服务器路由
    'test'                  => [ 'wxapi/Test/index',[ 'method' => 'get' ] ],
    'test2'                 => [ 'wxapi/Test/test',[ 'method' => 'get' ] ],
    //显示用户
    'ufs_supplier/ufs_supplier_users'    => [ 'wxapi/User/showSupplierUser',[ 'method' => 'post' ] ],
    //绑定/解绑用户与商品
    'ufs_supplier/ufs_bind_unbind'    => [ 'wxapi/ProductEnduser/bindOrONbind',[ 'method' => 'post' ] ],
    //获取所有规格
    'ufs_supplier/get_spec'    => [ 'wxapi/ProductSpec/index',[ 'method' => 'post' ] ],
    //新建商品
    'ufs_supplier/ufs_create_product'    => [ 'wxapi/Product/create',[ 'method' => 'post' ] ],


    //终端新建订单
    'ufs_order/add_order' => ['wxapi/NewOrder/create_order',['method'=>'post']],
    //终端获取商品列表
    'ufs_order/get_goodslist' => ['wxapi/NewGoods/get_goodslist',['method'=>'post']],
    //终端获取商品规格列表
    'ufs_order/get_specList' => ['wxapi/NewgGoods/specList',['method'=>'post']],

	//供应商获取家乐
    'ufs_supplier/ufs_products'=>['wxapi/Goods/getUfsProducts',[ 'method' => 'post' ] ],

    //供应商获取其他
    'ufs_supplier/other_products'=>['wxapi/Goods/getOtherProducts',[ 'method' => 'post' ] ],

    //供应商修改其他商品
    'ufs_supplier/update_other_product'=>['wxapi/Goods/updateOtherProduct',[ 'method' => 'post' ] ],

    //终端检索所有商品
    'ufs_order/search_goods'=>['wxapi/NewProduct/all_product',[ 'method' => 'post' ] ],

    //供应商查询商品是否存在
    'ufs_supplier/other_product_exist'=>['wxapi/Goods/otherProductExists',[ 'method' => 'post' ] ],

    //终端新建订单(新增产品有家乐)
    'ufs_order/add_order1121' => ['wxapi/NewOrder1121/create_order',['method'=>'post']],



];

