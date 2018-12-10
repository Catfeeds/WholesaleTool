
var util = require('../../../utils/util.js');
var user = require('../../../utils/user.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    userInfo: {},
    supplier_id: "",     //父级邀请人的unionId
    supplier_name: "",   //父级邀请人的姓名
    supplier_coupon: "",   //父级邀请人的姓名
    choiceIndex: 0,      //0直接注册绑定  1不是此供应商
    modelShow: false,    //显示页面 默认false 只有验证了信息后才true
    errorShow: false,      //错误提示 默认false
    cuponInfo: "",
  },

  // 初始加载
  onLoad: function (option) {

   console.log("传人0参数")
   console.log(option)
    let that = this;

    let supplier_id;
    let supplier_name;
    let supplier_coupon;
    if (option.supplier_id) {
      supplier_id = option.supplier_id;         //直接转发过来的信息
      supplier_name = option.supplier_name;
      supplier_coupon=option.supplier_coupon;
    }
   
    if (option.scene) {
      console.log("扫描二维码进来");
      console.log(option.scene);
      supplier_id = decodeURIComponent(option.scene) //扫描邀请二维码获取到的信息 
      console.log(supplier_id)
    }

    if (supplier_id) {
      //如果id存在
      // if(!supplier_name){
      //如果姓名不存在 则接口查询
      that.getSupplierInfo();
      that.setData({
        supplier_id: supplier_id,
        supplier_name: supplier_name,
        supplier_coupon: supplier_coupon,
        modelShow: true
      });
      // }else{
      //   //姓名存在 则直接显示
      //   that.setData({
      //     modelShow:true
      //   });
      // }
    
    } else {
      //如果id不存在,则显示错误
      that.setData({
        errorShow: true
      });
    }

    wx.setStorageSync("superInfo", { supplier_id: supplier_id, supplier_name: supplier_name });    
  
    let userInfo = wx.getStorageSync("userInfo");
    util.request(api.getcouponInfo, {
      unionId: userInfo.unionId,
      token: userInfo.token,
    }).then(function (res) {
      console.log("(*****", res)
      if (res.user_type == 2) {
        // 2是批发商则直接跳终端的登录
        wx.redirectTo({
          url: "/pages/login/login"
        })
      }
      if (res.code == 200) {
        that.setData({
          cuponInfo: res.data.desc
        });
        console.log("38line:");
        console.log(userInfo, supplier_id);
        if (userInfo.user_type == 2) {
          // 2是批发商则直接跳终端的登录
          wx.redirectTo({
            url: "/pages/login/login"
          })
        } else if (userInfo.user_type == 1 && userInfo.is_register == 0) {
          wx.setStorageSync('isFirst', "3");
          console.log("欢迎页是否第一次进入" + wx.getStorageSync('isFirst'))
          //1 是终端用户,有user_type则说明绑定成功过 直接跳全部订单页面
          wx.redirectTo({
            url: "/pages/customer/allOrder/allOrder"
          })
    
        } else {
          //不跳转之后再显示页面
          let modelShow = false;
          if (supplier_id && supplier_name) modelShow = true
          that.setData({
            supplier_id: supplier_id || "",
            supplier_name: supplier_name || "",
            modelShow: modelShow
          });
        }
      } else {
        //util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },

  call: function () {
    wx.makePhoneCall({
      phoneNumber: '021-34159566' //仅为示例，并非真实的电话号码
    })
  },
  // 获取父级邀请者的基本信息
  getSupplierInfo: function () {
    let that = this;
    let supplier_id = that.data.supplier_id;

    wx.showLoading({
      title: '加载中',
    })
    console.log("------------");
    util.request(api.getSuperInfoByIdNew, {
      supplier_id: supplier_id
    }).then(function (res) {
      wx.hideLoading();
      console.log(res);
      if (res.code == 200) {
        // that.setData({
        //   supplier_name: "res.data.name",
        //   supplier_coupon: "res.data.desc",
        //   modelShow: true
        // });
        wx.setStorageSync("superInfo", { supplier_id: res.data.id, supplier_name: res.data.name, supplier_coupon: res.data.desc });
        console.log("------------");
        console.log(res.data.user_type);
       
       if(res.data.supplier_type == -1){
        wx.redirectTo({
          url: '/pages/customer/registResult/registResult?resultStatus=0',
        })
       }
       if(res.data.supplier_type == -2){
        wx.redirectTo({
          url: '/pages/customer/registResult/registResult?resultStatus=1',
        })
       }
      } else {
        util.msg(1, res.msg);
        that.setData({
          errorShow: true
        });
      }
    }, function (res) {
      wx.hideLoading();
      // util.msg(1, "查询失败,请重新登录");
      console.log(res)
    });

  },

  // 获取用户信息
  bindGetUserInfo(e) {
    let that = this;
    let index = e.target.dataset.index;         //0马上加入  1我不是此供应商用户
    that.setData({
      choiceIndex: index
    });
    if (e.detail) {
      that.wxLogin(e.detail);
    } else {
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

  // 微信登陆
  wxLogin: function (e) {
    let that = this;
    //用户按了允许授权按钮
    let app = getApp();

    wx.showLoading({
      title: '跳转中...',
    })
   
    e["user_type"] = 1;       //终端
    console.log("welcome:145");
    console.log(e);
    user.loginByWeixin(e).then(res => {
      console.log("148Line");
      console.log(res);
      wx.hideLoading();
      if (res.code == 200) {
        //登录成功
        let userInfo = res.data;
        console.log(userInfo);
        this.setData({
          userInfo: userInfo
        });
        wx.setStorageSync('userInfo', userInfo);
        let supplier_name = that.data.supplier_name;
        console.log(supplier_name);
        // let url = "/pages/customer/bindResult/bindResult?supplier_name=" + supplier_name;
        setTimeout(() => {
          if (userInfo.is_register && userInfo.is_register == 1) {
            //需要注册
            let supplier_id =that.data.supplier_id;
            let choiceIndex = that.data.choiceIndex;
            console.log("111111111");
            console.log(supplier_id);
            let url = '/pages/customer/regist/regist?supplier_id=' + supplier_id + '&choiceIndex=' + choiceIndex;
            wx.navigateTo({
              url: url
            })
          } else {
            wx.setStorageSync('isFirst', "3");
            console.log("欢迎页不是第一次进入" + wx.getStorageSync('isFirst'))
            wx.redirectTo({
              url: "/pages/customer/allOrder/allOrder"
            })
          }
        }, 2000);
      } else {
        console.log(5555);
        util.msg(1, res.msg);
      }
    }).catch((err) => {
      console.log(6666);
      wx.hideLoading();
      util.msg(1, "登录失败");
    });
  },
  onShow: function () {

  },

  //一键清缓存
  clearStorage: function (e) {
    console.log(e)
    let supplier_id = e.currentTarget.dataset.supplier_id;
    let supplier_name = e.currentTarget.dataset.supplier_name;
    let supplier_coupon = e.currentTarget.dataset.supplier_coupon;
    console.log(supplier_id)
    console.log(supplier_name)
    console.log(supplier_coupon)
    wx.navigateTo({
      url: '/pages/clearStorage/clearStorage?pageNum=' + supplier_id + "&supplier_name=" + supplier_name + "&supplier_coupon=" + supplier_coupon
    })
  }


})
