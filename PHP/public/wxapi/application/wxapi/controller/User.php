<?php
// +----------------------------------------------------------------------
// | desc: User.php 用户管理
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/12 19:39
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use app\common\model\ProductEnduser;
use think\Cache;
use think\Config;
use think\Db;
use think\Exception;
use think\Loader;

class User extends Common
{
    /**
     * @description 2018年10月8日16:31:05 添加设置不显示引导层
     */
    public function setNotShowLayer()
    {
        $this->checkToken();
        $unionid = request()->param( 'unionId' );

        $user = \app\common\model\User::get( [ 'union_id' => $unionid ] );
        if( !$user->show_layer ){
            $this->returnSuccess( [],400,'无需更改' );
        }
        $orderNum = 0;
        if( $user->supplier_id ){
            $orderNum = Db::name( 'order' )->where( 'supplier_id',$user->supplier_id )->count();
        }

        if( $orderNum === 0 ){
            $this->returnSuccess( [],400,'无需更改' );
        }
        //设置不显示
        \app\common\model\User::where( [ 'union_id' => $unionid ] )->update( [ 'show_layer' => 0 ] );

        $this->returnSuccess( [],200,'更改成功' );
    }

    /**
     * @description 查看用户接口
     */
    public function showSupplierUser()
    {

        $this->checkToken();
        $unionid   = $this->request->param( 'unionId/s' );
        $type      = $this->request->param( 'type/d' );//1,家乐产品,2-其他产品
        $product_parent_id      = $this->request->param( 'product_parent_id/d' );//商品id
        if(!$product_parent_id){
            $this->returnSuccess( [],400,'product_parent_id 不存在' );
        }
//        $type = 2;
        if( !\in_array( $type,Config::get('allow_type') ) ){
            $this->returnSuccess( [],400,'type参数错误' );
        }
//        //判断缓存
//        Config::get( 'cache_switch' ) && $data = Cache::tag( Config::get( 'data_cache_tag' ) )->get( 'ufs_supplier_users',null );
//        if( !\is_null( $data ) ){
//            $this->returnSuccess( $data,200,'成功' );
//        }

        $modelArr = [ 1 => 'userProductEnduser',2 => 'userProductEnduserOther' ];

        //无缓存或过期
//        $data = \app\common\model\User::with( 'userList,' . $modelArr[ $type ] )->where( 'id',11 )->find();
        $data = \app\common\model\User::with( 'userList,' . $modelArr[ $type ] )->where( 'union_id',$unionid )->find();
        if( $data ){

            $userProductEnduser = Loader::parseName($modelArr[$type]);//将驼峰转换下划线

            $data            = $data->toArray();

            $data = $this->buildArray($data['user_list'],$data[$userProductEnduser]);
//            Config::get( 'cache_switch' ) && Cache::tag( Config::get( 'data_cache_tag' ) )->set( 'ufs_supplier_users',$data,5 );//5秒钟缓存
            $this->returnSuccess( $data,200,'成功' );
        }

        $this->returnSuccess( [],400,'系统错误' );


    }


    private function buildArray($userList,$userProductEndUser)
    {
        $array = [];
        $userProductEndUser = \array_column($userProductEndUser,'supplier_id','client_id');
        foreach($userList as $k => $v){

            $array[$k]['user_id'] = $v['user_id'];
            $array[$k]['restaurant_name'] = $v['restaurant_name'];
            $array[$k]['seleted'] = false;//是否选中 字段

            if(isset($userProductEndUser[$v['user_id']])){
                $array[$k]['seleted'] = true;
            }
        }
        return $array;

    }



}