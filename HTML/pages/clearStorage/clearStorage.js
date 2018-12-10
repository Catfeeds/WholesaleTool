
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
var user = require('../../utils/user.js');
const app = getApp()
import md5 from '../../utils/md5.js';

Page({
  data:{
    pageNum:"",
    supplier_name: "",
    supplier_coupon: "",
  },
  // 初始加载
  onLoad: function (option) {
    console.log(option)
    let that = this;
    let pageNum = option.pageNum;
    let supplier_name = option.supplier_name;
    let supplier_coupon = option.supplier_coupon;
    console.log('从页面pageNum=' + pageNum)
    console.log('从页面supplier_name=' + supplier_name)
    console.log('从页面supplier_coupon=' + supplier_coupon)
    that.setData({
      pageNum: pageNum,
      supplier_name: supplier_name,
      supplier_coupon: supplier_coupon
    });
  },

  //一键清缓存
  clearStorage: function (e) {
    console.log('清除缓存喽')
    let that = this;
    console.log(e)
    let pageNum = e.currentTarget.dataset.page;
    let supplier_name = e.currentTarget.dataset.supplier_name;
    let supplier_coupon = e.currentTarget.dataset.supplier_coupon;
    console.log(wx.getStorageSync('userInfo'))
    wx.clearStorage({
      success: function (res) {
        wx.showToast({
          title: "缓存清除成功",
          duration: 3000
        })
        console.log("清完之后呢" + wx.getStorageSync('userInfo'))
        console.log("pageNum = " + pageNum)
        console.log("supplier_name = " + supplier_name)
        console.log("supplier_coupon = " + supplier_coupon)
        if (pageNum=="1"){
          wx.navigateTo({
            url: '/pages/login/login'
          });
        } else{
          wx.navigateTo({
            url: '/pages/customer/welcome/welcome?supplier_id=' + pageNum + "&supplier_name=" + supplier_name + "&supplier_coupon=" + supplier_coupon
          });
        }
       
      },
    })
  }

})
