<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 * @author  Leon Nikitin <nlv@lab321.ru>
 */

 if (isset($admin_group_header['aos_erp'])) {
    $admin_group_header['aos_erp'][3]['Administration']['aos_erp_gen_quotes'] = array(
        'AOS_ERP_GEN_QUOTES',
        'LBL_AOS_ERP_GEN_QUOTES_TITLE',
        'LBL_AOS_ERP_GEN_QUOTES_INFO',
        './index.php?module=GenerationERPData&action=GenView',
        'diagnostic'
    );
 } else {

    $admin_option_defs = [];

    $admin_option_defs['Administration']['aos_erp_gen_quotes'] = array(
        'AOS_ERP_GEN_QUOTES',
        'LBL_AOS_ERP_GEN_QUOTES_TITLE',
        'LBL_AOS_ERP_GEN_QUOTES_INFO',
        './index.php?module=GenerationERPData&action=GenView',
        'diagnostic'
    );

    $admin_group_header['aos_erp'] = array(
        'LBL_AOS_ERP_HEADER',
        'LBL_AOS_ERP_HEADER',
        false,
        $admin_option_defs,
        ''
    );
}


