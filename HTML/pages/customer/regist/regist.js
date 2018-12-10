
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    regionModel:false,         //区域弹框
    regionList3:[],           //区列表
    regionList2:[],           //市列表
    regionList1:[],           //省列表
    regionSelect:[],          //已选择列表
    regionRoutStr:'',         //已选择区域字符串
    regionRoutArr:[0,0,0],         //已选择区域code数组
    regionIndex1:0,           //省index
    regionIndex2:0,           //市inedx
    regionIndex3:0,           //区index


    checkCodeModel:false,     //验证码弹出框
    dzdpAroundModel:false,    //大众点评附近餐厅弹出框
    dzdpAroundList:[],        //大众点评附近餐厅数据
    userLocationAccredit:false,  //用户位置授权
    codeUrl:"",               //验证码url
    ticket:"",                //图形验证码返回的票据
    randstr:"",               //图形验证码返回的字串
    shopswitch:0,             //0手动输入 1附件餐厅
    titleLable: [
      { shopswitch: 0, name: "手动输入" },
      { shopswitch: 1, name: "附近餐厅" },
    ],
    array:[
      {value:'0',name:"A.行政总厨/后厨主管/厨师长"},
      {value:'1',name:"B.普通厨师"},
      {value:'2',name:"C.餐厅老板"},
      {value:'3',name:"D.餐厅采购"},
      {value:'4',name:"E.调味品经销商/批发商"}
    ],
    busiArray:[
      {value:'0',name:"A.中餐厅"},
      {value:'1',name:"B.火锅"},
      {value:'2',name:"C.星级酒店"},
      {value:'3',name:"D.中式快餐（连锁）"},
      {value:'4',name:"E.中式快餐（非连锁）"},
      {value:'5',name:"F.西餐厅"},
      {value:'6',name:"G.其他"}
    ],
    openSelectRegion: false,                  //弹出行政区域选择
    selectRegionList: [
      { id: 0, name: '省份', pcode: 1, level: 1 },
      { id: 0, name: '城市', pcode: 1, level: 2 },
      { id: 0, name: '区县', pcode: 1, level: 3 }
    ],
    regionLevel: 1,
    regionList: [],
    selectRegionDone: false,
    address: {
      id: 0,
      province_id: 0,
      city_id: 0,
      district_id: 0,
      address: '',
      full_region: '',
    },
    codeText:"获取验证码",    //验证码按钮提示文字
    codeShow:true,           //是否展示事件的按钮
    codetime:60,

    phone:"",              //参数 电话
    name:"",               //参数 姓名
    addr:"",               //参数 具体地址/详细地址
    shopName:"",           //参数 餐厅名称/门店名称
    avg:"",                //参数 人均消费
    code:"",               //参数 验证码
    jonSelectIndex: 0,     //参数 职业类型选择index
    busiSelectIndex: 0,    //参数 业务类型选择index
    supplier_id:"",       //参数 父级邀请人unionId
    choiceIndex:0,         //0选择绑定 1选择重新注册
    isIphone6:false
  },

  // 初始加载
  onLoad: function (option) {
    console.log("21321312312312312312")
    console.log(option);
    let that = this;
    let supplier_id = option.supplier_id||'';
    let choiceIndex = option.choiceIndex;       //0 注册成功后绑定供应商 1 不是此供应商单独注册
    that.setData({
      supplier_id: supplier_id,
      choiceIndex: choiceIndex
    });
    util.request(api.getCode).then(function (res) {
      wx.hideLoading();
      if (res.code == 200) {
        console.log(that.codeUrl)
        that.setData({
          codeUrl: res.data.url,
          checkCodeModel:true
        });
        // that.loadTimer();     //触发定时器
      } else {
        util.msg(1, res.msg);
      }
    }).catch((err) => {
      util.msg(1, "获取失败");
    });



    //获取手机型号，适配iphone6
    wx.getSystemInfo({
      success: function (res) {
        console.log('手机信息res = ' + res.model)
        let modelmes = res.model;
        if (modelmes == "iPhone 6<iPhone7,2>") {
          that.setData({
            isIphone6: true
          })
        } 
      }
    })
    console.log('isIphone6 = ' + that.data.isIphone6)
  },

  // 返回上一级
  comback: function () {
    wx.switchTab({
      url: "/pages/my/my"
    });
  },

  // 选择职业
  bindPickerChange:function(e){
    let value = e.detail.value;
    this.setData({
      jonSelectIndex:value
    });
  },

  // 业务选择
  bindBusiChange:function(e){
    let value = e.detail.value;
    this.setData({
      busiSelectIndex:value
    });
  },

  // 选择餐厅切换
  changeStatus:function(e){
    let that = this;
    let value = e.target.dataset.shopswitch;
    let shopswitch = that.data.shopswitch;
    let dzdpAroundList = that.data.dzdpAroundList;
    console.log(value);
    console.log(shopswitch);
      if (value == 1) {
        // 点击附近餐厅
        that.setData({
          shopswitch: value,
          dzdpAroundModel: true
        });

        if (dzdpAroundList.length <= 0) {
          //当本地没有缓存数据时，重新获取
          that.getDzdpAroundList();
      
        }
      }else{
        //点击手动输入
        that.setData({
          shopswitch: value,
          dzdpAroundModel: false,
        });
      }

    
  },

  // 打开搜索餐厅页面
  openSearchShop:function(){
    let regionRoutStr = this.data.regionRoutStr;
    let regionSelect = this.data.regionSelect;    //已选择的区域数组
    if (!regionRoutStr){
      util.msg(1,"请先选择区域");
      return false;
    }
    let cityCode = regionSelect[1].code;
    let areaCode = regionSelect[2].code;
    let name = this.data.shopName;
      wx.navigateTo({
        url: '/pages/customer/searchShop/searchShop?name=' + name + '&cityCode=' + cityCode + '&areaCode=' + areaCode,
      })

  },

  // 获取大众点评周边店铺数据
  getDzdpAroundList:function(){
    let that = this;
    wx.getLocation({
      success: function (res) {
        var latitude = res.latitude
        var longitude = res.longitude
        wx.showLoading({
          title: '加载中...',
        })
        util.request(api.dzdpAround, {
            longitude: longitude,
            latitude: latitude,
          }).then(function (res) {
            console.log(res);
            wx.hideLoading();
            if (res.code == 200) {
              that.setData({
                dzdpAroundList: res.data,
              });
            } else {
              util.msg(1, res.msg);
            }
          }).catch((err) => {
            util.msg(1, "获取失败");
          });

      },fail:function(res){
        //用户按了拒绝按钮
        that.setData({
          userLocationAccredit:true          //显示重新授权提示
        });
      }
    })
    

  },

  //重新授权后回调
  handler:function(e){
    var that = this;
    if (e.detail.authSetting['scope.userLocation']) {
        //授权成功
        that.setData({
          userLocationAccredit: false          //隐藏提示
        });
        that.getDzdpAroundList();             //获取数据
    } else {
        that.setData({
          userLocationAccredit: true          //显示提示
        });
    }
  },

  // 隐藏附近餐厅模态框
  dzdpAroundModelFalse:function(){
      this.setData({
        dzdpAroundModel:false,
        shopswitch:0
      });
  },

  // 获取图形验证码
  // getCode:function(){
  //   let that = this;
  //   let phone = that.data.phone;
  //   let ticket = that.data.ticket;
  //   if (!phone) {
  //     util.msg(1,"请输入11位手机号码！");
  //     return false;
  //   }
  //   var myreg = /^[1][3,4,5,7,8][0-9]{9}$/;
  //   if (!myreg.test(phone)) {
  //     util.msg(1, "请输入11位手机号码！");
  //     return false;
  //   }
  //   wx.showLoading({
  //     title: '获取中...',
  //   })
  //   if (ticket){
  //     //票据存在 则直接发送短信
  //     that.sendPhoneCode();
  //   }else{
  //     //票据不存在  则重新获取

  //   }
  // },

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
          codeText: count + "重新获取",
          codeShow: false,
          codetime:count
        });
        count--;
      }
    }, 1000);
  },

  // 提交表单
  subForm:function(){
    let that = this;
    let data = that.data;
  
    let arr = ["A","B","C","D","E","F","G"];
    
    if(!data.name){
        util.msg(1,"请输入姓名");
        return false;
    }
    if(!data.phone){
        util.msg(1,"请输入手机号");
        return false;
    }
    if(!data.code){
        util.msg(1,"请输入验证码");
        return false;
    }
    if (!data.regionRoutStr){
        util.msg(1,"请选择省市区");
        return false;
    }
    if (!data.addr){
        util.msg(1,"请输入地址");
        return false;
    }
    if (data.jonSelectIndex==4){
        //选择的 E供货商
      if (!data.shopName){
          util.msg(1,"请输入门店名称");
          return false;
      }
    }else{
      //选择的 A-D
      if (!data.shopName){
          util.msg(1,"请输入餐厅名称");
          return false;
      }
      if (!data.avg){
          util.msg(1,"请输入人均消费");
          return false;
      }
    }
    

    console.log(data.address);
    let param = {};
    param["nickname"] = data.name;
    param["mobile"] = data.phone;
    param["code"] = data.code;
    if (data.supplier_id) param["supplier_id"] = data.supplier_id;
    param["user_type"] = 1;
    param["type"] = parseInt(data.jonSelectIndex)+1;
    param["province"] = data.regionSelect[0].name;
    param["province_code"] = data.regionSelect[0].code;
    param["city"] = data.regionSelect[1].name;
    param["city_code"] = data.regionSelect[1].code;
    param["district"] = data.regionSelect[2].name;
    param["district_code"] = data.regionSelect[2].code;
    param["address"] = data.addr;
    param["restaurant_name"] = data.shopName;
    param["job_code"] = arr[data.busiSelectIndex];
    param["avg_amount"] = data.avg;
    if(that.data.choiceIndex >0){
      param["supplier_type"] =-2
    }

    console.log(param);

    wx.showLoading({
      title: '正在提交...',
    })

    console.log("Line333");
    console.log(param);
    util.request(api.regist, param).then(function (res) {
      wx.hideLoading();
      console.log("Line337");
      console.log(res);
      if (res.code === 200) {
        let choiceIndex = that.data.choiceIndex;
        console.log("choiceIndex:", choiceIndex);
        if (choiceIndex==0){
            wx.setStorageSync('isFirst', "2");
            //绑定注册 跳转绑定成功页面
            wx.redirectTo({
              url: '/pages/customer/bindResult/bindResult?supplier_id='+data.supplier_id
            })
        }else{
            //单独注册 跳转到通知页面
            wx.redirectTo({
              url: '/pages/customer/registResult/registResult?resultStatus=1',
            })
        }
      } else if (res.code === 5001){
          //跳转本地区暂未开通
          wx.navigateTo({
            url: '/pages/customer/registResult/registResult?resultStatus=0',
          })
      } else if (res.code === 5002){
          //验证码错误
          util.msg(1, res.msg);
          that.setData({
            randstr: "",
            ticket: ""
          });
      } else if (res.code === 5003){
          //验证码过期
        util.msg(1, res.msg);
        that.setData({
          randstr: "",
          ticket: ""
        });
      } else {
        util.msg(1, res.msg);
      }
    }).catch(function(){  
        wx.hideLoading();
        util.msg(1, "注册失败");
    });


  },

  // 监听手机号码输入
  inputPhone:function(e){
    this.setData({
      phone: e.detail.value,
    });
  },
  // 监听姓名号码输入
  inputName:function(e){
    this.setData({
      name: e.detail.value,
    });
  },
  // 监听姓名号码输入
  inputCode:function(e){
    this.setData({
      code: e.detail.value,
    });
  },
  // 监听具体地址输入
  inputAddr:function(e){
    this.setData({
      addr: e.detail.value,
    });
  },
  // 监听餐厅名称输入
  inputShopName:function(e){
    this.setData({
      shopName: e.detail.value,
    });
  },
  // 监听人均消费输入
  inputAvg:function(e){
    this.setData({
      avg: e.detail.value,
    });
  },



  // ******************************************************
  // 选择行政区域
  chooseRegion:function() {
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
      selectRegionList[0].pcode = 1;

      selectRegionList[1].id = address.city_id;
      selectRegionList[1].name = address.city_name;
      selectRegionList[1].pcode = address.province_id;

      selectRegionList[2].id = address.district_id;
      selectRegionList[2].name = address.district_name;
      selectRegionList[2].pcode = address.city_id;

      this.setData({
        selectRegionList: selectRegionList,
        regionLevel: 3
      });

      this.getRegionList(address.city_id, 3);
    } else {
      this.setData({
        selectRegionList: [
          { id: 0, name: '省份', pcode: 1, level: 1 },
          { id: 0, name: '城市', pcode: 1, level: 2 },
          { id: 0, name: '区县', pcode: 1, level: 3 }
        ],
        regionLevel: 1
      })
      this.getRegionList(0,1);
    }
    this.setRegionDoneStatus();     //
  },

  // 获取区域地址
  getRegionList: function (regionId, level) {
      let that = this;
      let regionLevel = that.data.regionLevel;
      util.request(api.regionList, {
          code: regionId,
          level: level
      }).then(function (res) {
        if (res.code === 200) {
          that.setData({
            regionList: res.data.map(item => {
              //标记已选择的
              if (regionLevel == item.level && that.data.selectRegionList[regionLevel - 1].id == item.id) {
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

    setRegionDoneStatus:function() {
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
      let regionLevel = regionItem.level;
      let selectRegionList = this.data.selectRegionList;
      selectRegionList[regionLevel - 1] = regionItem;


      if (regionLevel != 3) {
        this.setData({
          selectRegionList: selectRegionList,
          regionLevel: regionLevel + 1
        })
        this.getRegionList(regionItem.code, parseInt(regionItem.level)+1);
      } else {
        this.setData({
          selectRegionList: selectRegionList
        })
      }

      //重置下级区域为空
      selectRegionList.map((item, index) => {
        if (index > regionLevel - 1) {
          item.id = 0;
          item.name = index == 1 ? '城市' : '区县';
          item.pcode = 0;
        }
        return item;
      });

      this.setData({
        selectRegionList: selectRegionList
      })


      that.setData({
        regionList: that.data.regionList.map(item => {

          //标记已选择的
          if (that.data.regionLevel == item.level && that.data.selectRegionList[that.data.regionLevel - 1].id == item.id) {
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
    address.province_id = selectRegionList[0].code;
    address.city_id = selectRegionList[1].code;
    address.district_id = selectRegionList[2].code;
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
  selectRegionLevel(event) {
    let that = this;
    let regionLevelIndex = event.target.dataset.regionLevelIndex;
    let selectRegionList = that.data.selectRegionList;

    //判断是否可点击
    if (regionLevelIndex + 1 == this.data.regionLevel || (regionLevelIndex - 1 >= 0 && selectRegionList[regionLevelIndex - 1].id <= 0)) {
      return false;
    }

    this.setData({
      regionLevel: regionLevelIndex + 1
    })

    let selectRegionItem = selectRegionList[regionLevelIndex];

    this.getRegionList(selectRegionItem.pcode, selectRegionItem.level);

    this.setRegionDoneStatus();

  },
  // 取消选择
  cancelSelectRegion() {
    this.setData({
      openSelectRegion: false,
      regionLevel: this.data.regionDoneStatus ? 3 : 1
    });

  },

  // 跳转验证码结果
  onShow:function(options){
    let that = this;
    const captchaResult = app.captchaResult; 
    app.captchaResult = null; 
    console.log("regist:line616:");
    console.log(captchaResult);
    if (captchaResult) {
      if (captchaResult.ret === 0){
          const ticket = captchaResult.ticket;
          // const randstr = captchaResult.randstr; 

          that.setData({
            checkCodeModel: false,     //验证码弹出框隐藏
            ticket: ticket,
            // randstr: randstr
          });

          //发送手机短信验证码
          that.sendPhoneCode();
           
      }else{
        util.msg(1,"验证失败");
        util.request(api.getCode).then(function (res) {
          wx.hideLoading();
          if (res.code == 200) {
            console.log(that.codeUrl)
            that.setData({
              codeUrl: res.data.url,
              checkCodeModel: false, 
            });
            // that.loadTimer();     //触发定时器
          } else {
            util.msg(1, res.msg);
          }
        }).catch((err) => {
          util.msg(1, "获取失败");
        });
      } 
    }else{
      util.request(api.getCode).then(function (res) {
        wx.hideLoading();
        if (res.code == 200) {
          console.log(that.codeUrl)
          that.setData({
            codeUrl: res.data.url,
            checkCodeModel: false, 
          });
          // that.loadTimer();     //触发定时器
        } else {
          util.msg(1, res.msg);
        }
      }).catch((err) => {
        util.msg(1, "获取失败");
      });
    }
    this.loadRegion(0,1);
  },

  // 发送手机短信验证码
  sendPhoneCode: function (){
    let that = this;
    let ticket = that.data.ticket;
    let randstr = that.data.randstr;
    let phone = that.data.phone;

    wx.showLoading({
      title: '发送中...',
    })

    util.request(api.getPhoneCode,{
      mobile:phone,
      captcha: ticket
    }).then(function (res) {
      wx.hideLoading();
      if (res.code == 200) {
        that.loadTimer();     //触发定时器
      } else if (res.code == 5001){
        util.msg(1, "图形验证码过期，请重新验证");
        that.setData({          //重置票据
          randstr:"",
          ticket:""
        });
      }else{
        that.setData({          //重置票据
          randstr: "",
          ticket: ""
        });
        util.msg(1, res.msg);
      }
    }).catch((err) => {
      wx.hideLoading();
      util.msg(1, "发送失败");
    });
  },

  // 关闭准则
  closeReader: function () {
    this.setData({
      dzdpAroundModel: false
    });
  },

  // 选择某个餐厅
  chioceShop:function(e){
    let index = e.target.dataset.index;
    let dzdpAroundList = this.data.dzdpAroundList;
    let selectRegionList = this.data.selectRegionList;
    let regionSelect = this.data.regionSelect;
    let regionRoutStr = this.data.regionRoutStr;
    let shop = dzdpAroundList[index];

    console.log(shop);
    
    regionSelect[0] = { name: shop.provinceName, code: shop.provinceId};
    regionSelect[1] = { name: shop.cityName, code: shop.cityId};
    regionSelect[2] = { name: shop.regionName, code: shop.regionId};
    regionRoutStr = (shop.provinceName || "") + " " + (shop.cityName || "") + " " + (shop.regionName || "");

    // let address = {
    //   id: 0,
    //   province_id: shop.provinceId||"",
    //   province_name: shop.provinceName||"",
    //   city_id: shop.cityId||"",
    //   city_name: shop.cityName||"",
    //   district_id: shop.regionId||"",
    //   district_name: shop.regionName||"",
    //   address: '',
    //   full_region: (shop.provinceName||"") + " " + (shop.cityName||"") + " " + (shop.regionName||""),
    // };

    // selectRegionList[0].id = address.province_id;
    // selectRegionList[0].name = address.province_name;
    // selectRegionList[0].pcode = 1;

    // selectRegionList[1].id = address.city_id;
    // selectRegionList[1].name = address.city_name;
    // selectRegionList[1].pcode = address.province_id;

    // selectRegionList[2].id = address.district_id;
    // selectRegionList[2].name = address.district_name;
    // selectRegionList[2].pcode = address.city_id;

    this.setData({
      addr: shop.address||"",
      shopName: shop.shopName,
      avg: shop.avg||"",
      regionRoutStr: regionRoutStr,
      regionSelect: regionSelect,
      // address: address,
      dzdpAroundModel:false,
      // selectRegionList : selectRegionList
    });
  },


    //控制区域弹框
   regionModel: function (e) {
     let shopswitch = this.data.shopswitch;
     if (shopswitch==1) return false;
    this.setData({
      regionModel: true
    })
  },

//************************************************ */
  //加载区域
  loadRegion:function(regionCode,level){
    let that = this;
    let regionSelect = this.data.regionSelect;

    util.request(api.regionList, {
      code: regionCode,
      level: level
    }).then(function (res) {
      if (res.code === 200) {
         if(level==1){
           let regionList1 = res.data;
            // regionSelect[0] = regionList1[0];
            that.setData({
              regionList1: regionList1,
              // regionSelect: regionSelect,
              regionIndex1:0
            });
           that.loadRegion(regionList1[0].code,level+1);
         }else if(level==2){
           let regionList2 = res.data;
          //  regionSelect[1] = regionList2[0];
           that.setData({
             regionList2: regionList2,
            //  regionSelect: regionSelect,
             regionIndex2: 0
           });
           that.loadRegion(regionList2[0].code,level+1);
         }else if(level==3){
           let regionList3 = res.data;
          //  regionSelect[2] = regionList3[0];
           that.setData({
             regionList3: regionList3,
            //  regionSelect: regionSelect,
             regionIndex3: 0
           });
         }
      }
    });    
  },

  // 触发选择省
  bindChangeRegion1:function(e){
    let that = this;
    let regionList1 = this.data.regionList1;
    let regionSelect = this.data.regionSelect;

    let index = e.detail.value[0];
    let region = regionList1[index];
    // regionSelect[0] = region;
    console.log(index);
    that.setData({
      // regionSelect: regionSelect,
      regionIndex1:index,
      regionIndex2:0,
      regionIndex3:0,
    });

    that.loadRegion(region.code,2);
  },

  // 触发选择城市
  bindChangeRegion2:function(e){
    let that = this;
    let regionList2 = this.data.regionList2;
    let regionSelect = this.data.regionSelect;

    let index = e.detail.value[0];
    let region = regionList2[index];
    console.log(regionList2[index]);

    // regionSelect[1] = region;
    that.setData({
      // regionSelect: regionSelect,
      regionIndex2: index,
      regionIndex3:0,
    });

    that.loadRegion(region.code, 3);
  },

  // 触发选择区县
  bindChangeRegion3:function(e){
    let that = this;
    let regionList3 = this.data.regionList3;
    let regionSelect = this.data.regionSelect;
    let index = e.detail.value[0];
    let region = regionList3[index];

    // regionSelect[2] = region;
    that.setData({
      // regionSelect: regionSelect,
      regionIndex3: index
    });

  },

  // 取消选择区域
  cancelRegion:function(){
    let regionRoutArr = this.data.regionRoutArr;        //已选择数组code
    let regionSelect = this.data.regionSelect;        //已选择数组

    this.setData({
      regionModel: !this.data.regionModel,
    });
    console.log(regionSelect);
  },

  // 确认选择
  yesRegion:function(){

    let regionList1 = this.data.regionList1;      //区域字符串
    let regionList2 = this.data.regionList2;      //区域字符串
    let regionList3 = this.data.regionList3;      //区域字符串

    let regionRoutStr = this.data.regionRoutStr;      //区域字符串
    let regionSelect = this.data.regionSelect;        //已选择数组
    let regionRoutArr = this.data.regionRoutArr;        //已选择数组index

    
    regionRoutArr[0] = this.data.regionIndex1;
    regionRoutArr[1] = this.data.regionIndex2;
    regionRoutArr[2] = this.data.regionIndex3;

    regionSelect[0] = regionList1[this.data.regionIndex1];
    regionSelect[1] = regionList2[this.data.regionIndex2];
    regionSelect[2] = regionList3[this.data.regionIndex3];

    console.log(regionSelect);

    regionRoutStr = regionSelect.map((item,index)=>{
      return item.name;
    }).join(" ");

    this.setData({
      regionRoutStr: regionRoutStr,
      regionModel:false,
      regionRoutArr: regionRoutArr,
      regionSelect: regionSelect
    });
  }


})
