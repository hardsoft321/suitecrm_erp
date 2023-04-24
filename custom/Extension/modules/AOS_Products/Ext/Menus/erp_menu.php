<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

if (isset($sugar_config['erp']['module'])) {
    $erp_module = $sugar_config['erp']['module'];  
  
          
    if (ACLController::checkAccess($erp_module, 'edit', true)) {
        $module_menu[]=array("index.php?module=AOS_ERP&action=createPurchaseContracts&return_module=AOS_Products&return_action=index", $mod_strings['LNK_CREATE_PURCHASE_CONTRACTS'],"Create2", 'Accounts2');
    }
}  

