<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/18
 * Time: 15:24
 */

namespace app\common\library;

use app\common\model\Config;
use think\Db;

class Order
{
    public static function getOrderDetail($order_id){
        if(empty($order_id)) return false;
        $order =Db::name('order')->where(['id'=>$order_id])->find();
        if(!$order) return false;
        $order_amount = 0;

        if($order['order_type']==0){
            //正常订单，联合利华产品
            $detail_1 = Db::name('order_detail')->where(["order_id"=>$order_id])->select();
            foreach($detail_1 as $k1=>$v1) {
                if ($v1['is_unlieve'] == 1) {
                    $detail_product = Db::name('product')->where(["id" => $v1['product_id']])->find();
                    $detail_1[$k1]['uploaded_img'] = Config::get('host_url') . $detail_product['product_pic'];
                }
            }
            //正常订单，非联合利华产品
            $detail_0 = Db::connect('db_con')->name("order_detail")->where(["order_id"=>$order_id])->select();
            foreach($detail_0 as $k1=>$v1) {
                if ($v1['is_unlieve'] == 1) {
                    $detail_product = Db::name('product')->where(["id" => $v1['product_id']])->find();
                    $detail_0[$k1]['uploaded_img'] = Config::get('host_url') . $detail_product['product_pic'];
                }
            }
            $detail = array_merge($detail_1,$detail_0);
            $order['product_list'] = $detail;
            foreach($order['product_list'] as $k => $v){
                $order_amount += $v['product_price']*$v['product_count'];
            }
        }else{
            //图片订单
            $detail = Db::connect('db_con')->name("order_detail")->where(["order_id"=>$order_id])->find();
            $order['product_list'] = json_decode($detail['uploaded_img'],true);
        }

        //获取买家的餐厅
        $order_user = Db::name("user") -> where(array("id"=>$order['client_id'])) -> find();
        $order['restaurant_name'] = $order_user['restaurant_name'];
        $order['province'] = $order_user['province'];
        $order['city'] = $order_user['city'];
        $order['district'] = $order_user['district'];
        $order['address'] = $order_user['address'];
        $order['nickname'] = $order_user['nickname'];
        /*if($order_user['type']==5){
            $order['nickname'] = $order_user['restaurant_name'];
        }else{
            $order['nickname'] = $order_user['nickname'];
        }*/

        $order['mobile'] = $order_user['mobile'];
        $order['order_amount'] =  round($order_amount, 2);

        //供应商信息
        $supplier = Db::name('supplier')->where(['id'=>$order['supplier_id']])->find();
        if($supplier){
            $order['supplier_name'] = $supplier['name'];
            $order['supplier_mobile'] = $supplier['mobile'];
            $order['supplier_address'] = $supplier['address'];
        }

        return $order;

    }

    public static function getOrderHtml($order_id){
        $order = self::getOrderDetail($order_id);

        if(!$order) return false;

        $str = '<!DOCTYPE html>';
        $str.= '<html>';
        $str.= '<head>';
        $str.= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $str.= '</head>';
        $str.= '</html>';
        $str.= '<body>';
        $str.= '<h1 style="text-align: center">订单</h1>';
        $str.= '<p style="">'.$order['restaurant_name'].'</p>';
        $str.= '<p style="">收货人:'.$order['nickname'].' <label style="margin-left:10px">'.$order['mobile'].'<label></p>';
        $str.= '<p style="">收货地址：'.$order['address'].'</p>';
        $str.= '<p style="">下单时间：'.date('Y年m月d日 H:i:s',$order['created_at']).'</p>';
        $str.= '<p style="">订单编号：'.$order['order_number'].'</p>';
        $str .='<hr>';

        foreach($order['product_list'] as $key=>$val){
            $str.= '<p>'.($key+1).'.'.$val['product_name'].'</p>';
            $str.= '<p>';
            $str.= '<span style="width:5%;"></span>';
            $str.= '<span style="width:20%;">'.$val['product_price'].'</span>';
            $str.= '<span style="width:20%;">元/'.$val['sku_unit'].'</span>';
            $str.= '<span style="width:50%;">'.$val['product_count'].$val['sku_unit'].'</span>';
        }
        $str .='<hr>';
        $str .='<p>总金额：'.$order['order_amount'].'</p>';
        $str .='<p>备注：'.$order['comments'].'</p>';
        $str .='<br>';
        $str .='<p>批发商：'.$order['supplier_name'].'</p>';
        $str .='<p>联系电话：'.$order['supplier_mobile'].'</p>';
        $str .='<p>联系电话：'.$order['supplier_address'].'</p>';
        $str .='<br>';
        $str .='</body>';
        $str .='</html>';

        return $str;
    }
}