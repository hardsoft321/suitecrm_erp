<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

    global $timedate, $sugar_config, $db;

    if (!(ACLController::checkAccess('AOS_Contracts', 'edit', true))) {
        ACLController::displayNoAccess();
        die;
    }

    require_once('modules/AOS_ERP/createContractFunction.php');

    $contract_id = createContract($_REQUEST['record']);

    header('Location: index.php?module=AOS_Contracts&action=EditView&record='.$contract_id);
