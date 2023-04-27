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

    public function action_getProducts() {
        $this->view = '';
        header('Content-Type: application/json');
        global $db;
        if (empty($_GET['term'])) return;
        $term = $db->quote($_GET['term']);
        $query = "SELECT id, CONCAT_WS(': ', part_number, name) as name FROM aos_products WHERE deleted = 0 AND (part_number like '%{$term}%' OR name like '%{$term}%') ORDER BY date_entered DESC";
        $res = $GLOBALS['db']->query($query);
        $products = [];
        while($row = $GLOBALS['db']->fetchByAssoc($res)) {
            $products[] = ['id' => $row['id'], 'name' => $row['name']];
        }
        echo json_encode(
            array($products)
        );
    }
}
?>