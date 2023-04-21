<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */

if (! defined('sugarEntry') || ! sugarEntry)
    die('Not A Valid Entry Point');

class GenerationERPDataController extends SugarController {

    public function action_GenView() {
        $this->view = 'gen';
    }

    public function action_GenRun() {
        $this->view = 'genrun';
        

    }
}
?>