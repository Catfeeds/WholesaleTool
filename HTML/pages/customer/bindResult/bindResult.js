
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    userInfo: {},
    supplier_name: "",
    supplier_coupon: "",
    supplier_id: ""
  },

  // 初始加载
  onLoad: function (option) {
    console.log("************",option)
    let _this = this
    _this.setData({
      // supplier_name: option.supplier_name,
      supplier_id: option.supplier_id
    })

    util.request(api.getSuperInfoById, {
      supplier_id: option.supplier_id
    }).then(function (result) {
      console.log("&&&&&&&&&&&&&&&&")
      console.log(result)
      if (result.code == 200) {
        _this.setData({
          supplier_coupon: result.data.desc2 ,
        });

      } else {
        util.msg(1, result.msg);
      }
    }, function (result) {
      // util.msg(1, "查询失败,请重新登录");
      console.log(result)
    });
  },
})
