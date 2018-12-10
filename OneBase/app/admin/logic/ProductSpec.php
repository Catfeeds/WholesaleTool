<?php
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/11/2
 * Time: 15:44
 */

namespace app\admin\logic;

class ProductSpec extends AdminBase
{
    /**
     * 商品单位信息编辑
     */
    public function productUnitEdit($data = [])
    {
        if($data['id']) return [RESULT_ERROR, '产品单位暂不支持更新'];

        $validate_result = $this->validateProductSpec->scene('edit')->check($data);

        if (!$validate_result) {
            return [RESULT_ERROR, $this->validateProductSpec->getError()];
        }

        $url = url('unitList');

        $result = \app\admin\model\ProductSpec::create(['name'=>$data['name']]);

        $handle_text = empty($data['id']) ? '新增' : '编辑';

        $result && action_log($handle_text, '产品单位' . $handle_text . '，name：' . $data['name']);

        return $result ? [RESULT_SUCCESS, '产品单位操作成功', $url] : [RESULT_ERROR, $this->modelProductSpec->getError()];
    }
}