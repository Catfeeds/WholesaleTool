<?php
// +----------------------------------------------------------------------
// | desc: Goods.php
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/16 9:50
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use app\common\model\ProductEnduserOther;
use app\common\model\ProductOther;
use app\common\model\ProductParent;
use app\common\model\ProductParentOther;
use app\common\model\ProductSupplier;
use app\common\model\User;
use think\Config;
use think\Db;
use think\Log;
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

    /**
     * 获取ufs产品
     */
    public function getUfsProducts(){
        $supplier = $this->preCheck();

        $res = ProductSupplier::where('supplier_id',$supplier)->column('product_id');
        if(empty($res)) $this->returnSuccess([]);

        $data  = ProductParent::with('products')->where('id','in',implode(',',$res))->select();
        $products = [];
        $host_url = Config::get('host_url');
        foreach($data as $key=>$val){

            $products[$key]=[
                'product_name'=>$val->product_name,
                'product_pic'=>isset($val['products'][0])?$host_url.$val['products'][0]['product_pic']:'',
                'product_parent_id'=>$val->id,
                'sku_unit'=>[],
            ];
            foreach($val['products'] as $v){
                if($v['status']!=1) continue;
                $products[$key]['sku_unit'][]=[
                    'spec_id'=>$v['spec_id'],
                    'spec_name'=>$v['sku_unit'],
                ];
            }
        }
        $this->returnSuccess($products);
    }

    /**
     * 获取其他商品
     */
    public function getOtherProducts(){
        $supplier = $this->preCheck();
        $data  = ProductParentOther::with('products')->where('supplier_id',$supplier)->select();
        $products = [];

        foreach($data as $key=>$val){
            $products[$key]=[
                'product_name'=>$val->product_name,
                'product_parent_id'=>$val->id,
                'from'=>$val->by_supplier,
                'sku_unit'=>[],
            ];
            foreach($val['products'] as $v){
                if($v['status']!=1) continue;
                $products[$key]['sku_unit'][]=[
                    'spec_id'=>$v['spec_id'],
                    'spec_name'=>$v['sku_unit'],
                ];
            }
        }
        $this->returnSuccess($products);
    }

    /**
     * 预检
     * @return mixed
     */
    private function preCheck(){
        $this->checkToken();//todo::这里在线上需要打开
        $union_id = $this->request->param('unionId');
        $supplier = User::where(['union_id'=>$union_id,'user_type'=>2])->value('supplier_id');
        if(!$supplier){
            $this->returnError('用户不存在');
        }
        return $supplier;
    }

    public function updateOtherProduct(){
        $supplier = $this->preCheck();
        $name = $this->request->param('product_name','','htmlspecialchars');
        $parent_id = $this->request->param('product_parent_id/d',0);
        $spec_ids = $this->request->param('spec_ids',-1);

        if(empty($name) || empty($parent_id)){
            $this->returnError('缺少必要的参数');
        }

        $product = ProductParentOther::get(['id'=>$parent_id,'supplier_id'=>$supplier]);
        if(empty($product)){
            $this->returnError('没有相关的商品');
        }

        $jiale_product_id = Db::name("product_parent") -> where(array("product_name"=>$name)) -> value('id');
        if($jiale_product_id){
            $this->returnError('该名称与已家乐产品重复,不允许修改');
        }

        //2018年11月21日14:31:33 同名合并，用户赋予合并的商品
        //检查原来是否有同名商品
        $do_del = false;
        $ori_product = ProductParentOther::where(['product_name'=>$name,'supplier_id'=>$supplier])->value('id');
        if($ori_product){
            //如果有同名商品，开始删除流程
            //获取商品下的所有用户
            $do_del = true;
            $product_enduser = ProductEnduserOther::where(['supplier_id'=>$supplier,'parent_id'=>['in',[$parent_id,$ori_product]]])->column('client_id');
            $product_enduser = array_unique($product_enduser);
            $insert_data = [];
            if($product_enduser){
                foreach($product_enduser as $val){
                    $insert_data[] = [
                        'client_id'=>$val,
                        'parent_id'=>$ori_product,
                        'supplier_id'=>$supplier
                    ];
                }
            }
        }

        /*
         * 这部分留给之后实现，1.5期先忽略
        if($spec_ids==-1){

        }else{
            $product_specs = ProductOther::where('pid',$parent_id)->column('spec_id');
            $spec_ids = explode(',',$spec_ids);

            $spec_data = \app\common\model\ProductSpec::cache( 3600 * 24 )->column('name','id');

            //求交集，过滤请求的id
            $spec_ids = array_intersect($spec_ids,array_keys($spec_data));
            //求差集，确定要新增或删除的商品规格
            //要新增的，是原来没有的而请求中有的
            $add_ids = array_diff($spec_ids,$product_specs);
            //要删除的，是原来有但请求中所没有的
            $del_ids = array_diff($product_specs,$spec_ids);
        }
        */

        //todo::更新商品，暂未处理规格
        Db::connect('db_con')->startTrans();
        try{
            if($do_del){
                //删除父亲表
                $product->delete();
                //删除商品表
                ProductOther::where('pid',$parent_id)->delete();
                //删除两者所有用户关系
                ProductEnduserOther::where(['supplier_id'=>$supplier,'parent_id'=>['in',[$parent_id,$ori_product]]])->delete();
                //添加新的用户关系
                ProductEnduserOther::insertAll($insert_data);
            }else{
                $product->save(['product_name'=>$name]);
                ProductOther::where('pid',$parent_id)->update(['product_name'=>$name]);
            }

            Db::connect('db_con')->commit();
            $this->returnSuccess('更新成功');

        }catch (\Exception $e){
            Db::connect('db_con')->rollback();

            Log::error('[updateOtherProduct]数据更新失败'.$e->getFile().':'.$e->getMessage());
            $this->returnError('更新失败');
        }

    }

    /**
     * 查询该供应商商品是否已存在
     */
    public function otherProductExists(){
        $supplier = $this->preCheck();
        $parent_id = $this->request->param('product_parent_id/d',0);

        if(empty($parent_id)){
            $this->returnError('缺少必要的参数');
        }
        $product = ProductParentOther::get(['id'=>$parent_id,'supplier_id'=>$supplier]);
        if(!empty($product)){
            $this->returnSuccess(['status'=>1,'msg'=>'该商品已存在']);
        }
        $jiale_product = ProductParent::get(['id'=>$parent_id,'supplier_id'=>$supplier]);
        if(!empty($jiale_product)){
            $this->returnSuccess(['status'=>1,'msg'=>'该商品已存在']);
        }
        $this->returnSuccess(['status'=>0,'msg'=>'该商品不存在']);
    }

}