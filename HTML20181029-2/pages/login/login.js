
var util = require('../../utils/util.js');
var api = require('../../config/api.js');
var user = require('../../utils/user.js');
const app = getApp()
import md5 from '../../utils/md5.js';

Page({
  data: {
    userInfo: {},
    account: "",
    pwd: "",
    pageShow: false
  },

  // 初始加载
  onLoad: function () {
    let that = this;

    let userInfo = wx.getStorageSync("userInfo");
    console.log(userInfo,"2018-09-11");
    if (userInfo) {
      if (userInfo.user_type == 2) {
        // 2是批发商则直接显示页面
        that.setData({
          pageShow: true
        });
      } else if (userInfo.user_type == 1) {
        //1 是终端用户,有user_type则说明绑定成功过 直接跳全部订单页面
        if (userInfo.is_register == 0) {
          //不需要注册
          wx.redirectTo({
            url: "/pages/customer/allOrder/allOrder"
          })
        } else {
          //跳转邀请页面
          let superInfo = wx.getStorageSync("superInfo");
          setTimeout(function(){
            wx.redirectTo({
              url: "/pages/customer/welcome/welcome?supplier_id=" + superInfo.superInfo + "&supplier_name=" + superInfo.supplier_name
            })
          },2000)
        
        }
      }
    } else {
      that.setData({
        pageShow: true
      });
    }
  },

  // 点击登录
  bindGetUserInfo: function (e) {
    let that = this;
    if (e.detail.userInfo) {
      let userInfo = wx.getStorageSync('userInfo');
      if (userInfo) {
        //如果缓存存在 则直接登录
        that.login();
      } else {
        //缓存不存在 则先请求获取基础数据
        that.wxLogin(e.detail);
      }
    } else {
      console.log("拒绝");
      //用户按了拒绝按钮
      wx.showModal({
        title: '警告通知',
        content: '您点击了拒绝授权,将无法正常显示个人信息,点击确定重新获取授权。',
        success: function (res) {
          if (res.confirm) {
            wx.openSetting({
              success: (res) => {
                if (res.authSetting["scope.userInfo"]) {////如果用户重新同意了授权登录
                  that.wxLogin(e.detail);
                }
              }
            })
          }
        }
      });
    }
  },
  // 登录
  login: function () {
    let that = this;
    let account = that.data.account;
    let pwd = that.data.pwd;

    if (!account) {
      util.msg(1, "请输入用户名");
      return false;
    }
    if (!pwd) {
      util.msg(1, "请输入密码");
      return false;
    }
    // pwd = md5(pwd)

    wx.showLoading({
      title: '正在登录',
    })
    util.request(api.superLogin, {
      mobile: account,
      password: pwd
    }).then(function (res) {
      wx.hideLoading();
      console.log("userInfo:80Line");
      console.log(res);
      if (res.code == 200) {
        wx.setStorageSync("superUser", res.data);        //保存供应商的信息 包含id和name
        let  newuserInfo =  wx.getStorageSync("userInfo");
        // newuserInfo.unionId = res.data.unionId;
        newuserInfo.token = res.data.token;
        console.log("asdfsadfsadfasdf");
        console.log(newuserInfo);
        wx.setStorageSync('userInfo', newuserInfo);
        console.log(res.data)
        that.queryUserInfo(res.data.supplier_id);   
        console.log("二批商是不是第一次登陆"+res.data.is_first)
        if (res.data.is_first > 0) {
          wx.setStorageSync('isFirst', "1");
         //获取供应商的基本信息
          setTimeout(() => {
            wx.switchTab({
              url: '../index/index',
            })
          }, 3000);

        }
        else {
          wx.setStorageSync('isFirst', "0");
          console.log(wx.getStorageSync('isFirst'));
          wx.navigateTo({
            url: '/pages/resetPsw/resetPsw'
          });
        }
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },

  //查询供应商信息
  queryUserInfo: function (supplier_id) {
    let that = this;
    util.request(api.getSuperInfoById, {
      supplier_id: supplier_id
    }).then(function (res) {
      if (res.code == 200) {
        wx.setStorageSync("baseInfo", res.data);
      } else {
        console.log("查询供应商信息失败");
        console.log(res);
      }
    }, function (res) {
      console.log(res);
    });
  },

  // 微信登陆
  wxLogin: function (e) {
    let that = this;
    let app = getApp();

    wx.showLoading({
      title: '登录中...',
    })
    e["user_type"] = 2;
    user.loginByWeixin2(e).then(res => {
      wx.hideLoading();
      if (res.code == 200) {
        //登录成功
        let userInfo = res.data;
        that.setData({
          userInfo: userInfo
        });
        wx.setStorageSync('userInfo', userInfo);
        that.login();
      } else {
        util.msg(1, res.msg);
      }
    }).catch((err) => {
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },

  // 点击登录 忘记密码
  bindGetUserInfo2: function (e) {
    let that = this;
    if (e.detail.userInfo) {
      let userInfo = wx.getStorageSync('userInfo');
      if (userInfo) {
        //如果缓存存在 则跳转忘记密码
        wx.navigateTo({
          url: '../forgetPsw/forgetPsw'
        })
      } else {
        //缓存不存在 则先请求获取基础数据
        that.wxLogin2(e.detail);
      }
    } else {
      console.log("拒绝");
      //用户按了拒绝按钮
      wx.showModal({
        title: '警告通知',
        content: '您点击了拒绝授权,将无法正常显示个人信息,点击确定重新获取授权。',
        success: function (res) {
          if (res.confirm) {
            wx.openSetting({
              success: (res) => {
                if (res.authSetting["scope.userInfo"]) {////如果用户重新同意了授权登录
                  that.wxLogin2(e.detail);
                }
              }
            })
          }
        }
      });
    }
  },

  // 微信登陆  忘记密码
  wxLogin2: function (e) {
    let that = this;
    let app = getApp();


    e["user_type"] = 2;
    user.loginByWeixin1(e).then(res => {

      if (res.code == 200) {
        //登录成功
        let userInfo = res.data;
        that.setData({
          userInfo: userInfo
        });
        wx.setStorageSync('userInfo', userInfo);
        wx.navigateTo({
          url: '../forgetPsw/forgetPsw'
        })
      } else {
        util.msg(1, res.msg);
      }
    }).catch((err) => {
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },


  // 监听账号输入
  accountInput: function (e) {
    this.setData({
      account: e.detail.value,
    });
  },

  // 监听密码输入
  pwdInput: function (e) {
    this.setData({
      pwd: e.detail.value,
    });
  },


  //一键清缓存
  clearStorage: function (e) {
    wx.navigateTo({
      url: '../clearStorage/clearStorage?pageNum=1'
    })
  }


})
