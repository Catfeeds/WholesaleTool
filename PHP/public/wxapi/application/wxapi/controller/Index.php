<?php
// +----------------------------------------------------------------------
// | desc: 首页测试
// +----------------------------------------------------------------------
// | author: yangsy
// +----------------------------------------------------------------------
// | time: 2018-08-11 16:20:28
// +----------------------------------------------------------------------
namespace app\wxapi\controller;

use think\Db;
use wechat\Wechatapi;
class Index extends Common
{
    public function index(){

        $unionId = request()->post('unionId');
        $supplier_id = request()->post('supplier_id');
        if(empty($unionId) && empty($supplier_id)){
            $this->returnError([],400,'供应商不存在！');
        }
        // 查询用户信息，并查询用户的supplier
        if(empty($supplier_id)){
            $supplier_id = Db::name('user')->where(['union_id'=>$unionId])->value('supplier_id');
            if(empty($supplier_id)){
                $this->returnError([],400,'供应商不存在！');
            }
        }
        $url = config('http_url');
        $weixin = new Wechatapi($url);
        $data = [
            'page' => 'pages/customer/welcome/welcome',
            'scene' => $supplier_id,
        ];
        $re = $weixin->smallCode($data);
        $this->saveImg($re,$supplier_id);
        /*$this->returnSuccess($re);*/
    }
    
    /**
     * @desc 获取微信token
     * @author: yangsy
     * @time: 2018-08-28 22:00:23 
     */
    public function getToken(){
        $url = config('http_url');
        $weixin = new Wechatapi($url);
        $token = $weixin->getAccessToken();
        $this->returnSuccess(['token'=>$token]);
    }
    
    
    /**
     * @desc 将二进制图片保存成图片
     * @author: yangsy
     * @time: 2018-08-29 23:06:55
     */
     public function saveImg($jpg,$supplier_id){
         //生成图片
         $imgDir = 'qrcode/';

         if(empty($jpg))
         {
             $this->returnError('',400,'生成失败');
         }
         /*$ret = file_put_contents($filePath, base64_decode($jpg), true);*/

         if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $jpg, $result)){
             $type = $result[2];
             $filename = 'us_'.$supplier_id.".{$type}";///要生成的图片名字
             $new_file = "./".$imgDir.$filename;
             if(file_exists($new_file))
             {
                 $return_url = config('host_url').'wxapi/public/'.$imgDir.$filename;
                 $this->returnSuccess($return_url);
             }
             if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $jpg)))){
                 $return_url = config('host_url').'wxapi/public/'.$imgDir.$filename;
                 $this->returnSuccess($return_url);
             }else{
                 $this->returnError('生成失败！');
             }
         }
         /*$file = fopen("./".$imgDir.$filename,"w");//打开文件准备写入
         fwrite($file,$jpg);//写入
         fclose($file);//关闭*/

     }

     public function test(){
         echo "<img src='https://osapp.unileverfoodsolutions.com.cn/qrcode/wxapi_14.png' width='200' height='200' />";
     }

}