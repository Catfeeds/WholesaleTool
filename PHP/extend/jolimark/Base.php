<?php
namespace jolimark;
/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/10
 * Time: 15:36
 */
class Base
{
    const BASE_URL = 'http://mcp.yingmei.me:8686/mcp/sys/';
    const MERCHANT_CODE = 'prowiser';

    protected $appId = '181010162938305';
    protected $appKey = 'ed6y8e2rgj256ydt';
    protected $sign_key = 'ed6y8e2r';

    public $lastError;
    public $lastReturn;

    /**
     * 获得随机字符
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 签名（待核实）
     * @param        $bill_no
     * @param        $printer_code
     * @param        $result_code
     * @param string $sign
     * @return string
     */
    protected function getSign($bill_no,$printer_code,$result_code,$sign='MD5'){
        $string = 'bill_no='.$bill_no.'&nonce_str='.$this->createNonceStr();
        $string.= '&printer_code='.$printer_code.'&result_code='.$result_code;
        $string.= '&sign_type='.$sign.'&time_stamp='.time();

        $sign_str = $string.'&key='.$this->sign_key;

        return strtoupper($sign($sign_str));
    }

    /**
     * 获取token
     * @return bool
     */
    public function getAccessToken(){
        $data = json_decode($this->getTokenFile(EXTEND_PATH.'jolimark/access_token.php'));

        if ($data->expire_time < time()) {

            $res = Curl::request(self::BASE_URL.'getAccessToken',['app_id'=>$this->appId,'app_key'=>$this->appKey]);
            $res = $this->curlReturn($res);
            if(false === $res){
                return false;
            }else{
                $access_token = $res['access_token'];
                if ($access_token) {
                    $data->expire_time = time() + 600000;
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
     * 获取token文件
     * @param $filename
     * @return string
     */
    protected function getTokenFile($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }

    /**
     * 设置token文件
     * @param $filename
     * @param $content
     */
    protected function setTokenFile($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    /**
     * 处理特定的curl返回
     * @param $data
     * @return bool
     */
    protected function curlReturn($data){
        if(empty($data)){
            $this->lastError = 'no return';
            return false;
        }
        if(isset($data['status'])){
            //return $data;
            if($data['status']==1){
                return $data['data'];
            }

            $this->lastError = $data['data'];
            return false;
        }
        if(isset($data['message'])){
            $this->lastError = $data['message'];
        }
        return false;
    }


}