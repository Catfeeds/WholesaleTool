
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    scrollHeight: 0,
    orderId: "",
    nomore: false,
    modelShow: false,      //模态框展示
    NOShow: false,         //拒绝后展示
    deleteGoodsId: null,   //要删除的商品id
    orderBackStatus: 1,    //订单 1确认/0拒绝 状态
    reason: '',            //拒绝原因,
    timer: 3,              //拒绝后 倒计时时间
    nameArr: ["", "未确认", "已确认", "已拒绝"],
    superInfo:{},
    orderDetail: {
      goodsList: []
    },

  },
   // 返回上一级
   pageBack: function () {
      wx.navigateBack({
         delta: 1
      })
   },
  // 初始加载
  onLoad: function (option) {
    let that = this;
    let orderId = option.orderId;
    let height = 0;

    wx.getSystemInfo({
      success: function (res) {
        height = res.windowHeight - 120;
      }
    });

    that.setData({
      scrollHeight: height,
      orderId: orderId,
      superInfo: wx.getStorageSync("superInfo")
    });


  },

  onShow:function(){
    this.queryOrderDetail();    //查询订单详情
  },

  // 切换查询时间
  changeStatus: function (e) {
    let that = this;
    let value = e.target.dataset.status;
    let status = that.data.status;
    if (status != value) {
      that.setData({
        status: value,
        page: 1                  //页数清空
      });
      that.queryOrderList();    //重载订单列表
    }
  },

  // 查询订单列表
  queryOrderDetail: function () {
    let that = this;
    let orderId = that.data.orderId;

    util.request(api.getOrderDetail, {
      order_id: orderId
    }).then(function (res) {
      console.log(res);
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code == 200) {
        let detail = res.data;
        if (detail){
          detail.created_at = util.timestampFormatter(detail.created_at);
        }
        that.setData({
          orderDetail:res.data
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

  // 返回上一级
  comback: function () {
    wx.switchTab({
      url: "/pages/index/index"
    });
  },
  // 订单 1确认/0拒绝
  orderYes: function (e) {
    let index = e.target.dataset.index;
    this.setData({
      modelShow: true,
      orderBackStatus: index             //保存是点击确认还是拒绝
    });
  },
  // 是/否
  toYes: function (e) {
    let that = this;
    let yesno = e.target.dataset.yesno;

    let orderBackStatus = this.data.orderBackStatus;        //1确认 0拒绝
    if (yesno == 1) {
      if (orderBackStatus == 1) {
        //执行确认操作
        console.log("确认操作。。。");
      } else {
        //执行拒绝操作
        let reason = that.data.reason;
        if (reason == "") {
          util.msg(1, "请输入理由");
          return false;
        }
        console.log("拒绝操作。。。");

        // util.request(api.login, userInfo, 'POST').then(function (res) {
        //   if (res.errno === 0) {
        that.refusedOrderAfter();  //接口成功后 执行拒绝后下一步
        //   } else {
        //     util.msg(1, "拒绝失败");
        //   }
        // }, function (res) {
        //   util.msg(1, "拒绝失败");
        // });
      }
    } else {
      this.setData({
        modelShow: false,         //隐藏
      });
    }

  },

  // 监听拒绝理由输入
  reasonInput: function (e) {
    this.setData({
      reason: e.detail.value,
    });
  },
  // 拒绝订单后执行
  refusedOrderAfter: function () {
    let that = this;
    that.setData({
      modelShow: false,         //隐藏原来的模态框
      NOShow: true,             //显示
    });

    let timer = that.data.timer;
    let t = setInterval(function () {
      console.log(timer);
      if (timer <= 0) {
        clearInterval(t);
        that.comback();           //返回上一级
      } else {
        that.setData({
          timer: timer,
          show: false
        });
        timer--;
      }
    }, 1000);
  },

  // 删除订单商品
  deleteGoods: function (e) {
    let goodId = e.target.dataset.id;     //要删除的商品id
    this.setData({
      deleteGoodsId: goodId
    });
  },

  // 是否删除商品
  toDeleteGoods: function (e) {
    let yesno = e.target.dataset.yesno;
    let deleteGoodsId = this.data.deleteGoodsId;
    if (yesno == "1") {
      //执行删除商品
      console.log("删除商品。。" + deleteGoodsId);
      // util.request(api.login, userInfo, 'POST').then(function (res) {
      //   if (res.errno === 0) {

      this.setData({
        deleteGoodsId: ""          //隐藏
      });
      util.msg(0, "删除成功");

      //   } else {
      //     util.msg(1, "拒绝失败");
      //   }
      // }, function (res) {
      //   util.msg(1, "拒绝失败");
      // });

    } else {
      this.setData({
        deleteGoodsId: ""          //隐藏
      });
    }
  },

  // 编辑 商品
  editGoods: function (e) {
    let that = this;
    let good = e.target.dataset;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表

    // 将更改前的商品保存到缓存中
    wx.setStorageSync(orderDetail.orderNo + 'edit' + good.goodId, goodsList[good.goodIndex]);

    goodsList[good.goodIndex]["isEdit"] = true
    orderDetail.goodsList = goodsList;
    that.setData({
      orderDetail: orderDetail
    });

  },


  // 取消编辑商品
  cancelGoods: function (e) {
    let that = this;
    let good = e.target.dataset;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表

    //取消时 取出缓存中原先的商品数据还原
    let beforGoods = wx.getStorageSync(orderDetail.orderNo + 'edit' + good.goodId);

    goodsList[good.goodIndex] = beforGoods;
    orderDetail.goodsList = goodsList;
    that.setData({
      orderDetail: orderDetail
    });

    //清除缓存中编辑时保存的对象
    wx.removeStorageSync(orderDetail.orderNo + 'edit' + good.goodId);
  },


  // 监听价格输入
  inputPriceFun: function (e) {
    console.log(e);
    let that = this;
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表

    goodsList[index]["price"] = value;
    orderDetail.goodsList = goodsList;
    that.setData({
      orderDetail: orderDetail
    });

  },

  // 监听count输入
  inputNumFun: function (e) {
    console.log(e);
    let that = this;
    let value = e.detail.value;
    let index = e.target.dataset.goodIndex;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表

    console.log(value);
    if (value) {
      goodsList[index]["count"] = value;
      orderDetail.goodsList = goodsList;
      that.setData({
        orderDetail: orderDetail
      });
    }

  },

  // 计数器增/减
  countNumFun: function (e) {
    let that = this;
    let index = e.target.dataset.goodIndex;
    let step = e.target.dataset.goodStep;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表
    let count = goodsList[index].count;

    if (step == 1) {
      count++;
    } else if (count > 0) {
      count--;
    }

    goodsList[index].count = count;
    orderDetail.goodsList = goodsList;
    that.setData({
      orderDetail: orderDetail
    });

  },

  // 保存更改
  saveGoods: function (e) {
    let that = this;
    let id = e.target.dataset.goodId;
    let index = e.target.dataset.goodIndex;
    let orderDetail = that.data.orderDetail;    //订单详情
    let goodsList = orderDetail.goodsList;      //商品列表
    let afterGoods = goodsList[index];          //更改后的商品。

    //执行更新操作

    // util.request(api.login, userInfo, 'POST').then(function (res) {
    //   if (res.errno === 0) {

    goodsList[index]["isEdit"] = false;
    //清除缓存中编辑时保存的对象
    wx.removeStorageSync(orderDetail.orderNo + 'edit' + id);
    orderDetail.goodsList = goodsList;
    that.setData({
      orderDetail: orderDetail
    });
    console.log(orderDetail);
    util.msg(0, "保存成功");

    //   } else {
    //     util.msg(1, "拒绝失败");
    //   }
    // }, function (res) {
    //   util.msg(1, "拒绝失败");
    // });


  },

  //图片出错
  iconError: function (e) {
    let index = e.target.dataset.index;
    var orderDetail = this.data.orderDetail;

    orderDetail[index].uploaded_img = "/static/images/defaule.png";

    this.setData({
      orderDetail: orderDetail
    });
  },

  // 查看图片预览
  imgDetail: function (e) {
    let url = e.target.dataset.url;
    let imgUrls = this.data.orderDetail.product_list;

    wx.previewImage({
      current: url, // 当前显示图片的http链接
      urls: imgUrls // 需要预览的图片http链接列表
    })
  },

  // 下拉刷新
  onPullDownRefresh: function () {
    this.queryOrderDetail();    //查询订单详情
  }



})
