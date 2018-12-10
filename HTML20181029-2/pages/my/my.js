
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
  data: {
    status: 0,             //0-专属小程序 1-我的客户
    ShareShow:true,       //分享弹框
    QRcodeUrl: "",
    titleLable: [
      { status: 0, name: "专属收单码" },
      { status: 1, name: "我的客户" }
    ],
    scrollHeight: 0,
    shopList:[],
    baseInfo:{}
  },

  // 初始加载
  onLoad: function () {
    let that = this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          scrollHeight: res.windowHeight-57
        });
      }
    });

    this.setData({
      baseInfo: wx.getStorageSync("baseInfo")          //获取基本信息
    });

    that.queryShopList();    //查询我的客户列表
    that.getQrCode();         //获取专属收单码
  },

  // 切换菜单状态
  changeStatus: function (e) {
    let that = this;
    let value = e.target.dataset.status;
    let status = that.data.status;
    if (status != value) {
      that.setData({
        status: value,
      });
    }
  },

  // 查询我的客户列表
  queryShopList: function () {
    let that = this;
    util.request(api.getShopList).then(function (res) {
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code == 200) {
          that.setData({
            shopList:res.data
          });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      //util.msg(1, "查询失败");
      console.log(res)
    });
  },

  // 查询专属收单码
  getQrCode: function () {
    let that = this;
    util.request(api.getQrCode).then(function (res) {
     console.log(res);
      if (res.code == 200) {
          that.setData({
            QRcodeUrl:res.data
          });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
     console.log(res);
    });
  },

  // 小程序码加载失败
  qrcodeError:function(){
      this.setData({
        QRcodeUrl: "/static/images/xcx.png"
      });
  },

  // 下载二维码
  download: function () {
    let QRcodeUrl = this.data.QRcodeUrl;
    wx.downloadFile({
      url: QRcodeUrl, //仅为示例，并非真实的资源
      success: function (res) {
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function (res) {
            console.log(res)
          },
          fail: function (res) {
            util.msg(1, "下载失败");
          }
        })
      }
    })
  },

  // 监听转发
  onShareAppMessage: function (o) {
    let superUser = wx.getStorageSync("superUser");
    let id = superUser.supplier_id||"";
    let name = this.data.baseInfo.name;//superUser.nickname||"";
    let coupon = this.data.baseInfo.desc
    console.log("转发：",id,name,coupon);
    return {
      title: '邀请订单小程序',
      path: 'pages/customer/welcome/welcome?supplier_id=' + id + "&supplier_name=" + name + "&supplier_coupon=" + coupon
    }
  },

  // 下拉刷新
  onPullDownRefresh:function(){
    wx.showNavigationBarLoading() //在标题栏中显示加载
    this.queryShopList();    //查询我的客户列表
  },

  // 控制弹出
  togglePopup() {
    this.setData({
      ShareShow: !this.data.ShareShow
    });
  }

})
