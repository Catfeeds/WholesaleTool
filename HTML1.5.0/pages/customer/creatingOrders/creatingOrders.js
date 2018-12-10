
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
   data: {
      tab: '0',
      userInfoData: [],
      createOrderList: [],
      create_other: [],
      create_newpro: [],
      choose_id:[],
      choose_qId: [],
      popWrap: false,
      pickerArray: [],
      backArr: [
      ],
      skuList:[],
      skuListKeyValue: {},
      newSkuList:{},
      index_u:[],
      index_q:[],
      index_J:[],
      textarea: '',
      baseInfo: '',
      shop_name: '',
      startX: 0,
      popShowDelete: false,
      popShowIndex: 0,
      popShowBack: false
   },
   // 返回上一级
   pageBack: function () {
      wx.navigateBack({
         delta: 1
      })
   },
   // 点击返回
   backPage: function () {
      this.setData({
         popShowBack: true
      })
   },
   // 确认返回
   back_affirm: function () {
      this.pageBack();
      this.setData({
         popShowBack: false
      });
   },
   // 取消
   back_cancel: function () {
      this.setData({
         popShowBack: false
      });
   },

   //家乐产品输入
   inputJl: function(e){
      let jlValue;
      if (!isNaN(parseInt(e.detail.value))){
         jlValue = parseInt(e.detail.value);
      }else{
         jlValue = 0;
      }
      let _index = e.target.dataset.key;
      let jlData = this.data.createOrderList;
      jlData[_index].num = jlValue;
      this.setData({
         createOrderList: jlData
      });
   },
   // 其它产品输入
   inputQt: function(e){
      let qtValue;
      if (!isNaN(parseInt(e.detail.value))) {
         qtValue = parseInt(e.detail.value);
      } else {
         qtValue = 0;
      }
      let _index = e.target.dataset.key;
      let qtData = this.data.create_other;
      qtData[_index].num = qtValue;
      this.setData({
         create_other: qtData
      });
   },
   //新建产品输入
   inputXj: function(e){
      let xjValue;
      let _index = e.target.dataset.key;
      let xjData = this.data.create_newpro;
      xjValue = parseInt(e.detail.value);
      xjData[_index].num = xjValue;
      this.setData({
         create_newpro: xjData
      });
   },
   // 新建失去焦点
   bindblurXj: function(e){
      let _index = e.target.dataset.key;
      let valueBlur = parseInt(e.detail.value);
      if(!valueBlur){
         valueBlur = 1;
         this.data.create_newpro[_index].num = valueBlur;
         this.setData({
            create_newpro: this.data.create_newpro
         });
      }
   },

   //删除事件
   delete: function (e) {
      let currentIndex = e.currentTarget.dataset.key;
      this.data.popShowDelete = true;
      this.setData({
         popShowIndex: currentIndex,
         popShowDelete: this.data.popShowDelete,
      });
   },
   // 确认删除
   delete_affirm: function(){
      let affIndex = this.data.popShowIndex;
      console.log(affIndex);
      this.data.create_newpro.splice(affIndex, 1);
      this.data.popShowDelete = false;
      this.setData({
         create_newpro: this.data.create_newpro,
         popShowDelete: this.data.popShowDelete
      })
   },
   // 取消删除
   delete_cancel: function(){
      this.data.popShowDelete = false;
      this.setData({
         popShowDelete: this.data.popShowDelete
      })
   },

   //选择器
   bindChangeJl: function (e) {
      var index_J = parseInt(e.detail.value);
      var index = e.target.dataset.key;
      var index_cache = this.data.index_J;
      index_cache[index] = {
         type: index_J,
      }
      this.setData({
         index_J: index_cache
      })
   },

   // 其它产品
   bindChangeQt: function(e){
      var index_q = parseInt(e.detail.value);
      var index = e.target.dataset.key;
      var index_cache = this.data.index_q;
      index_cache[index] = {
         type: index_q,
      }

      this.setData({
         index_q: index_cache
      })
   },
   // 新建产品
   bindpickerChange: function (e) {
      var index_u = parseInt(e.detail.value);
      var index = e.target.dataset.key;
      var index_cache = this.data.index_u;
      index_cache[index] = {
         type: index_u,
      }
      this.setData({
         index_u: index_cache
      })
   },
   // 新建输入
   bindInput: function (e) {
      var inputValue = e.detail.value;
      var index = e.target.dataset.key;
      var _arrInput = this.data.create_newpro;
      _arrInput[index].product_name = inputValue;
      _arrInput[index].value = inputValue;
      this.setData({
         create_newpro: _arrInput,
      })
   },
   // 输入框失去焦点
   bindblur: function (e) {
      // 判断是否新建重复
      var inputValue = e.detail.value;
      var arrIndex = e.currentTarget.dataset.key;
      let jlProductData = this.data.createOrderList;
      let qtProductData = this.data.create_other;
      let _arrInput = this.data.create_newpro;
      for (let i = 0; i < jlProductData.length; i++){
         if(_arrInput[arrIndex].product_name){
            if (_arrInput[arrIndex].product_name == jlProductData[i].product_name) {
               wx.showToast({
                  title: '产品已存在,之后请在家乐产品中选择下单',
                  icon: 'none'
               })
               _arrInput[arrIndex].is_unliever = 0;
               this.setData({
                  create_newpro: _arrInput
               })
               return;
            }
         }
      }
      for (let i = 0; i < qtProductData.length; i++) {
         if (_arrInput[arrIndex].product_name) {
            if (_arrInput[arrIndex].product_name == qtProductData[i].product_name) {
               wx.showToast({
                  title: '该产品已存在,您可以新增其他单位',
                  icon: 'none'
               })
               this.setData({
                  create_newpro: _arrInput
               })
               return;
            }
         }
      }
      for (var i = 0; i < _arrInput.length; i++) {
         if(inputValue){
            if (inputValue == _arrInput[i].product_name && arrIndex != i) {
               _arrInput[arrIndex].product_name = '';
               this.setData({
                  create_newpro: _arrInput
               });
               wx.showToast({
                  title: '产品名称重复',
                  icon: 'none'
               });
               return;
            }
         }
      }
   },

   // 切换列表
   change: function (e) {
      var _this = this;
      _this.setData({
         tab: e.target.dataset.tab,
      })
   },
   // 下单
   submit_tab: function () {
      //家乐产品
      var productJl = this.data.createOrderList;
      var flag = false;
      var flagXj = false;
      var title = '请选择数量';
      if (this.data.tab == 0 || this.data.tab == 1) {
         flagXj = true;
      }
      for(var i = 0;i < productJl.length; i++){
         if(productJl[i].num){
            flag = true;
            break;
         }
      }

      //其它品牌产品
      var productQt = this.data.create_other;
      for (var i = 0; i < productQt.length; i++){
         if (productQt[i].num) {
            flag = true;
            break;
         }
      }

      //新建产品
      var newProduct = this.data.create_newpro;
      var newLength = newProduct.length;
      // 判断是否输入产品名称
      if(!flag){
         if(this.data.tab == 2){
            title = '请选择产品或新建产品';
         }
      }
      for (var i = 0; i < newProduct.length;i++){
         if (newProduct[i].num && newProduct[i].product_name){
            flag = true;
            break;
         }
      }
      if (!flag){
         wx.showToast({
            title: title,
            icon: 'none'
         })
         return;
      }
      this.setData({
         popWrap: true
      })
   },

   pop_cancel: function () {
      this.setData({
         popWrap: false
      })
   },
   textInput: function(e){
      var value = e.detail.value;
      var textarea = this.data.textarea;
      this.setData({
         textarea: value 
      })
   },
   pop_affirm: function () {
      var _this = this;
      let getXjOrder = api.getXjOrder;
      let userInfoData = wx.getStorageSync('userInfo');
      let product_list = [];
      let user_product_list = [];
      let other_product_list = [];
      //家乐
      for (var i = 0; i < _this.data.createOrderList.length;i++){
         if (_this.data.createOrderList[i].num){
            let product_id = 0;
            if (typeof(_this.data.index_J[i]) != 'undefined' ){
               product_id = _this.data.createOrderList[i].sku_unit[_this.data.index_J[i].type].product_id
            }else{
               product_id = _this.data.createOrderList[i].sku_unit[0].product_id
            }
            product_list[i] = { 
               'product_id': product_id,
               'product_count': _this.data.createOrderList[i].num
            };
         }
      }  
      // 删除为空的元素
      for (var i = 0; i < product_list.length; i++) {
         if (product_list[i] == "" || product_list[i] == null || typeof (product_list[i]) == "undefined") {
            product_list.splice(i, 1);
            i = i - 1;
         }
      }  
      
      //其它
      for (var i = 0; i < _this.data.create_other.length; i++) {
         if (_this.data.create_other[i].num) {
            let product_id = 0;
            if (typeof(_this.data.index_q[i])!= 'undefined' ) {
               product_id = _this.data.create_other[i].sku_unit[_this.data.index_q[i].type].product_id
            } else {
               product_id = _this.data.create_other[i].sku_unit[0].product_id
            }
            user_product_list[i] = {
               'product_id': product_id,
               'product_count': _this.data.create_other[i].num
            };
         }
      }
      //
      for (var i = 0; i < user_product_list.length; i++) {
         if (user_product_list[i] == "" || user_product_list[i] == null || typeof (user_product_list[i]) == "undefined") {
            user_product_list.splice(i, 1);
            i = i - 1;
         }
      }  
      //新建产品
      for (var i = 0; i < _this.data.create_newpro.length; i++) {
         if (_this.data.create_newpro[i].product_name && _this.data.create_newpro[i].num){
            let spec_id = typeof(_this.data.index_u[i]) !== 'undefined' ? _this.data.index_u[i].type + 1  : 1;
            other_product_list[i] = {
               'product_count': _this.data.create_newpro[i].num,
               "product_name": _this.data.create_newpro[i].product_name,
               "spec_id": spec_id,
               "is_unliever": _this.data.create_newpro[i].is_unliever,//家乐0，其他1
               "sku_code": "1"
            };
         }
      }
      for (var i = 0; i < other_product_list.length; i++) {
         if (other_product_list[i] == "" || other_product_list[i] == null || typeof (other_product_list[i]) == "undefined") {
            other_product_list.splice(i, 1);
            i = i - 1;
         }
      }  
      
      // 获取备注
      var mark = _this.data.textarea
      wx.request({
         url: getXjOrder,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId,
            "order_type": 0,
            "comments": mark,
            "product_list": product_list,
            "user_product_list": user_product_list,
            "other_product_list": other_product_list,
            "form_id": "the formId is a mock one",
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            if(res.data.code == 200){
               wx.showToast({
                  title: '下单成功',
                  icon: 'success',

               });
               setTimeout(() =>{
                  wx.redirectTo({
                     url: '../allOrder/allOrder?status=1',
                  })
               },1200)
            }
         },
         fail: function (res) {

         },
         complete: function () {
            
         }
      })

      this.setData({
         popWrap: false
      })
   },
   // 返回上一级
   goBack: function(){
      wx.navigateBack({
         delta: 1
      })
   },
   pageBack:function(){
      wx.navigateBack({
         delta: 1
      })
   },
   // 减
   bindminus: function (e) {
      var index = e.target.dataset.key;
      var proJl = this.data.createOrderList;
      var numJl = proJl[index].num;
      numJl = numJl - 1;
      if (numJl <= 0){
         numJl = 0;
      }
      proJl[index].num = numJl;
      this.setData({
         createOrderList: proJl
      })
   },
   // 加
   bindadd: function (e) {
      var index = e.target.dataset.key;
      var proJl = this.data.createOrderList;
      var numJl = proJl[index].num;
      numJl = numJl + 1;
      proJl[index].num = numJl;
      this.setData({
         createOrderList: proJl
      })
   },
   // 其它
   // 减
   bindminusQt: function (e) {
      var index = e.target.dataset.key;
      var proJl = this.data.create_other;
      var numJl = proJl[index].num;
      numJl = numJl - 1;
      if (numJl <= 0) {
         numJl = 0;
      }
      proJl[index].num = numJl;
      this.setData({
         create_other: proJl
      })
   },
   // 加
   bindaddQt: function (e) {
      var index = e.target.dataset.key;
      var proJl = this.data.create_other;
      var numJl = proJl[index].num;
      numJl = numJl + 1;
      proJl[index].num = numJl;
      this.setData({
         create_other: proJl
      })
   },
   // 新建
   // 减
   bindminusXj: function (e) {
      var index = e.currentTarget.dataset.key;
      var proJl = this.data.create_newpro;
      var numJl = proJl[index].num;
      numJl = numJl - 1;
      if (numJl <= 1) {
         numJl = 1;
      }
      proJl[index].num = numJl;
      this.setData({
         create_newpro: proJl
      })
   },
   // 加
   bindaddXj: function (e) {
      var index = e.currentTarget.dataset.key;
      var proJl = this.data.create_newpro;
      var numJl = proJl[index].num;
      numJl = numJl + 1;
      proJl[index].num = numJl;
      this.setData({
         create_newpro: proJl
      })
   },
   // 新建产品
   newProduct: function(){
      var creData = this.data.create_newpro;
      var newData = {
         'product_count': 0,
         'product_name': '',
         'spec_id': this.data.skuList[0].id,
         'type': 0,
         'sku_code': 1,
         'num': 1,
         'isMove':false,
         'value': '',
         "is_unliever": 1,
      };
      creData.push(newData);
      this.setData({
         create_newpro: creData
      })
   },
  
   //下拉刷新
   onPullDownRefresh: function () {
      wx.showNavigationBarLoading() 
      this.requestProduct();
   },   

   // 初始加载
   onLoad: function () {
      var _this = this;
      let userInfoData = wx.getStorageSync('userInfo');
      var baseInfo;
      var infoGet = api.getSuperInfo;
      wx.request({
         url: infoGet,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            _this.setData({
               shop_name: res.data.data.name
            })
         }
      })
      _this.setData({
         userInfoData: userInfoData,
      }) 
      
      let getspecification = api.getspecification;
      var dataXinJ = [];
      wx.request({
         url: getspecification,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            dataXinJ = res.data.data;
         },
         fail: function (res) {

         },
         complete: function () {
            //jiang规格数据转成 key-> value
            var newData = {
               'product_count': 0,
               'product_name': '',
               'spec_id': 0,
               'type': 0,
               'sku_code': 1,
               'num': 1,
               'isMove': false,
               'value': '',
               'is_unliever': 1,
            };
            var create_newpro = _this.data.create_newpro;
            create_newpro.push(newData);
            var newSkuList = _this.data.newSkuList;
            for (var i = 0; i < dataXinJ.length; i++) {
               newSkuList[i] = dataXinJ[i].name;
            }
            _this.setData({
               skuList: dataXinJ,
               newSkuList: newSkuList,
               create_newpro: create_newpro
            })
         }
      })
      // 请求家乐和其它产品
      _this.requestProduct();
  
   },

   onShow: function(){
      // 重新加载产品数据
      this.requestProduct();
   },

   // 请求家乐和其它产品
   requestProduct: function(){
      let _this = this;
      let dataProJl = [];
      var dataProQt = [];
      let userInfoData = wx.getStorageSync('userInfo');
      wx.request({
         url: api.getClientPro,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            if (res.data.code === 200) {
               var dataJl = res.data.data.jiale_product_list;
               var dataQt = res.data.data.other_product_list;
               dataProJl = dataJl;
               dataProQt = dataQt;
               wx.stopPullDownRefresh();
               wx.hideNavigationBarLoading();
            }
         },
         fail: function (res) {

         },
         complete: function () {
            for (var i = 0; i < dataProJl.length; i++) {
               dataProJl[i]['num'] = 0;
               dataProJl[i]['product_id'] = dataProJl[i].sku_unit[0].product_id;
            }
            for (var i = 0; i < dataProQt.length; i++) {
               dataProQt[i]['num'] = 0;
               dataProQt[i]['product_id'] = dataProQt[i].sku_unit[0].product_id;
            }
            _this.setData({
               createOrderList: dataProJl,
               create_other: dataProQt
            })
         }
      })
   }
})
