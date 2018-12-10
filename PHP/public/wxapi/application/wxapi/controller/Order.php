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
use wechat\Wechatapi;

class Order extends Common
{
    /*
     * 新建订单接口
     * @param data
     * @return 无
     * */
    public function create_order(){
        // $this->checkToken();
        $unionid = request()->param('unionId');
        $form_id = request()->param('form_id');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=1){
            $this -> returnError([],400,"当前用户不是终端用户！");
        }
        //查询当前买家的卖家
        $client_supplier = Db::name('client_supplier')->where(['client_id'=>$user['id'],"status"=>1])->find();
        if(!$client_supplier){
            $this -> returnError([],400,"当前用户没有绑定供应商！");
        }
        $supplier_id = $client_supplier['supplier_id'];
        $client_id = $user['id'];
        //买家备注
        $comments = request()->param('comments');
        $order_type = request()->param('order_type');
        if($order_type==0){
            //正常订单
            //家乐品牌产品
            $product_list = request()->param('product_list');
            //其他品牌产品
            $user_product_list = request()->param('user_product_list');
            //新增品牌产品
            $other_product_list = request()->param('other_product_list');
            if($product_list){
                $product_list = json_decode($product_list,true);
            }else{
                $product_list = array();
            }
            if($user_product_list){
                $user_product_list = json_decode($user_product_list,true);
            }else{
                $user_product_list = array();
            }
            if($other_product_list){
                $other_product_list = json_decode($other_product_list,true);
            }else{
                $other_product_list = array();
            }
            if(!count($product_list)&&!count($user_product_list)&&!count($other_product_list)){
                $this -> returnError([],400,"订单产品数据不能为空！");
            }

            $detail = [];
            //查询联合利华产品价格
            foreach($product_list as $key => $value){
                $temp = [];
                //查询产品信息
                $product = Db::name('product')->where(['id'=>$value['product_id']])->find();
                $temp['product_id'] = $value['product_id'];
                $temp['product_name'] = $product['product_name'];
                $temp['sku_code'] = $product['sku_code'];
                $temp['sku_unit'] = $product['sku_unit'];
                $temp['product_count'] = $value['product_count'];
                $temp['product_price'] = $product['product_price'];
                $temp['is_unlieve'] = 1;
                $product_price = Db::name('product_price')->where(['product_id'=>$value['product_id'],"supplier_id"=>$supplier_id,"client_id"=>$client_id])->find();
                if($product_price){
                    $temp['product_price'] = $product_price['product_price'];
                }
                $detail[] = $temp;
            }
            //查询其他品牌产品价格
            foreach($user_product_list as $key => $value){
                $temp = [];
                //查询产品信息
                $product = Db::connect('db_con')->name('product')->where(['id'=>$value['product_id']])->find();
                $temp['product_id'] = $value['product_id'];
                $temp['product_name'] = $product['product_name'];
                $temp['sku_code'] = $product['sku_code'];
                $temp['sku_unit'] = $product['sku_unit'];
                $temp['product_count'] = $value['product_count'];
                $temp['product_price'] = $product['product_price'];
                $temp['is_unlieve'] = 0;
                $product_price = Db::connect('db_con')->name('product_price')->where(['product_id'=>$value['product_id'],"supplier_id"=>$supplier_id,"client_id"=>$client_id])->find();
                if($product_price){
                    $temp['product_price'] = $product_price['product_price'];
                }
                $detail[] = $temp;
            }
            //新增其他产品到副库产品表和产品价格表
            if(count($other_product_list)>0){
                Db::connect('db_con')->startTrans();
                //手动输入订单产品
                foreach($other_product_list as $key => $value){
                    //获取商品对照关系(以商品名称为准)
                    $product_parent_id = Db::connect('db_con')->name("product_parent") -> where(array("product_name"=>$value['product_name'],"supplier_id"=>$supplier_id,"created_by"=>$client_id)) -> value('id');
                    if(!$product_parent_id){
                        $product_parent = ['product_name'=>$value['product_name'],"supplier_id"=>$supplier_id,"created_by"=>$client_id,"by_supplier"=>0,"created_at"=>time(),"update_at"=>time()];
                        $product_parent_id = Db::connect('db_con')->name('product_parent')->insertGetId($product_parent);
                    }
                    //添加手动输入产品到产品表
                    $product_data = Db::connect('db_con')->name("product") -> where(array("product_name"=>$value['product_name'],"sku_unit"=>$value['sku_unit'],"created_by"=>$client_id)) -> find();
                    if($product_data){
                        $product_id = $product_data['id'];
                    }else{
                        $temp = [];
                        $temp['product_name'] = $value['product_name'];
                        $temp['sku_code'] = $value['sku_code'];
                        $temp['sku_unit'] = $value['sku_unit'];
                        $temp['created_by'] = $client_id;
                        $temp['created_at'] = time();
                        $temp['updated_at'] = time();
                        $temp['supplier_id'] = $supplier_id;

                        $result = Db::connect('db_con')->name("product") -> insert($temp);
                        if(!$result){
                            Db::connect('db_con')->rollback();
                            $this -> returnError([],400,"新增产品失败，联系管理员！");
                        }
                        $product_id = Db::connect('db_con')->name("product")->getLastInsID();
                    }

                    $temp = [];
                    $temp['product_name'] = $value['product_name'];
                    $temp['sku_code'] = $value['sku_code'];
                    $temp['sku_unit'] = $value['sku_unit'];
                    $temp['product_count'] = $value['product_count'];
                    $temp['product_id'] = $product_id;
                    $temp['is_unlieve'] = 0;
                    $temp['product_price'] = 0;
                    $detail[] = $temp;
                    $product_price_data = Db::connect('db_con')->name('product_price')->where(['product_id'=>$product_id,"supplier_id"=>$supplier_id,"client_id"=>$client_id]) -> find();
                    if(!$product_price_data){
                        $product_price = ['product_id'=>$product_id,"supplier_id"=>$supplier_id,"client_id"=>$client_id,"product_price"=>0,"created_at"=>time(),"updated_at"=>time()];
                        $result = Db::connect('db_con')->name('product_price')->insert($product_price);
                        if(!$result){
                            Db::connect('db_con')->rollback();
                            $this -> returnError([],400,"新增产品失败，联系管理员！");
                        }
                    }

                    //新增产品和终端用户关系
//                    $product_enduser_data = Db::connect('db_con')->name('product_enduser')->where(['product_id'=>$product_id,"supplier_id"=>$supplier_id,"client_id"=>$client_id]) -> find();
//                    if(!$product_enduser_data){
//                        $product_enduser = ['product_id'=>$product_id,"supplier_id"=>$supplier_id,"client_id"=>$client_id,"created_at"=>time()];
//                        $result = Db::connect('db_con')->name('product_enduser')->insert($product_enduser);
//                        if(!$result){
//                            Db::connect('db_con')->rollback();
//                            $this -> returnError([],400,"新增产品失败，联系管理员！");
//                        }
//                    }
                    $product_enduser_data = Db::connect('db_con')->name('product_enduser')->where(["supplier_id"=>$supplier_id,"client_id"=>$client_id,"parent_id"=>$product_parent_id]) -> find();
                    if(!$product_enduser_data){
                        $product_enduser = ['parent_id'=>$product_parent_id,"supplier_id"=>$supplier_id,"client_id"=>$client_id,"created_at"=>time()];
                        $result = Db::connect('db_con')->name('product_enduser')->insert($product_enduser);
                        if(!$result){
                            Db::connect('db_con')->rollback();
                            $this -> returnError([],400,"新增产品失败，联系管理员！");
                        }
                    }
                }
                Db::connect('db_con')-> commit();
            }

            $order['order_number'] = $this->get_order_sn();
            $order['client_id'] = $client_id;
            $order['supplier_id'] = $supplier_id;
            $order['order_type'] = $order_type;
            $order['comments'] = $comments;
            $order['order_status'] = 1;
            $order['created_at'] = time();
            $order['updated_at'] = time();
            Db::startTrans();
            Db::connect('db_con') -> startTrans();
            $result =  Db::name('order') -> insert($order);
            if(!$result){
                Db::rollback();
                $this -> returnError([],400,"新增订单失败，联系管理员！");
            }
            $order_id = Db::name('order')->getLastInsID();
            $product_name = [];
            foreach($detail as $key => $value){
                $product_name[] = $value['product_name'];
                $value['order_id'] = $order_id;
                if($value['is_unlieve']==1){
                    $result =  Db::name('order_detail') -> insert($value);
                }else{
                    $result = Db::connect('db_con')->name('order_detail')->insert($value);
                }
                if(!$result){
                    Db::rollback();
                    Db::connect('db_con') -> rollback();
                    $this -> returnError([],400,"新增订单失败，联系管理员！");
                }
            }
            Db::commit();
            Db::connect('db_con') -> commit();
            //存储用户的form_id
            $info = [];
            $info['form_id'] = $form_id;
            $info['unionid'] = $unionid;
            $info['create_time'] = time();
            DB::name("form") -> insert($info);
            //下单成功给供应商发送短信消息
            //查询供应商手机号码
            $supplier_info = DB::name("supplier") -> where(array("id"=>$supplier_id)) -> find();
            $mobile = $supplier_info['mobile'];
            $templateCode = "SMS_143866796";
            $sms_data = array(
                "code" => $order['order_number'],
                "customer" => $user['nickname']
                );
            $this -> send_sms_info($mobile,$sms_data,$templateCode);
            // $send_data = array(
            //             "keyword1"=>array(
            //                 "value"=>$order['order_number'],
            //                  //"value"=>'woshihaoren',
            //                 "color"=>"#4a4a4a"
            //             ),
            //             "keyword2"=>array(
            //                 "value"=>date("Y-m-d H:i:s",$order['created_at']),
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword3"=>array(
            //                 "value"=>$user['restaurant_name'],
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword4"=>array(
            //                 "value"=>implode(",", $product_name),
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword5"=>array(
            //                 "value"=>"等待确认，请点击查看详情确认订单",
            //                 "color"=>"#9b9b9b"
            //             )
            //         );
            // $page = "";
            // $result = $this -> send_sms($from_id,"omzUr5LljXUzJIy-h-9tvLMzIiqE",$send_data,"oFNLyblZU6atYz-wlsbB8MobHZdTWtpJjObImfc9qlI",$page);

            //确认订单给下单人发送模板消息
            // $send_data = array(
            //             "keyword1"=>array(
            //                 "value"=>$order['order_number'],
            //                  //"value"=>'woshihaoren',
            //                 "color"=>"#4a4a4a"
            //             ),
            //             "keyword2"=>array(
            //                 "value"=>date("Y-m-d H:i:s",$order['created_at']),
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword3"=>array(
            //                 "value"=>implode(",", $product_name),
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword4"=>array(
            //                 "value"=>"待确认",
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword5"=>array(
            //                 "value"=>"请耐心等待商家确认，或点击详情查看订单状态",
            //                 "color"=>"#9b9b9b"
            //             )
            //         );
            // $page = "";
            // $result = $this -> send_sms($form_id,$user['open_id'],$send_data,"1rotbEHivPe47eietFwj_T5ja2lKIEf5QIt_i2FPNa8",$page);

            $this -> returnSuccess([],200,"下单成功！");
        }else{
            //图片订单
            $img_product_list = request()->param('img_product_list');
            $img_product_list = json_decode($img_product_list,true);
            if(!count($img_product_list)){
                $this -> returnError([],400,"订单产品数据不能为空！");
            }
            $order['order_number'] = $this->get_order_sn();
            $order['client_id'] = $client_id;
            $order['supplier_id'] = $supplier_id;
            $order['order_type'] = $order_type;
            $order['comments'] = $comments;
            $order['order_status'] = 1;
            $order['created_at'] = time();
            Db::startTrans();
            Db::connect('db_con') -> startTrans();
            $result =  Db::name('order') -> insert($order);
            if(!$result){
                Db::rollback();
                $this -> returnError([],400,"新增订单失败，联系管理员！");
            }
            $order_id = Db::name('order')->getLastInsID();
            $detail['order_id'] = $order_id;
            $detail['uploaded_img'] = json_encode($img_product_list);
            $result = Db::connect('db_con')->name('order_detail')->insert($detail);
            if(!$result){
                Db::rollback();
                Db::connect('db_con') -> rollback();
                $this -> returnError([],400,"新增订单失败，联系管理员！");
            }
            Db::commit();
            Db::connect('db_con') -> commit();
            //下单成功给供应商发送模板消息
        
            // $send_data = array(
            //             "keyword1"=>array(
            //                 "value"=>$order['order_number'],
            //                  //"value"=>'woshihaoren',
            //                 "color"=>"#4a4a4a"
            //             ),
            //             "keyword2"=>array(
            //                 "value"=>date("Y-m-d H:i:s",$order['created_at']),
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword3"=>array(
            //                 "value"=>$user['restaurant_name'],
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword4"=>array(
            //                 "value"=>"图片订单",
            //                 "color"=>"#9b9b9b"
            //             ),
            //             "keyword5"=>array(
            //                 "value"=>"等待确认，请点击查看详情确认订单",
            //                 "color"=>"#9b9b9b"
            //             )
            //         );
            // $page = "";
            // $result = $this -> send_sms($from_id,$user['open_id'],$send_data,"oFNLyblZU6atYz-wlsbB8MobHZdTWtpJjObImfc9qlI",$page);

            $this -> returnSuccess([],200,"下单成功！");
        }

    }
    //上传图片接口
    public function upload_img(){
        $this->checkToken();
        // 获取表单上传文件
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $file_name = $info->getFilename();
                $file_path = $info->getSaveName();
                $file = ROOT_PATH . 'public' . DS . 'uploads'. DS .$file_path;
                $result = uploadFile($file_name,$file);
                //删除服务器文件
//                unlink($file);
                if(!$result){
                    $this -> returnError([],400,"上传图片失败！");
                }
                $config = config('aliyun_oss');
                $url = $config['Url']. DS .$file_name;
                $this -> returnSuccess($url,200,"上传成功");
            }else{
                // 上传失败获取错误信息
                $this -> returnError([],400, $file->getError());
            }
        }else{
            $this -> returnError([],400,"请选择上传的图片！");
        }
    }
    //买家获取订单列表
    public function get_list_client(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_status = request()->param('order_status');
        $date_type = request()->param('date_type');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']==0){
            $this -> returnError([],400,"未知类型的用户！");
        }

        if($user['user_type']==1){
            $client_id = $user['id'];
            //终端用户
            $where['client_id'] = $client_id;
            if($order_status>0&&$order_status<4){
                $where['order_status'] = $order_status;
            }
        }else{
            $this -> returnError([],400,"您不是终端用户！");
        }
        if($date_type>0){
            //最近三日
            $where_time = strtotime('-3 day');
            $where['created_at'] = array("gt",$where_time);
        }
        //查询当前买家的卖家
        $list = Db::name('order')->where($where) -> order("created_at desc") ->select();
        foreach($list as $key => $value){
            $order_amount = 0;
            if($value['order_type']==0){
                //正常订单,联合利华产品
                $detail_1 = Db::name('order_detail')->where(["order_id"=>$value['id']])->select();
                foreach($detail_1 as $k1=>$v1){
                    if($v1['is_unlieve']==1){
                        $detail_product = Db::name('product')->where(["id"=>$v1['product_id']])->find();
                        $detail_1[$k1]['uploaded_img'] = Config::get('host_url') .$detail_product['product_pic'];
                    }
                }
                //正常订单,非联合利华产品
                $detail_0 = Db::connect('db_con')->name('order_detail')->where(["order_id"=>$value['id']])->select();
                foreach($detail_0 as $k1=>$v1){
                    if($v1['is_unlieve']==1){
                        $detail_product = Db::name('product')->where(["id"=>$v1['product_id']])->find();
                        $detail_0[$k1]['uploaded_img'] = Config::get('host_url') .$detail_product['product_pic'];
                    }
                }
                $detail = array_merge($detail_1,$detail_0);
                $list[$key]['product_list'] = $detail;
                foreach($detail as $k => $v){
                    $order_amount += $v['product_price']*$v['product_count'];
                }
            }else{
                //图片订单
                $detail = Db::connect('db_con')->name('order_detail')->where(["order_id"=>$value['id']])->find();
                $list[$key]['product_list'] = json_decode($detail['uploaded_img'],true);
            }
            //获取买家的餐厅
            $order_user = Db::name("user") -> where(array("id"=>$value['client_id'])) -> find();
            $list[$key]['restaurant_name'] = $order_user['restaurant_name'];
            $list[$key]['province'] = $order_user['province'];
            $list[$key]['city'] = $order_user['city'];
            $list[$key]['district'] = $order_user['district'];
            $list[$key]['address'] = $order_user['address'];
            if($order_user['type']==5){
                $list[$key]['nickname'] = $order_user['restaurant_name'];
            }else{
                $list[$key]['nickname'] = $order_user['nickname'];
            }
            $list[$key]['mobile'] = $order_user['mobile'];
//          $list[$key]['order_amount'] = $order_amount;
            $list[$key]['order_amount'] = round($order_amount, 2);

        }
        $this -> returnSuccess($list,200,"获取订单列表成功");
    }
    //卖家获取订单列表
    public function get_list_supplier(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_status = request()->param('order_status');
        $date_type = request()->param('date_type');
        $client_id = '';
        $post = request()->post();
        if(isset($post['client_id'])) {
            $client_id = $post['client_id'];
        }
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']==0){
            $this -> returnError([],400,"未知类型的用户！");
        }

        if($user['user_type']==1){
            $this -> returnError([],400,"您不是供应商！");
        }else{
            $supplier_id = $user['supplier_id'];
            //供应商用户
            $where['supplier_id'] = $supplier_id;
            if($order_status>0&&$order_status<4){
                $where['order_status'] = $order_status;
            }
        }
        if($date_type>0){
            //最近三日
            $where_time = strtotime('-3 day');
            $where['created_at'] = array("gt",$where_time);
        }
        if($client_id>0){
            //买家id
            $where['client_id'] = $client_id;
        }

        //查询当前买家的卖家
        $list = Db::name('order')->where($where) -> order("created_at desc") ->select();
        foreach($list as $key => $value){
            $order_amount = 0;
            if($value['order_type']==0){
                //正常订单,联合利华产品
                $detail_1 = Db::name('order_detail')->where(["order_id"=>$value['id']])->select();
                foreach($detail_1 as $k1=>$v1){
                    if($v1['is_unlieve']==1){
                        $detail_product = Db::name('product')->where(["id"=>$v1['product_id']])->find();
                        $detail_1[$k1]['uploaded_img'] = Config::get('host_url') .$detail_product['product_pic'];
                    }
                }
                //正常订单,非联合利华产品
                $detail_0 = Db::connect('db_con')->name('order_detail')->where(["order_id"=>$value['id']])->select();
                foreach($detail_0 as $k1=>$v1){
                    if($v1['is_unlieve']==1){
                        $detail_product = Db::name('product')->where(["id"=>$v1['product_id']])->find();
                        $detail_0[$k1]['uploaded_img'] = Config::get('host_url') .$detail_product['product_pic'];
                    }
                }
                $detail = array_merge($detail_1,$detail_0);
                $list[$key]['product_list'] = $detail;
                foreach($detail as $k => $v){
                    $order_amount += $v['product_price']*$v['product_count'];
                }
            }else{
                //图片订单
                $detail = Db::connect('db_con')->name('order_detail')->where(["order_id"=>$value['id']])->find();
                $list[$key]['product_list'] = json_decode($detail['uploaded_img'],true);
            }
            //获取买家的餐厅
            $order_user = Db::name("user") -> where(array("id"=>$value['client_id'])) -> find();
            $list[$key]['restaurant_name'] = $order_user['restaurant_name'];
            $list[$key]['province'] = $order_user['province'];
            $list[$key]['city'] = $order_user['city'];
            $list[$key]['district'] = $order_user['district'];
            $list[$key]['address'] = $order_user['address'];
            if($order_user['type']==5){
                $list[$key]['nickname'] = $order_user['restaurant_name'];
            }else{
                $list[$key]['nickname'] = $order_user['nickname'];
            }
            $list[$key]['mobile'] = $order_user['mobile'];
//          $list[$key]['order_amount'] = $order_amount;
            $list[$key]['order_amount'] =  round($order_amount, 2);
            $list[$key]['date'] = date("Y年m月d日",$value['created_at']);
        }
        $data = [];
        foreach($list as $key => $value){
            $is_has = 0;
            $has_key = 0;
            //按照日期划分
            foreach($data as $k => $v){
                if($value['date']==$v['date']){
                    $is_has = 1;
                    $has_key = $k;
                    break;
                }
            }
            if($is_has){
                $data[$has_key]['list'][] = $value;
            }else{
                $temp = [];
                $temp['date'] = $value['date'];
                $temp['list'][] = $value;
                $data[] = $temp;
            }
        }

        //获取供应商下的订单数量
        $showLayer = 0;//默认不显示
        $orderNum  = Db::name('order')->where('supplier_id', $supplier_id)->count();
        if ((int)$user['show_layer'] === 1 && (int)$orderNum > 0) {
            $showLayer = 1;
        }
        $this -> returnSuccess_2($data,200,"获取订单列表成功",$showLayer);
    }

    /**
     * @desc 请求成功回调(添加供应商下订单的数量)
     * @author: 青菜
     * @time: 2018-10-8 15:05:46
     */
    public function returnSuccess_2($data,$code = 200,$msg = '请求成功',$show_layer){
        $da['code'] = $code;
        $da['msg'] = $msg;
        $da['data'] = $data;
        // 查询用户信息

        $unionid = request()->param('unionId');
        if(!empty($unionid)){
            $user_type = Db::name('user')->where(['union_id'=>$unionid])->value('user_type');
        }else{
            $token = request()->param('token');
            $unionid = Db::name('user_token')->where(['token'=>$token])->value('unionid');
            if(!empty($unionid)){
                $user_type = Db::name('user')->where(['union_id'=>$unionid])->value('user_type');
            }
        }
        if(empty($user_type)){
            $user_type = 0;
        }
        $da['user_type'] = $user_type;
        $da['show_layer'] = $show_layer;

        echo json_encode($da); exit;
    }
    //编辑订单
    public function edit_order(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_id = request()->param('order_id');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=2){
            $this -> returnError([],400,"您还不是供应商用户！");
        }
        if(!$order_id){
            $this -> returnError([],400,"参数不正确！");
        }
        //查询订单信息
        $order = DB::name("order") -> where(['id'=>$order_id]) -> find();
        if(!$order){
            $this -> returnError([],400,"未发现此订单！");
        }
        Db::startTrans();
        Db::connect('db_con') -> startTrans();
        if($order['order_status']==1){
            $product_list = request()->param('product_list');
            $product_list = json_decode($product_list,true);
            if(!count($product_list)){
                DB::rollback();
                Db::connect('db_con') -> rollback();
                $this -> returnError([],400,"要修改的产品数据不能为空！");
            }
            if($product_list['is_unlieve']==1){
                //查询订单信息,
                $order_detail = DB::name("order_detail") -> where(['order_id'=>$order_id,"product_id"=>$product_list['product_id']]) -> find();
                if(!$order_detail){
                    DB::rollback();
                    Db::connect('db_con') -> rollback();
                    $this -> returnError([],400,"要修改的产品数据在此订单中不存在！");
                }
            }else{
                //查询订单信息,
                $order_detail = Db::connect('db_con')->name("order_detail") -> where(['order_id'=>$order_id,"product_id"=>$product_list['product_id']]) -> find();
                if(!$order_detail){
                    DB::rollback();
                    Db::connect('db_con') -> rollback();
                    $this -> returnError([],400,"要修改的产品数据在此订单中不存在！");
                }
            }

            //修改订单数据
            $temp = [];
            $temp['product_count'] = $product_list['product_count'];
            $temp['product_price'] = $product_list['product_price'];
            if($product_list['is_unlieve']==1) {
                $result = DB::name("order_detail")->where(['id' => $order_detail['id']])->update($temp);
                if ($result === false) {
                    DB::rollback();
                    Db::connect('db_con')->rollback();
                    $this->returnError([], 400, "编辑订单失败！");
                }
            }else{
                $result = Db::connect('db_con')->name("order_detail")->where(['id' => $order_detail['id']])->update($temp);
                if ($result === false) {
                    DB::rollback();
                    Db::connect('db_con')->rollback();
                    $this->returnError([], 400, "编辑订单失败！");
                }
            }
            if($product_list['is_unlieve']==1){
                //联合利华产品
                //修改产品订单信息
                $price = DB::name("product_price") -> where(['product_id'=>$product_list['product_id'],"client_id"=>$order['client_id']]) -> find();
                if($price){
                    $temp = [];
                    $temp['product_price'] = $product_list['product_price'];
                    $temp['updated_at'] = time();
                    $result = DB::name("product_price") -> where(['id'=>$price['id']]) -> update($temp);
                    if($result===false){
                        DB::rollback();
                        Db::connect('db_con') -> rollback();
                        $this -> returnError([],400,"编辑订单失败！");
                    }
                }else{
                    $temp = [];
                    $temp['product_id'] = $product_list['product_id'];
                    $temp['product_price'] = $product_list['product_price'];
                    $temp['supplier_id'] = $order['supplier_id'];
                    $temp['client_id'] = $order['client_id'];
                    $temp['created_at'] = time();
                    $result = DB::name("product_price") -> where(['id'=>$price['id']]) -> insert($temp);
                    if($result===false){
                        DB::rollback();
                        Db::connect('db_con') -> rollback();
                        $this -> returnError([],400,"编辑订单失败！");
                    }
                }
            }else{
                //其他产品
                //修改产品订单信息
                $price = Db::connect('db_con')->name("product_price") -> where(['product_id'=>$product_list['product_id'],"client_id"=>$order['client_id']]) -> find();
                if($price){
                    $temp = [];
                    $temp['product_price'] = $product_list['product_price'];
                    $temp['updated_at'] = time();
                    $result = Db::connect('db_con')->name("product_price") -> where(['id'=>$price['id']]) -> update($temp);
                    if($result===false){
                        DB::rollback();
                        Db::connect('db_con') -> rollback();
                        $this -> returnError([],400,"编辑订单失败！");
                    }
                }else{
                    $temp = [];
                    $temp['product_id'] = $product_list['product_id'];
                    $temp['product_price'] = $product_list['product_price'];
                    $temp['supplier_id'] = $order['supplier_id'];
                    $temp['client_id'] = $order['client_id'];
                    $temp['created_at'] = time();
                    $result = Db::connect('db_con')->name("product_price") -> where(['id'=>$price['id']]) -> insert($temp);
                    if($result===false){
                        DB::rollback();
                        Db::connect('db_con') -> rollback();
                        $this -> returnError([],400,"编辑订单失败！");
                    }
                }
            }
        }
        DB::commit();
        Db::connect('db_con') -> commit();
        $this -> returnSuccess([],200,"编辑订单成功！");
    }
    //获取订单详情
    public function order_detail(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_id = request()->param('order_id');
        if(!$order_id){
            $this -> returnError([],400,"参数不正确！");
        }
        //查询当前创建人是否是买家
        $where['id'] = $order_id;
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']==1){
            //终端用户
            $where['client_id'] = $user['id'];
        }elseif($user['user_type']==2){
            //供应商用户
            $where['supplier_id'] = $user['supplier_id'];
        }else{
            $this -> returnError([],400,"未知类型用户！");
        }
        //查询订单信息
        $order = DB::name("order") -> where($where) -> find();
        if(!$order){
            $this -> returnError([],400,"未发现此订单！");
        }
        $order_amount = 0;
        //查询当前买家的卖家
        if($order['order_type']==0){
            //正常订单，联合利华产品
            $detail_1 = Db::name('order_detail')->where(["order_id"=>$order_id])->select();
            foreach($detail_1 as $k1=>$v1) {
                if ($v1['is_unlieve'] == 1) {
                    $detail_product = Db::name('product')->where(["id" => $v1['product_id']])->find();
                    $detail_1[$k1]['uploaded_img'] = Config::get('host_url') . $detail_product['product_pic'];
                }
            }
            //正常订单，非联合利华产品
            $detail_0 = Db::connect('db_con')->name("order_detail")->where(["order_id"=>$order_id])->select();
            foreach($detail_0 as $k1=>$v1) {
                if ($v1['is_unlieve'] == 1) {
                    $detail_product = Db::name('product')->where(["id" => $v1['product_id']])->find();
                    $detail_0[$k1]['uploaded_img'] = Config::get('host_url') . $detail_product['product_pic'];
                }
            }
            $detail = array_merge($detail_1,$detail_0);
            $order['product_list'] = $detail;
            foreach($order['product_list'] as $k => $v){
                $order_amount += $v['product_price']*$v['product_count'];
            }
        }else{
            //图片订单
            $detail = Db::connect('db_con')->name("order_detail")->where(["order_id"=>$order_id])->find();
            $order['product_list'] = json_decode($detail['uploaded_img'],true);
        }

        //获取买家的餐厅
        $order_user = Db::name("user") -> where(array("id"=>$order['client_id'])) -> find();
        $order['restaurant_name'] = $order_user['restaurant_name'];
        $order['province'] = $order_user['province'];
        $order['city'] = $order_user['city'];
        $order['district'] = $order_user['district'];
        $order['address'] = $order_user['address'];
        $order['nickname'] = $order_user['nickname'];
        /*if($order_user['type']==5){
            $order['nickname'] = $order_user['restaurant_name'];
        }else{
            $order['nickname'] = $order_user['nickname'];
        }*/

        $order['mobile'] = $order_user['mobile'];
