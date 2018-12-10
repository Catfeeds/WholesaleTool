<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    //sso接口域名
    'sso_http_url'  => "https://stage-oauth.unileverfoodsolutions.com.cn",
    //sso接口app_key
    'sso_app_key'   => "Oy3acp7Z00AOIVJpJDeXFifjhlPlHBgm",
    'cache_switch' => true,//缓存开关
    'data_cache_tag' => 'ufs_order_data_cache_tag',//缓存tag,方便测试清理缓存
    'allow_type' => [1,2],//允许的类型,1-家乐,2-其他品牌


];
