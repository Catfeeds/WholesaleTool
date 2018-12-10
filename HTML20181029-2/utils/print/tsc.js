var jpPrinter = {
　　　　createNew: function(){
　　　　　　var jpPrinter = {};
		              	var data = "";

	              		jpPrinter.name = "蓝牙打印机";
			
			jpPrinter.init = function(){				
			};
  
      jpPrinter.setSize = function (pageWidght, pageHeight) { //设置页面大小
        data += "SIZE " + pageWidght.toString() + " mm" + "," + pageHeight.toString() +" mm"+"\r\n";
      };
      
      jpPrinter.setSpeed = function (printSpeed) {  //设置打印机速度
        data += "SPEED " + printSpeed.toString() + "\r\n";
      };
      
      jpPrinter.setDensity = function (printDensity) { //设置打印机浓度
        data += "DENSITY " + printDensity.toString() + "\r\n";
      };
      
      jpPrinter.setGap = function (printGap) {  //传感器
        data += "GAP " + printGap.toString()+" mm\r\n";
      };

      jpPrinter.setCountry = function(country){  //选择国际字符集
        /*
        001:USA
        002:French
        003:Latin America
        034:Spanish
        039:Italian
        044:United Kingdom
        046:Swedish
        047:Norwegian
        049:German
         */
        data += "COUNTRY " + country + "\r\n";
      };

      jpPrinter.setCodepage = function(codepage){ //选择国际代码页
        /*
        8-bit codepage 字符集代表
        437:United States
        850:Multilingual
        852:Slavic
        860:Portuguese
        863:Canadian/French
        865:Nordic
        Windows code page
        1250:Central Europe
        1252:Latin I
        1253:Greek
        1254:Turkish
        以下代码页仅限于 12×24 dot 英数字体
        WestEurope:WestEurope
        Greek:Greek
        Hebrew:Hebrew
        EastEurope:EastEurope
        Iran:Iran
        IranII:IranII
        Latvian:Latvian
        Arabic:Arabic
        Vietnam:Vietnam
        Uygur:Uygur
        Thai:Thai
        1252:Latin I
        1257:WPC1257
        1251:WPC1251
        866:Cyrillic
        858:PC858
        747:PC747
        864:PC864
        1001:PC100
        */
        data += "CODEPAGE " +codepage +"\r\n";
      }
      
      jpPrinter.setCls = function () {  //清除打印机缓存
        data += "CLS" + "\r\n";
      };

      jpPrinter.setFeed = function(feed){  //将纸向前推出n
        data += "FEED " + feed + "\r\n";
      };

      jpPrinter.setBackup = function(backup){ //将纸向后回拉n
        data += "BACKUP " + backup + "\r\n";
      }
         
      jpPrinter.setDirection = function (direction) { //设置打印方向，参考编程手册  
        data += "DIRECTION " + direction +"\r\n";
      };

      jpPrinter.setReference = function(x,y){  //设置坐标原点，与打印方向有关
        data += "REFERENCE " + x + "," + y + "\r\n";
      };

      jpPrinter.setFromfeed = function(){  //根据Size进一张标签纸
        data += "FORMFEED \r\n";
      };

      jpPrinter.setHome = function(){ //根据Size找到下一张标签纸的位置
        data += "HOME \r\n";
      };
      
      jpPrinter.setSound = function(level,interval){ //控制蜂鸣器
        data += "SOUND " + level + "," + interval + "\r\n";
      };

      jpPrinter.setLimitfeed = function(limit){ // 检测垂直间距
        data += "LIMITFEED " + limit + "\r\n";
      };

      jpPrinter.setBar = function(x,y,width,height){  //绘制线条
        data += "BAR " + x + "," + y + "," + width + "," + height + "\r\n"
      };

      jpPrinter.setBox = function(x_start,y_start,x_end,y_end,thickness){ //绘制方框
        data += "BOX "+x_start+","+y_start+","+x_end+","+y_end+","+thickness+"\r\n";
      };

      // jpPrinter.GPBitmap = function(x,y,width,height,mode,bitmapdata){ //打印BITMAP位图，详细见编程手册
      //   data += "BITMAP " + x + "," + y + "," + width + "," + height + "," + mode + "," + bitmapdata + "\r\n";
      // };

      jpPrinter.setErase = function(x_start,y_start,x_width,y_height){ //清除指定区域的数据
        data += "ERASE " + x_start + "," + y_start + "," + x_width + "," + y_height +"\r\n";
      };

      jpPrinter.setReverse = function (x_start, y_start, x_width, y_height) { //将指定的区域反相打印
        data += "REVERSE " + x_start + "," + y_start + "," + x_width + "," + y_height + "\r\n";
      };

      jpPrinter.setText = function (x, y, font, x_, y_, str) {  //打印文字
        data += "TEXT " + x + "," + y + ",\"" + font + "\"," + 0 + "," + x_ + "," + y_ + ","+"\""+str+"\"\r\n"
      };

      jpPrinter.setQR = function (x, y, level, width, mode, content) { //打印二维码
        data += "QRCODE " + x + "," + y + "," + level + "," + width + "," + mode + "," + 0 + ",\"" + content + "\"\r\n"
      };

      jpPrinter.setBar = function (x, y, codetype, height, readable, content, narrow, wide) { //打印条形码
        data += "BARCODE " + x + "," + y + ",\"" + codetype + "\"," + height + "," + readable + "," + 0 + "," + narrow + "," + wide + ",\"" + content + "\"\r\n"
      };

      jpPrinter.setPagePrint = function () { //打印页面
        data += "PRINT 1,1\r\n";
      };	

      jpPrinter.setSendcommand = function (str) {
        data += str;
      };	

      //获取打印数据
      jpPrinter.getData = function () {
        return data;
      };

　　return jpPrinter;
　}
};

module.exports.jpPrinter = jpPrinter;

