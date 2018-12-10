<?php

namespace app\wxapi\controller;

use think\Controller;
use think\Request;

class ProductSpec extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->checkToken();
//        $unionid   = $this->request->param( 'unionId/s' );
//        $user = \app\common\model\User::where( 'union_id',$unionid )->find();//获取供应商信息

        $data = \app\common\model\ProductSpec::all();
//        $data = \app\common\model\ProductSpec::cache(3600*24)->select();
        if($data){
            $this->returnSuccess(\collection($data)->hidden(['created_at','updated_at'])->toArray(),200,'成功');
        }
        $this->returnSuccess([],400,'系统异常');
    }


}
