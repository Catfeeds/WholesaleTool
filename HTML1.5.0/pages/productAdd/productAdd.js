
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
   data: {
      titleLable: [
         { status: 1, name: "最近三天" },
         { status: 0, name: "全部" },
      ],
      status: 0,             //1-最近三日 0-全部
      addListArray: [],
      jlProductData:[],
      qtProductData:[],
      baseInfo:{},
      popWrap: false,
      popWrapXj: false,
      textArr:[],
      popShowBack: false
   },
   // 返回上一级
   pageBack: function () {
      wx.navigateBack({
         delta: 1
      })
   },
   // 点击返回
   backPage: function(){
      this.setData({
         popShowBack: true
      })
   },
   // 确认返回
   back_affirm: function(){
      this.pageBack();
      this.setData({
         popShowBack: false
      });
   },
   // 取消
   back_cancel: function(){
      this.setData({
         popShowBack: false
      });
   },
   // 新建产品
   bingNewPro: function(){
      this.verifyData('submitXj');
   },
   checkboxStatus: function(e){
      var check_index = parseInt(e.currentTarget.dataset.cellindex);
      var arrIndex = parseInt(e.currentTarget.dataset.key);
      var check_status = !this.data.addListArray[arrIndex].checkboxItems[check_index].checked;
      this.data.addListArray[arrIndex].checkboxItems[check_index].checked = check_status;
      this.setData({
         addListArray: this.data.addListArray
      })
   },
   // 清空输入
   emptyData: function(e){
      let _index = e.currentTarget.dataset.key;
      this.data.addListArray[_index].productName = '';
      this.setData({
         addListArray: this.data.addListArray
      });
   },
   // 产品输入内容
   bindInput: function(e){
      var inputValue = e.detail.value;
      var arrIndex = e.currentTarget.dataset.key;
      this.data.addListArray[arrIndex].productName = inputValue;
      this.setData({
         addListArray: this.data.addListArray
      })
     
   },
   // 输入框失去焦点
   bindblur: function(e){
       // 判断是否新建重复
      var inputValue = e.detail.value;
      var arrIndex = e.currentTarget.dataset.key;
      let jlProductData = this.data.jlProductData;
      let qtProductData = this.data.qtProductData;
      let _arrInput = this.data.addListArray;
      for (let i = 0; i < jlProductData.length;i++){
         if (_arrInput[arrIndex].productName == jlProductData[i].product_name){
            wx.showToast({
               title: '该产品已存在,请勿重复创建',
               icon: 'none'
            })
            _arrInput[arrIndex].productName = '';
            this.setData({
               addListArray: _arrInput
            })
            return;
         }
      }
      for (let i = 0; i < qtProductData.length; i++) {
         if (_arrInput[arrIndex].productName == qtProductData[i].product_name) {
            wx.showToast({
               title: '该产品已存在,请勿重复创建',
               icon: 'none'
            })
            _arrInput[arrIndex].productName = '';
            this.setData({
               addListArray: _arrInput
            })
            return;
         }
      }
      for (let i = 0; i < _arrInput.length; i++) {
         if(inputValue){
            if (inputValue == _arrInput[i].productName && arrIndex !=i) {
               wx.showToast({
                  title: '产品名称重复',
                  icon: 'none'
               })
               _arrInput[arrIndex].productName = '';
               this.setData({
                  addListArray: _arrInput
               })
               return;
            }
         }
      }

   },
  
   // 提交按钮
   bindSubmit: function(){
      this.verifyData('submit');
   },

   // 新建提交验证
   verifyData: function(type){
      var _addList = this.data.addListArray;
      for (var i = 0; i < _addList.length; i++) {
         let checkLength = _addList[i].checkboxItems.length;
         for (var j = 0; j < _addList[i].checkboxItems.length; j++) {
            if (_addList[i].productName == '') {
               wx.showToast({
                  title: '请输入产品名称',
                  icon: 'none'
               })
               return;
            }
            if (_addList[i].checkboxItems[j].checked == false) {
               checkLength = checkLength - 1;
               if (checkLength == 0) {
                  wx.showToast({
                     title: '请选择单位',
                     icon: 'none'
                  })
                  return;
               }
            }
         }
      }
      if(type == 'submit'){
         this.setData({
            popWrap: true
         })
      }
      if (type == 'submitXj') {
         this.setData({
            popWrapXj: true
         })
      }
   },

   // 取消
   pop_cancel: function(){
      this.setData({
         popWrap: false
      })
   },
   pop_cancelXj: function () {
      this.setData({
         popWrapXj: false
      })
   },
   // 确认提交
   pop_affirm: function(){
      var _this = this;
      _this.setData({
         popWrap: false
      })
      let userInfoData = wx.getStorageSync('userInfo');
      var submitNewProductUrl = api.submitNewProduct;
      let addListArray = _this.data.addListArray;
      var newArr = [];
      for (let i = 0; i < addListArray.length;i++){
         var checked = [];
         if (addListArray[i].productName){
            var name  = addListArray[i].productName;
         }
         for (let j = 0; j < addListArray[i].checkboxItems.length;j++){
            if (addListArray[i].checkboxItems[j].checked) {
               checked.push(addListArray[i].checkboxItems[j].id);
            }
         }
         newArr[i] = { "name": name, "spec_id": checked};
      }
      wx.request({
         url: submitNewProductUrl,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId,
            "product_data": newArr
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
             if(res.data.code == 200){
               wx.showToast({
                  title: '提交成功',  
                  icon: 'success'  
               });
               setTimeout(() => {
                  wx.switchTab({
                     url: '../productManage/productManage'
                  })
               },1200)
            }
         },
         fail: function (res) {

         },
         complete: function () {
             
         }
      })
   },

   // 新建提交
   pop_affirmXj: function () {
      var _this = this;
      _this.setData({
         popWrapXj: false
      })
      let userInfoData = wx.getStorageSync('userInfo');
      var submitNewProductUrl = api.submitNewProduct;
      let addListArray = _this.data.addListArray;
      var newArr = [];
      for (let i = 0; i < addListArray.length; i++) {
         var checked = [];
         if (addListArray[i].productName) {
            var name = addListArray[i].productName;
         }
         for (let j = 0; j < addListArray[i].checkboxItems.length; j++) {
            if (addListArray[i].checkboxItems[j].checked) {
               checked.push(addListArray[i].checkboxItems[j].id);
            }
         }
         newArr[i] = { "name": name, "spec_id": checked };
      }
      wx.request({
         url: submitNewProductUrl,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId,
            "product_data": newArr
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            if (res.data.code == 200) {
               wx.showToast({
                  title: '提交成功',
                  icon: 'success'
               });
               for (let i = 0; i < _this.data.addListArray.length;i++){
                  _this.data.addListArray[i].productName = '';
                  for (let j = 0; j < _this.data.addListArray[i].checkboxItems.length;j++){
                     _this.data.addListArray[i].checkboxItems[j].checked = false;
                  }
               }
               _this.setData({
                  addListArray: _this.data.addListArray
               })
            }
         },
         fail: function (res) {

         },
         complete: function () {

         }
      })
   },

   // 返回上一级
   goBack: function(){
      wx.navigateBack({
         delta: 1
      })
   },

   // 初始加载
   onLoad: function () {
      var _this = this;
      _this.setData({
         baseInfo: wx.getStorageSync("baseInfo")
      })
      let userInfoData = wx.getStorageSync('userInfo');
      var getspecification = api.getspecification;
      var specification = [];
      var checkboxItems = _this.data.addListArray.checkboxItems;
      // 请求家乐产品
      util.request(api.getProductManageJl).then(function(res){
         let jlPro = _this.data.jlProductData;
         jlPro = res.data;
         _this.setData({
            jlProductData: jlPro
         })
      },function(res) {
        
      });
      // 请求其它产品
      util.request(api.getProductManageQt).then(function (res) {
         let qtPro = _this.data.qtProductData;
         qtPro = res.data;
         _this.setData({
            qtProductData: qtPro
         })
      }, function (res) {

      });

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
            specification = res.data.data;
         },
         fail: function (res) {
            
         },
         complete: function () {
            var item = {};
            var spec = [];
            for (var i = 0; i < specification.length;i++){
               var tmp = { 
                  'id': specification[i].id,
                  'name': specification[i].name,
                  'checked':false,
                  };
               spec[i] = tmp;
            }
            item = { 'productName': '', 'checkboxItems': spec};
            var addArr = [];
            addArr[0] = item;
            _this.setData({
               addListArray: addArr
            })
         }
      })   
   }
})
