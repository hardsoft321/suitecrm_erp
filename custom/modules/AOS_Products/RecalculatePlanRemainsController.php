<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

require_once('include/formbase.php');

//var_dump($_REQUEST);
//die();

if ((BeanFactory::newBean('AOS_Products'))->ACLAccess('view') 
  && isset($_REQUEST['product_id'])) {

    require_once("custom/modules/AOS_Products_Quotes/RecalculateRemainsHook.php");

    RecalculateRemainsHook::recalculatePlan ($_REQUEST['product_id']);
}

handleRedirect($_REQUEST['product_id'], 'AOS_Products');
