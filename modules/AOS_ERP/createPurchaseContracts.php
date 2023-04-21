<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

    global $timedate, $sugar_config, $db;

    if (!isset($sugar_config['erp']['supplying']['seller_id'])) {
        throw new Exception ("ERP seller_id not set!");
    }
    $seller_id = $sugar_config['erp']['supplying']['seller_id'];  

    if (!(ACLController::checkAccess('AOS_Contracts', 'edit', true))) {
        ACLController::displayNoAccess();
        die;
    }

    require_once('modules/AOS_ERP/createPurchaseContractsFunction.php');

    $a = createPurchaseContracts($seller_id);

    $url = 'Location: index.php?module=AOS_Products';

    SugarApplication::headerRedirect($url);
