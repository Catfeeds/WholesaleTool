<?php

namespace app\wxapi\controller;

use app\common\model\ProductEnduser as ProductEnduserModel;
use app\common\model\ProductEnduserOther;
use app\common\model\ProductParent;
use app\common\model\ProductParentOther;
use app\common\model\ProductSupplier;
use think\Config;
use think\Controller;
use think\Exception;
use think\Request;

class ProductEnduser extends Common
{
    public function bindOrONbind()
    {
        $this->checkToken();
        $unionid   = $this->request->param( 'unionId/s' );
        $type = $this->request->param( 'type/d' );

        if( !\in_array( $type,Config::get( 'allow_type' ) ) ){
            $this->returnSuccess( [],400,'type错误' );
        }
        $product_parent_id = $this->request->param( 'product_parent_id/d' );
        if( !$product_parent_id ){
            $this->returnSuccess( [],400,'商品id错误' );
        }

        $checkArr = [ 1 => 'checkProductId_1',2 => 'checkProductId_2' ];
        $func     = $checkArr[ $type ];

        $user = \app\common\model\User::where( 'union_id',$unionid )->find();//获取供应商信息

        $user_id = $this->request->param( 'user_id/d' );//获取终端用户信息

        $client = \app\common\model\User::get( $user_id );

        if( !$client || $client->supplier_id !== $user->supplier_id ){
            $this->returnSuccess( [],400,'该终端用户未绑定您的供应商' );
        }


        if( !$this->$func( $user->supplier_id,$product_parent_id ) ){
            $this->returnSuccess( [],400,'无权对该商品进行操作' );
        }

        //找对象
        $objArr    = [ 1 => 'app\common\model\ProductEnduser',2 => 'app\common\model\ProductEnduserOther' ];//1-主库model,2-副库model
        $className = $objArr[ $type ];
        $obj       = new $className();
        $data      = $obj->where( [ 'client_id' => $user_id,'parent_id' => $product_parent_id ] )->find();

        try{
            if( $data ){
                $data->delete();
                $this->returnSuccess( [],200,'解绑成功' );
            }

            $obj::insert( [
                'client_id'   => $user_id,
                'parent_id'   => $product_parent_id,
                'supplier_id' => $user->supplier_id,
                'created_at'  => \time(),
            ] );

            $this->returnSuccess( [],200,'绑定成功' );
        }catch(Exception $e){
            //此处可以记录日志
            //...
            $this->returnSuccess( [],400,'系统异常' );
        }

    }

    /**
     * @description 检测家乐产品 权限
     * @param $supplier_id
     * @param $product_parent_id
     * @return bool
     */
    private function checkProductId_1( $supplier_id,$product_parent_id )
    {
        $obj = ProductSupplier::get( [ 'supplier_id' => $supplier_id,'product_id' => $product_parent_id ] );
        if( !$obj ){
            return false;
        }
        return true;
    }

    /**
     * @description 检测其他品牌 权限
     * @param $supplier_id
     * @param $product_parent_id
     * @return bool
     */
    private function checkProductId_2( $supplier_id,$product_parent_id )
    {
        $obj = ProductParentOther::get( $product_parent_id );

        if( !$obj || $obj->supplier_id !== $supplier_id ){
            return false;
        }
        return true;
    }
}
