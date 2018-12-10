<?php
// +----------------------------------------------------------------------
// | desc: Product.php 产品信息页面
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/18 23:45
// +----------------------------------------------------------------------
namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;

class Product extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Product');
    }
    
    /**
     * @desc 首页
     * @author: yangsy
     * @time: 2018-08-18 23:47:02 
     */
    /**
     * @desc 供货商首页
     * @author: yangsy
     * @time: 2018-08-18 21:51:05
     */
    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $s = "product_sort ASC";
            $total = $this->model
                ->where($where)
                ->order($s)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($s)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v)
            {
                $v->hidden(['password']);
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * @desc 添加产品
     * @author: yangsy
     * @time: 2018-08-18 22:38:19
     */
    public function add(){
        if(request()->isAjax()){
            $param = request()->param();
            $row = $param['row'];
            $product_supplier = $param['product_supplier'];
            $status = $row['status'] == 'normal' ? 1 : 0;
            $data = [
                'product_name' => $row['product_name'],
                'product_pic' => $row['product_pic'],
                'product_desc' => $row['product_desc'],
                'sku_unit' => $row['product_spec'],
                'product_sort' => $row['product_sort'],
                'sku_code' => $this->getSkuCode(),
                'status' => $status,
                'created_at' => time(),
                'updated_at' => time(),
            ];

            // 验证数据
            if(empty($data['product_name'])){
                $this->error('请输入产品名称！');
            }

            if(empty($data['product_pic'])){
                $this->error('请上传产品图片！');
            }

            if(empty($data['product_desc'])){
                $this->error('请填写产品详情！');
            }

            if(empty($data['sku_unit'])){
                $this->error('请选择产品规格！');
            }
            // 查询判断手机号是否在数据库中
            $id = Db::name('product')->insertGetId($data);
            if($id){
                // 添加关系数据
                foreach($product_supplier as $key => $value){
                    $da = [
                        'supplier_id' => $value,
                        'product_id' => $id,
                        'created_at' => time(),
                    ];
                    Db::name('product_supplier')->insert($da);
                }
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }else{
            // 查询产品规格信息
            $spec = Db::name('product_spec')->order('id DESC')->select();
            $this->assign('spec',$spec);
            // 查询出所有商户信息
            $supplier = Db::name('supplier')->order('id DESC')->select();
            $this->assign('supplier',$supplier);
            return $this->view->fetch();
        }
    }

    /**
     * @desc 生成随机字符串
     * @author: yangsy
     * @time: 2018-08-13 15:16:13
     */
    public function getSkuCode( $length = 32 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }

    /**
     * @desc 添加产品
     * @author: yangsy
     * @time: 2018-08-18 22:38:19
     */
    public function edit($ids = null){
        if(request()->isAjax()){
            $param = request()->param();
            $row = $param['row'];
            $status = $row['status'] == 'normal' ? 1 : 0;
            $product_supplier = $param['product_supplier'];
            $id = $row['id'];
            if(empty($id)){
                $this->error('请选择操作数据');
            }
            $data = [
                'product_name' => $row['product_name'],
                'product_pic' => $row['product_pic'],
                'product_desc' => $row['product_desc'],
                'product_sort' => $row['product_sort'],
                'sku_unit' => $row['product_spec'],
                'status' => $status,
                'updated_at' => time(),
            ];

            // 验证数据
            if(empty($data['product_name'])){
                $this->error('请输入产品名称！');
            }

            if(empty($data['product_pic'])){
                $this->error('请上传产品图片！');
            }

            if(empty($data['product_desc'])){
                $this->error('请填写产品详情！');
            }

            if(empty($data['sku_unit'])){
                $this->error('请选择产品规格！');
            }
            // 查询判断手机号是否在数据库中
            if(Db::name('product')->where(['id'=>$id])->update($data)){
                Db::name('product_supplier')->where(['product_id'=>$id])->delete();
                // 添加关系数据
                foreach($product_supplier as $key => $value){
                    $da = [
                        'supplier_id' => $value,
                        'product_id' => $id,
                        'created_at' => time(),
                    ];
                    Db::name('product_supplier')->insert($da);
                }
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $id = request()->param('ids');
            if(empty($id)){
                $this->error('请选择操作数据');
            }

            // 查询产品规格信息
            $spec = Db::name('product_spec')->order('id DESC')->select();
            $this->assign('spec',$spec);

            $info = Db::name('product')->where(['id'=>$id])->find();
            $info['status'] = $info['status'] == 1 ? 'normal' : 'hidden';
            $this->assign('row',$info);

            // 查询出所有商户信息
            $supplier = Db::name('supplier')->order('id DESC')->select();
            // 循环商户，查询判断用户是否有当前的权限
            foreach($supplier as $key => $value){
                if(Db::name('product_supplier')->where(['supplier_id'=>$value['id'],'product_id'=>$id])->count()){
                    $supplier[$key]['is_select'] = 1;
                }else{
                    $supplier[$key]['is_select'] = 0;
                }
            }

            $this->assign('supplier',$supplier);

            return $this->view->fetch();
            return $this->view->fetch();
        }
    }
     
}