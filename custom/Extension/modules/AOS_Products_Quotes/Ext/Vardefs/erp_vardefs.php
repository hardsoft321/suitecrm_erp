<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

$dictionary["AOS_Products_Quotes"]["fields"]["accdate"] = array (
    'name' => 'accdate',
    'vname' => 'LBL_ACCDATE',
    'type' => 'datetime',
    'required' => false,
    'audited' => true,
);

$dictionary["AOS_Products_Quotes"]["fields"]["wip_status"] = array (
    'name' => 'wip_status',
    'vname' => 'LBL_WIP_STATUS',
    'type' => 'enum',
    'required' => true,
    'default' => 'draft',
    'options' => 'porduct_quotes_wip_statuses',
    'audited' => true,
);
