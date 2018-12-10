// 蓝牙打印订单工具类
var encode = require("./encoding.js")
var tsc = require("./tsc.js");
var esc = require("./esc.js");
var app = getApp();


let sendContent="";
let looptime=0;
let currentTime= 1;
let lastData= 0;



//开始打印
function toPrint(data,count){
  looptime= 0

  var content = new encode.TextEncoder('gb18030', { NONSTANDARD_allowLegacyEncoding: true }).encode(data);

  var buff = new ArrayBuffer(content.length);
  var dataView = new DataView(buff);

  for (var i = 0; i < content.length; ++i) {
    dataView.setUint8(i, content[i]);
  }
  this.send(buff);
}

function send(buff) {
  wx.writeBLECharacteristicValue({
    deviceId: app.BLEInformation.deviceId,
    serviceId: app.BLEInformation.writeServiceId,
    characteristicId: app.BLEInformation.writeCharaterId,
    value: buff,
    success: function (res) {
      console.log(res)

    }, fail: function (e) {
      console.log(e)
    }
  })
}


module.exports = {
  toPrint
}





