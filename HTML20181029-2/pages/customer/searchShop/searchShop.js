
var util = require('../../../utils/util.js');
var user = require('../../../utils/user.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    name:"",
    cityCode:'',
    areaCode:'',
    scrollHeight:0,
    shopList:[]
  },

  // 初始加载
  onLoad: function (option) {
    let that = this;
    let name = option.name;         //传过来原来的名字
    let cityCode = option.cityCode;         //传过来原来的名字
    let areaCode = option.areaCode;         //传过来原来的名字

    console.log(option);
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          scrollHeight: res.screenHeight*2-55
        });
      },
    })

    this.setData({
      name:name,
      areaCode: areaCode,
      cityCode: cityCode
    });
    if(name){
      this.getShopByName(name);
    }
  },

  // 清除搜索框
  cleanName:function(){
    this.setData({
      name:"",
      shopList:[]
    });
  },

  // 监听餐厅输入
  searchShop:function(e){
    let name = e.detail.value;
    this.setData({
      name:name
    });
    this.getShopByName(name);
  },  

  // 选择餐厅
  choiceShop:function(e){
    let index = e.target.dataset.index;
    let shopList = this.data.shopList;
    let shop = shopList[index];

    var pages = getCurrentPages();
    var prevPage = pages[pages.length - 2]; //上一个页面
    prevPage.setData({
      shopName: shop.shopName,
      addr: shop.address
    })
    wx.navigateBack({
      delta: 1
    })

  },

  // 获取餐厅信息
  getShopByName(name) {
    let that = this;
    if(!name) return false;
    // let areaCode = Math.abs(parseInt(this.data.areaCode));
    // let cityCode = Math.abs(parseInt(this.data.cityCode));
    let areaCode = parseInt(this.data.areaCode);
    let cityCode = parseInt(this.data.cityCode);

    util.request(api.queryShop, {
      keyword:name,
      city_id: cityCode,
      // area_id: '',              //传这个字段大众点评查询不到数据 所以这里设置为空
      area_id: areaCode,           //传这个字段大众点评查询不到数据 所以这里设置为空
    }).then(function (res) {
      console.log(res);
      if (res.code == 200) {
        that.setData({
          shopList: res.data,
        });
      }
    }).catch((err) => {
      console.log(err);
    });
  },

  // 返回
  toback:function(){
    let name = this.data.name;
    var pages = getCurrentPages();
    var prevPage = pages[pages.length - 2]; //上一个页面
    prevPage.setData({
      shopName: name,
    })
    wx.navigateBack({
      delta:1
    })
  },


})
