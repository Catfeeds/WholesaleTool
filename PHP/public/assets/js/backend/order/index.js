define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/index/index',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'order_number', title: __('Order_number'), sortable: false},
                        {field: 'client', title: __('Client_id'), operate: false},
                        {field: 'supplier', title: __('Supplier_id'), operate: false},
                        {field: 'order_type', title: __('Order_type'), searchList: {0 : __('typea'), 1 : __('typeb')},visible:false},
                        {field: 'order_type_text', title: __('Order_type'), operate:false},
                        {field: 'comments', title: __('Comments'), operate: false},
                        {field: 'order_status', title: __('Order_status'), searchList: {1: __('statusa'), 2: __('statusb'),3:__('statusc')},visible:false},
                        {field: 'order_status_text', title: __('Order_status'), operate:false},
                        {field: 'created_at', title: __('Created_at'),  formatter: Table.api.formatter.datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"'},
                        {field: 'updated_at', title: __('Updated_at'),  formatter: Table.api.formatter.datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"'},
                        {field: 'remark', title: __('Remark'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                            {name: 'detail', text: '详情', title: '详情', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'order/detail/index'}
                        ],  events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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