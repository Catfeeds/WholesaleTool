
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
  data: {
    userInfo: {
      unionId:"123456"
    },
    QRcodeUrl:"https://lg-g07oaz6q-1257047110.cos.ap-shanghai.myqcloud.com/qrcode_for_gh_5b04a7946392_344.jpg",
 
  },

  // 初始加载
  onLoad: function (option) {
    let that = this;

    //that.queryOrderList();    //查询二维码

  },

  // 查询二维码
  queryQRcode:function(){
    util.request(api.queryOrderList, {
      page: page,
      rows: rows,
      status: status
    }, 'POST').then(function (res) {
      console.log(res);
      if (res.errno === 0) {

      } else {
       // util.msg(1, "查询失败");
      }
    }, function (res) {
      //util.msg(1, "查询失败");
      console.log(res)
    });
  },


  // 返回上一级
  comback: function () {
    wx.switchTab({
      url: "/pages/my/my"
    });
  },

  // 监听转发
  onShareAppMessage:function(o){
    let unionId = this.data.userInfo.unionId;
    return {
      title: '邀请订单小程序',
      path: 'pages/customer/welcome/welcome?pUnionId=' + unionId
    }
  },

  // 下载二维码
  download:function(){
    let QRcodeUrl = this.data.QRcodeUrl;
    console.log(QRcodeUrl);
    wx.downloadFile({

      url: QRcodeUrl, //仅为示例，并非真实的资源
      success: function (res) {
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function (res) {
            console.log(res)
          },
          fail: function (res) {
            util.msg(1,"下载失败");
          }
        })

      }, fail:function(){
        console.log(111);
      }, complete:function(){
        console.log(222);

      }
    })
  }
 


})
