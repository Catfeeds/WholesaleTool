<?php

namespace app\wxapi\controller;

use app\common\model\ProductOther;
use app\common\model\ProductParentOther;
use think\Config;
use think\Db;
use think\Exception;
use think\Log;

class Product extends Common
{
    /**
     * @description 绑定解绑商品
     */
    public function create()
    {

       $this->checkToken();
        $unionid = $this->request->param( 'unionId/s' );
//        $user    = \app\common\model\User::where( 'id',11 )->find();//获取供应商信息
       $user = \app\common\model\User::where( 'union_id',$unionid )->find();//获取供应商信息
        if( $user->user_type != 2 ){
            $this->returnSuccess( [],400,'不是供应商,暂无权限' );
        }
        $createData = $this->request->param( 'product_data/a' );
        //获取规格表数据

        $specData  = \app\common\model\ProductSpec::cache( 3600 * 24 )->select();
        $specData  = \array_column( \collection( $specData )->toArray(),null,'id' );
        $errorData = [];

        Db::connect( 'db_con' )->startTrans();
        try{
            //添加总表商品
            foreach( $createData as $k1 => $v1 ){
                //前台传过来的商品名称
                $productName = \htmlspecialchars( trim( $v1[ 'name' ] ) );

                $productParentData                   = [];
                $productParentData[ 'product_name' ] = $productName;
                $productParentData[ 'supplier_id' ]  = $user->supplier_id;
                $productParentData[ 'by_supplier' ]  = 1;
                $productParentData[ 'created_by' ]   = $user->id;
                $productParent                       = ProductParentOther::where( $productParentData )->find();
                //判断商品是否存在,存在则取出id,不存在新建
                if( $productParent ){
                    $pid = $productParent;
                }else{
                    $pid = ProductParentOther::create( $productParentData );
                }
                //添加规格表商品
                if(!\is_array($v1[ 'spec_id' ])){
                    Db::connect( 'db_con' )->rollback();
                    $this->returnSuccess( [],400,'spec_id不是数组,参数错误' );
                }

                foreach( $v1[ 'spec_id' ] as $k2 => $v2 ){

                    if( !isset( $specData[ $v2 ] ) ){
                        Db::connect( 'db_con' )->rollback();
                        $this->returnSuccess( [],400,'规格id:' . $v2 . ' 不存在' );
                    }

                    //判断商品规格表是否存在同样规格,存在则跳过,不存在则新增
                    $productData = ProductOther::where( 'supplier_id',$user->supplier_id )->where( 'spec_id',$specData[ $v2 ][ 'id' ] )->where( 'pid',$pid->id )->find();
                    if( $productData ){
                        $errorData[] = $productParentData[ 'product_name' ] . '的规格:' . $specData[ $v2 ] . '已存在!';
                        continue;
                    }

                    $productSpecData                   = [];
                    $productSpecData[ 'product_name' ] = $productName;
                    $productSpecData[ 'sku_code' ]     = 1;//不知道填啥
                    $productSpecData[ 'sku_unit' ]     = $specData[ $v2 ][ 'name' ];
                    $productSpecData[ 'spec_id' ]      = $specData[ $v2 ][ 'id' ];
                    $productSpecData[ 'product_desc' ] = '';//商品描述
                    $productSpecData[ 'status' ]       = 1;
                    $productSpecData[ 'created_by' ]   = $user->id;
                    $productSpecData[ 'supplier_id' ]  = $user->supplier_id;
                    $productSpecData[ 'by_supplier' ]  = 1;
                    $productSpecData[ 'pid' ]          = $pid->id;//父级id
                    //新增
                    ProductOther::create( $productSpecData );

                }
            }

        }catch( Exception $e ){
            Db::connect( 'db_con' )->rollback();
            //错误日志
            $logData                 = [];
            $logData[ 'getMessage' ] = $e->getMessage();
            $logData[ 'getLine' ]    = $e->getLine();
            $logData[ 'getData' ]    = $e->getData();
            $logData[ 'data' ]       = $createData;
            Log::record( $logData,'create_product_error' );

            $this->returnSuccess( [],400,'添加失败,请稍后再试' );
        }
        Db::connect( 'db_con' )->commit();

        $msg = '添加成功';
        if( $errorData ){
            $msg = join( "\r\n",$errorData );
        }
        $this->returnSuccess( [],200,$msg );
    }
}
