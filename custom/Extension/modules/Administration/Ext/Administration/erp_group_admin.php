<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */
$admin_option_defs = [];

// $admin_option_defs['Administration']['aos_erp_settings'] = array(
//     'AOS_ERP_SETTINGS',
//     'LBL_AOS_ERP_SETTINGS_TITLE',
//     'LBL_AOS_ERP_SETTINGS_INFO',
//     'index.php?module=AOS_ERP&action=createPurchaseContracts&return_module=Administration',
//     'diagnostic'
// );

$admin_option_defs['Administration']['aos_erp_create_purchase_contracts'] = array(
    'AOS_ERP_CREATE_PURCHASE_CONTRACTS',
    'LBL_AOS_ERP_CREATE_PURCHASE_CONTRACTS_TITLE',
    'LBL_AOS_ERP_CREATE_PURCHASE_CONTRACTS_INFO',
    'index.php?module=AOS_ERP&action=createPurchaseContracts&return_module=Administration',
    'diagnostic'
);

$admin_group_header['aos_erp'] = array(
    'LBL_AOS_ERP_HEADER',
    'LBL_AOS_ERP_HEADER',
    false,
    $admin_option_defs,
    ''
);
