<?php
namespace app\index\controller;

use app\common\model\ProductParent;
use app\common\model\ProductParentOther;
use app\common\model\ProductSupplier;
use app\common\model\User;
use think\Db;
use think\Log;

class Index
{
    public function index()
    {
        return 'aaabbbcccdddeee';

        //return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }

    /**
     * @desc 初始化
     * @author: Nicole.An
     * @time: 2018-08-16 09:50:44
     */
//2.产品表 :product(双库) 新增spec_id/pid
//    ###  2.1在product_parent新建数据,将pid同步到product表(副库相同供应商产品去重)
//    ###  2.2 spec_id
//(双库)sql:update tb_product m  set m.spec_id= (select id from tb_product_spec mp where mp.name= m.sku_unit);

//update tb_product m  set m.pid= (select id from tb_product_parent mp where mp.product_name= m.product_name);

    public function product()
    {
        $list = Db::name('product')->where(['status'=>1])->order('product_sort ASC') ->group('product_name')->select();
//        dump($list);
        foreach($list as $key => $value){
            //获取商品对照关系(以商品名称为准)
            $product_parent_id = Db::name( "product_parent" )->where( array( "product_name" => $value[ 'product_name' ]) )->value( 'id' );
            if( !$product_parent_id ){
                $product_parent    = [ 'product_name' => $value[ 'product_name' ],"supplier_id" => $value['supplier_id'], "created_by" => 0,"by_supplier" => 0,"created_at" => time(),"updated_at" => time() ];
                $product_parent_id = Db::name( 'product_parent' )->insertGetId( $product_parent );
                dump($product_parent_id);
            }
        }
        $sql = "update tb_product m  set m.pid= (select id from tb_product_parent mp where mp.product_name= m.product_name)";
        Db::name( 'product' )->query($sql);

    }

    public function product_other()
    {
        $list = Db::connect('db_con')->name('product')->where(['status'=>1])->group('product_name')->select();
        foreach($list as $key => $value){
            //获取商品对照关系(以商品名称为准)
            $product_parent_id = Db::connect('db_con')->name( "product_parent" )->where( array( "product_name" => $value[ 'product_name' ]) )->value( 'id' );
//            dump($product_parent_id);
            if( !$product_parent_id ){
                $product_parent    = [ 'product_name' => $value[ 'product_name' ],"supplier_id" => $value['supplier_id'], "created_by" => 0,"by_supplier" => 0,"created_at" => time(),"updated_at" => time() ];
                $product_parent_id = Db::connect('db_con')->name( 'product_parent' )->insertGetId( $product_parent );
                dump($product_parent_id);
            }
        }

        $sql = "update tb_product m  set m.pid= (select id from tb_product_parent mp where mp.product_name= m.product_name)";
        Db::connect('db_con')->name( 'product' )->query($sql);

    }

    public function productOthers(){
        //终端与供应商的key-val
        $user = Db::name('user')->where(['user_type'=>1,'supplier_id'=>['<>',0]])->column('supplier_id','id');

        //先把商品的供应商序做相应的复制
        $res = Db::connect('db_con')->name('product')->select();

        foreach($res as $key=>$val){
            if($val['created_by']!=0 && isset($user[$val['created_by']])){
                Db::connect('db_con')->name('product')->update(['supplier_id'=>$user[$val['created_by']],'id'=>$val['id']]);
            }else{
                Db::connect('db_con')->name('product')->delete($val['id']);
                Log::info('----删除了商品----');
                Log::info($val);
            }
        }

        //把商品根据供应商和商品名分组加入parent
        $parent = Db::connect('db_con')->name('product')->where('supplier_id','<>',0)->group('product_name,supplier_id')->select();
        foreach($parent as $val){
            $data = [
                'product_name'=>$val['product_name'],
                'supplier_id'=>$val['supplier_id'],
                'by_supplier'=>0,
                'created_by'=>$val['created_by'],
                'created_at'=>$val['created_at'],
                'updated_at'=>$val['updated_at'],
            ];
            //新增
            $parent_id = Db::connect('db_con')->name('product_parent')->insertGetId($data);
            //回填pid
            Db::connect('db_con')->name('product')->where(['supplier_id'=>$val['supplier_id'],'product_name'=>$val['product_name']])->update(['pid'=>$parent_id]);
        }


        $del = [];
        $ori_data = [];
        $insert_data = [];
        //重新获取一次
        $res = Db::connect('db_con')->name('product')->select();
        $time = time();
        foreach($res as $val){
            //产品名+规则+供应商id应该是唯一的
            $this_key = $val['product_name'].'-'.$val['sku_unit'].'-'.$val['supplier_id'];

            if(isset($ori_data[$this_key])){
                //重复的删除
                $del[] = $val['id'];
            }else{
                $ori_data[$this_key] = $val;
            }
            //需要加入enduser的数据
            $insert_data[] = ['client_id'=>$val['created_by'],'parent_id'=>$val['pid'],'supplier_id'=>$val['supplier_id'],'created_at'=>$time];
        }

        //删除多余商品
        Db::connect('db_con')->name('product')->delete($del);
        //添加对应关系
        Db::connect('db_con')->name('product_enduser')->insertAll($insert_data);
    }

}
