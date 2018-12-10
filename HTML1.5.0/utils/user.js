/**
 * 用户相关服务
 */

const util = require('util.js');
const api = require('../config/api.js');


/**
 * 调用微信登录
 */
function loginByWeixin(userInfo) {
  let code = null;
  delete userInfo.userInfo;  
  return new Promise(function (resolve, reject) {
    return util.login().then((res) => {
      code = res.code;
      return userInfo;
    }).then((userInfo) => {
      //登录远程服务器
      let param = userInfo;
      param["code"] = code;
      util.request(api.login, param , 'POST').then(res => {
          resolve(res);
      }).catch((err) => {
        reject(err);
      });
    }).catch((err) => {
      reject(err);
    })
  });
}
function loginByWeixin1(userInfo) {
  let code = null;
  delete userInfo.userInfo;  
  return new Promise(function (resolve, reject) {
    return util.login().then((res) => {
      code = res.code;
      return userInfo;
    }).then((userInfo) => {
      //登录远程服务器
      let param = userInfo;
      param["code"] = code;
      util.request(api.login1, param , 'POST').then(res => {
          resolve(res);
      }).catch((err) => {
        reject(err);
      });
    }).catch((err) => {
      reject(err);
    })
  });
}

/**
 * 调用微信登录
 */
function loginByWeixin2(userInfo) {
  let code = null;
  delete userInfo.userInfo;
  return new Promise(function (resolve, reject) {
    return util.login().then((res) => {
      code = res.code;
      return userInfo;
    }).then((userInfo) => {
      //登录远程服务器
      let param = userInfo;
      param["code"] = code;
      util.request(api.login1, param, 'POST').then(res => {
        resolve(res);
      }).catch((err) => {
        reject(err);
      });
    }).catch((err) => {
      reject(err);
    })
  });
}
/**
 * 判断用户是否登录
 */
function checkLogin() {
  return new Promise(function (resolve, reject) {
    if (wx.getStorageSync('userInfo')) {
      util.checkSession().then(() => {
        resolve(true);
      }).catch(() => {
        reject(false);
      });
    } else {
      reject(false);
    }
  });
}


module.exports = {
  loginByWeixin,
  loginByWeixin1,
  loginByWeixin2,
  checkLogin,
};











