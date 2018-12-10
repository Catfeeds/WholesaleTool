
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    userInfo: {},
    superInfo:{},
    resultStatus:1            //注册成功1  0-本地区暂未开通
  },

  // 初始加载
  onLoad: function (option) {
    let that = this;
    let resultStatus = option.resultStatus; 
    that.setData({
      resultStatus:resultStatus
    });

    that.getSuperInfo();      //获取供应商基本信息

  },

  call: function () {
    wx.makePhoneCall({
      phoneNumber: '021-34159566' //仅为示例，并非真实的电话号码
    })
  },
  // 获取供应商基本信息
  getSuperInfo: function () {
    let that = this;
    util.request(api.getSuperInfo).then(function (res) {
      if (res.code === 200) {
        that.setData({
          superInfo: res.data
        });
        wx.setStorageSync("superInfo", res.data);
      } else {
        console.log("查询供货商信息失败");
      }
    }, function (res) {
      console.log(res);
    });
  },




})
