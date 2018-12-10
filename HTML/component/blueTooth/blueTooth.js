// component/reader.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {

  },

  /**
   * 组件的初始数据
   */
  data: {
    modelShow: false,          //弹出框
    list: [],
    services: [],
    serviceId: 0,
    writeCharacter: false,
    readCharacter: false,
    notifyCharacter: false
  },

  /**
   * 组件的方法列表
   */
  methods: {
    search:function(){
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

    //
    CheckPemission: function () {  //android 6.0以上需授权地理位置权限
      var that = this
      var platform = app.BLEInformation.platform
      if (platform == "ios") {
        that.getBluetoothDevices();
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
                var devices = []
                var num = 0
                for (var i = 0; i < res.devices.length; ++i) {
                  if (res.devices[i].name != "未知设备") {
                    devices[num] = res.devices[i]
                    num++
                  }
                }
                that.setData({
                  list: devices,
                })
                wx.hideLoading()
              },
            })
          }, 3000)
        },
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
            that.openControl()
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


  }
})
