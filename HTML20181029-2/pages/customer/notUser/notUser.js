
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    userInfo: {},
    roleIndex:0,               //角色选择
    jonSelectIndex: 0,         //职业类型选择index
    busiSelectIndex: 0,        //业务类型选择index
    status: 0,
    shopSwitch: 0,
    titleLable: [
      { shopSwitch: 0, name: "手动输入" },
      { shopSwitch: 1, name: "附近餐厅" },
    ],
    array: [
      { value: '0', name: "A.行政总厨/后厨主管/厨师长" },
      { value: '1', name: "B.普通厨师" },
      { value: '2', name: "C.餐厅老板" },
      { value: '3', name: "D.餐厅采购" },
      { value: '4', name: "E.调味品经销商/批发商" }
    ],
    busiArray: [
      { value: '0', name: "A.中餐厅" },
      { value: '1', name: "B.火锅" },
      { value: '2', name: "C.星级酒店" },
      { value: '3', name: "D.中式快餐（连锁）" },
      { value: '4', name: "E.中式快餐（非连锁）" },
      { value: '4', name: "F.西餐厅" },
      { value: '4', name: "G.其他" }
    ],
    openSelectRegion: false,
    selectRegionList: [
      { id: 0, name: '省份', parent_id: 1, type: 1 },
      { id: 0, name: '城市', parent_id: 1, type: 2 },
      { id: 0, name: '区县', parent_id: 1, type: 3 }
    ],
    regionType: 1,
    regionList: [],
    selectRegionDone: false,
    address: {
      id: 0,
      province_id: 0,
      city_id: 0,
      district_id: 0,
      address: '',
      full_region: '',
      userName: '',
      telNumber: '',
      is_default: 0
    },
    codeText: "获取验证码",    //验证码按钮提示文字
    codeShow: true,           //是否展示事件的按钮

    phone: "",              //参数 电话
    name: "",               //参数 姓名
    addr: "",               //参数 具体地址/详细地址
    shopName: "",           //参数 餐厅名称/门店名称
    avg: "",                //参数 人均消费
    code: "",                //参数 验证码

  },

  // 初始加载
  onLoad: function (option) {
    let that = this;
    console.log(option);


    this.getRegionList(1);          //获取区域列表
  },

  // 查询二维码
  queryQRcode: function () {
    util.request(api.queryOrderList, {
      page: page,
      rows: rows,
      status: status
    }, 'POST').then(function (res) {
      console.log(res);
      if (res.errno === 0) {

      } else {
        //util.msg(1, "查询失败");
      }
    }, function (res) {
      console.log(res)
     // util.msg(1, "查询失败");
    });
  },


  // 返回上一级
  comback: function () {
    wx.switchTab({
      url: "/pages/my/my"
    });
  },

  // 选择批发商/终端用户
  radioChange:function(e){
    let value = e.detail.value;
    console.log(value);
    this.setData({
      roleIndex:value
    });
  },

  // 选择职业
  bindPickerChange: function (e) {
    let value = e.detail.value;
    console.log(value);
    this.setData({
      jonSelectIndex: value
    });
  },

  // 业务选择
  bindBusiChange: function (e) {
    let value = e.detail.value;
    console.log(value);
    this.setData({
      busiSelectIndex: value
    });
  },

  // 选择餐厅切换
  changeStatus: function (e) {
    let that = this;
    let value = e.target.dataset.shopswitch;
    let shopSwitch = that.data.shopwitch;
    if (shopSwitch != value) {
      that.setData({
        shopSwitch: value,
      });
    }
  },

  // 获取验证码
  getCode: function () {
    let that = this;
    let phone = that.data.phone;
    if (!phone) {
      util.msg(1, "请输入手机号码！");
      return false;
    }
    // wx.showLoading({
    //   title: '发送中...',
    // })
    // util.request(api.sendCode, {
    //   phone: that.data.phone
    // }).then(function (res) {
    //   if (res.resultCode == 0) {
    //     util.msg(res.msg);
    //     that.setData({
    //       uuid: res.result
    //     });
    that.loadTimer();     //触发定时器
    //   } else {
    //     util.msg(1, res.msg);
    //   }
    //   wx.hideLoading();
    // });

  },

  // 触发定时器
  loadTimer: function () {
    let that = this;
    let count = 60;
    let t = setInterval(function () {
      if (count <= 0) {
        that.setData({
          codeText: "重新获取",
          codeShow: true
        });
        clearInterval(t);
      } else {
        that.setData({
          codeText: count + "s重新获取",
          codeShow: false
        });
        count--;
      }
    }, 1000);
  },

  // 提交表单
  subForm: function () {
    let that = this;
    let data = that.data;
    if (!data.name) {
      util.msg(1, "请输入姓名");
      return false;
    }
    if (!data.phone) {
      util.msg(1, "请输入手机号");
      return false;
    }
    if (!data.code) {
      util.msg(1, "请输入验证码");
      return false;
    }
    if (!data.address.full_region) {
      util.msg(1, "请选择省市区");
      return false;
    }
    if (!data.addr) {
      util.msg(1, "请输入地址");
      return false;
    }
    if (jonSelectIndex == 4) {
      //选择的 E供货商
      if (!data.shopName) {
        util.msg(1, "请输入门店名称");
        return false;
      }
    } else {
      //选择的 A-D
      if (!data.shopName) {
        util.msg(1, "请输入餐厅名称");
        return false;
      }
      if (!data.avg) {
        util.msg(1, "请输入人均消费");
        return false;
      }
    }

    // util.request(api.queryOrderList, {

    // }, 'POST').then(function (res) {
    //   console.log(res);
    //   if (res.errno === 0) {

    //   } else {
    //     util.msg(1, "查询失败");
    //   }
    // }, function (res) {
    //   util.msg(1, "查询失败");
    // });


  },

  // 监听手机号码输入
  inputPhone: function (e) {
    this.setData({
      phone: e.detail.value,
    });
  },
  // 监听姓名号码输入
  inputName: function (e) {
    this.setData({
      name: e.detail.value,
    });
  },
  // 监听姓名号码输入
  inputCode: function (e) {
    this.setData({
      code: e.detail.value,
    });
  },
  // 监听具体地址输入
  inputAddr: function (e) {
    this.setData({
      addr: e.detail.value,
    });
  },
  // 监听餐厅名称输入
  inputShopName: function (e) {
    this.setData({
      shopName: e.detail.value,
    });
  },
  // 监听人均消费输入
  inputAvg: function (e) {
    this.setData({
      avg: e.detail.value,
    });
  },



  // ******************************************************
  // 选择行政区域
  chooseRegion: function () {
    let that = this;
    this.setData({
      openSelectRegion: !this.data.openSelectRegion
    });

    //设置区域选择数据
    let address = this.data.address;
    if (address.province_id > 0 && address.city_id > 0 && address.district_id > 0) {
      let selectRegionList = this.data.selectRegionList;
      selectRegionList[0].id = address.province_id;
      selectRegionList[0].name = address.province_name;
      selectRegionList[0].parent_id = 1;

      selectRegionList[1].id = address.city_id;
      selectRegionList[1].name = address.city_name;
      selectRegionList[1].parent_id = address.province_id;

      selectRegionList[2].id = address.district_id;
      selectRegionList[2].name = address.district_name;
      selectRegionList[2].parent_id = address.city_id;

      this.setData({
        selectRegionList: selectRegionList,
        regionType: 3
      });

      this.getRegionList(address.city_id);
    } else {
      this.setData({
        selectRegionList: [
          { id: 0, name: '省份', parent_id: 1, type: 1 },
          { id: 0, name: '城市', parent_id: 1, type: 2 },
          { id: 0, name: '区县', parent_id: 1, type: 3 }
        ],
        regionType: 1
      })
      this.getRegionList(1);
    }
    this.setRegionDoneStatus();     //
  },

  // 获取区域地址
  getRegionList: function (regionId) {
    let that = this;
    let regionType = that.data.regionType;
    util.request(api.RegionList, { parentId: regionId }).then(function (res) {
      if (res.errno === 0) {
        that.setData({
          regionList: res.data.map(item => {
            //标记已选择的
            if (regionType == item.type && that.data.selectRegionList[regionType - 1].id == item.id) {
              item.selected = true;
            } else {
              item.selected = false;
            }

            return item;
          })
        });
      }
    });
  },

  setRegionDoneStatus: function () {
    let that = this;
    let doneStatus = that.data.selectRegionList.every(item => {
      return item.id != 0;
    });

    that.setData({
      selectRegionDone: doneStatus
    })

  },

  //选择触发
  selectRegion(event) {
    let that = this;
    let regionIndex = event.target.dataset.regionIndex;
    let regionItem = this.data.regionList[regionIndex];
    let regionType = regionItem.type;
    let selectRegionList = this.data.selectRegionList;
    selectRegionList[regionType - 1] = regionItem;


    if (regionType != 3) {
      this.setData({
        selectRegionList: selectRegionList,
        regionType: regionType + 1
      })
      this.getRegionList(regionItem.id);
    } else {
      this.setData({
        selectRegionList: selectRegionList
      })
    }

    //重置下级区域为空
    selectRegionList.map((item, index) => {
      if (index > regionType - 1) {
        item.id = 0;
        item.name = index == 1 ? '城市' : '区县';
        item.parent_id = 0;
      }
      return item;
    });

    this.setData({
      selectRegionList: selectRegionList
    })


    that.setData({
      regionList: that.data.regionList.map(item => {

        //标记已选择的
        if (that.data.regionType == item.type && that.data.selectRegionList[that.data.regionType - 1].id == item.id) {
          item.selected = true;
        } else {
          item.selected = false;
        }

        return item;
      })
    });

    this.setRegionDoneStatus();

  },

  // 选择完成
  doneSelectRegion() {
    if (this.data.selectRegionDone === false) {
      return false;
    }

    let address = this.data.address;
    let selectRegionList = this.data.selectRegionList;
    address.province_id = selectRegionList[0].id;
    address.city_id = selectRegionList[1].id;
    address.district_id = selectRegionList[2].id;
    address.province_name = selectRegionList[0].name;
    address.city_name = selectRegionList[1].name;
    address.district_name = selectRegionList[2].name;
    address.full_region = selectRegionList.map(item => {
      return item.name;
    }).join(' ');

    this.setData({
      address: address,
      openSelectRegion: false
    });
  },
  //  跳转选择
  selectRegionType(event) {
    let that = this;
    let regionTypeIndex = event.target.dataset.regionTypeIndex;
    let selectRegionList = that.data.selectRegionList;

    //判断是否可点击
    if (regionTypeIndex + 1 == this.data.regionType || (regionTypeIndex - 1 >= 0 && selectRegionList[regionTypeIndex - 1].id <= 0)) {
      return false;
    }

    this.setData({
      regionType: regionTypeIndex + 1
    })

    let selectRegionItem = selectRegionList[regionTypeIndex];

    this.getRegionList(selectRegionItem.parent_id);

    this.setRegionDoneStatus();

  },
  // 取消选择
  cancelSelectRegion() {
    this.setData({
      openSelectRegion: false,
      regionType: this.data.regionDoneStatus ? 3 : 1
    });

  },




})
