<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp_forms
 */
$module_name = 'AOS_Products_Quotes';
$viewdefs [$module_name] =
array(
  'DetailView' =>
  array(
    'templateMeta' =>
    array(
      'form' =>
      array(
        'buttons' =>
        array(
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
        ),
      ),
      'maxColumns' => '2',
      'widths' =>
      array(
        0 =>
        array(
          'label' => '10',
          'field' => '30',
        ),
        1 =>
        array(
          'label' => '10',
          'field' => '30',
        ),
      ),
    ),
    'panels' =>
    array(
      'default' =>
      array(
        array(
          array(
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
          array(
            'name' => 'accdate',
            'label' => 'LBL_ACCDATE',
          ),
          array(
            'name' => 'product_qty',
            'label' => 'LBL_PRODUCT_QTY',
          ),
        ),
        array(
          array(
            'name' => 'product_cost_price',
            'label' => 'LBL_PRODUCT_COST_PRICE',
          ),
          array(
            'name' => 'product_list_price',
            'label' => 'LBL_PRODUCT_LIST_PRICE',
          ),
        ),
        array(
          array(
            'name' => 'product_unit_price',
            'label' => 'LBL_PRODUCT_UNIT_PRICE',
          ),
          array(
            'name' => 'vat',
            'label' => 'LBL_VAT',
          ),
        ),
        array(
          array(
            'name' => 'vat_amt',
            'label' => 'LBL_VAT_AMT',
          ),
          array(
            'name' => 'product_total_price',
            'label' => 'LBL_PRODUCT_TOTAL_PRICE',
          ),
        ),
        array(
          array(
            'name' => 'product',
            'label' => 'LBL_PRODUCT',
          ),
          array(
            'name' => 'parent_name',
            'label' => 'LBL_FLEX_RELATE',
          ),
        ),
        array(
          array(
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
      ),
    ),
  ),
);
