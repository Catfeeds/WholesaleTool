<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class Detail extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('OrderDetail');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        $id = $this->request->param("ids");
        $where["order_id"] = $id;
        $order = DB::name("order") -> where(array("id"=>$id)) -> find();
        $list_1 = DB::name("order_detail")
            ->where($where)
            ->select();
        $list_0 = Db::connect('db_con')->name("order_detail")
            ->where($where)
            ->select();
        $list = array_merge($list_1,$list_0);
        foreach($list as $key=>$value){
            if(!$order['order_type']){
                if($value['is_unlieve']==1){
                    $detail_product = Db::name('product')->where(["id"=>$value['product_id']])->find();
                    $list[$key]['uploaded_img'] = $detail_product['product_pic'];
                }
            }else{
                $list[$key]['uploaded_img'] = json_decode($value['uploaded_img'],true);
            }
        }
        $this -> assign("list",$list);
        if(!$order['order_type']){
            return $this->view->fetch();
        }else{
            return $this->view->fetch("detail_photo");
        }

    }


}
