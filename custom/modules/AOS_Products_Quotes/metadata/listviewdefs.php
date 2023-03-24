<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp_forms
 */
$module_name = 'AOS_Products_Quotes';
$listViewDefs [$module_name] =
array(
  'NAME' =>
  array(
    'width' => '32%',
    'label' => 'LBL_NAME',
    'default' => true,
    'link' => true,
  ),
  'ACCDATE' =>
  array(
    'width' => '32%',
    'label' => 'LBL_ACCDATE',
    'default' => true,
    'link' => false,
  ),
  'PRODUCT_COST_PRICE' =>
  array(
    'width' => '10%',
    'label' => 'LBL_PRODUCT_COST_PRICE',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' =>
  array(
    'width' => '9%',
    'label' => 'LBL_ASSIGNED_TO_NAME',
    'default' => true,
  ),
);
