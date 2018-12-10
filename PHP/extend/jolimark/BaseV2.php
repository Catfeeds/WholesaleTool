<?php
namespace jolimark;
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/10
 * Time: 15:36
 */
class BaseV2 extends Base
{
    const BASE_URL = 'http://mcp.jolimark.com/mcp/v2/sys/';

    /**
     * V2版获取token的sign
     * @param        $time_stamp
     * @param string $sign_type
     * @return string
     */
    protected function getTokenSign($time_stamp,$sign_type='MD5'){

        $string = 'app_id='.$this->appId.'&sign_type='.$sign_type.'&time_stamp='.$time_stamp.'&key='.$this->appKey;
        return strtoupper($sign_type($string));
    }

    /**
     * 获取token
     * @return bool
     */
    public function getAccessToken(){
        $data = json_decode($this->getTokenFile(EXTEND_PATH.'jolimark/access_token.php'));

        if ($data->expire_time < time()) {
            $time_now = time();
            $args = [
                'app_id'=>$this->appId,
                'time_stamp'=>$time_now,
                'sign'=>$this->getTokenSign($time_now),
                'sign_type'=>'MD5'
            ];

            $res = Curl::request(self::BASE_URL.'GetAccessToken',$args);
            $res = $this->curlReturn($res);
            if(false === $res){
                return false;
            }else{
                $access_token = $res['access_token'];
                if ($access_token) {
                    $data->expire_time = time() + 2590000; //2592000 30days
                    $data->access_token = $access_token;
                    $this->setTokenFile(EXTEND_PATH.'jolimark/access_token.php', json_encode($data));
                }
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    /**
     * 处理特定的curl返回
     * @param $data
     * @return bool
     */
    protected function curlReturn($data){
        $this->lastReturn = $data;
        if(empty($data)){
            $this->lastError = 'no return';
            return false;
        }
        if(isset($data['return_code'])){
            if($data['return_code']==0){
                return $data['return_data'];
            }
            $this->lastError = $data['return_msg'];
            return false;
        }
        if(isset($data['message'])){
            $this->lastError = $data['message'];
        }
        return false;
    }

    /**
     * 共用的方法(暂未使用)
     * @param array  $args
     * @param string $function
     * @param string $method get/post
     * @return bool
     */
    protected function commonPrinterFuntion(array $args,$function,$method='get'){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $json_header = false;
        $method = strtolower($method);
        if($method=='post'){
            $args = json_encode($args);
            $json_header = true;
        }
        $res = Curl::request(parent::BASE_URL.$function,$args,$method,$json_header);
        return $this->curlReturn($res);
    }

}