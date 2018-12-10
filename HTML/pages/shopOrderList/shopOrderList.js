
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()

Page({
  data: {
    titleLable: [
      { status: 1, name: "未确认" },
      { status: 2, name: "已确认" },
      { status: 3, name: "已拒绝" },
      { status: 0, name: "全部" },
    ],
    nameArr: ["", "未确认", "已确认", "已拒绝"],
    userInfo: {},
    account: "",
    scrollHeight: 0,
    status: 0,
    page: 1,
    rows: 3,
    nomore: false,
    list: [{ id: '0', title: '商品详情' }, { id: '1', title: '用户评价' }],
    orderList: [],
    shopName:"",
    shopId:"",
    baseInfo:{}
  },

  // 初始加载
  onLoad: function (option) {
    let that = this;
    
    let shopName = option.shopName;         //传递过来的餐厅名称
    let shopId = option.shopId;             //传递过来的餐厅id
    console.log(shopName);
    console.log(shopId);
    wx.setNavigationBarTitle({
      title: shopName
    })
    this.setData({
      shopName: shopName,
      shopId: shopId
    });

    this.setData({
      baseInfo: wx.getStorageSync("baseInfo")          //获取基本信息
    });

    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          scrollHeight: res.windowHeight - 125
        });
      }
    });


  },

  onShow: function () {
    this.queryOrderList();    //查询订单列表
  },

  // 切换查询订单状态
  changeStatus: function (e) {
    let that = this;
    let value = e.target.dataset.status;
    let status = that.data.status;
    console.log(value);
    if (status != value) {
      that.setData({
        status: value,
        orderList:[]
      });
      that.queryOrderList();    //重载订单列表
    }
  },

  // 查询订单列表
  queryOrderList: function () {
    let that = this;
    let status = that.data.status;
    let shopId = that.data.shopId;

    wx.showLoading({
      title: '加载中...',
    })
    util.request(api.getSuperOrderList, {
      order_status: status,
      client_id: shopId
    }).then(function (res) {
      wx.hideLoading();
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code === 200) {
        let orderList = res.data;
        orderList.forEach((item) => {
          item.list.forEach((it) => {      //遍历处理时间
            it.created_at = util.timestampFormatter(it.created_at, 1);
          });
        });
        that.setData({
          orderList: orderList
        });
        if (orderList.length <= 0) {
          util.msg(1, "没有数据");
        }
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      //util.msg(1, "查询失败");
      console.log(res)
    });
  },

  // 下拉刷新
  onPullDownRefresh: function () {
    wx.showNavigationBarLoading() //在标题栏中显示加载
    this.queryOrderList();    //查询订单列表
  }

})
