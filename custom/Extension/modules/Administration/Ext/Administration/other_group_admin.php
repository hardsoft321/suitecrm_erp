<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */
$admin_option_defs = [];
$admin_option_defs['Administration']['gen_erp'] = array(
    'GenERP',
    'LBL_GEN_ERP_TITLE',
    'LBL_GEN_ERP_TITLE_INFO',
    './index.php?module=GenerationERPData&action=GenView',
    'diagnostic'
);

$admin_group_header['other'] = array(
    'LBL_OTHER_HEADER',
    '',
    false,
    $admin_option_defs,
    ''
);
