<?php
// +----------------------------------------------------------------------
// | desc: 空控制器处理
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018-08-11 16:28:15
// +----------------------------------------------------------------------
namespace app\wxapi\controller;


class Error
{
    /**
     * @desc 定义空操作返回方法
     * @author: yangsy
     * @time: 2018-08-11 16:10:56
     */
    public function _empty(){
        $da['code'] = '404';
        $da['msg'] = '请求接口不存在';
        $da['data'] = '';
        echo json_encode($da); exit;
    }
}