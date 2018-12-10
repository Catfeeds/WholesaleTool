/*
Navicat MySQL Data Transfer

Source Server         : jhwxnew_test
Source Server Version : 50637
Source Host           : 47.104.238.249:3306
Source Database       : fastadmin

Target Server Type    : MYSQL
Target Server Version : 50637
File Encoding         : 65001

Date: 2018-09-25 16:13:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tb_order_detail
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_detail`;
CREATE TABLE `tb_order_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku_code` varchar(64) NOT NULL,
  `sku_unit` varchar(32) NOT NULL,
  `product_count` int(11) NOT NULL,
  `uploaded_img` text,
  `product_price` decimal(10,2) DEFAULT '0.00',
  `is_unlieve` tinyint(4) DEFAULT '1' COMMENT '是否联合利华(0.否 1.是)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tb_order_detail
-- ----------------------------
INSERT INTO `tb_order_detail` VALUES ('1', '88', '10', '测测产品1', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('2', '88', '11', '测测产品2', '1', '袋', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('3', '89', '12', '测测产品1', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('4', '89', '13', '测测产品2', '1', '箱', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('5', '90', '10', '测测产品1', '1', '瓶', '2', null, '58.00', '0');
INSERT INTO `tb_order_detail` VALUES ('6', '90', '11', '测测产品2', '1', '袋', '162', null, '88.80', '0');
INSERT INTO `tb_order_detail` VALUES ('7', '90', '14', '测测产品1', '1', '瓶', '3', null, '68.80', '0');
INSERT INTO `tb_order_detail` VALUES ('8', '91', '10', '测测产品1', '1', '瓶', '1', null, '58.00', '0');
INSERT INTO `tb_order_detail` VALUES ('9', '91', '11', '测测产品2', '1', '袋', '1', null, '88.80', '0');
INSERT INTO `tb_order_detail` VALUES ('10', '92', '10', '测测产品1', '1', '瓶', '2', null, '62.80', '0');
INSERT INTO `tb_order_detail` VALUES ('11', '92', '11', '测测产品2', '1', '袋', '2', null, '188.47', '0');
INSERT INTO `tb_order_detail` VALUES ('12', '93', '15', '测试', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('13', '94', '15', '测试', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('14', '95', '10', '测测产品1', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('15', '95', '13', '测测产品2', '1', '箱', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('16', '96', '16', '测测产品3', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('17', '96', '17', '测测产品4', '1', '千克', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('18', '97', '16', '测测产品3', '1', '瓶', '1', null, '0.00', '0');
INSERT INTO `tb_order_detail` VALUES ('19', '97', '17', '测测产品4', '1', '千克', '1', null, '0.00', '0');

-- ----------------------------
-- Table structure for tb_product
-- ----------------------------
DROP TABLE IF EXISTS `tb_product`;
CREATE TABLE `tb_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `product_pic` varchar(512) DEFAULT NULL,
  `sku_code` varchar(64) NOT NULL,
  `sku_unit` varchar(32) NOT NULL,
  `product_desc` varchar(512) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0.失效 1.有效',
  `created_by` int(11) NOT NULL DEFAULT '0' COMMENT '创建人',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `product_price` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tb_product
-- ----------------------------
INSERT INTO `tb_product` VALUES ('1', '哇哈哈', null, '1', '瓶', null, '1', '3', '1535279610', '1535279610', '0.00');
INSERT INTO `tb_product` VALUES ('2', '测试输入产品', null, '1', '袋', null, '1', '4', '1535280862', '1535280862', '0.00');
INSERT INTO `tb_product` VALUES ('3', '中翅', null, '1', '箱', null, '1', '6', '1535282559', '1535282559', '0.00');
INSERT INTO `tb_product` VALUES ('4', '翅根', null, '1', '袋', null, '1', '6', '1535282714', '1535282714', '0.00');
INSERT INTO `tb_product` VALUES ('5', '太太乐鸡精', null, '1', '瓶', null, '1', '3', '1535289245', '1535289245', '0.00');
INSERT INTO `tb_product` VALUES ('6', '哇哈哈', null, '1', '瓶', null, '1', '8', '1535470282', '1535470282', '0.00');
INSERT INTO `tb_product` VALUES ('7', 'hghg', null, '1', '瓶', null, '1', '9', '1535556511', '1535556511', '0.00');
INSERT INTO `tb_product` VALUES ('8', '这回家', null, '1', '瓶', null, '1', '9', '1535556717', '1535556717', '0.00');
INSERT INTO `tb_product` VALUES ('9', '广州塔', null, '1', '瓶', null, '1', '9', '1535556943', '1535556943', '0.00');
INSERT INTO `tb_product` VALUES ('10', '测测产品1', null, '1', '瓶', null, '1', '253', '1537533119', '1537533119', '0.00');
INSERT INTO `tb_product` VALUES ('11', '测测产品2', null, '1', '袋', null, '1', '253', '1537533119', '1537533119', '0.00');
INSERT INTO `tb_product` VALUES ('12', '测测产品1', null, '1', '瓶', null, '1', '253', '1537601719', '1537601719', '0.00');
INSERT INTO `tb_product` VALUES ('13', '测测产品2', null, '1', '箱', null, '1', '253', '1537601719', '1537601719', '0.00');
INSERT INTO `tb_product` VALUES ('14', '测测产品1', null, '1', '瓶', null, '1', '253', '1537603423', '1537603423', '0.00');
INSERT INTO `tb_product` VALUES ('15', '测试', null, 'aVAlYSSe1fJRzSFrmPEAzyeraEQd66KGzO8Z', '瓶', null, '1', '313', '1537767491', '1537767491', '0.00');
INSERT INTO `tb_product` VALUES ('16', '测测产品3', null, '0lVomashDNuJq3XaxZlG0tG8ja7JvWE4DqLm', '瓶', null, '1', '253', '1537783115', '1537783115', '0.00');
INSERT INTO `tb_product` VALUES ('17', '测测产品4', null, 'eMgKO3CTpgJUFd26CIHMnM3lMk8jSC8lCgE9', '千克', null, '1', '253', '1537783115', '1537783115', '0.00');

-- ----------------------------
-- Table structure for tb_product_price
-- ----------------------------
DROP TABLE IF EXISTS `tb_product_price`;
CREATE TABLE `tb_product_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_price` double(10,2) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tb_product_price
-- ----------------------------
INSERT INTO `tb_product_price` VALUES ('1', '1', '0.00', '6', '3', '1535279610', '1535279610');
INSERT INTO `tb_product_price` VALUES ('2', '2', '15.00', '6', '4', '1535280862', '1535285645');
INSERT INTO `tb_product_price` VALUES ('3', '3', '10.00', '6', '6', '1535282559', '1535285296');
INSERT INTO `tb_product_price` VALUES ('4', '4', '0.00', '6', '6', '1535282714', '1535282714');
INSERT INTO `tb_product_price` VALUES ('5', '5', '5.00', '6', '3', '1535289245', '1535289330');
INSERT INTO `tb_product_price` VALUES ('6', '6', '0.00', '4', '8', '1535470282', '1535470282');
INSERT INTO `tb_product_price` VALUES ('7', '7', '0.00', '6', '9', '1535556511', '1535556511');
INSERT INTO `tb_product_price` VALUES ('8', '8', '0.00', '6', '9', '1535556717', '1535556717');
INSERT INTO `tb_product_price` VALUES ('9', '9', '0.00', '6', '9', '1535556943', '1535556943');
INSERT INTO `tb_product_price` VALUES ('10', '10', '62.80', '15', '253', '1537533119', '1537604531');
INSERT INTO `tb_product_price` VALUES ('11', '11', '188.47', '15', '253', '1537533119', '1537604544');
INSERT INTO `tb_product_price` VALUES ('12', '12', '0.00', '15', '253', '1537601719', '1537601719');
INSERT INTO `tb_product_price` VALUES ('13', '13', '0.00', '15', '253', '1537601719', '1537601719');
INSERT INTO `tb_product_price` VALUES ('14', '14', '68.80', '15', '253', '1537603423', '1537603587');
INSERT INTO `tb_product_price` VALUES ('15', '15', '0.00', '29', '313', '1537767491', '1537767491');
INSERT INTO `tb_product_price` VALUES ('16', '16', '0.00', '15', '253', '1537783115', '1537783115');
INSERT INTO `tb_product_price` VALUES ('17', '17', '0.00', '15', '253', '1537783115', '1537783115');
