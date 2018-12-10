var util = require('../../utils/util.js');
var api = require('../../config/api.js');
const app = getApp()
Page({
  data: {
    codeShow: true,
    codeTime: 0,
    tokenData: {},
    unionId: "",
    token: "",
    password: "",
    password_confirm: "",
    code: "",
    mobile: ""
  },

  bindPhone:function(e){
    this.setData({
      mobile: e.detail.value
    });
  },
  bindCode:function(e){
    this.setData({
      code: e.detail.value
    });
  },
  bindPassword:function(e){
    this.setData({
      password: e.detail.value
    });
  },
  bindPassword_sure:function(e){
    this.setData({
      password_confirm: e.detail.value
    });
  },
  // 初始加载
  onLoad: function (option) {

  },
  getCode: function () {
    let _this = this
    console.log("用户手机是：" + _this.data.mobile)
    if (_this.data.mobile == "") {
      wx.showToast({
        title: '请先输入手机号',
        icon: 'none',
        duration: 2000
      })
    } else {

      _this.loadTimer();     //触发定时器
      wx.showLoading({
        title: '发送中...',
      })
      let userInfo = wx.getStorageSync('userInfo');
      console.log(userInfo);
      util.request(api.sendCode, {

        // unionId: userInfo.data.unionId,
        // token: userInfo.data.token,
        mobile: _this.data.mobile

      }).then(function (res) {
        wx.hideLoading();
        console.log("短信", res)
        if (res.code == 200) {
          util.msg(1, "发送成功");
        } else {
          util.msg(1, res.msg);
        }
      }).catch((err) => {
        wx.hideLoading();
        util.msg(1, "发送失败");
      });

    }

  },
  loadTimer: function () {
    let _this = this;
    let time = 60;
    let t = setInterval(function () {
      time--
      _this.setData({
        codeShow: false,
        codeTime: time
      });
      if (time <= 0) {
        _this.setData({
          codeShow: true
        });
        clearInterval(t);
      } 

   
      
    }, 1000);
  },

  resetPwd: function () {
    let _this = this
    let userInfo = wx.getStorageSync('userInfo');
    //  if(!_this.data.tokenData.data)
    //  {
    //   util.msg(1, "请先获取验证码");
    //   return
    //  }
    wx.request({
      url: api.changePassword,
      data: {
        unionId: userInfo.unionId,
        //token: userInfo.token,
        password: _this.data.password,
        password_confirm: _this.data.password_confirm,
        code: _this.data.code,
        mobile: _this.data.mobile
      },
      method: 'POST',
      success: function (res) {
        if (res.data.code == 200) {
          wx.setStorageSync("superUser", res.data);        //保存供应商的信息 包含id和name
          _this.queryUserInfo(res.data.data.supplier_id);  
          setTimeout(function () {
            wx.switchTab({
              url: '../login/login',
            })
          }, 3000)      //获取供应商的基本信息        
        } else {
          console.log(res)
          util.msg(1, res.data.msg);
        }
      }
    })
  },

  queryUserInfo: function (supplier_id) {
    let that = this;
    util.request(api.getSuperInfoById, {
      supplier_id: supplier_id
    }).then(function (res) {
      console.log(supplier_id);
      if (res.code == 200) {
        console.log(res.data);
        wx.setStorageSync("baseInfo", res.data);
      } else {
        console.log("查询供应商信息失败");
        console.log(res);
      }
    }, function (res) {
      console.log(res);
    });
  },
  
  call: function () {
    wx.makePhoneCall({
      phoneNumber: '021-34159566' //仅为示例，并非真实的电话号码
    })
  }


})
