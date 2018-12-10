<?php
namespace app\index\controller;

use app\common\library\Order;
use jolimark\Base;

use jolimark\BaseV2;
use jolimark\Printer;
use jolimark\PrinterV2;
use think\Controller;
use think\Url;

class Yingmei extends Controller
{

    public function index()
    {
        // $jolimark = new Base();
        // $access = $jolimark->getAccessToken();
        // var_dump($access);
        $jolimark = new Printer();
        $res = $jolimark->bindPrinters(['#18290779AY#E715']);
        //$jolimark->getPrinterStatus([]);
        var_dump($res);
        if(false===$res){
            echo $jolimark->lastError;
        }else{
            var_dump($res);
        }
    }

    public function aaa(){
        $jolimark = new Printer();
        $res = $jolimark->getBindPrinters();
        if(false===$res){
            echo $jolimark->lastError;
        }else{
            var_dump($res);
        }
    }

    public function test(){
        $jolimark = new PrinterV2();
        $res = $jolimark->printReceipt(['18290779AY'],10000004,Url::build('index/yingmei/ggg',['id'=>4],'',true));
        // $res = $jolimark->printReceipt(['18290779AY'],10000004,'http://wscrm.prowiser.cn/ufs_order/index.php/index/yingmei/dingdan');
        //$res = $jolimark->printReceipt(['18290779AY'],10000001,'http://www.ufsorder.local/index/yingmei/dingdan');
        var_dump($jolimark->lastReturn);
    }

    public function dingdan(){
        return $this->fetch('dingdan2');
    }

    public function bbb(){
        return json_encode(['status'=>1,'data'=>'1111']);
    }

    public function ccc(){
        //$jo = new Base2();
        // dump(Base2::MERCHANT_CODE);
        var_dump(Base2::MERCHANT_CODE);
        //dump(Base2::BASE_URL);
    }

    public function ddd(){
        $d = new BaseV2();
        $res = $d->getAccessToken();
        dump($res);
        dump($d->lastReturn);
    }

    public function eee(){
        $e = new PrinterV2();
        //$e->getPrinterStatus('18290779AY');
        $e->getPrintOrderStatue('10000004');
        //$res = $e->printReceipt(['18290779AY'],'10000002','http://open.yingmei.me/content/billtemplate/hanhongtest.html');
        var_dump($e->lastReturn);
    }

    public function fff(){
        $res = Order::getOrderDetail(21);

        dump($res);
    }

    public function ggg(){

        $order_id = $this->request->param('id/d');
        if(empty($order_id)){
            return 'id缺失';
        }

        $res = Order::getOrderDetail($order_id);

        $this->assign('data',$res);
        return $this->fetch('dingdan3');
    }
}