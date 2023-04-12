<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

require_once('include/formbase.php');

if ((BeanFactory::newBean('AOS_Products'))->ACLAccess('view') 
  && isset($_REQUEST['product_id'])) {

    require_once("modules/AOS_ERP/RecalculateRemainsHook.php");

    RecalculateRemainsHook::recalculatePlan ($_REQUEST['product_id']);
}

handleRedirect($_REQUEST['return_id'], $_REQUEST['return_module']);