<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

 require_once('include/formbase.php');

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

    $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : null;
    createPurchaseContracts($seller_id, $product_id);

    // handleRedirect('','AOS_Products');
    $return_id = isset($_REQUEST['return_id']) ? $_REQUEST['return_id'] : '';
    $return_module = isset($_REQUEST['return_module']) ? $_REQUEST['return_module'] : '';
    $return_action = isset($_REQUEST['return_action']) ? $_REQUEST['return_action'] : '';
    if (!$return_module && !$return_id) $return_module = 'AOS_Products';
    if (!$return_action) $return_action = $return_id ? 'DetailView' : 'index';
    $header_URL = "Location: index.php?module=$return_module&record=$return_id&action=$return_action";
    SugarApplication::headerRedirect($header_URL);
