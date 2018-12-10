<?php
// +----------------------------------------------------------------------
// | desc: Sms.php 发送获取验证码的接口
// +----------------------------------------------------------------------
// | author: xiaodu
// +----------------------------------------------------------------------
// | time: 2018/8/13 10:00
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use think\Config;
use think\Db;


class Dining extends Common
{
    /*
    * 获取大众点评附近餐厅
    * @param longitude 经度
    * @param latitude 纬度
    * @return data  二维数据数据
    * */
    public function index()
    {
        $this->checkToken();
        $post = request()->post();
        $longitude = $post['longitude'];
        $latitude = $post['latitude'];
        if (!$longitude) {
            $this->returnError([], 400, "经度不能为空");
        }
        if (!$latitude) {
            $this->returnError([], 400, "纬度不能为空");
        }
        $url = Config::get('sso_http_url') . "/api/micro-app/dianping/lbs-search?app_key=" . Config::get('sso_app_key');
        $request = [];
        $request['longitude'] = floatval($longitude);
        $request['latitude'] = floatval($latitude);
        $result = httpPost($url,json_encode($request));
        $result = json_decode($result,true);
        if ($result['code'] != 200) {
            $this->returnError([], 400, $result['message']);
        }
        $this->returnSuccess($result['data'], 200, "获取附近餐厅成功");
    }

    /*
    * 关键字获取大众点评餐厅
    * @param city_id 城市id
    * @param area_id 地区id
    * @param keyword 关键字
    * @return data  二维数据数据
    * */
    public function get_keyword_dining()
    {
        $this->checkToken();
        $post = request()->post();
        $city_id = $post['city_id'];
        $area_id = '';
        if(isset($post['area_id'])){
            $area_id = $post['area_id'];
        }

        $keyword = $post['keyword'];
        $request = [];
        if (!$keyword) {
            $this->returnError([], 400, "关键字不能为空");
        }
        if (!$city_id) {
            $this->returnError([], 400, "城市不能为空");
        }
        $url = Config::get('sso_http_url') . "/api/micro-app/dianping/keyword-search?app_key=" . Config::get('sso_app_key');

        $request['keyword'] = $keyword;
        $request['city_id'] = intval($city_id);
        if ($area_id) {
            $request['region_id'] = intval($area_id);
        }
        $result = httpPost($url,json_encode($request));
        $result = json_decode($result,true);
        if ($result['code'] != 200) {
            $this->returnError([], 400, $result['message']);
        }
        $this->returnSuccess($result['data'], 200, "获取餐厅成功");
    }

    /*
    * 获取下级地区列表
    * @param code 当前地区code码 如果获取省级 code为0
    * @return data 二维数据数据
    * */
    public function get_area_list()
    {
        $this->checkToken();
        $post = request()->post();
        $code = $post['code'];
        $level = $post['level'];
        if($code){
            if($level != 1){
                $data = Db::name("area") -> where(["pcode"=>$code,"level"=>$level]) -> select();
            }else{
                $data = Db::name("area") -> where(["pcode"=>$code,"level"=>$level,'is_kt' => 1]) -> select();
            }
        }else{
            $data = Db::name("area") -> where(["level"=>1,'is_kt' => 1]) -> select();
        }
        $this->returnSuccess($data, 200, "获取地区数据成功");
    }
    /*
   * 获取卖家绑定的餐厅列表
   * @param 无
   * @return data 二维数据数据
   * */
    public function get_restaurant_list()
    {
        $this->checkToken();
        $unionid = request()->param('unionId');
        //查询当前创建人是否是买家
        $user = Db::name('user') ->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=2){
            $this -> returnError([],400,"您还不是供应商用户！");
        }
        $client = Db::name('client_supplier')  ->where(['supplier_id'=>$user['supplier_id'],"status"=>1])->select();
        $data = [];
        foreach($client as $key=>$value){
            $data[] = Db::name('user') -> field("id,nickname,mobile,province,city,district,address,restaurant_name,restaurant_type") ->where(['id'=>$value['client_id']])->find();
        }
        $this -> returnSuccess($data,200);
    }
    /*
   * 终端获取卖家信息
   * @param 无
   * @return data 二维数据数据
   * */
    public function get_supplier()
    {
        $this->checkToken();
        $unionid = request()->param('unionId');
        //查询当前创建人是否是买家
        $user = Db::name('user') -> field("id,user_type") ->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=1){
            $this -> returnError([],400,"您还不是终端用户！");
        }
        $client_supplier = Db::name('client_supplier') -> field("supplier_id") ->where(['client_id'=>$user['id'],"status"=>1])->find();
        $data['supplier_id'] = $client_supplier['supplier_id'];
        $supplier = Db::name('supplier') ->where(['id'=>$client_supplier['supplier_id']])->find();
        $data['name'] = $supplier['name'];
        $data['mobile'] = $supplier['mobile'];
        $data['address'] = $supplier['address'];
        $data['desc'] = $supplier['desc'];
        $data['desc2'] = $supplier['desc2'];
        $this -> returnSuccess($data,200);

    }

    /*
  * 店员获取卖家信息
  * @param supplier_id
  * @return data 二维数据数据
  * */
    public function get_supplier_info()
    {
        $this->checkToken();
        $unionid = request()->param('unionId');
        $supplier_id = request()->param('supplier_id');
        //查询供应商是否正确
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['supplier_id']!= $supplier_id ){
            $this -> returnError([],400,"绑定供应商信息有误！");
        }
        $supplier = Db::name('supplier') ->where(['id'=>$supplier_id])->find();
        $data['name'] = $supplier['name'];
        $data['mobile'] = $supplier['mobile'];
        $data['address'] = $supplier['address'];
        $data['desc'] = $supplier['desc'];
        $data['desc2'] = $supplier['desc2'];
        $this -> returnSuccess($data,200);

    }

    /*
  * 获取卖家信息
  * @param supplier_id
  * @return data 二维数据数据
  * */
    public function get_supplier_info1()
    {
        $supplier_id = request()->param('supplier_id');
        $supplier = Db::name('supplier') ->where(['id'=>$supplier_id])->find();
        $data['name'] = $supplier['name'];
        $data['desc'] = $supplier['desc'];
        $data['desc2'] = $supplier['desc2'];
        $unionid = request()->param('unionId');
        if(!empty($unionid)){
            $data['supplier_type'] = Db::name('user')->where(['union_id'=>$unionid])->value('supplier_type');
        }
        $this -> returnSuccess($data,200);

    }
}