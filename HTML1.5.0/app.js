//app.js


var util = require('/utils/util.js');

App({
  getModel: function () { //获取手机型号
    return this.globalData.sysinfo["model"]
  },
  getVersion: function () { //获取微信版本号
    return this.globalData.sysinfo["version"]
  },
  getSystem: function () { //获取操作系统版本
    return this.globalData.sysinfo["system"]
  },
  getPlatform: function () { //获取客户端平台
    return this.globalData.sysinfo["platform"]
  },
  getSDKVersion: function () { //获取客户端基础库版本
    return this.globalData.sysinfo["SDKVersion"]
  },
  onLaunch: function () {
    this.globalData.sysinfo = wx.getSystemInfoSync()
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
     // 获取手机系统信息
     wx.getSystemInfo({
        success: res => {
           //导航高度
           this.globalData.navHeight = res.statusBarHeight + 46;
        }, fail(err) {
           console.log(err);
        }
     })
    // 登录
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
      }
    })
    // 获取用户信息
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
          wx.getUserInfo({
            success: res => {
              // 可以将 res 发送给后台解码出 unionId
              this.globalData.userInfo = res.userInfo

              // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
              // 所以此处加入 callback 以防止这种情况
              if (this.userInfoReadyCallback) {
                this.userInfoReadyCallback(res)
              }
            }
          })
        }
      }
    })
  },
  globalData: {
    userInfo: null
  },

  globalData: {
    flag: false
  },


  BLEInformation: {
    platform: "",
    deviceId: "",
    writeCharaterId: "",
    writeServiceId: "",
    notifyCharaterId: "",
    notifyServiceId: "",
    readCharaterId: "",
    readServiceId: "",
  },
  // 跳转验证码结果
  onShow: function (options) {
    if(!this.captchaTicketExpire) this.captchaTicketExpire = {};
    console.log("appJS:line78:");
    console.log(options);
    if (options.scene == 1038 && (options.referrerInfo.appId === 'wx06584f9a2c9099e3'||options.referrerInfo.appId === 'wx5a3a7366fd07e119')){
      const result = options.referrerInfo.extraData; 
      if (result && result.ret === 0) {
        const ticket = result.ticket; 
        if (!this.captchaTicketExpire[ticket]) { 
            this.captchaResult = result; 
            this.captchaTicketExpire[ticket] = true;
        } 
      }else{
        //用户关闭了校验码
        console.log("用户关闭了校验码");
      }
    }

  }
})