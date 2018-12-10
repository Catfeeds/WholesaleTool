<?php
namespace jolimark;

/**
 * Created by PhpStorm.
 * User: jie.yu
 * Date: 2018/10/10
 * Time: 15:36
 */
class Printer extends Base
{
    /**
     * 绑定打印机
     * @param array $printer_codes
     * @return bool
     */
    public function bindPrinters(array $printer_codes)
    {
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $printer_codes = implode($printer_codes);
        $args = ['app_id'=>$this->appId,'access_token' => $access_token, 'merchant_code'=>parent::MERCHANT_CODE,'printer_codes' => $printer_codes];
        $res = Curl::request(parent::BASE_URL . 'bindPrinters',json_encode($args),'post',true );
        return $this->curlReturn($res);
    }

    /**
     * 打印小票（未测试）
     * @param array $printer_codes
     * @param       $copies
     * @param       $bill_no
     * @param       $bill_content
     * @param int   $bill_type
     * @return bool
     */
    public function printReceipt(array $printer_codes,$bill_no,$bill_content,$copies=1,$bill_type=1){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $printer_codes = implode($printer_codes);
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'merchant_code'=>parent::MERCHANT_CODE,
            'printer_codes'=>$printer_codes,
            'copies'=>intval($copies),
            'bill_no'=>$bill_no,
            'bill_type'=>$bill_type,
            'bill_content'=>$bill_content,
        ];
        $res = Curl::request(parent::BASE_URL . 'print', json_encode($args),'post',true);
        return $this->curlReturn($res);
    }

    /**
     * 已绑定的打印机
     * @return bool
     */
    public function getBindPrinters(){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'merchant_code'=>parent::MERCHANT_CODE,
        ];

        $res = Curl::request(parent::BASE_URL . 'GetBindPrinters', json_encode($args),'post',true);
        return $this->curlReturn($res);
    }

    /**
     * 打印机状态
     * @param array $printer_codes
     * @return bool
     */
    public function getPrinterStatus(array $printer_codes){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $printer_codes = implode($printer_codes);
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'printer_codes'=>$printer_codes,
        ];
        $res = Curl::request(parent::BASE_URL . 'GetPrintStatus', $args);
        return $this->curlReturn($res);
    }

    /**
     * 解绑打印机
     * @param array $printer_codes
     * @return bool
     */
    public function unbindPrinters(array $printer_codes)
    {
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $printer_codes = implode($printer_codes);
        $args = ['access_token' => $access_token, 'printer_codes' => $printer_codes,'merchant_code'=>parent::MERCHANT_CODE];
        $res  = Curl::request(parent::BASE_URL . 'unbindPrinters',json_encode($args),'post',true );
        return $this->curlReturn($res);
    }

    /**
     * 获取任务状态
     * @param $bill_no
     * @return bool
     */
    public function getPrintOrderStatue($bill_no){
        $access_token  = $this->getAccessToken();
        if(!$access_token) return false;
        $args = [
            'app_id'=>$this->appId,
            'access_token'=>$access_token,
            'bill_no'=>$bill_no,
        ];
        $res = Curl::request(parent::BASE_URL.'GetPrintOrderStatue',$args);
        return $this->curlReturn($res);
    }

}