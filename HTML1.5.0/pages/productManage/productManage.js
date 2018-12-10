
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
   data: {
      titleLable: [
         { status: 1, name: "家乐产品" },
         { status: 0, name: "其它品牌产品" },
      ],
      status: 1, //1-家乐产品 0-其它品牌产品
      modify: [],
      JiaLeProduct: [],
      otherProduct: [],
      userInfoData:[],
      baseInfo: {},
      flag: true,
      scrollTop:0,
      popWrap: false,
      popindex:0,
      inputValue:'',
   },
   modifytab: function (e) {
      var index = e.target.dataset.index;
      let modify1 = [];
      modify1[index] = true;
      this.setData({
         modify: modify1
      })
      
   },
   // 修改
   bindInput: function(e){
      // 去除空格
      function trim(str) {
         return str.replace(/\s/g, "");
      }
      var inputValue = trim(e.detail.value);
      var inputIndex = e.target.dataset.key;
      this.setData({
         inputValue: inputValue
      })
   },
   
   affirmtab: function (e) {
      var _this = this;
      var index = e.target.dataset.key;
      var modifyData = _this.data.otherProduct;
      var inputValue = _this.data.inputValue;
      for (var i = 0; i < modifyData.length; i++) {
         // 判断是否合并
         if (inputValue == ''){
            closeModify();
            return;
         }
         if (inputValue == modifyData[i].product_name && index != i) {
            _this.setData({
               popWrap: true,
               popindex: index,
            })
            closeModify();
            return;
         }
      }
      function closeModify(){
         let modifyqt = [];
         modifyqt[index] = !_this.data.modify[index];
         _this.setData({
            modify: modifyqt
         })
      }
      // 判断用户输入家乐产品
      let jlProduct = _this.data.JiaLeProduct;
      for (let i = 0; i < jlProduct.length;i++){
         if (inputValue == jlProduct[i].product_name) {
            wx.showToast({
               title: '该家乐产品已存在，请勿修改',
               icon: 'none'
            })
            closeModify();
            return;
         }
      }

      if (inputValue){
         modifyData[index].product_name = inputValue;
      }
      _this.setData({
         otherProduct: modifyData
      })
      let modify2 = [];
      modify2[index] = !_this.data.modify[index];
      var inputValue = _this.data.otherProduct[index].product_name;
      _this.setData({
         modify: modify2,
         inputValue: inputValue
      })
      var product_parent_id = _this.data.otherProduct[index].product_parent_id;
      var token = _this.data.userInfoData.token;
      var unionId = _this.data.userInfoData.unionId;
      var modifyProQt = api.modifyProQt;
      wx.request({
         url: modifyProQt,
         method: 'POST',
         data: {
            "token": token,
            "unionId": unionId,
            "product_name": inputValue,
            "product_parent_id": product_parent_id,
            "spec_ids": "-1"
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {

         },
         fail: function (res) {

         }
      })

   },
   // 确认合并
   pop_affirm: function(){
      let _this = this;
      let _index = _this.data.popindex;
      var inputValue = _this.data.inputValue;
      let otherPro = _this.data.otherProduct;
      // 重新调用接口
      var product_parent_id = _this.data.otherProduct[_index].product_parent_id;
      otherPro[_index].product_name = inputValue;
      var token = _this.data.userInfoData.token;
      var unionId = _this.data.userInfoData.unionId;
      var modifyProQt = api.modifyProQt;
      _this.data.inputValue = '';
      wx.request({
         url: modifyProQt,
         method: 'POST',
         data: {
            "token": token,
            "unionId": unionId,
            "product_name": inputValue,
            "product_parent_id": product_parent_id,
            "spec_ids": "-1"
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            if(res.data.code == 200){
               otherPro.splice(_index, 1);
               _this.setData({
                  popWrap: false,
                  otherProduct: otherPro
               })
            }
         },
         fail: function (res) {

         }
      });
   },

   // 取消合并
   pop_cancel: function(){
      this.setData({
         popWrap: false
      })
   },
   // 初始加载
   onLoad: function (options) {
     this.requestJlandQt();
   },

   // 页面刷新加载数据
   onShow: function(){
      this.requestJlandQt();
   },

   // 请求家乐和其它产品接口
   requestJlandQt: function(){
      var _this = this;
      let getProductJlUrl = api.getProductManageJl;
      let userInfoData = wx.getStorageSync('userInfo');
      _this.setData({
         userInfoData: userInfoData,
         baseInfo: wx.getStorageSync("baseInfo"),
      })
      let dataProJl = [];
      wx.request({
         url: getProductJlUrl,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            dataProJl = res.data.data;
            for (let i = 0; i < dataProJl.length; i++) {
               dataProJl[i]['isSecond'] = false;
               if (checksum(dataProJl[i].product_name) > 18) {
                  dataProJl[i].isSecond = true;
               }
            }
            // 判断字数个数
            function checksum(chars) {
               var sum = 0;
               for (var i = 0; i < chars.length; i++) {
                  var c = chars.charCodeAt(i);
                  if ((c >= 0x0001 && c <= 0x007e) || (0xff60 <= c && c <= 0xff9f)) {
                     sum++;
                  }
                  else {
                     sum += 2;
                  }
               }
               return sum;
            }
            _this.setData({
               JiaLeProduct: dataProJl
            })
         },
         fail: function (res) {

         },
         complete: function () {

         }
      });
      // 请求其它产品
      let getProductQtUrl = api.getProductManageQt;
      let dataProQt = [];
      wx.request({
         url: getProductQtUrl,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            dataProQt = res.data.data;
            _this.setData({
               otherProduct: dataProQt
            })
         },
         fail: function (res) {

         },
         complete: function () {

         }
      });

   },

   // 列表切换
   changeStatus: function (e) {
      let _this = this;
      let value = e.target.dataset.status;
      let status = this.data.status;
      if (status != value) {
         this.setData({
            status: value 
         });
      }
   },

   // 回到顶部
   scroll: function (e) {
      if (e.detail.scrollTop > 10) {
         this.setData({
            flag: false
         });
      } else {
         this.setData({
            flag: true
         });
      }
   },
   //回到顶部
   goTop: function (e) { 
      this.setData({
         scrollTop:0
      })
   },



})
