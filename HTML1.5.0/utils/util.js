var api = require('../config/api.js');

function formatTime(date) {
  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()


  return [year, month, day].map(formatNumber).join('-') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

function formatNumber(n) {
  n = n.toString()
  return n[1] ? n : '0' + n
}

// 时间戳转换为时间字符串 
function timestampFormatter(timestamp, type) {
  let date = new Date(parseInt(timestamp) * 1000);

  var year = date.getFullYear()
  var month = date.getMonth() + 1
  var day = date.getDate()

  var hour = date.getHours()
  var minute = date.getMinutes()
  var second = date.getSeconds()
  if (type) {
    return [hour, minute, second].map(formatNumber).join(':')
  } else {
    return year + "年" + month + "月" + day + "日" + ' ' + [hour, minute, second].map(formatNumber).join(':')
  }
}

/**
 * 封装微信的的request
 */
function request(url, data = {}, method = "POST") {

  let userInfo = wx.getStorageSync('userInfo');
  if (userInfo) {
    data['token'] = userInfo.token;
    data['unionId'] = userInfo.unionId;
  }


  return new Promise(function (resolve, reject) {
    wx.request({
      url: url,
      data: data,
      method: method,
      header: {
        'content-type': 'application/x-www-form-urlencoded;charset=utf-8',
      },
      success: function (res) {
        
        if (res.statusCode == 200) {
          let pdata = res.data;
          if (pdata.code == 401) {
            console.log(pdata);
            //表示token无效 则清除缓存并重新
            wx.removeStorageSync("userInfo");
            if (pdata.user_type == 1) {
              //终端
              let superUser = wx.getStorageSync("superInfo");
              
              setTimeout(() => {
                let id = superUser.supplier_id;
               wx.redirectTo({
                  // url: "/pages/login/login"
                  
                 url: "/pages/customer/welcome/welcome?supplier_id=" + id + "&supplier_name=" + superUser.name + "&supplier_coupon=" + superUser.desc
                })


                return {
                  
                  title: '邀请订单小程序',
                  path: 'pages/customer/welcome/welcome?supplier_id=' + id + "&supplier_name=" + ''
                }
               
              }, 500);

            }
            else {
              //供应商
              if (url != api.getcouponInfo) {
                wx.redirectTo({
                  url: "/pages/login/login"
                })
              }
            }
          }
          resolve(res.data);
        } else {
          reject(res.errMsg);
        }
      },
      fail: function (err) {
        console.log("************");
        reject(err)
      }
    })
  });
}

/**
 * 检查微信会话是否过期
 */
function checkSession() {
  return new Promise(function (resolve, reject) {
    wx.checkSession({
      success: function () {
        resolve(true);
      },
      fail: function () {
        reject(false);
      }
    })
  });
}

/**
 * 调用微信登录
 */
function login() {
  return new Promise(function (resolve, reject) {
    wx.login({
      success: function (res) {
        if (res.code) {
          //登录远程服务器
          resolve(res);
        } else {
          reject(res);
        }
      },
      fail: function (err) {
        reject(err);
      }
    });
  });
}

function redirect(url) {

  //判断页面是否需要登录
  if (false) {
    wx.redirectTo({
      url: '/pages/auth/login/login'
    });
    return false;
  } else {
    wx.redirectTo({
      url: url
    });
  }
}


//  提示 0-成功 1-失败
function msg(type, msg) {
  if (type == 0) {
    wx.showToast({
      title: msg,
    })
  } else if (type == 1) {
    wx.showToast({
      title: msg,
      icon: "none"
    })
  }
}

module.exports = {
  formatTime,
  request,
  redirect,
  msg,
  checkSession,
  login,
  timestampFormatter,
}


