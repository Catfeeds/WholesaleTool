<?php
// +----------------------------------------------------------------------
// | desc: Login.php 用户登录注册
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018/8/12 19:39
// +----------------------------------------------------------------------

namespace app\wxapi\controller;

use think\Config;
use think\Db;
use think\Log;
use wechat\Wechatapi;

class Login extends Common
{
    /**
     * @desc 账号登录注册
     * @author: yangsy
     * @time: 2018-08-13 10:43:51
     */
    public function login(){
        $post = request()->post();
        //$url = 'http://os.prowiser.cn';
        $url = Config::get('host_url');
        $weixin = new Wechatapi($url);
        $wx_info = $weixin->getAccessToken();
        dump($wx_info);

    }
    
    /**
     * @desc 获取用户 UnionID 以及 登录 token 买家
     * @author: yangsy
     * @time: 2018-08-13 11:23:53
     */
    public function eu_login(){
        if(request()->isPost()){
            // 验证请求数据是否正确
            $post = request()->post();
            if(empty($post)){
               $this->returnError([],'400','提交信息错误！');
            }
            $login_info = $post;

            // 解析微信用户信息
            $weixin = new Wechatapi(1);
            $wx_info = $weixin->getWechatUserInfoBySmall($login_info);

            $wx_info = json_decode($wx_info,true);
            if(empty($wx_info)){
                $this->returnError([],'400','解析用户信息失败！');
            }
            $gender = '';
            if($wx_info['gender'] == 1){
                $gender = 'male';
            }elseif($wx_info['gender'] == 1){
                $gender = 'female';
            }

            // 会员信息查询接口
            $data = [
                'uid' => $wx_info['unionId'],
                'name' => $wx_info['nickName'],
                'avatar' => $wx_info['avatarUrl'],
                'country' => $wx_info['country'],
                'province' => $wx_info['province'],
                'city' => $wx_info['city'],
                'gender' => $gender,
            ];

            $data = json_encode($data);

            $url = config('sso_http_url').'/api/micro-app/member/member-info?app_key='.config('sso_app_key');

            $re = httpPost($url,$data);
            $re = json_decode($re,true);
            if($re['code'] != 200){
                $this->returnError([],'400',$re['message']);
            }

            // 验证用户是否存在，如果没有，保存基本信息
            $user = Db::name('user')->where(['union_id'=>$wx_info['unionId']])->find();
            if(empty($user)){
                // 添加新用户
                $data = [
                    'nickname' => $wx_info['nickName'],
                    'union_id' => $wx_info['unionId'],
                    'open_id' => $wx_info['openId'],
                    'created_at' => time(),
                    'user_type' => empty($login_info['user_type'])?0:$login_info['user_type'],
                    'avatar' => $wx_info['avatarUrl']
                ];

                if($login_info['user_type'] == 2){
                    $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                }
                Db::name('user')->insert($data);
            }else{
                $data = [
                    'open_id' => $wx_info['openId'],
                ];
                Db::name('user')->where(['id'=>$user['id']])->update($data);
            }

            if($re['data']['member_info']['is_activated'] != false || !empty($user)){
                // 查询当前用户是否保存在了本地数据库中
                if(!empty($user)){
                    // 用户存在，判断当前用户状态、!= 0  不需要登录 否则需要进行登录
                    if($user['status'] != 0){
                        $this->returnSuccess(['token'=>$this->token($wx_info['unionId']),'is_register'=>0,'unionId'=>$wx_info['unionId'],'user_type'=>$user['user_type']]);
                    }else{
                        $this->returnSuccess(['token'=>$this->token($wx_info['unionId']),'is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>$user['user_type']]);
                    }
                }else{
                    $this->returnSuccess(['token'=>$this->token($wx_info['unionId']),'is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>empty($login_info['user_type'])?0:$login_info['user_type']]);
                }
            }else{
                $this->returnSuccess(['token'=>$this->token($wx_info['unionId']),'is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>empty($login_info['user_type'])?0:$login_info['user_type']]);
            }
        }
    }
    
    /**
     * @desc 注册会员数据 买家
     * @author: yangsy
     * @time: 2018-08-13 15:42:24
     */
    public function eu_register(){
        $this->checkToken();
        $post = request()->post();

        $data = [
            'phone' => $post['mobile'],
            'smscode' => $post['code'],
        ];
        $data = json_encode($data);

        // 手机号登录绑定
        $url = config('sso_http_url').'/api/micro-app/member/check-sms-code?app_key='.config('sso_app_key');
        $re = httpPost($url,$data);
        $re = json_decode($re,true);

//        if($re['code'] != 200){
//            if($re['message'] == 'Sms code is invalid'){
//                $this->returnError('验证码输入错误，请认真核对！',5002,'验证码输入错误，请认真核对！');
//            }else{
//                $this->returnError('验证码超时！',5003,'验证码超时！');
//            }
//        }

        // 验证地区是否开通
        $province_code = $post['province_code'];
        $city_code = $post['city_code'];
        $province_status = Db::name('area')->where(['code'=>$province_code,'level'=>1])->value(['is_kt']);
        $city_status = Db::name('area')->where(['code'=>$city_code,'level'=>2])->value(['is_kt']);
        /*if($province_status != 1 || $city_status != 1){
            $this->returnError('该地区暂未开通',5001,'该地区暂未开通');
        }*/

        // 注册添加用户
        $unionid = $post['unionId'];
        $user = Db::name('user')->where(['union_id'=>$unionid])->find();
        $data = [
            'union_id' => $unionid,
            'job_code' => $post['job_code'],
            'aid'   => $re['data']['aid'],
            'mobile' => $post['mobile'],
            'user_type' => $post['user_type'],
            'province' => $post['province'],
            'province_code' => $post['province_code'],
            'city' => $post['city'],
            'city_code' => $post['city_code'],
            'district' => $post['district'],
            'district_code' => $post['district_code'],
            'address' => $post['address'],
            'restaurant_name' => $post['restaurant_name'],
            'nickname' => $post['nickname'],
            'type' => $post['type'],
            'status' => 1,
        ];
        $check_status = 0;
        foreach($data as $key => $value){
            if(empty($value)){
//                echo "<pre>";print_r( $key.'-'.$value );die;
//                Log::record('下面是empty的数据');
//                Log::record($key.'-'.$value);
                $check_status = 1;
                break;
            }
        }
        $data['avg_amount'] = $post['avg_amount'];
//        if($check_status == 1){
//            $this->returnError('请输入完整信息');
//        }
        if(!empty($post['supplier_id'])){
            $data['supplier_id'] = $post['supplier_id'];
        }

        if(!empty($post['supplier_type'])){
            $data['supplier_type'] = $post['supplier_type'];
        }

        if($province_status != 1 || $city_status != 1){
            $data['supplier_type'] = -1;
        }

        if(!empty($user)){
            $data['updated_at'] = time();
            // 修改用户信息
            if(Db::name('user')->where(['union_id'=>$unionid])->update($data)){
                // 判断生成用户关系
                if(!empty($post['supplier_id'])) {
                    if (!empty($data['supplier_id'])) {
                        $da = [
                            'client_id' => $user['id'],
                            'supplier_id' => $data['supplier_id'],
                            'status' => 1,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        Db::name('client_supplier')->insert($da);
                    }
                }
                //将供应商的家乐产品绑定到终端用户
                $this->insertIntoProductEnduser($user['id'],$data['supplier_id']);

                if($province_status != 1 || $city_status != 1){
                    $this->returnError('该地区暂未开通',5001,'该地区暂未开通');
                }
                $this->returnSuccess(['nickname'=>$post['nickname'],'user_type'=>$post['user_type'],'mobile'=>$post['mobile']]);
            }else{
                $this->returnError([],'400','注册失败');
            }
        }else{
            $data['created_at'] = time();
            // 添加用户信息
            $id = Db::name('user')->insertGetId($data);
            if($id){
                // 判断生成用户关系
                if(!empty($data['supplier_id'])){
                    $data = [
                        'client_id' => $id,
                        'supplier_id' => $data['supplier_id'],
                        'status' => 1,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                    Db::name('client_supplier')->insert($data);
                }
                //将供应商的家乐产品绑定到终端用户
                $this->insertIntoProductEnduser($id,$data['supplier_id']);

                if($province_status != 1 || $city_status != 1){
                    $this->returnError('该地区暂未开通',5001,'该地区暂未开通');
                }
                $this->returnSuccess(['nickname'=>$post['nickname'],'user_type'=>$post['user_type'],'mobile'=>$post['mobile']]);
            }else{
                $this->returnError([],'400','注册失败');
            }
        }
    }


    private function insertIntoProductEnduser($id,$supplier_id)
    {

        $addData     = [];
        $productData = Db::name( 'product_supplier' )->where( 'supplier_id',$supplier_id )->select();

        foreach( $productData as $k1 => $v1 ){
            $addData[$k1][ 'client_id' ]   = $id;//用户id
            $addData[$k1][ 'parent_id' ]   = $v1[ 'product_id' ];//供应商拥有的额商品id
            $addData[$k1][ 'supplier_id' ] = $supplier_id;//供应商id
            $addData[$k1][ 'created_at' ]  = time();
        }

        Db::name( 'product_enduser' )->insertAll( $addData );


    }

    
    /**
     * @desc 生成会员token
     * @author: yangsy
     * @time: 2018-08-13 15:05:44
     */
    public function token($unionid){
        $token = Db::name('user_token')->where(['unionid'=>$unionid])->find();
        $to = $this->generate_password();
        $data = [
            'unionid' => $unionid,
            'token' => $to,
            'expire_time' => time(),
        ];
        if(!empty($token)){
            $data['updated_at'] = time();
            Db::name('user_token')->where(['id'=>$token['id']])->update($data);
        }else{
            $data['created_at'] = time();
            // 生成新的token 并且保存
            Db::name('user_token')->insert($data);
        }
        return $to;
    }
    
    
    /**
     * @desc 生成随机字符串
     * @author: yangsy
     * @time: 2018-08-13 15:16:13
     */
    public function generate_password( $length = 32 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }

    
    /**
     * @desc 供货商登录
     * @author: yangsy
     * @time: 2018-08-16 16:12:48
     */
    public function supplierLogin(){
//        $this->checkToken();

        $post = request()->post();
        if(empty($post)){
            $this->returnError([],'400','提交信息错误！');
        }

        // 验证登录信息
            $user = Db::name('user')->where(['union_id'=>$post['unionId']])->find();
        if(empty($user)){
            $this->returnError([],'400','登录失败！');

        }

        if($user['user_type'] != 2){
            $this->returnError([],'400','您不是供货商！');
        }

        $password = $post['password'];
        $mobile = $post['mobile'];
        if(empty($password) || empty($mobile)){
            $this->returnError([],'400','请输入正确的登录信息！');
        }

        $supplier = Db::name('supplier')
            ->where(['mobile'=>$mobile])
            ->whereOr(['name'=>$mobile])
            ->find();
        if(empty($supplier)){
            $this->returnError([],'400','登录失败,请检查登录信息！');
        }

        $iterations = 1000;
        $salt = "lihua";
        $password = hash_pbkdf2("sha256", $password, $salt, $iterations, 32);

        if($supplier['password'] != $password){
            $this->returnError([],'400','登录失败,请检查登录信息！');
        }

        if(!empty($user['supplier_id'])){
            if($supplier['id'] != $user['supplier_id']){
                $this->returnError([],'400','登录失败,请检查登录信息！');
            }else{
                $this->returnSuccess(['token'=>$this->token($post['unionId']),'nickname'=>$user['nickname'],'avatar'=>$user['avatar'],'mobile'=>$mobile,'user_type'=>1,'supplier_id'=>$supplier['id'],'is_first'=>$supplier['is_first']]);
            }
        }else{
            // 修改用户绑定的卖家信息
            if(Db::name('user')->where(['id'=>$user['id']])->update(['supplier_id'=>$supplier['id']])){
                $this->returnSuccess(['token'=>$this->token($post['unionId']),'nickname'=>$user['nickname'],'avatar'=>$user['avatar'],'mobile'=>$mobile,'user_type'=>1,'supplier_id'=>$supplier['id'],'is_first'=>$supplier['is_first']]);
            }else{
                $this->returnError([],'400','登录失败！');
            }
        }
    }


    /**
     * @desc 重置密码
     * @author: yangsy
     * @time: 2018-09-02 19:03:19
     */
    public function resetPassword(){
        $this->checkToken();
        // 验证当前登录账号
        $unionId = request()->post('unionId');
        // 查询当前账号绑定
        $user = Db::name('user')->where(['union_id'=>$unionId])->find();

        if(empty($user) || $user['user_type'] != 2){
            $this->returnError('',400,'当前用户不可更改密码');
        }

        $password = request()->post('password');
        $password_confirm = request()->post('password_confirm');

        if($password != $password_confirm){
            $this->returnError('',400,'确认密码不一致');
        }
        $iterations = 1000;
        $salt = "lihua";
        $password = hash_pbkdf2("sha256", $password, $salt, $iterations, 32);
        $data = [
            'password' => $password,
            'is_first' => 1,
        ];

        if(Db::name('supplier')->where(['id'=>$user['supplier_id']])->update($data)){
            $this->returnSuccess('操作成功');
        }else{
            $this->returnError('',400,'重置失败，请检查是否作出修改');
        }
    }
    
    /**
     * @desc 找回密码
     * @author: yangsy
     * @time: 2018-09-02 19:18:40
     */
     public function retrievePassword(){
//         $this->checkToken();
         $mobile = request()->post('mobile');
         $password = request()->post('password');
         $password_confirm = request()->post('password_confirm');
         $code = request()->post('code');
         $unionId = request()->post('unionId');
         // 验证数据
         if(empty($mobile) || empty($password_confirm) || empty($password) || empty($code) || empty($unionId)){
             $this->returnError('',400,'请输入完整数据');
         }

         $where = [
             'union_id' => $unionId,
             'code' => $code,
             'mobile' => $mobile,
         ];
         $code = Db::name('sms_code')->where($where)->find();
         if(empty($code)){
             $this->returnError('',400,'验证码不正确！');
         }

         if($code['created_at'] < (time()-600)){
             $this->returnError('',400,'验证码超时！');
         }

         if($password != $password_confirm){
             $this->returnError('',400,'确认密码不一致！');
         }

         // 查询当前账号绑定
         $user = Db::name('user')->where(['union_id'=>$unionId])->find();

         if(empty($user) || $user['user_type'] != 2){
             $this->returnError('',400,'当前用户不可更改密码');
         }

         $supplier = Db::name('supplier')->where(['id'=>$user['supplier_id']])->find();

         if(empty($supplier)){
             $this->returnError('',400,'当前用户未绑定供应商');
         }

         if($supplier['mobile'] != $mobile){
             $this->returnError('',400,'用户手机号不正确');
         }
         $iterations = 1000;
         $salt = "lihua";
         $password = hash_pbkdf2("sha256", $password, $salt, $iterations, 32);
         $data = [
             'password' => $password,
             'updated_at' => time(),
         ];
         $result = Db::name('supplier')->where(['id'=>$user['supplier_id']])->update($data);
         if($result){
             $this->returnSuccess(['nickname'=>$user['nickname'],'avatar'=>$user['avatar'],'mobile'=>$mobile,'user_type'=>1,'supplier_id'=>$supplier['id'],'is_first'=>$supplier['is_first']]);
         }else{
             $this->returnError('',400,'找回失败，请检查是否作出修改');
         }

     }
     
     /**
      * @desc 发送短信验证码
      * @author: yangsy
      * @time: 2018-09-02 19:19:11
      */
      public function smsSend(){
//          $this->checkToken();
          $mobile = request()->post('mobile');
          $templateCode = "SMS_109720616";
          $code = rand('100000','999999');
          $data = array(
              "code" => $code
          );

          // 将数据保存到数据库中
          $da = [
              'union_id' => request()->post('unionId'),
              'code' => $code,
              'created_at' => time(),
              'mobile' => $mobile,
          ];
          if(Db::name('sms_code')->insert($da)){
              $this -> send_sms_info($mobile,$data,$templateCode);
              $this->returnSuccess('发送成功');
          }else{
              $this->returnError('',400,'发送失败');
          }
      }
      
      /**
       * @desc 获取供货商基本信息
       * @author: yangsy
       * @time: 2018-09-03 17:14:38
       */
       public function supplier(){
           $this->checkToken();
           // 验证当前登录账号
           $unionId = request()->post('unionId');
           // 查询当前账号绑定
           $user = Db::name('user')->where(['union_id'=>$unionId])->find();

           if(empty($user)){
               $this->returnError('',400,'用户信息错误！');
           }

           // 判断用户是否绑定供货商
           if(empty($user['supplier_id'])){
               $this->returnError('',400,'用户信息错误！');
           }

           $supplier = Db::name('supplier')->where(['id'=>$user['supplier_id']])->find();
           $this->returnSuccess($supplier);
       }

       public function test(){
           $user = Db::name('user')->where(1)->select();
           dump($user);
       }
     
       /**
        * @desc 查询token
        * @author: yangsy
        * @time: 2018-09-05 12:44:06
        */
       public function getToken(){
           $token = Db::name('user_token')->where(['unionid'=>'oT2jev5llA8XMERqaHsRTwP8DXQ0'])->select();
           dump($token);
       }


    /**
     * @desc 获取用户 UnionID 以及 登录 token 买家
     * @author: yangsy
     * @time: 2018-08-13 11:23:53
     */
    public function eu_login1(){
        if(request()->isPost()){
            // 验证请求数据是否正确
            $post = request()->post();
            if(empty($post)){
                $this->returnError([],'400','提交信息错误！');
            }
            $login_info = $post;
            // 解析微信用户信息
            $weixin = new Wechatapi(1);
            $wx_info = $weixin->getWechatUserInfoBySmall($login_info);

            $wx_info = json_decode($wx_info,true);
            if(empty($wx_info)){
                $this->returnError([],'400','解析用户信息失败！');
            }
            $gender = '';
            if($wx_info['gender'] == 1){
                $gender = 'male';
            }elseif($wx_info['gender'] == 1){
                $gender = 'female';
            }
            // 会员信息查询接口
            $data = [
                'uid' => $wx_info['unionId'],
                'name' => $wx_info['nickName'],
                'avatar' => $wx_info['avatarUrl'],
                'country' => $wx_info['country'],
                'province' => $wx_info['province'],
                'city' => $wx_info['city'],
                'gender' => $gender,
            ];

            $data = json_encode($data);

            $url = config('sso_http_url').'/api/micro-app/member/member-info?app_key='.config('sso_app_key');

            $re = httpPost($url,$data);
            $re = json_decode($re,true);
            if($re['code'] != 200){
                $this->returnError([],'400',$re['message']);
            }

            // 验证用户是否存在，如果没有，保存基本信息
            $user = Db::name('user')->where(['union_id'=>$wx_info['unionId']])->find();
            if(empty($user)){
                // 添加新用户
                $data = [
                    'nickname' => $wx_info['nickName'],
                    'union_id' => $wx_info['unionId'],
                    'open_id' => $wx_info['openId'],
                    'created_at' => time(),
                    'user_type' => empty($login_info['user_type'])?0:$login_info['user_type'],
                    'avatar' => $wx_info['avatarUrl']
                ];

                if($login_info['user_type'] == 2){
                    $data['status'] = 1;
                }else{
                    $data['status'] = 0;
                }
                Db::name('user')->insert($data);
            }else{
                $data = [
                    'open_id' => $wx_info['openId'],
                ];
                Db::name('user')->where(['id'=>$user['id']])->update($data);
            }

            if($re['data']['member_info']['is_activated'] != false || !empty($user)){
                // 查询当前用户是否保存在了本地数据库中
                if(!empty($user)){
                    // 用户存在，判断当前用户状态、!= 0  不需要登录 否则需要进行登录
                    if($user['status'] != 0){
                        $this->returnSuccess(['is_register'=>0,'unionId'=>$wx_info['unionId'],'user_type'=>$user['user_type']]);
                    }else{
                        $this->returnSuccess(['is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>$user['user_type']]);
                    }
                }else{
                    $this->returnSuccess(['is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>empty($login_info['user_type'])?0:$login_info['user_type']]);
                }
            }else{
                $this->returnSuccess(['is_register'=>1,'unionId'=>$wx_info['unionId'],'user_type'=>empty($login_info['user_type'])?0:$login_info['user_type']]);
            }
        }
    }
        
}
