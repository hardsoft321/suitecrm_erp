<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

$manifest = array(
    'name' => 'hs321_erp_forms',
    'acceptable_sugar_versions' => array(),
    'acceptable_sugar_flavors' => array('CE'),
    'author' => 'Leon.V.Nikitin (nlv@lab321.com)',
    'description' => 'Добавление ERP функционала в SuiteCRM',
    'is_uninstallable' => true,
    'published_date' => '2023-03-15',
    'type' => 'module',
    'version' => '0.0.1',
  'dependencies' => array(
    array(
      'id_name' => 'hs321_erp',
      'version' => '0.0',
    ),
  ),
);
$installdefs = array(
    'id' => 'hs321_erp_forms',
    'copy' => array (
        array (
            'from' => '<basepath>/source/copy',
            'to' => '.',
        ),
    ),
);
