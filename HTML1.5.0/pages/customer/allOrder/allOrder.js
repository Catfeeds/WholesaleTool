
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    status: 0,             //1-未确认 2-已确认 3-已拒绝 0-全部
    titleLable: [
      { status: 1, name: "未确认" },
      { status: 2, name: "已确认" },
      { status: 3, name: "已拒绝" },
      { status: 0, name: "全部" },
    ],
    nameArr:["","未确认","已确认","已拒绝"],
    userInfo: {},
    account: "",
    scrollHeight: 0,
    nomore: false,
    orderList: [],
    superInfo:{}
  },

  // 初始加载
  onLoad: function (options) {
   let that = this;
   let _status = options.status;
   let _statusdata = that.data.status;
   if(_status){
      _statusdata = _status;
   }
   that.setData({
      status: _statusdata
   });
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          scrollHeight: res.windowHeight - 200
        });
      }
    });
    let userInfo = wx.getStorageSync('userInfo');
    console.log(userInfo);
    that.getSuperInfo();      //获取供应商基本信息
    that.queryOrderList();    //查询订单列表

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

  // 切换查询订单状态
  changeStatus: function (e) {
    let that = this;
    let value = e.target.dataset.status;
    let status = that.data.status;
    if (status != value) {
      that.setData({
        status: value,
      });
      that.queryOrderList();    //重载订单列表
    }
  },

  // 查询订单列表
  queryOrderList: function () {
    let that = this;
    let status = that.data.status;      //订单状态

    wx.showLoading({
      title: '查询中',
    })

    util.request(api.getOrderList, {
      order_status: status
    }).then(function (res) {
      wx.hideLoading();
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code == 200) {
        if (res.data.length>0){
          let list = res.data;
          list.forEach((item)=>{
            item.created_at = util.timestampFormatter(item.created_at);   //转化时间戳为日期字符串

          });
          that.setData({
            orderList:list
          });
        }else{
          that.setData({
            orderList: []
          });
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


  //图片出错
  iconError: function (e) {
    let index = e.target.dataset.index;
    let pindex = e.target.dataset.pindex;

    var orderList = this.data.orderList;
    orderList[pindex].product_list[index].uploaded_img = "/static/images/defaule.png";

    this.setData({
      orderList: orderList
    });
  },

  // 下拉刷新
  onPullDownRefresh: function () {
    wx.showNavigationBarLoading() //在标题栏中显示加载
    this.queryOrderList();    //查询订单列表
  },


    //一键清缓存
  clearStorage: function (e) {
    let superInfo = e.currentTarget.dataset.supplier_id;
    let supplier_id = superInfo.supplier_id;
    let supplier_name = superInfo.name;
    let supplier_coupon = superInfo.desc;
    console.log(superInfo)
    console.log(supplier_id)
    console.log(supplier_name)
    console.log(supplier_coupon)
    wx.navigateTo({
      url: '/pages/clearStorage/clearStorage?pageNum=' + supplier_id + "&supplier_name=" + supplier_name + "&supplier_coupon=" + supplier_coupon
    })
  }


})
