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

namespace app\common\logic;

/**
 * 小程序-订单逻辑
 */
class Order extends LogicBase
{
    
    /**
     * 文章分类编辑
     */
    public function articleCategoryEdit($data = [])
    {
        
        $validate_result = $this->validateArticleCategory->scene('edit')->check($data);
        
        if (!$validate_result) {
            
            return [RESULT_ERROR, $this->validateArticleCategory->getError()];
        }
        
        $url = url('articleCategoryList');
        
        $result = $this->modelArticleCategory->setInfo($data);
        
        $handle_text = empty($data['id']) ? '新增' : '编辑';
        
        $result && action_log($handle_text, '文章分类' . $handle_text . '，name：' . $data['name']);
        
        return $result ? [RESULT_SUCCESS, '操作成功', $url] : [RESULT_ERROR, $this->modelArticleCategory->getError()];
    }
    
    /**
     * 获取订单列表
     */
    public function getOrderList($where = [], $field = 'a.*,m.nickname,,m.mobile,c.name as supplier_name,c.mobile as supplier_mobile', $order = '')
    {
        $this->modelOrder->alias('a');

       $join = [
                    [SYS_DB_PREFIX . 'user m', 'a.client_id = m.id'],
                    [SYS_DB_PREFIX . 'supplier c', 'a.supplier_id = c.id'],
                ];

        $where['a.' . DATA_STATUS_NAME] = ['neq', DATA_DELETE];

        $this->modelOrder->join = $join;


        return $this->modelOrder->getList($where, $field, $order);


    }
    
    /**
     * 获取订单列表搜索条件
     */
    public function getWhere($data = [])
    {

        $where = [];
        
        !empty($data['search_data']) && $where['a.id|a.client_id|a.supplier_id'] = ['like', '%'.$data['search_data'].'%'];

        if( isset( $data[ 'search_order_status' ] ) && (int)$data[ 'search_order_status' ] !== 0 ){
            $where[ 'order_status' ] = (int)$data[ 'search_order_status' ];
        }
        
        return $where;
    }
    
    /**
     * 文章信息编辑
     */
    public function articleEdit($data = [])
    {
        
        $validate_result = $this->validateArticle->scene('edit')->check($data);
        
        if (!$validate_result) {
            
            return [RESULT_ERROR, $this->validateArticle->getError()];
        }
        
        $url = url('articleList');
        
        empty($data['id']) && $data['member_id'] = MEMBER_ID;
        
        $result = $this->modelArticle->setInfo($data);
        
        $handle_text = empty($data['id']) ? '新增' : '编辑';
        
        $result && action_log($handle_text, '文章' . $handle_text . '，name：' . $data['name']);
        
        return $result ? [RESULT_SUCCESS, '文章操作成功', $url] : [RESULT_ERROR, $this->modelArticle->getError()];
    }

    /**
     * 获取订单详情信息
     */
    public function getOrderInfo($where = [], $field = 'd.*,p.product_pic,a.id,m.nickname,m.mobile,c.name as supplier_name,c.mobile as supplier_mobile')
    {
        
        $this->modelOrderDetail->alias('d');
        
        $join = [
                    [SYS_DB_PREFIX . 'order a', 'd.order_id = a.id'],
                    [SYS_DB_PREFIX . 'product p', 'd.product_id =  p.id'],
                    [SYS_DB_PREFIX . 'user m', 'a.client_id = m.id'],
                    [SYS_DB_PREFIX . 'supplier c', 'a.supplier_id = c.id'],
                ];

//        $where['d.' . DATA_STATUS_NAME] = ['neq', DATA_DELETE];

        $this->modelOrderDetail->join = $join;
        
        return $this->modelOrderDetail->getList($where, $field);
    }
    
    /**
     * 获取分类信息
     */
    public function getArticleCategoryInfo($where = [], $field = true)
    {
        
        return $this->modelArticleCategory->getInfo($where, $field);
    }
    
    /**
     * 获取文章分类列表
     */
    public function getArticleCategoryList($where = [], $field = true, $order = '', $paginate = 0)
    {
        
        return $this->modelArticleCategory->getList($where, $field, $order, $paginate);
    }
    
    /**
     * 文章分类删除
     */
    public function articleCategoryDel($where = [])
    {
        
        $result = $this->modelArticleCategory->deleteInfo($where);
        
        $result && action_log('删除', '文章分类删除，where：' . http_build_query($where));
        
        return $result ? [RESULT_SUCCESS, '文章分类删除成功'] : [RESULT_ERROR, $this->modelArticleCategory->getError()];
    }
    
    /**
     * 订单删除
     */
    public function articleDel($where = [])
    {
        
        $result = $this->modelArticle->deleteInfo($where);
        
        $result && action_log('删除', '订单删除，where：' . http_build_query($where));
        
        return $result ? [RESULT_SUCCESS, '订单删除成功'] : [RESULT_ERROR, $this->modelArticle->getError()];
    }
}
