// pages/index/guide.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imgs: [
      "/static/guide1/sh_edit.png",
      "/static/guide1/sh_comfirm.png",
    ],

    img: "http://img.kaiyanapp.com/7ff70fb62f596267ea863e1acb4fa484.jpeg",
    url:"",
    indicatorDots: true,
    autoplay: true,
    interval: 2000,
    duration: 200,
    orderId: "",
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log('这是订单详情')
    console.log(options)
    let orderId = options.orderId;
    this.setData({
      orderId: orderId
    });

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

  },
  


  start() {
    console.log('引导层结束'+ this.data.orderId)
    wx.setStorageSync('isFirst', "1");
    wx.reLaunch({
      url: '../orderDetail/orderDetail?orderId=' + this.data.orderId
    })
  },

  
})