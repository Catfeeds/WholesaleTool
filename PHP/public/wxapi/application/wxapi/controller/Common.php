<?php
// +----------------------------------------------------------------------
// | desc: 微信api公共方法
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018-08-11 16:02:11
// +----------------------------------------------------------------------
namespace app\wxapi\controller;

use think\Controller;
use think\Db;
use think\Log;

class Common extends Controller
{
    /**
     * @desc 请求成功回调
     * @author: yangsy
     * @time: 2018-08-11 16:05:54
     */
    public function returnSuccess($data,$code = 200,$msg = '请求成功'){
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

        echo json_encode($da,JSON_UNESCAPED_UNICODE); exit;
    }
    
    /**
     * @desc 请求失败数据返回
     * @author: yangsy
     * @time: 2018-08-11 16:07:42
     */
    public function returnError($data,$code=400,$msg = '请求失败'){
        $da['code'] = $code;
        $da['msg'] = $msg;
        $da['data'] = $data;
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
        echo json_encode($da,JSON_UNESCAPED_UNICODE); exit;
    }
    
    /**
     * @desc 定义空操作返回方法
     * @author: yangsy
     * @time: 2018-08-11 16:10:56
     */
    public function _empty(){
        $da['code'] = '404';
        $da['msg'] = '请求接口不存在';
        $da['data'] = '';
        echo json_encode($da,JSON_UNESCAPED_UNICODE); exit;
    }
    
    /**
     * @desc 验证会员token
     * @author: yangsy
     * @time: 2018-08-13 16:11:16
     */
    public function checkToken(){
        $token = request()->param('token');
        $unionid = request()->param('unionId');
        $token = Db::name('user_token')->where(['unionid'=>$unionid,'token'=>$token])->find();
        if(empty($token) || ($token['expire_time']+28800) < time()){ 
//		if(empty($token) || ($token['expire_time']+1800) < time()){ 原本过期时间8小时，测试改为30分钟
            $this->returnError('','401','token无效！');
        }
    }

    /**
     * 获取惟一订单号
     * @return string
     */
    function get_order_sn(){
        $trade_no =  date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return $trade_no;
    }

    /**
     * 发送短信
     * $mobile 手机号码
     * $data array("code"=>123); 短信模板变量数组
     * $TemplateCode 模板编号
     */
    function send_sms_info($mobile,$data,$TemplateCode){
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \SignatureHelper();
        $params = array ();
        $code = rand(1000, 9999);
        $accessKeyId = "LTAImX5QQX4kgepp";
        $accessKeySecret = "zrUgNOidwx4alTfMBTg186GGcyzA2u";
        $params["PhoneNumbers"] = $mobile;
        $params["SignName"] = "订单助手";
        $params["TemplateCode"] = $TemplateCode;
        $params['TemplateParam'] = $data;
        $params['OutId'] = "12345";
        $params['SmsUpExtendCode'] = "1234567";
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $result = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        /**
         * 记录发送订单短信的日志 -start
         */
        $logData['Scenes']   = "订单通知";
        $logData['mobile'] = $mobile;
        $logData['data']   = $data;
        $logData['TemplateCode']   = $TemplateCode;
        $logData['time']   = date('Y-m-d H:i:s');
        Log::write($logData, 'sms');

        return $result;
    }
     
     
     
     
}