  
// var NewApiRootUrl = 'https://os.prowiser.cn';  //测试
// var NewApiRootUrl = 'https://osapp.unileverfoodsolutions.com.cn';    //正式
 var NewApiRootUrl = "http://wscrm.prowiser.cn/ufs_order/wxapi/public/index.php/" //SVN测试服
 // var NewApiRootUrl = "http://wscrm.prowiser.cn/ufs_order/wxapi/public/index.php" //更新接口
//var NewApiRootUrl = "https://osapp.unileverfoodsolutions.com.cn/wxapi/public/index.php" //更新接口(正式服)


module.exports = {

  login: NewApiRootUrl + '/ufs_register/index',              //2-2后台终端用户获取open_id和token
  login1: NewApiRootUrl + '/ufs_register/index1',            //1-1后台供应商获取open_id接口
  superLogin: NewApiRootUrl + '/ufs_supplier_login/index',   //1-2供应商登录和获取token接口
  getCode: NewApiRootUrl + '/ufs_verification/index',        //2-3获取验证码链接
  getPhoneCode: NewApiRootUrl + '/ufs_verification/captcha_verify',  //2-14获取短信验证码
  regionList: NewApiRootUrl + '/ufs_area/index',              //2-4获取行政区域
  uploadPic: NewApiRootUrl + '/ufs_order/upload_img',         //上传图片
  regist: NewApiRootUrl + '/ufs_login/index',                 //2-12注册并验证手机号
  dzdpAround: NewApiRootUrl + '/ufs_dining/index',            //2-11获取大众点评附近餐厅接口
  getSuperProductList: NewApiRootUrl + '/ufs_product/index',  //2-7获取父级供应商所有商品列表
  getHistoryList: NewApiRootUrl + '/ufs_product/history',     //2-6获取历史商品
  getSkuList: NewApiRootUrl + '/ufs_product/spec',            //2-8获取商品单位
  addOrder: NewApiRootUrl + '/ufs_order/insert',              //2-9买家新增订单
  getOrderList: NewApiRootUrl + '/ufs_order/index',           //2-5客户端获取订单列表
  getSuperOrderList: NewApiRootUrl + '/ufs_order/supplier_order',    //1-4批发商获取订单列表
  getOrderDetail: NewApiRootUrl + '/ufs_order/detail',         //1-8获取订单详情
  getSuperInfo: NewApiRootUrl + '/ufs_supplier/info',          //1-3获取供应商信息
  getSuperInfoById: NewApiRootUrl + '/ufs_supplier/get_info',  //1-7获取供应商信息by id
  getSuperInfoByIdNew: NewApiRootUrl + '/ufs_supplier/get_basic_info',   //2-1获取供应商信息by id
  getShopList: NewApiRootUrl + '/ufs_supplier/dining',         //1-5获取我的客户列表
  updateProduct: NewApiRootUrl + '/ufs_order/submit',          //1-9编辑订单商品
  deleteProduct: NewApiRootUrl + '/ufs_order/del_product',     //1-10删除订单商品
  yesOrder: NewApiRootUrl + '/ufs_order/agree_order',          //1-11确认订单
  noOrder: NewApiRootUrl + '/ufs_order/cancel',                //1-12拒绝订单
  queryShop: NewApiRootUrl + '/ufs_supplier/get_dining',       //2-13终端注册搜索餐厅
  getQrCode: NewApiRootUrl + '/ufs_wxapi/get_qrcode',          //1-6获取供应商专属收单码
  resetPwd: NewApiRootUrl + '/ufs_password/reset',             //1-15供货商首次登陆重置密码
  sendCode: NewApiRootUrl + '/ufs_password/sms',               //1-16供货商忘记密码发送短信
  changePassword: NewApiRootUrl + '/ufs_password/retrieve',    //1-17供货商忘记密码重置新的
  getcouponInfo:NewApiRootUrl+'/ufs_password/retrieve',       
  set_not_show_layer: NewApiRootUrl + '/set_not_show_layer',   //1-14关闭订单详情浮层

   getProductManageJl: NewApiRootUrl + '/ufs_supplier/ufs_products', //获取产品管理家乐产品
   getProductManageQt: NewApiRootUrl + '/ufs_supplier/other_products', //获取产品管理其它产品
   submitNewProduct: NewApiRootUrl + '/ufs_supplier/ufs_create_product', //供应商新建产品
   getsupplierUser: NewApiRootUrl + '/ufs_supplier/ufs_supplier_users', //获取供应商对应的用户
   productBind: NewApiRootUrl + '/ufs_supplier/ufs_bind_unbind', //商品绑定和解绑 
   modifyProQt: NewApiRootUrl + '/ufs_supplier/update_other_product', //产品修改 
   getspecification: NewApiRootUrl + '/ufs_supplier/get_spec', //获取规格


   getClientPro: NewApiRootUrl + 'ufs_order/get_goodslist', //终端获取家乐产品
  getXjOrder: NewApiRootUrl + 'ufs_order/add_order', //新建订单

};
