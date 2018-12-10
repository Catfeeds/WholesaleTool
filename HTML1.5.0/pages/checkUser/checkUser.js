
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
   data: {
      checkUserList: [],
      product_parent_id: '',
      proType:'',
      userInfo:[],
      baseInfo: {}
   },
   // 返回上一级
   pageBack: function () {
      wx.navigateBack({
         delta: 1
      })
   },
   checkIcon: function(e){
      var checkIcon_arr = [];
      var iconIndex = parseInt(e.currentTarget.dataset.index);
      var _seleted = !this.data.checkUserList[iconIndex].seleted;
      this.data.checkUserList[iconIndex].seleted = _seleted;
      checkIcon_arr = this.data.checkUserList;
      this.setData({
         checkUserList: checkIcon_arr
      })
      var productBind = api.productBind;
      var proType = this.data.proType;
      var product_parent_id = this.data.product_parent_id;
      var userId = checkIcon_arr[iconIndex].user_id;
      var token = this.data.userInfo.token;
      var unionId = this.data.userInfo.unionId;
      wx.request({
         url: productBind,
         method: 'POST',
         data: {
            "token": token,
            "unionId": unionId,
            "user_id": userId,
            "type": proType,
            "product_parent_id": product_parent_id
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            console.log(res.data.msg);
         },
         fail: function (res) {

         }
      })
   },


   // 初始加载
   onLoad: function (options) {
      var _this = this;
      var product_parent_id = options.product_parent_id;
      var proType = options.type;
      let getsupplierUser = api.getsupplierUser;
      let userInfoData = wx.getStorageSync('userInfo');
      _this.setData({
         userInfo: userInfoData,
         baseInfo: wx.getStorageSync("baseInfo")
      })
      var checkUserData = [];
      wx.request({
         url: getsupplierUser,
         method: 'POST',
         data: {
            "token": userInfoData.token,
            "unionId": userInfoData.unionId,
            "type": proType,
            "product_parent_id": product_parent_id
         },
         header: {
            'content-type': 'application/json'
         },
         success(res) {
            checkUserData = res.data.data;
         },
         fail: function (res) {
           
         },
         complete: function () {
            _this.setData({
               checkUserList: checkUserData,
               product_parent_id: product_parent_id,
               proType: proType
            })
         }
      })
   }
})
