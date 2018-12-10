<?php
// +----------------------------------------------------------------------
// | desc: Goods.php
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/16 9:50
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use app\common\model\ProductParent;
use app\common\model\ProductParentOther;
use app\common\model\ProductSupplier;
use app\common\model\User;
use think\Db;
use think\Config;
use think\paginator\driver\Bootstrap;

class NewProduct extends Common
{
    /**
     * @desc 终端查询根据产品名称模糊查询所有商品
     * @author: Nicole.An
     * @time: 2018-11-21 10:50:00
     */
    public function all_product(){
//        $this->checkToken();
        //家乐品牌商品
        $product_name = request()->param('product_name');
        $supplier_id = request()->param('supplier_id');
        $where = 'supplier_id = '.$supplier_id.' AND product_name LIKE "%'.$product_name.'%" or product_name LIKE "'.$product_name.'%" or product_name LIKE "%'.$product_name.'"';
        $product_parent_list = Db::name('product_parent')->where($where)->select();
        foreach($product_parent_list as $key => $value){
            $product_parent_list[$key]['is_unliever'] = 0;
        }
        //其他品牌商品
        $other_product_parent_list = Db::connect('db_con')->name('product_parent')->where($where)->select();
        foreach($other_product_parent_list as $key => $value){
            $other_product_parent_list[$key]['is_unliever'] = 1;
        }
        $list = [
            'product_list'=>$product_parent_list,
            'other_product_list'=>$other_product_parent_list,
        ];
        $this->returnSuccess($list);
    }



     
}