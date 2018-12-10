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

namespace app\admin\controller;
use think\Db;
use think\Config;

/**
 * 小程序-订单控制器
 */
class Order extends AdminBase
{
    
    /**
     * 订单列表
     */
    public function orderList()
    {
        
        $where = $this->logicOrder->getWhere($this->param);

      $this->assign('list', $this->logicOrder->getOrderList($where, 'a.*,m.nickname,m.mobile,c.name as supplier_name,c.mobile as supplier_mobile', 'a.created_at desc'));
        
        return $this->fetch('order_list');
    }
    
    /**
     * 文章添加
     */
    public function articleAdd()
    {
        
        $this->articleCommon();
        
        return $this->fetch('article_edit');
    }
    
    /**
     * 文章编辑
     */
    public function orderEdit()
    {
        
        $this->articleCommon();
        
        $info = $this->logicArticle->getArticleInfo(['a.id' => $this->param['id']], 'a.*,m.nickname,c.name as category_name');
        
        !empty($info) && $info['img_ids_array'] = str2arr($info['img_ids']);
        
        $this->assign('info', $info);
        
        return $this->fetch('order_edit');
    }
    
    /**
     * 商品添加与编辑通用方法
     */
    public function articleCommon()
    {
        
        IS_POST && $this->jump($this->logicArticle->articleEdit($this->param));
        
        $this->assign('article_category_list', $this->logicArticle->getArticleCategoryList([], 'id,name', '', false));
    }

    
    /**
     * 数据状态设置
     */
    public function setStatus()
    {
        
        $this->jump($this->logicAdminBase->setStatus('Order', $this->param));
    }
    /**
     * 订单详情(1.0)
     */
    public function orderDetail()
    {

        $where = $this->param['id'];

        $list = $this->logicOrder->getOrderInfo(['d.order_id' => $this->param['id']], 'd.*,p.product_pic,a.id,m.nickname,m.mobile,c.name as supplier_name,c.mobile as supplier_mobile');

        $this->assign('list', $list);

        return $this->fetch('order_detail');
    }

    /**
     * 订单详情(2.0)
     */
    public function orderDetail2()
    {

        $id = $this->param['id'];
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
                    $list[$key]['uploaded_img'] = Config::get('host_url') . $detail_product['product_pic'];
                }
            }else{
                $list[$key]['uploaded_img'] = json_decode($value['uploaded_img'],true);
            }
        }
        $this -> assign("list",$list);
        return $this->fetch('order_detail2');
    }
}
