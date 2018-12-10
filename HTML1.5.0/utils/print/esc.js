
var encode = require("./encoding.js")
var jpPrinter = {
　　　　createNew: function(){
　　　　　　var jpPrinter = {};
			var data = [];

			jpPrinter.name = "蓝牙打印机";
			
			jpPrinter.init = function(){
        data.push(27)
        data.push(64)				
			};

      jpPrinter.setText = function(content){
        var code = new encode.TextEncoder(
          'gb18030', { NONSTANDARD_allowLegacyEncoding: true }).encode(content)
        for (var i = 0; i < code.length;++i){
          data.push(code[i])
        }
        // for (var i = 0, strLen = content.length; i < strLen; i++) {
        //  data.push(content.charCodeAt(i))
        // }
      }

      jpPrinter.setBitmap = function(res){
        console.log(res)
         var width = parseInt((res.width + 7) / 8 * 8 / 8)
          var height = res.height;
          var time = 1;
          var temp = res.data.length - res.width * 32;
          var point_list = []
          console.log(width+"--"+height)
          data.push(29)
          data.push(118)
          data.push(48)
          data.push(0)
          data.push((parseInt((res.width + 7) / 8) * 8) / 8)
          data.push(0)
          data.push(parseInt(res.height % 256)) 
          data.push(parseInt(res.height / 256))
          console.log(res.data.length)
          console.log("temp="+temp)
          for(var i=0;i<height;++i){
            console.log(temp)
            for(var j=0;j<width;++j){
              for(var k=0;k<32;k+=4){
                var po = {}
                if (res.data[temp] == 0 && res.data[temp + 1] == 0 && res.data[temp + 2] == 0 && res.data[temp+3] == 0){
                  po.point = 0;
                }else{
                  po.point = 1;
                }
                point_list.push(po)
                temp+=4
              }
            }
            time++ 
            temp = res.data.length - width * 32 * time
          }
          for (var i = 0; i < point_list.length; i += 8) {
            var p = point_list[i].point * 128 + point_list[i + 1].point * 64 + point_list[i + 2].point * 32 + point_list[i + 3].point * 16 + point_list[i + 4].point * 8 + point_list[i + 5].point * 4 + point_list[i + 6].point * 2 + point_list[i + 7].point
            data.push(p)
          }
      }

      jpPrinter.setPrint = function(){
          data.push(10)
      }


      //获取打印数据
      jpPrinter.getData = function () {
        return data;
      };

　　return jpPrinter;
　}
};

module.exports.jpPrinter = jpPrinter;

