<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/31
 * Time: 15:44
 */

namespace app\admin\logic;

use app\admin\model\ProductSupplier;

class ProductOther extends Product
{
    /**
     * 获取商品列表
     * @param array  $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function getProductList($where = [], $field = 'a.*,ps.supplier_id', $order = '')
    {
        $this->modelProductOther->alias('a');

        $join = [
            [SYS_DB_PREFIX . 'product_supplier ps', 'ps.product_id = a.id','LEFT'],
        ];
        $this->modelProductOther->join = $join;
        $where['a.' . DATA_STATUS_NAME] = ['neq', DATA_DELETE];
        return $this->modelProductOther->getList($where, $field, $order);
    }



}