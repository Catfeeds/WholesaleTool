<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/31
 * Time: 15:44
 */

namespace app\admin\logic;

use app\admin\model\ProductSupplier;

class Product extends AdminBase
{
    /**
     * 获取商品列表
     * @param array  $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function getProductList($where = [], $field = 'a.*,p.path', $order = '')
    {
        $this->modelProduct->alias('a');

        $join = [
            [SYS_DB_PREFIX . 'picture p', 'a.cover_id = p.id','LEFT'],
        ];
        $this->modelProduct->join = $join;
        $where['a.' . DATA_STATUS_NAME] = ['neq', DATA_DELETE];
        return $this->modelProduct->getList($where, $field, $order);
    }

    /**
     * 获取商品列表搜索条件
     * @param array $data
     * @return array
     */
    public function getWhere($data = [])
    {
        $where = [];

        !empty($data['search_data']) && $where['product_name|product_desc'] = ['like', '%' . $data['search_data'] . '%'];

        return $where;
    }

    /**
     * 获取分类信息
     */
    public function getProductUnitInfo($where = [], $field = true)
    {
        return $this->modelProductSpec->getInfo($where, $field);
    }

    /**
     * 获取分类列表
     */
    public function getProductUnitList($where = [], $field = true, $order = '', $paginate = 0)
    {
        return $this->modelProductSpec->getList($where, $field, $order, $paginate, false);
    }

    /**
     * 获取供应商列表
     */
    public function getSupplierList($where = [], $field = true, $order = '', $paginate = 0)
    {
        return $this->modelSupplier->getList($where, $field, $order, $paginate, false);
    }

    /**
     * 商品信息编辑
     */
    public function productEdit($data = [])
    {

        $validate_result = $this->validateProduct->scene('edit')->check($data);

        if (!$validate_result) {

            return [RESULT_ERROR, $this->validateProduct->getError()];
        }

        $url = url('productList');

        if (empty($data['id'])) {
            $data['sku_code'] = $this->getSkuCode();
        } else {
            unset($data['sku_code']);
        }

        $result = $this->setData($data);

        $handle_text = empty($data['id']) ? '新增' : '编辑';

        $result && action_log($handle_text, '产品' . $handle_text . '，name：' . $data['product_name']);

        return $result ? [RESULT_SUCCESS, '产品操作成功', $url] : [RESULT_ERROR, $this->modelProduct->getError()];
    }

    /**
     * @desc  生成随机字符串
     * @author: yangsy
     * @time  : 2018-08-13 15:16:13
     */
    public function getSkuCode($length = 32)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * 新增或更新数据
     * @param array $data
     * @return bool
     */
    private function setData(array $data)
    {
        $product = new \app\admin\model\Product();
        $where   = empty($data['id']) ? [] : ['id' => $data['id']];
        $res     = $product->allowField(true)->save($data, $where);
        if (false === $res) return false;

        if (!empty($data['id'])) {
            ProductSupplier::destroy(['product_id' => $data['id']]);
        } else {
            $data['id'] = $product->id;
        }

        if (!empty($data['supplier_ids'])) {
            $insert_data = [];
            foreach ($data['supplier_ids'] as $val) {
                $insert_data[] = [
                    'supplier_id' => $val,
                    'product_id'  => $data['id'],
                ];
            }
            $product_supplier = new ProductSupplier();
            $res              = $product_supplier->insertAll($insert_data);
            if (false == $res) return false;
        }
        return true;
    }

    /**
     * 获取产品信息
     */
    public function getProductInfo($where = [], $field = '*')
    {
        return $this->modelProduct->getInfo($where, $field);
    }

    /**
     * 获取产品供应商信息
     */
    public function getProductSupplierInfo($where = [], $field = 'supplier_id')
    {
        $res = $this->modelProductSupplier->getColumn($where, $field, '', '', false);

        if ($res) return $res;
        return [];
    }

}