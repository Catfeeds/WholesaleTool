<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use OSS\OssClient;
use OSS\Core\OssException;
//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    }else{
        return json_decode($data,true);
    }
}

/**
 * 实例化阿里云OSS
 * @return object 实例化得到的对象
 * @return 此步作为共用对象，可提供给多个模块统一调用
 */
function new_oss(){
    Vendor('aliyuncs.autoload');
    //获取配置项，并赋值给对象$config
    $config=config('aliyun_oss');
    //实例化OSS
    $oss=new \OSS\OssClient($config['KeyId'],$config['KeySecret'],$config['Endpoint']);
    return $oss;
}
/**
 * 上传指定的本地文件内容
 *
 * @param OssClient $ossClient OSSClient实例
 * @param string $object 上传的文件名称
 * @param string $Path 本地文件路径
 * @return null
 */
function uploadFile($object,$Path)
{
    $config = config('aliyun_oss');
    //try 要执行的代码,如果代码执行过程中某一条语句发生异常,则程序直接跳转到CATCH块中,由$e收集错误信息和显示
    try {
        //没忘吧，new_oss()是我们上一步所写的自定义函数
        $ossClient = new_oss();
        //uploadFile的上传方法
        $ossClient->uploadFile($config['Bucket'], $object, $Path);
    } catch (OssException $e) {
        //如果出错这里返回报错信息
        return $e->getMessage();
    }
    //否则，完成上传操作
    return true;
}
function httpPost($url,$data) {
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, false);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //执行命令
    $re = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $re;
}


/**
 * @description 从二维数组中提取指定多列(没做验证,谨慎传值,报错不管)
 * @param array $data 原数组(二维)
 * @param array $column 获取/排除 的列
 * @param bool $type true:获取column,false:排除指定列
 * @return array 新数组(二维)
 */
function my_array_column( array $data,array $column,$type = true )
{
    $newData = [];
    foreach( $data as $k => $v ){
        foreach( $v as $k2 => $v2 ){
            if( $type ){
                if( in_array( $k2,$column ) ){
                    if( $type ){
                        $newData[ $k ][ $k2 ] = $v2;
                        continue;
                    }
                }
                continue;
            }

            if( !in_array( $k2,$column ) ){
                $newData[ $k ][ $k2 ] = $v2;
                continue;
            }
        }
    }
    return $newData;
}
