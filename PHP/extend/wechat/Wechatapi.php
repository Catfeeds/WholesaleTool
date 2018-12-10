<?php
namespace wechat;
use Think\Log;

// +----------------------------------------------------------------------
// | Blinq
// +----------------------------------------------------------------------
// | Copyright (c) 2014 htto://www.blinq.cn All rights reserved.
// +----------------------------------------------------------------------
// | @date 2014-11-4 上午11:40:06
// +----------------------------------------------------------------------
// | Author: 麦苗儿 <zhaoyadong00@sina.com>
// +----------------------------------------------------------------------‘
class Wechatapi {
    // 测试
	/*protected $AppId="wx1556e3dc7fbed999"; 					//公众平台开发者APPID
	protected $AppSecret ="28e60602f133b83f05db473535cc61b3";  //公众平台开发者 AppSecret*/

    // 正式
    /*protected $AppId="wx3bd94678ca39cec2"; 					//公众平台开发者APPID
    protected $AppSecret ="8feed99c5aa40d10b18258e88570a2a3";  //公众平台开发者 AppSecret*/

    // 小程序
    protected $AppId="wx06584f9a2c9099e3"; 					//公众平台开发者APPID
    protected $AppSecret ="8309b72cba8d9c64578e481e5f0c1ac5";  //公众平台开发者 AppSecret

