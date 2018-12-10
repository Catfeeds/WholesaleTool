var util = require("../../utils/util");
var api = require("../../config/api")
// pages/resetPsw/resetPsw.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    unionId: "",
    token: "",
    password: "",
    password_confirm: "",
  },

  // 监听密码输入
  password: function (e) {
    this.setData({
      password: e.detail.value,
    });
  },
  password_confirm: function (e) {
    this.setData({
      password_confirm: e.detail.value,
    });
  },
  resetPwd: function () {
    console.log(12321312)
    let userInfo = wx.getStorageSync("userInfo");
    wx.showLoading({
      title: '请稍后',
    })
    let _this = this
    util.request(api.resetPwd, {
      unionId: userInfo.unionId,
      token: userInfo.token,
      password: _this.data.password,
      password_confirm: _this.data.password_confirm,
    }).then(function (res) {
      wx.hideLoading();
      if (res.code == 200) {
        wx.switchTab({
          url: '../index/index',
        });
        // wx.redirectTo({
        //   url: '../guide/guide',
        // });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})