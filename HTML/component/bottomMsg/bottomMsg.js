// component/reader.js
Component({

  externalClasses: ['my-class'],
  /**
   * 组件的属性列表
   */
  properties: {

  },

  /**
   * 组件的初始数据
   */
  data: {
    
  },

  /**
   * 组件的方法列表
   */
  methods: {
    call: function () {
      wx.makePhoneCall({
        phoneNumber: '021-34159566' //仅为示例，并非真实的电话号码
      })
    }
  }
})
