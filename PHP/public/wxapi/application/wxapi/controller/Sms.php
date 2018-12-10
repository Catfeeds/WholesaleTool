<?php
// +----------------------------------------------------------------------
// | desc: Sms.php 发送获取验证码的接口
// +----------------------------------------------------------------------
// | author: xiaodu
// +----------------------------------------------------------------------
// | time: 2018/8/13 10:00
// +----------------------------------------------------------------------

namespace app\wxapi\controller;
use app\wxapi\controller\Common;
use think\Config;
use think\Log;

class Sms extends Common
{
    /*
     * 获取图形验证码接口
     * @param 无
     * @return url 天御验证码请求url
     * */
    public function get_url(){
        $this->checkToken();
        //curl请求获取天御验证码
        $url = Config::get('sso_http_url')."/api/micro-app/tianyu/get-tianyu-captcha?app_key=".Config::get('sso_app_key');
        $result = curl_request($url);
        if($result['code']!=200){
            $this -> returnError([],400,$result['message']);
        }
        $this -> returnSuccess(['url'=>$result['data']['url']]);
    }
    /*
     * 天御验证码验证票据,并发送验证码
     * @param captcha 天御验证码验证返回的票据
     * @param mobile 发送验证码的手机号
     * @return url 天御验证码请求url
     * */
    public function check_captcha(){
        $this->checkToken();
        $post = request()->post();
        $captcha = $post['captcha'];
        $mobile = $post['mobile'];
        if(!$mobile){
            $this -> returnError([],400,"手机号码不能为空");
        }
        if(!$captcha){
            $this -> returnError([],400,"验证票据不能为空");
        }
        $url = Config::get('sso_http_url')."/api/micro-app/tianyu/check-tianyu-captcha?app_key=".Config::get('sso_app_key');
        $request = [];
        $request['ticket'] = $captcha;
        $request['phone'] = $mobile;


        $result = httpPost($url,json_encode($request));
        $result = json_decode($result,true);
        if($result['code']!=200){
            $this -> returnError([],5002,$result['message']);
        }
        /**
         * 记录发送注册短信的日志 -start
         */
        $logData['time']   = date('Y-m-d H:i:s');
        $logData['mobile'] = $mobile;
        Log::write($logData, 'sms');
        /*-end*/
        $this -> returnSuccess([],200,"验证码发送成功");
    }
}