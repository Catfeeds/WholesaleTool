define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    del_url: 'user/user/del',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'user.id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'nickname', title: __('Nickname'), sortable: false},
                        {field: 'gender', title: __('Gender'), searchList: {1: __('Male'), 0: __('unknown'),2:__('FeMale')},visible:false},
                        {field: 'gender_text', title: __('Gender'), operate:false},
                        {field: 'user_type', title: __('user_type'), searchList: {1: __('supplier'), 0: __('unknown'),2:__('client')},visible:false},
                        {field: 'user_type_text', title: __('user_type'), operate:false},
                        {field: 'supplier', title: __('supplier_bd'), operate:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});