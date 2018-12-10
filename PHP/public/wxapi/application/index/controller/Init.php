<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;

class Init extends Controller
{

    public function index()
    {

        //主库1.tb_product_supplier :product_id换成product_parent_id

        //1.1查询家乐产品与供应商关系表
        $jialeData  = Db::name( 'product_supplier' )->select();
        $productIds = join( ',',\array_column( $jialeData,'product_id' ) );

        //1.2查询product表
        $products = Db::name( 'product' )->where( 'id','in',$productIds )->column( 'pid','id' );
        //产品与供应商对应关系中存在，但是产品表中不存在的产品id
        $notEx = [];

        $newProductSupplierData = [];//储存新数据
        $time                   = \time();
        foreach( $jialeData as $k => $v ){
            if(!isset($products[ $v[ 'product_id' ] ]) || !$products[ $v[ 'product_id' ]] ){
                $notEx[$k][] = $v;
                continue;
            }

            $newProductSupplierData[ $k ][ 'supplier_id' ] = $v[ 'supplier_id' ];
            $newProductSupplierData[ $k ][ 'product_id' ]  = $products[ $v[ 'product_id' ] ];
            $newProductSupplierData[ $k ][ 'created_at' ]  = $time;
        }
        Log::record($notEx,'家乐产品不存在的id');
        Log::record($products,'家乐产品');

        $newProductSupplierData = $this->arrayUnique2d( $newProductSupplierData );//去重后的新数据

        try{
            Db::startTrans();//开启事务
            //先删除原表数据
            Db::name( 'product_supplier' )->where( 'id','>=',1 )->delete();
            model( 'product_supplier' )->saveAll( $newProductSupplierData );
            Db::commit();//提交
            echo '1.tb_product_supplier:商品id换成parent_id  <span style="color: #008800">成功<span>';
            $this->add_1();//添加家乐商品
//            $this->add_2();//添加副库商品
        }catch( Exception $e ){
            echo '1.tb_product_supplier:商品id换成parent_id  <span style="color: #880000">失败<span>';
            Log::record( '下面是product_id换成product_parent_id的错误信息','log' );
            Log::record( $e,'log' );

            Db::rollback();//回滚
        }
    }

    /**
     * @description 默认添加所有家乐产品
     */
    public function add_1()
    {
        $time       = \time();
        $clientData = Db::name( 'user' )->where( 'user_type',1 )->where( 'supplier_id','<>',0 )->select();

        try{
            Db::startTrans();
            foreach( $clientData as $k => $v ){
                $addData     = [];
                $productData = Db::name( 'product_supplier' )->where( 'supplier_id',$v[ 'supplier_id' ] )->select();
                if( !$productData ){
                    continue;
                }
                foreach( $productData as $k1 => $v1 ){
                    $addData[ 'client_id' ]   = $v[ 'id' ];//用户id
                    $addData[ 'parent_id' ]   = $v1[ 'product_id' ];//供应商拥有的额商品id
                    $addData[ 'supplier_id' ] = $v[ 'supplier_id' ];//供应商id
                    $addData[ 'created_at' ]  = $time;

                    Db::name( 'product_enduser' )->insert( $addData );
                }
            }
            Db::commit();
            echo '2.默认添加所有家乐产品  <span style="color: #008800">成功<span>';
        }catch( Exception $e ){

            echo '2.默认添加所有家乐产品  <span style="color: #008800">失败<span>:'.$e->getMessage();
            Db::rollback();
        }

    }


    public function add_2()
    {
        $time       = \time();
        $clientData = Db::name( 'user' )->where( 'user_type',1 )->where( 'supplier_id','<>',0 )->select();

        try{
            Db::startTrans();
            foreach( $clientData as $k => $v ){
                $addData     = [];
                $productData = Db::connect( 'db_con' )->name( 'product_parent' )->where( 'created_by',$v[ 'id' ] )->select();
                if( !$productData ){
                    continue;
                }
                foreach( $productData as $k1 => $v1 ){
                    $addData[ 'client_id' ]   = $v[ 'id' ];//用户id
                    $addData[ 'parent_id' ]   = $v1[ 'id' ];//当前用户创建的商品id
                    $addData[ 'supplier_id' ] = $v1[ 'supplier_id' ];//供应商id
                    $addData[ 'created_at' ]  = $time;

                    Db::connect( 'db_con' )->name( 'product_enduser' )->insert( $addData );
                }
            }
            Db::commit();
            echo '2.默认添加用户创建商品  <span style="color: #008800">成功<span>';
        }catch( Exception $e ){
            echo '2.默认添加用户创建商品  <span style="color: #008800">失败<span>';
            Db::rollback();
        }


    }


    /**
     * @description
     * @param array $arr2d
     * @return mixed
     */
    public function arrayUnique2d( array $arr2d,$joinString = '@@@##$#@' )
    {
        //合并
        $temp        = [];
        $return_info = [];
        $flag        = true;
        $arrKeys     = array();
        foreach( $arr2d as $k => $v ){
            $temp[ $k ] = join( $joinString,$v );
            //获取二位数组的key,确保二位数组的key完全一致
            if( $flag ){
                $flag    = false;
                $arrKeys = array_keys( $v );   //获取二维数组里面所有的键
            }
        }
        $temp = array_unique( $temp );
        //分割
        foreach( $temp as $k => $v ){
            $m          = 0;//依次获取第二维数组的value
            $temp[ $k ] = explode( $joinString,$v );

            foreach( $arrKeys as $key => $value ){    //遍历出来
                $return_info[ $k ][ $value ] = $temp[ $k ][ $m ];
                $m++;
            }
        }
        return $return_info;
    }


}
