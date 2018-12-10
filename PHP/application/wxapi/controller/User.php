<?php
// +----------------------------------------------------------------------
// | desc: User.php 用户管理
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/12 19:39
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use think\Db;

class User extends Common
{
    /**
     * @description 2018年10月8日16:31:05 添加设置不显示引导层
     */
    public function setNotShowLayer()
    {
        $this->checkToken();
        $unionid = request()->param('unionId');

        $user = \app\common\model\User::get(['union_id' => $unionid]);
        if (!$user->show_layer) {
            $this->returnSuccess([], 400, '无需更改');
        }
        $orderNum = 0;
        if ($user->supplier_id) {
            $orderNum = Db::name('order')->where('supplier_id', $user->supplier_id)->count();
        }

        if ($orderNum === 0) {
            $this->returnSuccess([], 400, '无需更改');
        }
        //设置不显示
        \app\common\model\User::where(['union_id' => $unionid])->update(['show_layer'=>0]);

       $this->returnSuccess([], 200, '更改成功');
    }
}