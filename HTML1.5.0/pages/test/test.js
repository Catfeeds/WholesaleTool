var util = require('../../utils/util.js');
var api = require('../../config/api.js');
var tsc = require("../../utils/print/tsc.js");
var esc = require("../../utils/print/esc.js");
var encode = require("../../utils/print/encoding.js");

const app = getApp()

Page({

  /**
   * 页面的初始数据
   */
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
    notifyCharacter: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
  },

  submitInfo: function (e) {
    console.log(e.detail.formId);
  },

  testPrint:function(e){
 
    let that = this;
    let printCount = that.data.printCount;    //打印数量
    let order = that.data.orderDetail;    //打印数量
    let userInfo = that.data.userInfo;    //打印数量
    // let str = "测试打印文字123456的地方的苟富贵法国韩国法国和法国韩国"
    let str = "\n------------------------------------------\n";
    str += "                  订  单                  \n";
    str +=   "测试餐厅地址123456789\n";
    str += "收货人：刘德华 "+"             18388888888\n";
    str += "收货地址：南京路142号附201巷\n";
    str += "下单时间：2018-05-06 12:21:45\n";
    str += "订单编号：123456789798\n";
    str += "------------------------------------------\n";
    str += "   南瓜          100/kg          小计：100元\n";
    str += "   南瓜          100/kg          小计：100元\n";
    str += "   南瓜          100/kg          小计：100元\n";
    str += "   南瓜          100/kg          小计：100元\n";
    str += "   南瓜          100/kg          小计：100元\n";
    str += "------------------------------------------\n";
    str += "商品金额：500元\n";
    str += "备    注：备注备注被中国第三得分点23123\n\n";
    str += "批 发 商：测试批发商反对反对12121212\n";
    str += "联系电话：1566666666\n";
    str += "地    址：测试地址反对法12121215151521\n";

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
    let data = wx.getStorageSync("printStr");
    this.setData({
      looptime: 0
    })
    var content = new encode.TextEncoder(
      'gb18030', { NONSTANDARD_allowLegacyEncoding: true }).encode(data);

    console.log('编码GBKstring###:' + content);

    var buff = new ArrayBuffer(content.length);

    var dataView = new DataView(buff);

    for (var i = 0; i < content.length; ++i) {
      dataView.setUint8(i, content[i]);
    }

    this.send(buff)
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

  close() {
    app.globalData.flag = true;
    wx.reLaunch({
      url: '../index/index',
    })
  },

  
})