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
use think\Exception;
use think\Request;
use think\Url;

/**
 * 会员逻辑
 */
class User extends AdminBase
{
    /**
     * 获取会员列表
     */
    public function getUserList( $where = [],$order = '',$paginate = DB_LIST_ROWS )
    {
        $request = Request::instance();
        //搜索条件
        $query = [
            'search_user_type' => $request->param( 'search_user_type' ),
            'search_nickname'  => $request->param( 'search_nickname' ),
        ];
        $page = max(1,$request->param( 'page' ));
        $data  = \app\common\model\User::with( 'supplier' )->where( $where )->order( $order )->paginate( $paginate,false,[ 'page'=>$page,'query' => $query ] );
        return $data;
    }


    public function del( $ids )
    {
        $list  = \app\common\model\User::where( 'id','in',$ids )->select();
        $count = 0;
        try{
            foreach( $list as $k => $v ){
                //1.删除订单表
                $orderListObj = Order::where( 'client_id',$v->id )->select();
                //如果存在订单
                if( $orderListObj ){
                    foreach( $orderListObj as $orderKey => $order ){
                        //删除订单详细表(主库)
                        OrderDetail::destroy( function( $query ) use ( $order ){
                            $query->where( 'order_id',$order->id );
                        } );

                        //删除订单详细表(副库)
                        OrderDetailOther::destroy( function( $query ) use ( $order ){
                            $query->where( 'order_id',$order->id );
                        } );

                        //删除order表的数据
                        $order->delete();
                    }
                }
                //2.删除产品表(只删除副库,如果失败,事务无法回滚)
                ProductOther::destroy( function( $query ) use ( $v ){
                    $query->where( 'created_by',$v->id );
                } );

                //3.删除产品价格表(主库)
                ProductPrice::destroy( function( $query ) use ( $v ){
                    $query->where( 'client_id',$v->id );
                } );
                //3.删除产品价格表(副库)
                ProductPriceOther::destroy( function( $query ) use ( $v ){
                    $query->where( 'client_id',$v->id );
                } );
                //4.删除用户token
                UserToken::destroy( function( $query ) use ( $v ){
                    $query->where( 'unionid',$v->union_id );
                } );

                //5.删除终端与供应商关联表
                ClientSupplier::destroy( function( $query ) use ( $v ){
                    $query->where( 'client_id',$v->id );
                } );
                //6.删除用户表
                $count += $v->delete();
            }
        }catch( Exception $e ){
            return [RESULT_ERROR, '删除失败'];
        }
        if ($count) {
            action_log('删除', '删除会员，where：' . $ids);
        }
        return $count ? [RESULT_SUCCESS, '删除成功'] : [RESULT_ERROR, '删除失败'];
    }

    /**
     * 获取会员列表搜索条件
     */
    public function getWhere( $data = [] )
    {
        $where = [];

        if( isset( $data[ 'search_nickname' ] ) ){
            $where[ 'nickname' ] = [ 'like','%' . trim( $data[ 'search_nickname' ] ) . '%' ];
        }
        if( isset( $data[ 'search_user_type' ] ) && (int)$data[ 'search_user_type' ] !== 999 ){
            $where[ 'user_type' ] = (int)$data[ 'search_user_type' ];
        }
        return $where;
    }


    public function userAdd($post)
    {

    }


}
