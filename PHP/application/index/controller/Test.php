<?php
namespace app\index\controller;

use app\common\tool\Curl;
use think\Controller;
use \miniprogram\WXBizDataCrypt;

class Test extends Controller
{


    public function index()
    {
        $appid = 'wx466db2aeee508112';
        $appsecret = '6c7d21a3fc35d608d30616c482eeaedb';
        $redirect_uri = \urlencode(url('Test/index'));
        $getCodeUrl = "https://open.weixin.qq.com/connect/qrconnect?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_login&state=1234#wechat_redirect";

        $this->redirect($getCodeUrl);


        // // \setcookie('qingcai',123);
        // $appid = 'wx153270dfde655f9c';
        // $appsecret = 'fdc75fbe995b06be69f6b3b9564d9e42';
        //
        // $post = $this->request->post();
        // $code = $post['code'];
        //
        // $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code";
        // $info = Curl::request($url);
        // // $info['openid'] = 'OPENID';
        // // $info['session_key'] = 'SESSIONKEY';
        // // $info['unionid'] = 'unionid';//可能有
        //
        // // usertype	1行政总厨/后厨/厨师长2普通厨师3餐厅老板4餐厅采购5调味品经销商/批发商
        //
        //
        // // $pc = new WXBizDataCrypt($appid, $sessionKey);
        // // $errCode = $pc->decryptData($encryptedData, $iv, $data );
        //
        // echo "<pre>";
        // print_r($this->request->server('HTTP_TOKEN'));
        // die;
    }

    public function index2()
    {
        $appid = 'wx466db2aeee508112';
        $appsecret = '6c7d21a3fc35d608d30616c482eeaedb';
        $code = $this->request->get('code');
        $state = $this->request->get('state');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";

        $info = Curl::request($url);
        echo "<pre>";print_r( $info );die;

    }
}