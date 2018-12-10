/*主库和副库*/
ALTER TABLE `tb_product`
ADD COLUMN `cover_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '封面图id' AFTER `product_sort`,
ADD COLUMN `supplier_id`  int(11) NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `cover_id`,
ADD COLUMN `by_supplier`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为供应商创建的商品 0 不是 1是' AFTER `supplier_id`;

CREATE TABLE `tb_product_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '终端id',
  `product_id` int(11) DEFAULT NULL COMMENT '商品id',
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `tb_product`
ADD UNIQUE INDEX `idx_product_unit` (`product_name`, `sku_unit`) USING BTREE ;


