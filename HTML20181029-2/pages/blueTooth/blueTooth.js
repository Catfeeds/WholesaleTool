// pages/blueconn/blueconn.js
var app = getApp()

var tsc = require("../../utils/print/tsc.js");
var esc = require("../../utils/print/esc.js");
var encode = require("../../utils/print/encoding.js");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    services: [],
    serviceId: 0,
    writeCharacter: false,
    readCharacter: false,
    notifyCharacter: false,
    sendContent: "",
    looptime: 0,
    currentTime: 1,
    lastData: 0
    
  },

  startSearch: function () {
    var that = this
    wx.openBluetoothAdapter({
      success: function (res) {
        wx.getBluetoothAdapterState({
          success: function (res) {
            if (res.available) {
              if (res.discovering) {
                wx.stopBluetoothDevicesDiscovery({
                  success: function (res) {
                    console.log(res)
                  }
                })
              }
              that.CheckPemission()
            } else {
              wx.showModal({
                title: '提示',
                content: '本机蓝牙不可用',
              })
            }
          },
        })
      }, fail: function () {
        wx.showModal({
          title: '提示',
          content: '蓝牙初始化失败，请打开蓝牙',
        })
      }
    })
  },
  CheckPemission: function () {  //android 6.0以上需授权地理位置权限
    var that = this
    var platform = app.BLEInformation.platform
    if (platform == "ios") {
      that.getBluetoothDevices()
    } else if (platform == "android") {
      console.log(app.getSystem().substring(app.getSystem().length - (app.getSystem().length - 8), app.getSystem().length - (app.getSystem().length - 8) + 1))
      if (app.getSystem().substring(app.getSystem().length - (app.getSystem().length - 8), app.getSystem().length - (app.getSystem().length - 8) + 1) > 5) {
        wx.getSetting({
          success: function (res) {
            console.log(res)
            if (!res.authSetting['scope.userLocation']) {
              wx.authorize({
                scope: 'scope.userLocation',
                complete: function (res) {
                  that.getBluetoothDevices()
                }
              })
            } else {
              that.getBluetoothDevices()
            }
          }
        })
      }
    }
  },
  getBluetoothDevices: function () {  //获取蓝牙设备信息
    var that = this
    console.log("start search")
    wx.showLoading({
      title: '正在加载',
    })
    wx.startBluetoothDevicesDiscovery({
      success: function (res) {
        console.log(res)
        setTimeout(function () {
          wx.getBluetoothDevices({
            success: function (res) {
              console.log(res);
              var devices = []
              var num = 0
              for (var i = 0; i < res.devices.length; ++i) {
                if (res.devices[i].name != "未知设备") {
                  devices[num] = res.devices[i]
                  num++
                }
              }
              console.log(devices);
              that.setData({
                list: devices,
              })
              wx.hideLoading()
            }, fail:function(res){
                console.log(res);
            }
          })
        }, 3000)
      },
    })
  },
  bindViewTap: function (e) {
    var that = this
    wx.stopBluetoothDevicesDiscovery({
      success: function (res) { console.log(res) },
    })
    that.setData({
      serviceId: 0,
      writeCharacter: false,
      readCharacter: false,
      notifyCharacter: false
    })
    console.log(e.currentTarget.dataset.title)
    wx.showLoading({
      title: '正在连接',
    })
    
    wx.createBLEConnection({
      deviceId: e.currentTarget.dataset.title,
      success: function (res) {
        console.log(res)
        app.BLEInformation.deviceId = e.currentTarget.dataset.title
        that.getSeviceId()
      }, fail: function (e) {
        wx.showModal({
          title: '提示',
          content: '连接失败',
        })
        console.log(e)
        wx.hideLoading()
      }, complete: function (e) {
        console.log(e)
      }
    })
  },
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
                wx.navigateBack({
                  delta: 1
                })
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
  openControl: function () {
    wx.navigateTo({
      url: '../sendCommand/sendCommand',
    })
  },

  sendData: function () {
    let that = this;
    let data = wx.getStorageSync("printStr");
    this.setData({
      looptime: 0
    })
    var content = new encode.TextEncoder(
      'gb18030', { NONSTANDARD_allowLegacyEncoding: true }).encode(data);
    console.log('编码GBKstring###:' + content);
    var buff = new ArrayBuffer(content.length);
    var dataView = new DataView(buff);

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

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.BLEInformation.platform = app.getPlatform()
  },


})