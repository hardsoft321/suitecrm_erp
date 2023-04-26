<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class GenerationERPDataViewGen extends SugarView
{

    public function preDisplay() {
        parent::preDisplay();
    }

    public function display()
    {
        global $mod_strings;
        global $app_strings;
        $this->ss->assign('MOD', $mod_strings);
        $this->ss->assign('APP', $app_strings);
        $this->ss->assign("JAVASCRIPT", get_set_focus_js());
        $query = "SELECT id, CONCAT_WS(' ', first_name, last_name) as full_name
            FROM contacts
            WHERE deleted = 0";
        $res = $GLOBALS['db']->query($query);
        $users = [];
        while($row = $GLOBALS['db']->fetchByAssoc($res)) {
            $users[$row['id']] = $row['full_name'];
        }
        $this->ss->assign("CURRENT_CONTACTS", $users);

        $query = "SELECT id, CONCAT_WS(': ', part_number, name) as name FROM aos_products WHERE deleted = 0";
        $query .= " ORDER BY date_entered DESC LIMIT 500"; //TODO: Нужно сделать динамическую подгрузку
        $res = $GLOBALS['db']->query($query);
        $products = ['' => ''];
        while($row = $GLOBALS['db']->fetchByAssoc($res)) {
            $products[$row['id']] = $row['name'];
        }
        $this->ss->assign("SELECT_PRODUCTS", $products);

        $this->ss->display('modules/GenerationERPData/tpls/genView.tpl');

        $javascript = new javascript();
        $javascript->setFormName("GenerationERPData");
        $javascript->addFieldGeneric("count_gen_orders", "int", $mod_strings['LBL_MAX_COUNT_GEN_ORDERS'], true, "");
        echo $javascript->getScript();
    }
}
