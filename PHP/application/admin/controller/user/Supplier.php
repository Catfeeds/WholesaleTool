<?php
// +----------------------------------------------------------------------
// | desc: Supplier.php 供货商
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/18 21:49
// +----------------------------------------------------------------------
namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;

class Supplier extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Supplier');
    }
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
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
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
     * @desc 添加供货商
     * @author: yangsy
     * @time: 2018-08-18 22:38:19 
     */
    public function add(){
        if(request()->isAjax()){
            $param = request()->param();
            $row = $param['row'];

            $password = $row['password'];
            $iterations = 1000;
            $salt = "lihua";
            $password = hash_pbkdf2("sha256", $password, $salt, $iterations, 32);

            $data = [
                'name' => $row['name'],
                'mobile' => $row['mobile'],
                'password' => $password,
                'address' => $row['address'],
                'desc' => $row['desc'],
                'desc2' => $row['desc2'],
                'created_at' => time(),
            ];

            // 验证数据
            if(empty($data['name'])){
                $this->error('请输入用户名！');
            }

            if(empty($data['mobile'])){
                $this->error('请输入手机号！');
            }

            if(!preg_match("/^1[3457896]\d{9}$/", $data['mobile'])){
                $this->error('请输入正确手机号');
            }

            // 查询判断手机号是否在数据库中
            if(Db::name('supplier')->where(['mobile'=>$data['mobile']])->count()){
                $this->error('当前手机号已存在');
            }

            if(empty($row['password'])){
                $this->error('请输入密码！');
            }

            if(Db::name('supplier')->insert($data)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }else{
            return $this->view->fetch();
        }
    }
    
    /**
     * @desc 修改供货商信息
     * @author: yangsy
     * @time: 2018-08-18 23:03:03 
     */
    public function edit($ids = NULL){
        if(request()->isAjax()){
            $param = request()->param();
            $row = $param['row'];
            $id = $row['id'];
            if(empty($id)){
                $this->error('请选择操作数据');
            }
            $data = [
                'name' => $row['name'],
                'mobile' => $row['mobile'],
                'updated_at' => time(),
                'desc' => $row['desc'],
                'desc2' => $row['desc2'],
                'address' => $row['address'],
            ];

            $password = $row['password'];
            $iterations = 1000;
            $salt = "lihua";
            $password = hash_pbkdf2("sha256", $password, $salt, $iterations, 32);

            if(!empty($row['password'])){
                $data['password'] = $password;
            }

            // 验证数据
            if(empty($data['name'])){
                $this->error('请输入用户名！');
            }

            if(empty($data['mobile'])){
                $this->error('请输入手机号！');
            }

            if(!preg_match("/^1[3457896]\d{9}$/", $data['mobile'])){
                $this->error('请输入正确手机号');
            }

            // 查询判断手机号是否在数据库中
            $info = Db::name('supplier')->where(['id'=>$id])->find();

            if(Db::name('supplier')->where(['mobile'=>$data['mobile']])->count() && $info['mobile'] != $data['mobile']){
                $this->error('当前手机号已存在');
            }

            if(Db::name('supplier')->where(['id'=>$id])->update($data)){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $id = request()->param('ids');
            if(empty($id)){
                $this->error('请选择操作数据');
            }
            $info = Db::name('supplier')->where(['id'=>$id])->find();
            $this->assign('row',$info);
            return $this->view->fetch();
        }
    }
     
     
     
}