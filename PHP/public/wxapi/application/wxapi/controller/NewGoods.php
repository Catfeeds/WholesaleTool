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

class NewGoods extends Common
{
    /**
     * @desc 查询供货商所有商品
     * @author: yangsy
     * @time: 2018-08-16 09:50:44
     */
    public function lists_old(){
        $this->checkToken();
        // 查询用户关联 供应商id
        $supplier_id = Db::name('user')->where(['union_id'=>request()->param('unionId')])->value('supplier_id');
        $list = Db::name('product')->where(['status'=>1])->order('product_sort ASC')->select();
        foreach($list as $key => $value){
            unset($list[$key]['status']);
            unset($list[$key]['created_by']);
            unset($list[$key]['is_unliever']);
            unset($list[$key]['created_at']);
            unset($list[$key]['updated_at']);
            unset($list[$key]['id']);
            $list[$key]['product_id'] = $value['id'];
            $list[$key]['product_pic'] = config('host_url').$value['product_pic'];

            if(!Db::name('product_supplier')->where(['supplier_id'=>$supplier_id,'product_id'=>$value['id']])->count()){
                unset($list[$key]);
            }

        }
        $list = array_values($list);
        $this->returnSuccess($list);
    }
    
    /**
     * @desc 获得产品规格接口
     * @author: yangsy
     * @time: 2018-08-16 10:30:36
     */
    public function specList(){
        $list = Db::name('product_spec')->select();
        $data = [];
        foreach($list as $key => $value){
            $data[$key]['sku_unit'] = $value['name'];
            $data[$key]['sku_code'] = $value['id'];
        }
        $data = array_values($data);
        $this->returnSuccess($data);
    }
    
    /**
     * @desc 查询自定义商品
     * @author: yangsy
     * @time: 2018-08-16 11:01:11
     */
    public function myList(){
        $this->checkToken();
        $id = Db::name('user')->where(['union_id'=>request()->post('unionId')])->value('id');
        if(!$id){
            $this->returnError('信息错误');
        }
        $list = Db::connect('db_con')->name('product')->where(['status'=>1,'created_by'=>$id])->select();
        foreach($list as $key => $value){
            unset($list[$key]['status']);
            unset($list[$key]['created_by']);
            unset($list[$key]['is_unliever']);
            unset($list[$key]['created_at']);
            unset($list[$key]['updated_at']);
            unset($list[$key]['id']);
            $list[$key]['product_pic'] = config('host_url').$value['product_pic'];
            $list[$key]['product_id'] = $value['id'];
        }
        $list = array_values($list);
        $this->returnSuccess($list);
    }

    /**
     * @desc 查询终端查询绑定供货商分配商品
     * @author: yangsy
     * @time: 2018-08-16 09:50:44
     */
    public function get_goodslist_1112(){
        $this->checkToken();
        // 查询用户关联 供应商id
        $user_data = Db::name('user')->where(['union_id'=>request()->param('unionId')])->find();
        $where = [
            'client_id'=>$user_data['id'],
            'supplier_id'=>$user_data['supplier_id'],
        ];
        $product_pid = Db::connect('db_con')->name('product_enduser')->where($where)->column('parent_id');
        //家乐品牌商品
        $product_list = Db::name('product')->where(['status'=>1,'pid'=>['in',$product_pid]])->select();
        foreach($product_list as $key => $value){
            unset($product_list[$key]['status']);
            unset($product_list[$key]['created_by']);
            unset($product_list[$key]['is_unliever']);
            unset($product_list[$key]['created_at']);
            unset($product_list[$key]['updated_at']);
            unset($product_list[$key]['id']);
            $product_list[$key]['product_id'] = $value['id'];
            $product_list[$key]['product_pic'] = config('host_url').$value['product_pic'];
            if(!Db::name('product_supplier')->where(['supplier_id'=>$user_data['supplier_id'],'product_id'=>$value['id']])->count()){
                unset($product_list[$key]);
            }
        }
        $product_list = array_values($product_list);

        //其他品牌商品
        $user_product_list = Db::connect('db_con')->name('product')->where(['status'=>1,'pid'=>['in',$product_pid]])->select();
//        dump($user_product_list);
        foreach($user_product_list as $key => $value){
            unset($user_product_list[$key]['status']);
            unset($user_product_list[$key]['created_by']);
            unset($user_product_list[$key]['is_unliever']);
            unset($user_product_list[$key]['created_at']);
            unset($user_product_list[$key]['updated_at']);
            unset($user_product_list[$key]['id']);
            $user_product_list[$key]['product_pic'] = config('host_url').$value['product_pic'];
            $user_product_list[$key]['product_id'] = $value['id'];
        }
        $user_product_list = array_values($user_product_list);
        $list = [
            'product_list'=>$product_list,
            'user_product_list'=>$user_product_list,
        ];
//        dump($list);exit();
        $this->returnSuccess($list);
    }


    /**
     * @desc 查询终端绑定供货商分配商品
     * @author: yangsy
     * @time: 2018-08-16 09:50:44
     */
    public function get_goodslist(){
        $this->checkToken();
        // 查询用户关联 供应商id
        $user_data = Db::name('user')->where(['union_id'=>request()->param('unionId')])->find();
        $where = [
            'client_id'=>$user_data['id'],
            'supplier_id'=>$user_data['supplier_id'],
        ];

        $product_pid_2 = Db::name('product_enduser')->where($where)->column('parent_id');

        $product_pid = Db::connect('db_con')->name('product_enduser')->where($where)->column('parent_id');


        $host_url = Config::get('host_url');

        //家乐品牌商品
        $product_list = ProductParent::with('products')->where(['id'=>['in',$product_pid_2]])->select();
        $products_jiale = [];

        foreach($product_list as $key=>$val){
            $products_jiale[$key]=[
                'product_name'=>$val->product_name,
                'product_parent_id'=>$val->id,
                'product_pic'=>isset($val['products'][0])?$host_url.$val['products'][0]['product_pic']:'',
                'sku_unit'=>[],
            ];
            foreach($val['products'] as $v){
                $products_jiale[$key]['sku_unit'][]=[
                    'product_id' =>$v['id'],
                    'spec_id'=>$v['spec_id'],
                    'spec_name'=>$v['sku_unit'],
                ];
            }
        }
//        dump($products_jiale);
        //其他品牌商品
        $user_product_list  = ProductParentOther::with('products')->where(['id'=>['in',$product_pid]])->select();
        $products_other = [];

        foreach($user_product_list as $key=>$val){
            $products_other[$key]=[
                'product_name'=>$val->product_name,
                'product_parent_id'=>$val->id,
                'product_pic'=>isset($val['products'][0])?$host_url.$val['products'][0]['product_pic']:'',
                'sku_unit'=>[],
            ];
            foreach($val['products'] as $v){
                $products_other[$key]['sku_unit'][]=[
                    'product_id' =>$v['id'],
                    'spec_id'=>$v['spec_id'],
                    'spec_name'=>$v['sku_unit'],
                ];
            }
        }

        $list = [
            'jiale_product_list'=>$products_jiale,
            'other_product_list'=>$products_other,
        ];
        $this->returnSuccess($list);
    }

    /**
     * 预检
     * @return mixed
     */
    private function preCheck(){
        // $this->checkToken();
        $union_id = $this->request->param('unionId');
        $supplier = User::where(['union_id'=>$union_id,'user_type'=>2])->value('supplier_id');
        if(!$supplier){
            $this->returnError('用户不存在');
        }
        return $supplier;
    }
     
     
     
}