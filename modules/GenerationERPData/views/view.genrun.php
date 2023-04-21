<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class GenerationERPDataViewGenRun extends SugarView
{
    public function display()
    {
        global $app_list_strings;
        $gen = new GenerationERPData();
        $data = $gen->genRun();
        $this->ss->assign('APP_LIST', $app_list_strings);
        $this->ss->assign('data' , $data);
        $this->ss->display('modules/GenerationERPData/tpls/genRun.tpl');
    }
}