	public function __construct($REDIRECT_URL){
		$this->redirect_url = $REDIRECT_URL;
	}
	public function getAccessToken() {

        $data = json_decode(file_get_contents("access_token.json"));
        if(empty($data)){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret;
            $res =  json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                $fp = fopen("access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }else{
            if ($data->expire_time < time()) {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppId."&secret=".$this->AppSecret;
                $res =  json_decode($this->httpGet($url));
                $access_token = $res->access_token;
                if ($access_token) {
                    $data->expire_time = time() + 7000;
                    $data->access_token = $access_token;
                    $fp = fopen("access_token.json", "w");
                    fwrite($fp, json_encode($data));
                    fclose($fp);
                }
            } else {
                $access_token = $data->access_token;
            }
        }
        return $access_token;
    }
	
	/**
	 * 获取code
	 */
	public function getCode($scope='snsapi_base') {
		$url =  "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppId."&redirect_uri=".$this->redirect_url."&response_type=code&scope=$scope&state=baolidai#wechat_redirect";
		redirect($url);
	}
	
	/**
	 * 获取openid 返回string OR FALSE 判断是否 ===FALSE
	 * 可以直接通过此方法获得openid
	 * @param string $code code
	 * @return mixed|boolean
	 */
	public function getOpenId($scope='snsapi_base'){
		$code = I("get.code");
		if(!$code){
			$this->getCode($scope);
		}
		$openid_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->AppId . "&secret=" . $this->AppSecret . "&code=$code&grant_type=authorization_code";
		$info = json_decode($this->httpGet($openid_url),true);
		$getOpenid = $info['openid'];
		if($getOpenid){
			if($scope=='snsapi_userinfo'){
				return array('openid'=>$getOpenid,'accessToken'=>$info['access_token']);
			}
			return $getOpenid;
		}else{
			return false;
		}
	}

    public function getUserInfoByOpenid($accessToken,$openid){
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openid&lang=zh_CN";
        return $this->httpGet($url);
    }

	public function getWechatUserInfoByOpenid($openid){
        $accessToken = $this -> getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN";
		return $this->httpGet($url);
	}
	/**
	 *
	 * 得到签名Signature 的查询字符串
	 */

	private function getSortQueryString($params = array(), $exceptKeys = array())
	{
		$querySting = '';
		ksort($params);
		foreach ($params as $key => $value) {
			if(!in_array($key, $exceptKeys)) {
				$querySting .= self::urlencode($key) . '=' . self::urlencode($value) . '&';
			}
		}
		return mb_substr($querySting, 0, mb_strlen($querySting) - 1);
	}


	/**
	 * URLencode加密字符串
	 */

	private static function urlencode($string = '')
	{
		return str_replace('~', '%7E', rawurlencode($string));
	}




	private function signature($params = array(),$token)
	{
		$queryString = $this->getSortQueryString($params, array('sig'));
		return urlencode(base64_encode(hash_hmac('sha256', $queryString, $token, true)));
	}

    /*
     * 调用微信模版消息
     * */
    public function useTemplate($openid,$template_id,$data,$href=''){
        $token=$this->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token";
        $template_data=array(
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url' => $href,
            'data'=>$data
        );
        $template_data_json=json_encode($template_data);
        $result=$this -> httpPost($url,$template_data_json);
        $result=json_decode($result,true);
        $info['openid'] = $openid;
        $info['template_id'] = $template_id;
        $info['url'] = $href;
        $info['data'] = json_encode($data);
        $info['create_time'] = time();
        $info['errcode'] = $result['errcode'];
        $info['errmsg'] = $result['errmsg'];
        $info['msgid'] = $result['msgid'];
        M("templete") -> add($info);
        if($result['errcode']==0){
            return true;
        }else{
            return $result['errmsg'];
        }
    }


	/**
	 *
	 * 获取微信官方API Sign
	 */
	public function getSignPackage() {
		$jsapiTicket = $this->getJsApiTicket();
		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();

		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);

		$signPackage = array(
				"appId"     => $this->AppId,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
		);

		return $signPackage;
	}

	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getJsApiTicket() {
        $data = json_decode(file_get_contents("jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen("jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
	}
    //生成带参数的二维码返回ticket
    public function qrcode_ticket($action_name,$value) {
        $accessToken = $this->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$accessToken";
        $data['action_name'] = $action_name;

        if($action_name=='QR_LIMIT_SCENE'){
            $action_info['scene'] = array("scene_id"=>$value);
        }else{
            $action_info['scene'] = array("scene_str"=>$value);
        }
        $data['action_info'] = $action_info;
        $res = json_decode($this -> httpPost($url,json_encode($data)),true);
        return $res;
    }
    //上传图片到临时素材返回二维码
    public function upload_media($imgurl) {
        $accessToken = $this->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=image";
        if (class_exists('\CURLFile')) {
            $data['media'] = new \CURLFile(realpath($imgurl));
        } else {
            $data['media'] = '@'.realpath($imgurl);
        }
        $res = json_decode($this -> httpPost($url,$data),true);
        return $res;
    }
    //添加客服
    public function add_customer($data) {
        $accessToken = $this->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        $url = "https://api.weixin.qq.com/customservice/kfaccount/add?access_token=$accessToken";
        $res = json_decode($this -> httpPost($url,$data),true);
        return $res;
    }
    //获取客服账号
    public function get_customer() {
        $accessToken = $this->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        $url = "https://api.weixin.qq.com/cgi-bin/customservice/getkflist?access_token=$accessToken";
        $res = json_decode($this -> httpGet($url),true);
        return $res;
    }
    //客服发送消息
    public function customer_info($data) {
        $accessToken = $this->getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$accessToken";
        $res = json_decode($this -> httpPost($url,$data),true);
        return $res;
    }
    //发布菜单
    public function createMenu($data){
        $access_token = $this -> getAccessToken();
        if (!$access_token) return false;

        $result = $this->httpPost("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token,$this -> json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            dump($json);
            if (!$json || !empty($json['errcode'])) {
                return false;
            }
            return true;
        }
        return false;
    }
    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    public function json_encode($arr) {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
    function httpPost($url,$data) {
        $ch = curl_init();

        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }





	/**
	 * URLDecode解密字符串
	 */

	protected static function urldecode($string = '' )
	{
		return str_replace('%20', '+', str_replace('%2A', '*', rawurldecode($string)));
	}

	/**
	 * @desc 解析获取微信小程序基本信息
	 * @author: yangsy
	 * @time: 2018-08-15 10:58:53
	 */
	public function getWechatUserInfoBySmall($post){
	    $encryptedData = $post['encryptedData'];
        $iv = $post['iv'];
        $rawData = $post['rawData'];
        $signature = $post['signature'];
        $code = $post['code'];
        $grant_type = "authorization_code"; //授权（必填）

        /*$AppId = 'wx9b6be2395da24006';
        $AppSecret = 'db5e3b362585db2830d1d34ff5e6632b';*/

        $AppId = 'wx06584f9a2c9099e3';
        $AppSecret = '8309b72cba8d9c64578e481e5f0c1ac5';

        $params = "appid=".$AppId."&secret=".$AppSecret."&js_code=".$code."&grant_type=".$grant_type;
        $url = "https://api.weixin.qq.com/sns/jscode2session?".$params;
        $res = json_decode($this->httpGet($url),true);


        //取出json里对应的值
        $sessionKey = $res['session_key'];

        $re = $this->decryptData($encryptedData,$iv,$sessionKey);

        return $re;
    }
    
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $sessionKey sessionKey值
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv,$sessionKey )
    {
        /*$AppId = 'wx9b6be2395da24006';*/
        $AppId = 'wx06584f9a2c9099e3';

        if (strlen($sessionKey) != 24) {
            return -41001;
        }
        $aesKey=base64_decode($sessionKey);

        if (strlen($iv) != 24) {
            return -41002;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return -41003;
        }
        if( $dataObj->watermark->appid != $AppId )
        {
            return -41003;
        }
        return $result;
    }
    
    /**
     * @desc 获取小程序二维码
     * @author: yangsy
     * @time: 2018-08-28 19:54:05
     */
    public function smallCode($post_data){
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$token;

        $post_data=json_encode($post_data);
        $data = httpPost($url,$post_data);
        $result = $this->data_uri($data,'image/png');
        return $result;
    }

    //二进制转图片image/png
    public function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }
     

}