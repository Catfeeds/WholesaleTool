
var util = require('../../../utils/util.js');
var api = require('../../../config/api.js');
const app = getApp()

Page({
  data: {
    menuIndex:0,                      //菜单选择
    selectIndex:false,                //下拉选择开始true 关闭false
    userInfo: {},
    scrollHeight: 0,
    modelShow: false,       //模态框展示
    hisModelShow:false,      //历史弹出层
    successModelShow:false,    //成功弹出层
    submitModelShow:false,    //提交确认弹出层
    remark: "",              //备注信息
    successTimer:3,           //成功后倒计时
    goodsList: [],              //父级供应商商品列表
    inputSelectList:[     //填选项
      { product_count: 0, product_name: "", sku_unit: "", product_price: "", skuIndex: 0, type: 0},
      { product_count: 0, product_name: "", sku_unit: "", product_price: "", skuIndex: 0, type: 0},
    ],
    skuList:[],               //单位选择
    historyList: [],                   //历史产品列表
    imgOrderList:[],                   //图片订单列表        
    superInfo:{},              //父级对象
    true: true,
    ShareShow4: false, 
    ShareShow: false,
    ShareShow2: false, 
    ShareShow3: false,
    historyListShow:false
  },

  // "supplier_id": "4",
  // "name": "yyy",
  // "mobile": "13641788291",
  // "address": null

  // 初始加载
  onLoad: function (option) {
    let that = this;
    let historyList = that.data.historyList;
    let height = 0;

    wx.getSystemInfo({
      success: function (res) {
        height = res.windowHeight - 120;
        that.setData({
          scrollHeight: height,
        });
      }
    });
    util.request(api.getSuperInfo).then(function (res) {
      if (res.code === 200) {
        that.setData({
          superInfo: res.data
        });
        wx.setStorageSync("superInfo", res.data);
      } else {
        console.log("查询供货商信息失败");
      }
    }, function (res) {
      console.log(res);
    });
    
    if (historyList.length<=0){
      that.getHistoryList();        //获取历史数据
    }
    console.log("判断是否显示历史产品按钮"+that.data.historyListShow)

    //获取手机型号，适配iphoneX ，Nokia X6
    wx.getSystemInfo({
      success: function (res) {
        console.log('手机信息res = ' + res.model)
        let modelmes = res.model;
        if (modelmes.search('iPhone X') != -1) {
          that.setData({
            isIphoneX: true
          })
        } else if (res.pixelRatio == 3) {
          that.setData({
            isIphoneX: 'Nokia'
          })
        }else{
          that.setData({
            isIphoneX: false
          })
        }
      }
    })
    console.log('isIphoneX = ' + that.data.isIphoneX)

    //根据缓存判断终端用户是否第一次进入
    if (wx.getStorageSync('isFirst') === "2") {
      this.setData({
        ShareShow4: true
      });
      //引导层显示一次就将isFirst值改掉
      wx.setStorageSync('isFirst', "5");
    }
    console.log("终端用户是否第一次进入"+wx.getStorageSync('isFirst'))
  },

  onShow:function(){
    this.getSuperProductList();    //查询父级供应商所有商品列表
   
    this.getSkuList();            //获取单位列表
    
  },

  // 切换菜单选择方式
  changeMenu: function (e) {
    let that = this;
    let index = e.target.dataset.index;
    let menuIndex = that.data.menuIndex;
    if (menuIndex != index) {
      that.setData({
        menuIndex: index,
      });
    }
  },

  // 切换下拉选择开启关闭
  changeDownUp: function (e) {
    let that = this;
    let selectIndex = that.data.selectIndex;
      that.setData({
        selectIndex: !selectIndex
      });
  },

  // 查询父级供应商所有商品列表
  getSuperProductList: function () {
    let that = this;
    let goodsList = that.data.goodsList;

    util.request(api.getSuperProductList).then(function (res) {
      if (res.code === 200) {
        goodsList = res.data;
        that.setData({
          goodsList: goodsList
        });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
     // util.msg(1, "查询失败");
     console.log(res)
    });
  },

  // 返回上一级
  comback: function () {
    wx.navigateBack();
  },


  // 监听count输入
  inputNumFun: function (e) {
    let that = this;
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let goodsList = that.data.goodsList;    //商品列表
    if (!value) {
      value = 0;
    }
    goodsList[index]["product_count"] = parseInt(value);
    
    that.setData({
      goodsList: goodsList
    });

  },

  // 填选监听count输入
  inputInputNumFun: function (e) {
    let that = this;
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let inputSelectList = that.data.inputSelectList;    //订单详情
    
    if (!value) {
      value = 0;
    }
    inputSelectList[index]["product_count"] = parseInt(value);
    that.setData({
      inputSelectList: inputSelectList
    });

  },

  // 计数器增/减
  countNumFun: function (e) {
    let that = this;
    let index = e.target.dataset.index;
    let step = e.target.dataset.step;
    let goodsList = that.data.goodsList;      //商品列表
    let product_count = goodsList[index].product_count;   //选择数量
    if (!product_count) product_count = 0;
    if (step == 1) {
      product_count++;
    } else if (product_count > 0) {
      product_count--;
    }

    goodsList[index].product_count = product_count;
    that.setData({
      goodsList: goodsList
    });

  },

  // 填选计数器增/减
  inputCountNumFun: function (e) {
    let that = this;
    let index = e.target.dataset.index;
    let step = e.target.dataset.step;

    let inputSelectList = this.data.inputSelectList;      //填选商品列表
    let product_count = inputSelectList[index].product_count;      //选择数量
    if (!product_count) product_count = 0;
    if (step == 1) {
      product_count++;
    } else if (product_count > 0) {
      product_count--;
    }

    inputSelectList[index].product_count = product_count;
    that.setData({
      inputSelectList: inputSelectList
    });

  },

  // 填选商品单位选择
  bindPickerChange: function (e) {
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let skuList = this.data.skuList;
    let inputSelectList = this.data.inputSelectList;

    inputSelectList[index].skuIndex = value;
    inputSelectList[index].sku_unit = skuList[value].sku_unit;

    this.setData({
      inputSelectList: inputSelectList
    })
  },

  // 填选项输入框监听
  inpSelectFun:function(e){
    let value = e.detail.value;
    let index = e.target.dataset.index;
    let inputSelectList = this.data.inputSelectList;

    inputSelectList[index].product_name = value;

    this.setData({
      inputSelectList: inputSelectList
    })
  },

  // 监听备注输入
  remarkInputFun:function(e){
    let value = e.detail.value;
    this.setData({
      remark: value
    })
  },

  // 添加历史数据
  openHistory:function(){
    let that = this;
    this.setData({
      hisModelShow:true
    });
   

  },
  
  // 获取单位列表
  getSkuList:function(){
    let that = this;
    wx.showLoading({
      title: '加载中...',
    })
    util.request(api.getSkuList).then(function (res) {
      wx.hideLoading();
      if (res.code === 200) {
        that.setData({
          skuList: res.data
        });
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "查询单位失败");
    });
  },

  // 获取用户历史选择商品
  getHistoryList:function(){
    let that = this;
    wx.showLoading({
      title: '加载中...',
    })
    util.request(api.getHistoryList).then(function (res) {
      wx.hideLoading();
      console.log("查询历史产品长度" + res.data.length)
      if (res.code === 200) {
        that.setData({
          historyList: res.data
        });
        if (res.data.length > 0){
          that.setData({
            historyListShow: true
          });
        }
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "查询历史失败");
    });
  },

  // 历史多选框监听
  checkboxChange:function(e){
    let selectIndexArr = e.detail.value;
    let historyList = this.data.historyList;
    selectIndexArr.map(function(item){
      historyList[item].checked = true
    });
    this.setData({
      historyList: historyList
    });
  },

  // 确认添加历史
  yesHistory:function(){
      let that = this;
      let historyList = this.data.historyList;          //全部历史数据
      let inputSelectList = this.data.inputSelectList;  //全部填选框
      let selectHis = historyList.filter(function (item) {    //获取选择的历史
        return item.checked==true;
      });

    console.log(selectHis);
      //如果选择的有历史
      let newinputSelectList;
      if (selectHis.length>0){
        //清除原历史数组的选过的商品
        selectHis.forEach(function(his,i){
          historyList.splice(historyList.findIndex(item => item.product_id === his.product_id), 1);
        });

        let count = 0;              //已经插入的数
        newinputSelectList = inputSelectList.map(function(item,index){
          if (item.product_name == "" && count < selectHis.length){
            // { product_count: 0, product_name: "", sku_unit: "" ,skuIndex: 0, type: 0},
            item.product_name = selectHis[count].product_name;
            item.product_id = selectHis[count].product_id;
            item.sku_unit = selectHis[count].sku_unit;
            item.skuIndex = that.getSkuIndex(selectHis[count].sku_unit);
            item.product_count = 1;
            item.type = 1;          //标识为历史的
            count++;
          }
          return item;
        });
        for (let i = count; i < selectHis.length;i++){
          selectHis[i]["type"] = 1;
          selectHis[i]["product_count"] = 1;
          selectHis[i]["skuIndex"] = that.getSkuIndex(selectHis[i]["skuIndex"].sku_unit);
          newinputSelectList.push(selectHis[i]);
        }
      }

      this.setData({
        hisModelShow: false,
        inputSelectList: newinputSelectList,
        historyList: historyList
      });

  },

  // 通过单位名称获取index
  getSkuIndex:function(name){
    let skuList = this.data.skuList;
    let ind = 0;
    skuList.forEach((item,index)=>{
        
      if (item.sku_unit==name){
        ind =  index;
        return false;
      }

    });
    return ind;

  },

  // 取消添加历史
  noHistory:function(){
    let historyList = this.data.historyList;
    historyList.map(function (item) {
      item.checked = false
    });
    this.setData({
        hisModelShow:false,
        historyList: historyList
    });
  },

  // 选择图片
  choicePic:function(){
    let that = this;
    let imgOrderList = that.data.imgOrderList;
    wx.chooseImage({
      count: 4 - that.data.imgOrderList.length,
      success: function (res) {
       
        var imgOrderList = res.tempFilePaths
        // imgOrderList = imgOrderList.concat(tempFilePaths);
        // that.setData({
        //   imgOrderList: imgOrderList
        // });
        wx.showLoading({
          title: '图片上传中...',
        });
        let userInfo = wx.getStorageSync('userInfo');
        let data = {};
        if (userInfo) {
          data['token'] = userInfo.token;
          data['unionId'] = userInfo.unionId;
        }

        for (let i = 0; i < imgOrderList.length; i++) {
          wx.uploadFile({
            url: api.uploadPic,
            filePath: imgOrderList[i],
            name: 'image',
            formData: data,
            header: {
              "Content-Type": "multipart/form-data",
              'accept': 'application/json',
            },
            success: function (res) {
              if (res.statusCode == 200) {
                let data = JSON.parse(res.data);
                if(data.code==200){
                  let imgOrderList = that.data.imgOrderList;
                  imgOrderList.push({url:data.data});
                  that.setData({
                    imgOrderList: imgOrderList
                  });
                }else{
                  util.msg(1, data.msg);
                }
              }else{
                util.msg(1, "上传失败");
              }
            },
            fail: function (res) {
              util.msg(1, res.msg);
            },
            complete: function () {
              if (i == imgOrderList.length - 1) wx.hideLoading();
            }

          })
        }
        
      }
    })
  },

  // 提交图片
  submitPic:function(){
    let that = this;
    let imgOrderList = that.data.imgOrderList;
  },

  //图片出错
  iconError: function (e) {
    let index = e.target.dataset.index;
    var goodsList = this.data.goodsList;

    goodsList[index].product_pic = "/static/images/defaule.png";
    this.setData({
      goodsList: goodsList  
    });
  },

  // 新增填选框
  addChoiceInputv:function(){
    let inputSelectList = this.data.inputSelectList;
    let skuList = this.data.skuList;

    inputSelectList.push({ product_count: 0, product_name: "", sku_code: skuList[0].sku_code,sku_unit: skuList[0].sku_unit, skuIndex: 0, type: 0 });
    this.setData({
      inputSelectList: inputSelectList
    });
  },

  // 长按删除图片
  deleteImage:function(e){
    let index = e.target.dataset.index;
    let imgOrderList = this.data.imgOrderList;
    imgOrderList[index]["delete"] = true;
    this.setData({
      imgOrderList: imgOrderList
    });
  },

  // 确认删除
  removePicOrder:function(e){
    let that = this;
    let index = e.target.dataset.index;
    wx.showModal({
      title: '提示',
      content: '是否确认删除此图片订单？',
      success: function (res) {
        let imgOrderList = that.data.imgOrderList;
        if (res.confirm) {
          imgOrderList.splice(index, 1);
          that.setData({
            imgOrderList: imgOrderList
          });
        } else if (res.cancel) {
          imgOrderList[index]["delete"] = false;
          that.setData({
            imgOrderList: imgOrderList
          });

        }
      }
    })
  },


  // 打开确认提示框
  toSubmit:function(){
    let that = this;
    let data = that.data;
    let skuList = data.skuList;
    let param = {};

    let historyList = [];       //历史的
    let inputList = [];         //填的

    //处理填选商品
    data.inputSelectList.forEach(function (item, index) {
      if (item.product_name != "" && item.product_count > 0) {
        if (item.type == 1) {
          historyList.push(item);         //type 1 历史的
        } else {
          if (!item.sku_unit) item["sku_unit"] = skuList[0].sku_unit;
          if (!item.sku_code) item["sku_code"] = skuList[0].sku_code;
          inputList.push(item);           //填写的
        }
      }
    });

    //处理选择商品
    let goodsList = data.goodsList.filter(function (item, index) {
      return item.product_count > 0;
    });

    param["order_type"] = data.menuIndex;    //0默认 1图片
    param["comments"] = data.remark;         //备注
    if (goodsList.length > 0)
      param["product_list"] = JSON.stringify(goodsList);       //选择供应商的商品列表
    if (historyList.length > 0)
      param["user_product_list"] = JSON.stringify(historyList);  //选择历史商品列表
    if (inputList.length > 0)
      param["other_product_list"] = JSON.stringify(inputList);//手填商品列表
    if (data.imgOrderList.length > 0) {
      let imgArr = data.imgOrderList.map((item, index) => {
        return item.url;
      });
      param["img_product_list"] = JSON.stringify(imgArr);  //图片商品列表
    }

    if (param.order_type == 0) {
      if (!param.product_list && !param.user_product_list && !param.other_product_list) {
        util.msg(1, "请选择商品");
        return false;
      }
    } else {
      if (!param.img_product_list) {
        util.msg(1, "请上传图片订单");
        return false;
      }
    }

    this.setData({
      submitModelShow:true
    });
  },

  // 关闭
  cloceModel:function(){
    this.setData({
      submitModelShow: false
    });
  },

  // 提交订单
  submitInfo:function(e){
    this.setData({
      submitModelShow: false
    });

    let formId = e.detail.formId;
    let that = this;
    let data = that.data;
    let skuList = data.skuList;
    let param = {};

    let historyList = [];       //历史的
    let inputList = [];         //填的

    console.log("formId:" +formId);

    //处理填选商品
    data.inputSelectList.forEach(function(item,index){
        if(item.product_name!=""&&item.product_count>0){
            if(item.type==1){
              historyList.push(item);         //type 1 历史的
            }else{
              if (!item.sku_unit) item["sku_unit"] = skuList[0].sku_unit;
              if (!item.sku_code) item["sku_code"] = skuList[0].sku_code;
              inputList.push(item);           //填写的
            }
        }
    });

    console.log("line533:");
    console.log(data);
    console.log(data.goodsList);
    //处理选择商品
    let goodsList = data.goodsList.filter(function(item,index){
        return item.product_count>0;
    });

    param["order_type"] = data.menuIndex;    //0默认 1图片
    param["comments"] = data.remark;         //备注
    if (goodsList.length>0)
      param["product_list"] = JSON.stringify(goodsList);       //选择供应商的商品列表
    if (historyList.length > 0)
      param["user_product_list"] = JSON.stringify(historyList);  //选择历史商品列表
    if (inputList.length > 0)
      param["other_product_list"] = JSON.stringify(inputList);//手填商品列表
    if (data.imgOrderList.length>0){
      let imgArr = data.imgOrderList.map((item,index)=>{
            return item.url;
      });
      param["img_product_list"] = JSON.stringify(imgArr);  //图片商品列表
    }

    if (param.order_type==0){
      if (!param.product_list && !param.user_product_list && !param.other_product_list){
          util.msg(1,"请选择商品");
          return false;
      }
    }else{
      if (!param.img_product_list) {
        util.msg(1, "请上传图片订单");
        return false;
      }
    }

    param['form_id'] = formId;

    wx.showLoading({
      title: '提交中...',
    })

    util.request(api.addOrder,param).then(function (res) {
          wx.hideLoading();
      if (res.code === 200) {
        that.setData({
          successModelShow:true,
          remark:""
        });
        let timer = setInterval(function(){
          let successTimer = that.data.successTimer;
          if (successTimer>0){
            that.setData({
              successTimer: --successTimer
            });
          }else{
            clearInterval(timer);
            wx.redirectTo({
              url: '/pages/customer/allOrder/allOrder',
            })
          }
        },1000);
      } else {
        util.msg(1, res.msg);
      }
    }, function (res) {
      wx.hideLoading();
      util.msg(1, "提交失败");
    });

  },

  // 控制导航层显示
  togglePopup() {
    this.setData({
      ShareShow: !this.data.ShareShow,
      ShareShow2: true
    });
  },
  togglePopup2() {
    this.setData({
      ShareShow2: false,
      ShareShow3: true
    });
  },
  togglePopup3() {
    console.log('1454654')
    this.setData({
      ShareShow3: false,
    });
  },
  togglePopup4:function() {
    this.setData({
      ShareShow4: false,
      ShareShow:true
    });
  },
})
