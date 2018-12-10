
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
var tsc = require("../../utils/print/tsc.js");
var esc = require("../../utils/print/esc.js");
var encode = require("../../utils/print/encoding.js");

const app = getApp()

Page({
  data: {
    userInfo: {},
    account: "",
    scrollHeight: 0,
    orderId: "",
    status: 0,             //0-最近三日 1-全部
    page: 1,
    rows: 3,
    nomore: false,
    modelShow: false,      //模态框展示
    printOrserShow: false, //显示打印弹出框
    NOShow: false,         //拒绝后展示
    deleteGoodsId: null,   //要删除的商品id
    deleteIndex: null,    //要删除的商品index
    orderBackStatus: 1,    //订单 1确认/0拒绝 状态
    reason: '',            //拒绝原因,
    timer: 3,              //拒绝后 倒计时时间
    orderDetail: {},
    printCount: 1,          //打印数量


    list: [],
    services: [],
    serviceId: 0,
    writeCharacter: false,
    readCharacter: false,
    notifyCharacter: false,
    deviceId: null,
    baseInfo: {},
    ShareShow: false,
    ShareShow2: false,   
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
      orderId: orderId
    });

    this.setData({
      baseInfo: wx.getStorageSync("baseInfo")          //获取基本信息
    });

    that.queryUserInfo();      //查询供应商信息
    that.queryOrderDetail();    //查询订单详情

    //获取手机型号，适配iphoneX
    wx.getSystemInfo({
      success: function (res) {
        console.log('手机信息res = ' + res.model)
        let modelmes = res.model;
        if (modelmes.search('iPhone X') != -1) {
          that.setData({
            isIphoneX: true
          })
        } else if (res.pixelRatio == 3) {
          that.setData({
            isIphoneX: 'Nokia'
          })
        } else {
          that.setData({
            isIphoneX: false
          })
        }
      }
    })
    console.log('isIphoneX = ' + that.data.isIphoneX)  

    //根据缓存判断是否第一次进入
    if (wx.getStorageSync('isFirst') === "0") {
      this.setData({
        ShareShow: true
      });
      // wx.redirectTo({
      //   url: '../guide/guide2?orderId=' + orderId,
      // });
      //引导层显示一次就将isFirst值改掉
      wx.setStorageSync('isFirst', "5");
    }
    console.log(wx.getStorageSync('isFirst'))    

  },




  // 查询订单列表
  queryOrderDetail: function () {
    let that = this;
    let orderId = that.data.orderId;

    util.request(api.getOrderDetail, {
      order_id: orderId
    }).then(function (res) {
      wx.stopPullDownRefresh();
      wx.hideNavigationBarLoading();
      if (res.code == 200) {
        let detail = res.data;
        if (detail) {
          detail.created_at = util.timestampFormatter(detail.created_at);
        }
        that.setData({
          orderDetail: res.data
        });
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

  //查询供应商信息
  queryUserInfo: function () {
    let that = this;
    let u = wx.getStorageSync("superUser");
    util.request(api.getSuperInfoById, {
      supplier_id: u.supplier_id
    }).then(function (res) {
      if (res.code == 200) {
        that.setData({
          userInfo: res.data
        });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      //util.msg(1, "查询失败");
      console.log(res)
    });
  },

  // 返回上一级
  comback: function () {
    wx.navigateBack({
      delta: 1
    })
  },
  // 订单 1确认/0拒绝
  orderYes: function (e) {
    let index = e.target.dataset.index;
    this.setData({
      modelShow: true,
      orderBackStatus: index             //保存是点击确认还是拒绝
    });
  },

  // 
  toNo: function (e) {
    console.log("拒绝订单信息"+e)
    this.setData({
      modelShow: false
    });
  },

  // 是/否
  submitInfo: function (e) {
    let that = this;
    let yesno = e.target.dataset.yesno;
    let orderDetail = that.data.orderDetail;
    let orderBackStatus = this.data.orderBackStatus;        //1确认 0拒绝
    let formId = e.detail.formId;         //发送模板消息的formId
    console.log("formId:" + formId);

    if (yesno == 1) {
      if (orderBackStatus == 1) {
        that.setData({
          modelShow: false
        });
        //执行确认操作
        console.log("确认操作。。。");
        util.request(api.yesOrder, {
          order_id: orderDetail.id,
          form_id: formId,
          remark: ""
        }).then(function (res) {
          if (res.code === 200) {
            util.msg(1, "确认成功");
            console.log(res);
            that.queryOrderDetail();        //重新刷新页面
          } else {
            util.msg(1, res.msg);
          }
        }, function (res) {
          util.msg(1, "确认失败");
        });

      } else {
        //执行拒绝操作
        let reason = that.data.reason;
        if (reason == "") {
          util.msg(1, "请输入理由");
          return false;
        }
        console.log("拒绝操作。。。");

        util.request(api.noOrder, {
          order_id: orderDetail.id,
          form_id: formId,
          remark: reason
        }).then(function (res) {
          if (res.code == 200) {
            console.log("拒绝成功");
            console.log(res);
            that.setData({
              reason: "  ",
              modelShow: false
            });
            that.refusedOrderAfter();  //接口成功后 执行拒绝后下一步
          } else {
            util.msg(1, res.msg);
          }
        }, function (res) {
          util.msg(1, "拒绝失败");
        });
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
    let that = this;
    if (that.data.orderDetail.product_list.length < 2) {
      that.setData({
        modelShow: true,
        orderBackStatus: 0             //保存是点击确认还是拒绝
      });
    } else {
      let goodId = e.target.dataset.id;     //要删除的商品id
      let index = e.target.dataset.index;     //要删除的商品id
      this.setData({
        deleteGoodsId: goodId,
        deleteIndex: index
      });
    }
  },

  // 是否删除商品
  toDeleteGoods: function (e) {
    let that = this;
    let yesno = e.target.dataset.yesno;
    let deleteGoodsId = this.data.deleteGoodsId;
    let deleteIndex = this.data.deleteIndex;
    let orderDetail = this.data.orderDetail;
    let pro = orderDetail.product_list[deleteIndex];
    console.log(deleteGoodsId);
    console.log(orderDetail);
    console.log(pro);

    this.setData({
      deleteGoodsId: "",          //隐藏
      deleteIndex: ""
    });
    if (yesno == "1") {
      //执行删除商品
      wx.showLoading({
        title: '删除中...',
      })
      util.request(api.deleteProduct, {
        order_id: orderDetail.id,
        product_id: deleteGoodsId,
        is_unlieve: pro.is_unlieve
      }).then(function (res) {
        wx.hideLoading();
        console.log(res);
        if (res.code == 200) {
          //util.msg(0,"删除成功");   
          that.queryOrderDetail();
          //重新刷新页面
        } else {
          util.msg(1, res.msg);
        }
      }, function (res) {
        util.msg(1, "删除失败");
      });
    }
  },

  // 编辑 商品
  editGoods: function (e) {
    let that = this;
    let good = e.target.dataset;
    console.log(good);
    let orderDetail = that.data.orderDetail;    //订单详情
    let product_list = orderDetail.product_list;      //商品列表

    // 将更改前的商品保存到缓存中
    wx.setStorageSync(orderDetail.orderNo + 'edit' + good.goodId, product_list[good.goodIndex]);

    product_list[good.goodIndex]["isEdit"] = true
    orderDetail.product_list = product_list;
    that.setData({
      orderDetail: orderDetail
    });

  },


  // 取消编辑商品
  cancelGoods: function (e) {
    let that = this;
    let good = e.target.dataset;
    let orderDetail = that.data.orderDetail;    //订单详情
    let product_list = orderDetail.product_list;      //商品列表

    //取消时 取出缓存中原先的商品数据还原
    let beforGoods = wx.getStorageSync(orderDetail.orderNo + 'edit' + good.goodId);

    product_list[good.goodIndex] = beforGoods;
    orderDetail.product_list = product_list;
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
    let product_list = orderDetail.product_list;      //商品列表
    console.log(index);
    product_list[index]["product_price"] = value;
    orderDetail.product_list = product_list;
    that.setData({
      orderDetail: orderDetail
    });
  },

  // 监听价格获取焦点
  inputPriceFocus: function (e) {
    console.log(e);
    let that = this;
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let orderDetail = that.data.orderDetail;    //订单详情
    let product_list = orderDetail.product_list;      //商品列表
    console.log(index);
    if (parseInt(value) == 0) {
      product_list[index]["product_price"] = "";
    } else {
      product_list[index]["product_price"] = value;
    }
    orderDetail.product_list = product_list;
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
    let product_list = orderDetail.product_list;      //商品列表

    console.log(value);
    if (value) {
      product_list[index]["product_count"] = value;
      orderDetail.product_list = product_list;
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
    let product_list = orderDetail.product_list;      //商品列表
    let count = product_list[index].product_count;

    if (step == 1) {
      count++;
    } else if (count > 0) {
      count--;
    }

    product_list[index].product_count = count;
    orderDetail.product_list = product_list;
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
    let product_list = orderDetail.product_list;      //商品列表
    let afterGoods = product_list[index];          //更改后的商品。

    console.log(afterGoods);
    afterGoods.product_price = !afterGoods.product_price ? 0 : afterGoods.product_price;
    if (!afterGoods.product_price) {
      util.msg(1, "价格不能为空！");
      return false;
    }
    var reg = /^(([0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
    if (!reg.test(afterGoods.product_price)) {
      util.msg(1, "只能保留两位小数价格");
      return false;
    }
    //执行更新操作
    wx.showLoading({
      title: '保存中...',
    })
    util.request(api.updateProduct, {
      order_id: orderDetail.id,
      product_list: JSON.stringify(afterGoods)
    }).then(function (res) {
      wx.hideLoading();
      if (res.code === 200) {
        product_list[index]["isEdit"] = false;
        //清除缓存中编辑时保存的对象
        wx.removeStorageSync(orderDetail.orderNo + 'edit' + id);
        util.msg(0, "修改成功");
        that.queryOrderDetail();        //重新刷新页面

      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading()
      util.msg(1, "保存失败");
    });


  },

  // 查看图片预览
  imgDetail: function (e) {
    console.log(e);
    let url = e.target.dataset.url;
    let imgUrls = this.data.orderDetail.product_list;

    console.log(imgUrls);
    wx.previewImage({
      current: url, // 当前显示图片的http链接
      urls: imgUrls // 需要预览的图片http链接列表
    })
  },


  // 打印计数器增/减
  countPrintNum: function (e) {
    let that = this;
    let step = e.target.dataset.step;
    let printCount = that.data.printCount;    //打印数量

    if (step == 1) {
      printCount++;
    } else if (printCount > 1) {
      printCount--;
    }
    that.setData({
      printCount: printCount
    });
  },

  // 下拉刷新
  onPullDownRefresh: function () {
    wx.showNavigationBarLoading() //在标题栏中显示加载
    this.queryOrderDetail();    //查询订单详情
  },

  //取消
  printCancel: function () {
    this.setData({
      printOrserShow: false
    });
  },

  // 打印
  searchBlue: function () {
    let deviceId = app.BLEInformation.deviceId;
    this.setData({
      printOrserShow: true,
      deviceId: deviceId
    });
  },

  //重置deviceId
  printReset: function () {
    this.setData({
      deviceId: null
    });
    wx.navigateTo({
      url: '/pages/blueTooth/blueTooth',
    })
  },

  // 开始打印
  printOrder: function () {
    let that = this;
    let printCount = that.data.printCount;    //打印数量
    let order = that.data.orderDetail;    //打印数量
    let userInfo = that.data.userInfo;    //打印数量
    let str = "\n------------------------------------------\n";
    str += "                  订  单                  \n";
    str += order.restaurant_name + "\n";
    str += "收货人：" + order.nickname + "             " + order.mobile + "\n";
    str += "收货地址：" + order.address + "\n";
    str += "下单时间：" + order.created_at + "\n";
    str += "订单编号：" + order.order_number + "\n";
    str += "------------------------------------------\n";
    order.product_list.forEach((item, index) => {
      let ind = (index + 1) < 10 ? '0' + (index + 1) : (index + 1);
      let price = item.product_price;
      let length = 6 - price.length;
      for (let i = 1; i <= length; i++) {
        price += " ";
      }

      str += ind + '.' + item.product_name + "\n   " + price + "元/" + item.sku_unit + "        " + item.product_count + item.sku_unit + '\n';
    });
    str += "------------------------------------------\n";
    str += "总 金 额：" + order.order_amount + "元\n";
    str += "备    注：" + order.comments + "\n\n";
    str += "批 发 商：" + userInfo.name + "\n";
    str += "联系电话：" + userInfo.mobile + "\n";
    str += "地    址：" + userInfo.address + "\n\n\n";

    for (let i = 1; i < printCount; i++) {
      str += str;
    }

    wx.setStorageSync("printStr", str);

    if (app.BLEInformation.deviceId) {
      that.getSeviceId();
    } else {
      wx.navigateTo({
        url: '/pages/blueTooth/blueTooth',
      })
    }

  },

  onShow: function () {
    let deviceId = app.BLEInformation.deviceId;
    this.setData({
      deviceId: deviceId
    });
  },

  // ***************************************
  getSeviceId: function () {
    var that = this
    var platform = app.BLEInformation.platform
    console.log(app.BLEInformation.deviceId)
    wx.getBLEDeviceServices({
      deviceId: app.BLEInformation.deviceId,
      success: function (res) {
        console.log(res)
        that.setData({
          services: res.services
        })
        that.getCharacteristics()
      }, fail: function (e) {
        console.log(e)
      }, complete: function (e) {
        console.log(e)
      }
    })
  },
  getCharacteristics: function () {
    var that = this
    var list = that.data.services
    var num = that.data.serviceId
    var write = that.data.writeCharacter
    var read = that.data.readCharacter
    var notify = that.data.notifyCharacter
    wx.getBLEDeviceCharacteristics({
      deviceId: app.BLEInformation.deviceId,
      serviceId: list[num].uuid,
      success: function (res) {
        console.log(res)
        for (var i = 0; i < res.characteristics.length; ++i) {
          var properties = res.characteristics[i].properties
          var item = res.characteristics[i].uuid
          if (!notify) {
            if (properties.notify) {
              app.BLEInformation.notifyCharaterId = item
              app.BLEInformation.notifyServiceId = list[num].uuid
              notify = true
            }
          }
          if (!write) {
            if (properties.write) {
              app.BLEInformation.writeCharaterId = item
              app.BLEInformation.writeServiceId = list[num].uuid
              write = true
            }
          }
          if (!read) {
            if (properties.read) {
              app.BLEInformation.readCharaterId = item
              app.BLEInformation.readServiceId = list[num].uuid
              read = true
            }
          }
        }
        if (!write || !notify || !read) {
          num++
          that.setData({
            writeCharacter: write,
            readCharacter: read,
            notifyCharacter: notify,
            serviceId: num
          })
          that.getCharacteristics()
        } else {
          wx.hideLoading();
          wx.showModal({
            title: '提示',
            content: '连接成功，是否开始打印？',
            success: function (res) {
              if (res.confirm) {
                that.sendData();
              } else if (res.cancel) {
                wx.navigateBack({
                  delta: 1
                })
              }
            }
          })
        }
      }, fail: function (e) {
        console.log(e)
      }, complete: function (e) {
        console.log("write:" + app.BLEInformation.writeCharaterId)
        console.log("read:" + app.BLEInformation.readCharaterId)
        console.log("notify:" + app.BLEInformation.notifyCharaterId)
      }
    })
  },

  sendData: function () {
    let that = this;
    let data = wx.getStorageSync("printStr");
    this.setData({
      looptime: 0,
      lastData: 0,
      currentTime: 0
    })
    var content = new encode.TextEncoder(
      'gb18030', { NONSTANDARD_allowLegacyEncoding: true }).encode(data);
    console.log('编码GBKstring###:' + content);
    // var buff = new ArrayBuffer(content.length);
    // var dataView = new DataView(buff);

    //1. 一次打印
    // for (var i = 0; i < content.length; ++i) {
    //   dataView.setUint8(i, content[i]);
    // }

    // this.send(buff)

    //2. 分包打印

    var looptime = parseInt(content.length / 20);
    var lastData = parseInt(content.length % 20);
    console.log(looptime + "---" + lastData)
    that.setData({
      looptime: looptime + 1,
      lastData: lastData,
    })
    that.EscSend(content)
  },

  send: function (buff) {
    wx.writeBLECharacteristicValue({
      deviceId: app.BLEInformation.deviceId,
      serviceId: app.BLEInformation.writeServiceId,
      characteristicId: app.BLEInformation.writeCharaterId,
      value: buff,
      success: function (res) {
        console.log(res)

      }, fail: function (e) {
        console.log(e)
      }
    })
  },

  EscSend: function (buff) {
    var that = this
    var currentTime = that.data.currentTime
    var loopTime = that.data.looptime
    var lastData = that.data.lastData
    var buf
    var dataView
    if (currentTime < loopTime) {
      buf = new ArrayBuffer(20)
      dataView = new DataView(buf)
      for (var i = 0; i < 20; ++i) {
        // dataView.setUint8(i,buff[i])
        dataView.setUint8(i, buff[(currentTime - 1) * 20 + i])
      }
    } else {
      buf = new ArrayBuffer(lastData)
      dataView = new DataView(buf)
      for (var i = 0; i < lastData; ++i) {
        dataView.setUint8(i, buff[(currentTime - 1) * 20 + i])
      }
    }
    console.log("第" + currentTime + "次发送数据大小为：" + buf.byteLength)
    console.log(buf);
    wx.writeBLECharacteristicValue({
      deviceId: app.BLEInformation.deviceId,
      serviceId: app.BLEInformation.writeServiceId,
      characteristicId: app.BLEInformation.writeCharaterId,
      value: buf,
      success: function (res) {
        currentTime++
        if (currentTime <= loopTime) {
          that.setData({
            currentTime: currentTime
          })
          that.EscSend(buff)
        } else {
          that.setData({
            looptime: 0,
            lastData: 0,
            currentTime: 0
          })
        }
      }, fail: function (e) {
        console.log(e)
      }
    })

  },

  // 控制导航层显示
  togglePopup() {
    this.setData({
      ShareShow: !this.data.ShareShow,
      ShareShow2: true
    });
  },
  togglePopup2() {
    this.setData({
      ShareShow2: false
    });
  }

})
