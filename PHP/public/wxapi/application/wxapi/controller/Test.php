<?php
namespace app\wxapi\controller;

use think\Db;
use think\Env;

class Test
{
    public function index()
    {
       $data =  Db::name('user')->where('id', '>', 1)->find();
       dump($data);
        for ($i = 0; $i <= 100; $i++) {
            if ($i === 50 || $i === 70 || $i === 100) {
                $data[] =  Db::name('user')->where('id', '>', $i)->find();
            }
        }

    }

    public function test(){
        echo 'hello world';
    }



}