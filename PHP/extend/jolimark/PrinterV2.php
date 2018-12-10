<?php
namespace jolimark;

/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/10
 * Time: 15:36
 */
class PrinterV2 extends BaseV2
{
    /**
     * 绑定打印机
     * @param array $device_codes
     * @return bool
     */
    public function bindPrinters($device_codes)
    {
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = ['app_id'=>$this->appId,'access_token' => $access_token,'device_codes' => $device_codes];
        $res = Curl::request(parent::BASE_URL . 'BindPrinter',json_encode($args),'post',true );
        return $this->curlReturn($res);
    }

    /**
     * 打印小票
     * @param array $printer_codes
     * @param       $bill_no
     * @param       $bill_content
     * @param int   $copies 份数
     * @param string $method PrintHtmlUrl/PrintHtmlCode(url的时候content为url，code时content为html代码)
     * @param bool  $sign 是否签名
     * @return bool
     */
    public function printReceipt(array $printer_codes,$bill_no,$bill_content,$copies=1,$method='PrintHtmlUrl',$sign=false){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $printer_codes = implode($printer_codes);
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'device_ids'=>$printer_codes,
            'copies'=>intval($copies),
            'cus_orderid'=>$bill_no,
            'bill_content'=>$bill_content,
        ];
        if($sign){
            $args['sign']=strtoupper(md5($bill_content));
            $args['sign_type']='MD5';
        }
        $res = Curl::request(parent::BASE_URL . $method, json_encode($args),'post',true);
        return $this->curlReturn($res);
    }

    /**
     * 当前打印机状态
     * @param string $device_id
     * @return bool
     */
    public function getPrinterStatus($device_id){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'device_id'=>$device_id,
        ];
        $res = Curl::request(parent::BASE_URL . 'QueryPrinterStatus', $args);
        return $this->curlReturn($res);
    }

    /**
     * 解绑打印机
     * @param array $device_id
     * @return bool
     */
    public function unbindPrinters(array $device_id)
    {
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'device_id'=>$device_id,
        ];
        $res  = Curl::request(parent::BASE_URL . 'UnBindPrinter',json_encode($args),'post',true );
        return $this->curlReturn($res);
    }

    /**
     * 获取任务状态
     * @param $cus_orderid
     * @return bool
     */
    public function getPrintOrderStatue($cus_orderid){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'cus_orderid'=>$cus_orderid,
        ];
        $res = Curl::request(parent::BASE_URL.'QueryPrintTaskStatus',$args);
        return $this->curlReturn($res);
    }

    /**
     * 取消未打印任务
     * @param $device_id
     * @return bool
     */
    public function cancelNotPrintTask($device_id){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'device_id'=>$device_id,
        ];
        $res = Curl::request(parent::BASE_URL.'CancelNotPrintTask',$args);
        return $this->curlReturn($res);
    }

    /**
     * @param $device_id
     * @return bool
     */
    public function queryPrinterInfo($device_id){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'device_id'=>$device_id,
        ];
        $res = Curl::request(parent::BASE_URL.'QueryPrinterInfo',$args);
        return $this->curlReturn($res);
    }
}