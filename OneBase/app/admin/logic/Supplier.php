<?php
// +---------------------------------------------------------------------+
// | OneBase    | [ WE CAN DO IT JUST THINK ]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | Bigotry <3162875@qq.com>                               |
// +---------------------------------------------------------------------+
// | Repository | https://gitee.com/Bigotry/OneBase                      |
// +---------------------------------------------------------------------+

namespace app\admin\logic;

use app\common\model\ClientSupplier;
use app\common\model\Order;
use app\common\model\OrderDetail;
use app\common\model\OrderDetailOther;
use app\common\model\ProductOther;
use app\common\model\ProductPrice;
use app\common\model\ProductPriceOther;
use app\common\model\UserToken;
use think\Config;
use think\Exception;
use think\Request;
use think\Url;

/**
 * 会员逻辑
 */
class Supplier extends AdminBase
{
    /**
     * 获取会员列表
     */
    public function getSupplierList( $where = [],$order = '',$paginate = DB_LIST_ROWS )
    {
        $request = Request::instance();
        //搜索条件
        $query = [
            'name' => $request->param( 'name' ),
        ];
        $page  = max( 1,$request->param( 'page' ) );
        $data  = \app\common\model\Supplier::where( $where )->order( $order )->paginate( $paginate,false,[ 'page' => $page,'query' => $query ] );
        return $data;
    }


    public function del( $ids )
    {
        $list  = \app\common\model\Supplier::where( 'id','in',$ids )->select();
        $count = 0;
        try{
            foreach( $list as $k => $v ){
                //查询当前供应商是否有绑定的用户,有则不让删除
                $user = \app\common\model\User::where( 'supplier_id',$v->id )->select();
                if( $user ){
                    $userIds = join( ',',\array_column( \collection( $user )->toArray(),'id' ) );
                    return [ RESULT_ERROR,'无法删除,请先删除该供应商下的用户:' . $userIds ];
                }
                $count += $v->delete();

            }
        }catch( Exception $e ){
            return [ RESULT_ERROR,'删除失败2' ];
        }
        if( $count ){
            action_log( '删除','删除供应商，where：' . $ids );
        }
        return $count ? [ RESULT_SUCCESS,'删除成功' ] : [ RESULT_ERROR,'删除失败' ];
    }

    /**
     * 获取会员列表搜索条件
     */
    public function getWhere( $data = [] )
    {
        $where = [];

        if( isset( $data[ 'name' ] ) ){
            $where[ 'name' ] = [ 'like','%' . trim( $data[ 'name' ] ) . '%' ];
        }

        return $where;
    }


    public function add( $post )
    {
        $password   = $post[ 'password' ];
        $iterations = 1000;
        $salt       = Config::get( 'supplier_salt' );
        $password   = hash_pbkdf2( "sha256",$password,$salt,$iterations,32 );

        $data = [
            'name'       => $post[ 'name' ],
            'mobile'     => $post[ 'mobile' ],
            'password'   => $password,
            'address'    => $post[ 'address' ],
            'desc'       => $post[ 'desc' ],
            'desc2'      => $post[ 'desc2' ],
            'created_at' => time(),
        ];

        $Supplier = \app\common\model\Supplier::create( $data );
        if( $Supplier->id > 0 ){
            return [ RESULT_SUCCESS,'新增成功' ];
        }
        return [ RESULT_ERROR,'新增失败' ];
    }

    public function edit( $id )
    {
        $id = (int)$id;
        if( !$id ){
            return [ RESULT_ERROR,'参数错误' ];
        }

        return \app\common\model\Supplier::get( $id );

    }

    public function myUpdate( $post )
    {
        $updateData             = [];
        $updateData[ 'name' ]   = $post[ 'name' ];
        $updateData[ 'mobile' ] = $post[ 'mobile' ];
        if( $post[ 'password' ] ){
            $iterations               = 1000;
            $salt                     = Config::get( 'supplier_salt' );
            $password                 = hash_pbkdf2( "sha256",$post[ 'password' ],$salt,$iterations,32 );
            $updateData[ 'password' ] = $password;
        }
        $updateData[ 'address' ] = $post[ 'address' ];
        $updateData[ 'desc' ]    = $post[ 'desc' ];
        $updateData[ 'desc2' ]   = $post[ 'desc2' ];

        try{
            \app\common\model\Supplier::update( $updateData,[ 'id' => $post[ 'id' ] ] );
            return [ RESULT_SUCCESS,'更新成功' ];
        }catch( Exception $e ){
            return [ RESULT_ERROR,'更新失败' . $e->getMessage() ];
        }
    }


}
