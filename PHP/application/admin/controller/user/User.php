<?php

namespace app\admin\controller\user;

use app\common\model\Order;
use app\common\model\OrderDetail;
use app\common\model\ClientSupplier;
use app\common\model\OrderDetailOther;
use app\common\controller\Backend;
use app\common\model\ProductOther;
use app\common\model\ProductPrice;
use app\common\model\ProductPriceOther;
use app\common\model\UserToken;
use think\Db;
use think\Exception;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->alias('user')
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->alias('user')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v)
            {
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * @description
     * 删除订单表的数据
     * 删除订单详细表数据(主表+副库)
     * 删除产品表(副表)
     * 删除产品价格表(主表+副表)
     * 删除用户的token
     * 删除用户表user的数据
     * @param string $ids
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function del($ids = "")
    {

        if (!$ids) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk       = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $count = $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        //开启事务
        $this->model->startTrans();
        try {
            foreach ($list as $k => $v) {
                //1.删除订单表
                $orderListObj = Order::where('client_id', $v->id)->select();
                //如果存在订单
                if ($orderListObj) {
                    foreach ($orderListObj as $orderKey => $order) {
                        //删除订单详细表(主库)
                        \app\common\model\OrderDetail::destroy(function ($query) use ($order) {
                            $query->where('order_id', $order->id);
                        });

                        //删除订单详细表(副库,如果失败,事务无法回滚)
                        OrderDetailOther::destroy(function ($query) use ($order) {
                            $query->where('order_id', $order->id);
                        });

                        //删除order表的数据
                        $order->delete();
                    }
                }
                //2.删除产品表(只删除副库,如果失败,事务无法回滚)
                ProductOther::destroy(function ($query) use ($v) {
                    $query->where('created_by', $v->id);
                });

                //3.删除产品价格表(主库)
                ProductPrice::destroy(function ($query) use ($v) {
                    $query->where('client_id', $v->id);
                });
                //3.删除产品价格表(副库)
                ProductPriceOther::destroy(function ($query) use ($v) {
                    $query->where('client_id', $v->id);
                });
                //4.删除用户token
                UserToken::destroy(function ($query) use ($v) {
                    $query->where('unionid', $v->union_id);
                });

                //5.删除终端与供应商关联表
                ClientSupplier::destroy(function ($query) use ($v) {
                    $query->where('client_id', $v->id);
                });
                //6.删除用户表
                $count += $v->delete();
            }
        } catch (Exception $e) {
            //回滚
            $this->model->rollback();
            $this->error(__('delete fail'));//删除失败
        }
        //提交事务
        $this->model->commit();

        if ($count) {
            $this->success();
        } else {
            $this->error(__('No rows were deleted'));
        }
    }

}
