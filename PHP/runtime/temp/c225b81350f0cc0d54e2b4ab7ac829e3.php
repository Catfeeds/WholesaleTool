<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:113:"C:\Users\nicole.hui\Desktop\ufs_wxapp1.5.0\trunk\SRC\PHP\public/../application/admin\view\order\detail\index.html";i:1542278611;s:99:"C:\Users\nicole.hui\Desktop\ufs_wxapp1.5.0\trunk\SRC\PHP\application\admin\view\layout\default.html";i:1542278611;s:96:"C:\Users\nicole.hui\Desktop\ufs_wxapp1.5.0\trunk\SRC\PHP\application\admin\view\common\meta.html";i:1542278612;s:98:"C:\Users\nicole.hui\Desktop\ufs_wxapp1.5.0\trunk\SRC\PHP\application\admin\view\common\script.html";i:1542278612;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <div class="panel panel-default panel-intro">
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <table id="table" class="table table-striped table-bordered table-hover"
                           width="100%">
                        <tr>
                            <th>ID</th>
                            <th>图片</th>
                            <th>产品名称</th>
                            <th>sku编码</th>
                            <th>sku名称</th>
                            <th>购买数量</th>

                            <th>产品单价</th>
                            <th>是否是联合利华产品</th>
                        </tr>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo $vo['id']; ?></td>
                            <?php if($vo['uploaded_img'] != ''): ?>
                            <td><img src="<?php echo $vo['uploaded_img']; ?>" width="100"></td>
                            <?php else: ?>
                            <td>无</td>
                            <?php endif; ?>

                            <td><?php echo $vo['product_name']; ?></td>
                            <td><?php echo $vo['sku_code']; ?></td>
                            <td><?php echo $vo['sku_unit']; ?></td>

                            <td><?php echo $vo['product_count']; ?></td>
                            <td><?php echo $vo['product_price']; ?></td>
                            <td><?php echo $vo['is_unlieve']==1?'是':'否'; ?></td>
                        </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>