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

namespace app\common\model;
use think\Model;

/**
 * 用户模型
 */
class User extends Model
{
    /**
     * @description 性别获取器
     * @param $value
     * @return mixed
     */
    public function getGenderAttr($value)
    {
        $gender = [0=>'未知',1=>'男',2=>'女'];
        return $gender[$value];
    }

    /**
     * @description 用户类型获取器
     * @param $value
     * @return mixed
     */
    public function getUserTypeAttr($value)
    {
        $user_type = [0=>'未知',1=>'终端',2=>'供应商'];

        return ['val' => $value, 'text' => $user_type[$value]];
    }

    public function supplier()
    {
        return $this->hasOne('Supplier','id','supplier_id');
    }
}
