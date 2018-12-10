<?php

namespace app\admin\controller;

use think\Db;
use think\Loader;
use think\Request;

class Supplier extends AdminBase
{
    /**
     * 显示资源列表
     */
    public function index()
    {
        if( $this->param ){
            $validate = Loader::validate( 'Supplier' );
            if( !$validate->scene( 'index' )->check( $this->param ) ){
                $this->jump( RESULT_ERROR,$validate->getError() );
            }
        }
        $where = $this->logicSupplier->getWhere( $this->param );
        $this->assign( 'list',$this->logicSupplier->getSupplierList( $where ) );
        return $this->fetch();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        if( IS_POST && $this->param ){
            $validate = Loader::validate( 'Supplier' );
//            $validate->extend([
//                'mobileUnique'
//
////                'mobileUnique'=> [$this,$this->param]
//            ]);
            if( !$validate->scene( 'add' )->check( $this->param ) ){
                $this->jump( RESULT_ERROR,$validate->getError() );
            }
            $this->jump( $this->logicSupplier->add( $this->param ) );
        }
        return $this->fetch();
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit()
    {
        if( IS_POST ){
            $validate = Loader::validate( 'Supplier' );
            if( !$validate->scene( 'update' )->check( $this->param ) ){
                $this->jump( RESULT_ERROR,$validate->getError() );
            }
            $this->jump( $this->logicSupplier->myUpdate( $this->param ) );

        }

        if( isset( $this->param[ 'id' ] ) ){
            $this->assign( 'list',$this->logicSupplier->edit( $this->param[ 'id' ] ) );
        }
        return $this->fetch();
    }


    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function del()
    {
        if( isset( $this->param[ 'id' ] ) ){
            $this->jump( $this->logicSupplier->del( $this->param[ 'id' ] ) );
        }
    }
}
