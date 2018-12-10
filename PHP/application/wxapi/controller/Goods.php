<?php
// +----------------------------------------------------------------------
// | desc: Goods.php
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/16 9:50
// +----------------------------------------------------------------------

namespace app\wxapi\controller;


use think\Db;
use think\paginator\driver\Bootstrap;

class Goods extends Common
{
    /**
     * @desc 查询供货商所有商品
     * @author: yangsy
     * @time: 2018-08-16 09:50:44
     */
    public function lists(){
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
     
     
     
}