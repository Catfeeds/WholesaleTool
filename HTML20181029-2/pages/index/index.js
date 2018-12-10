
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
  data: {
    titleLable: [
      { status: 1, name: "最近三天" },
      { status: 0, name: "全部" },
    ],
    userInfo: {},
    scrollHeight:0,
    status:0,             //1-最近三日 0-全部
    orderList:[],
    baseInfo:{},
    ShareShow: false,   
    ShareShow2: false,   
  },

  // 初始加载
  onLoad: function () {
    let that = this;
    wx.getSystemInfo({
      success: function (res) {
        console.log(res.windowHeight)
        that.setData({
          scrollHeight: res.windowHeight - 100
        });
      }
    });

    this.setData({
      baseInfo: wx.getStorageSync("baseInfo")          //获取基本信息
    });

  
    //获取手机型号，适配iphone手机
    wx.getSystemInfo({
      success: function (res) {
        // console.log('手机品牌 = ' + res.brand)
        // console.log('手机型号 = ' + res.model)
        // console.log('设备像素比 = ' + res.pixelRatio)
        // console.log('操作系统版本 = ' + res.system)
        // console.log('客户端平台 = ' + res.platform)
        // console.log('微信版本号 = ' + res.version)
        // console.log('屏幕宽度 = ' + res.screenWidth)
        // console.log('屏幕高度 = ' + res.screenHeight)
        // console.log('可使用窗口宽度 = ' + res.windowWidth)
        // console.log('可使用窗口高度 = ' + res.windowHeight)
        let modelmes = res.model;
        if (modelmes.search('iPhone X') != -1) {
          that.setData({
            isIphoneX: true
          })
        } else if (res.pixelRatio == 3){
          that.setData({
            isIphoneX: 'Nokia'
          })
        }else{
          that.setData({
            isIphoneX: false
          })
        }
      }
    })
    console.log('isIphoneX = ' + that.data.isIphoneX)  
  
  
    //根据缓存判断是否第一次进入
    if (wx.getStorageSync('isFirst') === "0"){
      this.setData({
        ShareShow: true          
      });
    }
    console.log(wx.getStorageSync('isFirst'))

  },

  onShow:function(){
    this.queryOrderList();    //查询订单列表
  },

  // 切换查询时间
  changeStatus:function(e){
    let that = this;
    let value = e.target.dataset.status;
    let status = that.data.status;
    if (status!=value){
        that.setData({
          status:value,
          page:1                  //页数清空
        });
      that.queryOrderList();    //重载订单列表
    }
  },

  // 查询订单列表
  queryOrderList: function () {
    let that = this;
    let status = that.data.status;

    util.request(api.getSuperOrderList, {
      order_status: 1,            //未确认
      date_type:status            //0全部 1近三天
    }).then(function (res) {
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code === 200) {
        
        let orderList = res.data;
        let show_layer = res.show_layer;
        orderList.forEach((item) => {
          item.list.forEach((it) => {      //遍历处理时间
            it.created_at = util.timestampFormatter(it.created_at, 1);
          });
        });
        that.setData({
          orderList: orderList
        });
        console.log("是1就显示订单详情引导" + res.show_layer)
        if(show_layer == "1"){
          that.setData({
            ShareShow2: true
          });
        }
        if (orderList.length <= 0) {
          util.msg(1, "没有数据");
        }
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
     // util.msg(1, "查询失败");
     console.log(res)
    });
  },

  // 下拉刷新
  onPullDownRefresh: function () {
    wx.showNavigationBarLoading() //在标题栏中显示加载
    this.queryOrderList();    //查询订单列表
  },

  // 控制导航层显示
  togglePopup() {
    this.setData({
      ShareShow: !this.data.ShareShow,
    });
  },
  togglePopup2() {
    this.setData({
      ShareShow2: false
    });
    util.request(api.set_not_show_layer, {
    }).then(function (res) {
    }, function (res) {
      wx.hideLoading();
      console.log(res)
    });
  }
})
