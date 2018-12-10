// component/reader.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {

  },

  /**
   * 组件的初始数据
   */
  data: {
    modelShow: false,          //弹出框
  },

  /**
   * 组件的方法列表
   */
  methods: {
    // 打开准则
    openReader: function () {
      this.setData({
        modelShow: true
      });
    },
    // 关闭准则
    closeReader: function () {
      this.setData({
        modelShow: false
      });
    }
  }
})
