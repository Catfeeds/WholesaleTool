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

use think\Request;

/**
 * 小程序-商品控制器
 */
class Product extends AdminBase
{
    private $isOther; //是否为其他产品（其他商品在副库且规则有所不同）

    public function __construct()
    {
        parent::__construct();
        $this->isOther = Request::instance()->param('product_type/d',0)===1?1:0;
    }

    /**
     * 商品列表
     */
    public function productList()
    {
        $where = $this->logicProduct->getWhere($this->param);

        if($this->isOther){
            $this->assign('list', $this->logicProductOther->getProductList($where));
            return $this->fetch('product_other_list');
        }else{
            $this->assign('list', $this->logicProduct->getProductList($where, 'a.*,p.path', 'a.id asc'));
        }

        return $this->fetch('product_list');
    }
    
    /**
     * 产品添加
     */
    public function productAdd()
    {
        $this->productCommon();
        $this->assign('product_suppliers', json_encode([]));
        return $this->fetch('product_edit');
    }
    
    /**
     * 产品编辑
     */
    public function productEdit()
    {
        $this->productCommon();

        $info = $this->logicProduct->getProductInfo(['id' => $this->param['id']]);
        $product_suppliers = $this->logicProduct->getProductSupplierInfo(['product_id'=>$this->param['id']]);

        $this->assign('product_suppliers', json_encode($product_suppliers));
        $this->assign('info', $info);

        return $this->fetch('product_edit');
    }
    
    /**
     * 产品添加与编辑通用方法
     */
    public function productCommon()
    {
        IS_POST && $this->jump($this->logicProduct->productEdit($this->param));
        
        $this->assign('article_category_list', $this->logicProduct->getProductUnitList([], 'id,name', '', false));
        $this->assign('supplier_list', $this->logicProduct->getSupplierList([], 'id,name,mobile', '', false));
    }
    
    /**
     * 商品单位添加
     */
    public function unitAdd()
    {
        IS_POST && $this->jump($this->logicProductSpec->productUnitEdit($this->param));
        
        return $this->fetch('unit_edit');
    }
    
    /**
     * 商品单位编辑(暂不提供)
     */
    /*public function unitEdit()
    {
        IS_POST && $this->jump($this->logicArticle->articleCategoryEdit($this->param));
        
        $info = $this->logicArticle->getArticleCategoryInfo(['id' => $this->param['id']]);
        
        $this->assign('info', $info);
        
        return $this->fetch('article_category_edit');
    }*/
    
    /**
     * 商品单位列表
     */
    public function unitList()
    {
        $this->assign('list', $this->logicProduct->getProductUnitList([],'*'));
       
        return $this->fetch('unit_list');
    }
    
    /**
     * 商品单位删除
     */
    public function unitDel($id = 0)
    {
        $this->jump($this->logicArticle->unitDel(['id' => $id]));
    }
    
    /**
     * 数据状态设置
     */
    public function setStatus()
    {
        $this->jump($this->logicAdminBase->setStatus($this->isOther?'ProductOther':'Product', $this->param));
    }
}
