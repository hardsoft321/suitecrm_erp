<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

$dictionary["AOS_Products"]["fields"]["qty_plan"] = array (
        'name' => 'qty_plan',
        'vname' => 'LBL_QTY_PLAN',
        'type' => 'decimal',
        'massupdate' => 0,
        'importable' => 'false',
        'duplicate_merge' => 'disabled',
        'duplicate_merge_dom_value' => '0',
        'audited' => true,
        'reportable' => true,
        'len' => '18',
        'size' => '20',
        'enable_range_search' => false,
        'precision' => '4',
        'required' => true,
        'default' => 0,
        'editable' => false,
);

$dictionary["AOS_Products"]["fields"]["qty_fact"] = array (
    'name' => 'qty_fact',
    'vname' => 'LBL_QTY_FACT',
    'type' => 'decimal',
    'massupdate' => 0,
    'importable' => 'false',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => '0',
    'audited' => true,
    'reportable' => true,
    'len' => '18',
    'size' => '20',
    'enable_range_search' => false,
    'precision' => '4',
    'required' => true,
    'default' => 0,
    'editable' => false,
);