//      $order['order_amount'] = $order_amount;

        $order['order_amount'] =  round($order_amount, 2);
        $this -> returnSuccess($order,200,"获取订单详情成功");
    }
    //删除订单产品
    public function del_order(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_id = request()->param('order_id');
        $product_id = request()->param('product_id');
        $is_unlieve = request()->param('is_unlieve');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=2){
            $this -> returnError([],400,"您还不是供应商用户！");
        }
        if(!$order_id || !$product_id){
            $this -> returnError([],400,"参数不正确！");
        }
        //查询订单信息
        $order = DB::name("order") -> where(['id'=>$order_id]) -> find();
        if(!$order){
            $this -> returnError([],400,"未发现此订单！");
        }
        if($is_unlieve==1){
            //查询订单信息
            $order_detail = DB::name("order_detail") -> where(['order_id'=>$order_id,"product_id"=>$product_id,"is_unlieve"=>$is_unlieve]) -> find();
            if(!$order_detail){
                $this -> returnError([],400,"要删除的产品数据在此订单中不存在！");
            }
            //修改订单数据
            $result = DB::name("order_detail") -> where(['id'=>$order_detail['id']]) -> delete();
            if($result===false){
                $this -> returnError([],400,"删除失败！");
            }
        }else{
            //查询订单信息
            $order_detail = Db::connect('db_con')->name("order_detail") -> where(['order_id'=>$order_id,"product_id"=>$product_id,"is_unlieve"=>$is_unlieve]) -> find();
            if(!$order_detail){
                $this -> returnError([],400,"要删除的产品数据在此订单中不存在！");
            }
            //修改订单数据
            $result = Db::connect('db_con')->name("order_detail") -> where(['id'=>$order_detail['id']]) -> delete();
            if($result===false){
                $this -> returnError([],400,"删除失败！");
            }
        }

        $this -> returnSuccess([],200,"删除成功！");
    }
    //确认订单
    public function agree_order(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_id = request()->param('order_id');
        $remark = request()->param('remark');
        $form_id = request()->param('form_id');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=2){
            $this -> returnError([],400,"您还不是供应商用户！");
        }
        if(!$order_id){
            $this -> returnError([],400,"参数不正确！");
        }
        //查询订单信息
        $order = DB::name("order") -> where(['id'=>$order_id]) -> find();
        if(!$order){
            $this -> returnError([],400,"未发现此订单！");
        }
        //查询下单人信息
        $order_user = Db::name("user") -> where(array("id"=>$order['client_id'])) -> find();
        $num = Db::name("order") -> where(array("order_status"=>2,"client_id"=>$order_user['id'])) -> count();
        $supplier = Db::name("supplier") -> where(array("id"=>$user['supplier_id'])) -> find();
        if($supplier['desc']){
            $notice_text = "点击详情查看订单状态，持续下单还可获得更多奖励哦！";
        }else{
            $notice_text = "您的订单已被商家确认，点击详情查看订单状态";
        }

        //修改订单状态
        $temp = [];
        $temp['order_status'] = 2;
        $temp['updated_at'] = time();
        $temp['remark'] = $remark;
        $result = Db::name("order") -> where(array("id"=>$order_id)) -> update($temp);
        if($result===false){
            $this -> returnError([],400,"确认订单失败！");
        }
        DB::commit();
        Db::connect('db_con') -> commit();

        //查询下单产品名称
        $detail = Db::name("order_detail") -> where(array("order_id"=>$order_id)) -> select();
        $product_name = [];
        foreach ($detail as $key => $value) {
            $product_name[] = $value['product_name'];
        }
        $detail = Db::connect('db_con')->name("order_detail") -> where(array("order_id"=>$order_id)) -> select();
        foreach ($detail as $key => $value) {
            $product_name[] = $value['product_name'];
        }
        //存储用户的form_id
        $info = [];
        $info['form_id'] = $form_id;
        $info['unionid'] = $unionid;
        $info['create_time'] = time();
        DB::name("form") -> insert($info);
        //确认订单给下单人发送模板消息
        $form_info = DB::name("form") -> where(array("unionid"=>$order_user['union_id'],"status"=>1)) -> select();
        $form = [];
        foreach($form_info as $key => $value){
            if(time()<($value['create_time']+24*60*60*7)){
                $form = $value;
                break;
            }else{
                DB::name("form") -> where(array("id"=>$value['id'])) -> update(array("status"=>0));
            }
        }
        if(count($form)>0){
            $send_data = array(
                "keyword1"=>array(
                    "value"=>$order['order_number'],
                    //"value"=>'woshihaoren',
                    "color"=>"#4a4a4a"
                ),
                "keyword2"=>array(
                    "value"=>date("Y-m-d H:i:s",$order['created_at']),
                    "color"=>"#9b9b9b"
                ),
                "keyword3"=>array(
                    "value"=>implode(",", $product_name),
                    "color"=>"#9b9b9b"
                ),
                "keyword4"=>array(
                    "value"=>"已确认",
                    "color"=>"#9b9b9b"
                ),
                "keyword5"=>array(
                    "value"=>$notice_text,
                    "color"=>"#9b9b9b"
                )
            );
            $page = "/pages/customer/allOrder/allOrder";
            $this -> send_sms($form['form_id'],$order_user['open_id'],$send_data,"1rotbEHivPe47eietFwj_T5ja2lKIEf5QIt_i2FPNa8",$page);
            DB::name("form") -> where(array("id"=>$form['id'])) -> update(array("status"=>0));
        }

        //确认订单成功给用户发送短信消息
            $mobile = $order_user['mobile'];
            $templateCode = "SMS_143718088";
            $sms_data = array(
                "code" => $order['order_number']
                );
            $this -> send_sms_info($mobile,$sms_data,$templateCode);

        $this -> returnSuccess([],200,"确认订单成功！");
    }
    //拒绝订单
    public function cancel_order(){
        $this->checkToken();
        $unionid = request()->param('unionId');
        $order_id = request()->param('order_id');
        $remark = request()->param('remark');
        $form_id = request()->param('form_id');
        //查询当前创建人是否是买家
        $user = Db::name('user')->where(['union_id'=>$unionid,"status"=>1])->find();
        if($user['user_type']!=2){
            $this -> returnError([],400,"您还不是供应商用户！");
        }
        if(!$order_id){
            $this -> returnError([],400,"参数不正确！");
        }
        //查询订单信息
        $order = DB::name("order") -> where(['id'=>$order_id]) -> find();
        if(!$order){
            $this -> returnError([],400,"未发现此订单！");
        }
        //修改订单状态
        $temp = [];
        $temp['order_status'] = 3;
        $temp['updated_at'] = time();
        $temp['remark'] = $remark;
        $result = Db::name("order") -> where(array("id"=>$order_id)) -> update($temp);
        if($result===false){
            $this -> returnError([],400,"拒绝订单失败！");
        }
        //查询下单人信息
        $order_user = Db::name("user") -> where(array("id"=>$order['client_id'])) -> find();
        //查询下单产品名称
        $detail = Db::name("order_detail") -> where(array("order_id"=>$order_id)) -> select();
        $product_name = [];
        foreach ($detail as $key => $value) {
            $product_name[] = $value['product_name'];
        }
        $detail = Db::connect('db_con')->name("order_detail") -> where(array("order_id"=>$order_id)) -> select();
        foreach ($detail as $key => $value) {
            $product_name[] = $value['product_name'];
        }
        $supplier = Db::name("supplier") -> where(array("id"=>$user['supplier_id'])) -> find();
        if($supplier['desc']){
            $notice_text = "请点击查看拒绝原因，及时与批发商进行沟通";
        }else{
            $notice_text = "您的订单已被商家拒绝，点击详情查看订单状态";
        }
        //拒绝订单给下单人发送模板消息
        $form_info = DB::name("form") -> where(array("unionid"=>$order_user['union_id'],"status"=>1)) -> select();
        $form = [];
        foreach($form_info as $key => $value){
            if(time()<($value['create_time']+24*60*60*7)){
                $form = $value;
                break;
            }else{
                DB::name("form") -> where(array("id"=>$value['id'])) -> update(array("status"=>0));
            }
        }
        if(count($form)>0){
            $send_data = array(
                "keyword1"=>array(
                    "value"=>$order['order_number'],
                    //"value"=>'woshihaoren',
                    "color"=>"#4a4a4a"
                ),
                "keyword2"=>array(
                    "value"=>date("Y-m-d H:i:s",$order['created_at']),
                    "color"=>"#9b9b9b"
                ),
                "keyword3"=>array(
                    "value"=>implode(",", $product_name),
                    "color"=>"#9b9b9b"
                ),
                "keyword4"=>array(
                    "value"=>"已拒绝",
                    "color"=>"#9b9b9b"
                ),
                "keyword5"=>array(
                    "value"=>$notice_text,
                    "color"=>"#9b9b9b"
                )
            );
            $page = "/pages/customer/allOrder/allOrder";
            $this -> send_sms($form['form_id'],$order_user['open_id'],$send_data,"1rotbEHivPe47eietFwj_T5ja2lKIEf5QIt_i2FPNa8",$page);
            DB::name("form") -> where(array("id"=>$form['id'])) -> update(array("status"=>0));
        }
        //拒绝订单成功给用户发送短信消息
            $mobile = $order_user['mobile'];
            $templateCode = "SMS_143713203";
            $sms_data = array(
                "code" => $order['order_number']
                );
            $this -> send_sms_info($mobile,$sms_data,$templateCode);
        $this -> returnSuccess([],200,"订单已拒绝");
    }

    public function send_sms($form_id,$touser,$data,$template_id,$page=''){
        $url = config('http_url');
        $weixin = new Wechatapi($url);
        $token = $weixin->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$token;
        $dd = array();
        //$dd['access_token']=$access_token;
        $dd['touser']=$touser;
        $dd['template_id']=$template_id;
        $dd['page']=$page;  //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,该字段不填则模板无跳转。
        $dd['form_id']=$form_id;

        $dd['data']=$data;                        //模板内容，不填则下发空模板

        $dd['color']='';                        //模板内容字体的颜色，不填默认黑色
        //$dd['color']='#ccc';
        $dd['emphasis_keyword']='';    //模板需要放大的关键词，不填则默认无放大
        //$dd['emphasis_keyword']='keyword1.DATA';

        //$send = json_encode($dd);   //二维数组转换成json对象

        /* curl_post()进行POST方式调用api： api.weixin.qq.com*/
        $result = $this->https_curl_json($url,$dd,'json');
        return $result;
    }
    /* 发送json格式的数据，到api接口 -xzz0704  */
    function https_curl_json($url,$data,$type){
        if($type=='json'){//json $_POST=json_decode(file_get_contents('php://input'), TRUE);
            $headers = array("Content-type: application/json;charset=UTF-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            $data=json_encode($data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
        return $output;
    }

    public function get_data(){
        $list = Db::name('user') -> select();
        var_dump($list);
    }
    public function get_data_supplier(){
        $list = Db::name('supplier') -> select();
        var_dump($list);
    }

    public function get_form_data(){
        $list = Db::name('user_token') -> select();
        var_dump($list);
    }
    public function sms_test(){
        $mobile = "15618837152";
        $templateCode = "SMS_109720616";
        $data = array(
            "code" => "123456"
            );
        $this -> send_sms_info($mobile,$data,$templateCode);
    }

    public function save_pwd(){
        $password = "989898";
        $data = [
             'is_first' => 0,
         ];
        $result = Db::name('supplier')->where(['id'=>21])->update($data);
        var_dump($result);exit;
    }

}