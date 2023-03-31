<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

class RecalculateRemainsHook {

    function before_save ($bean, $event, $arguments) {
        if ($bean->deleted) {
            throw new Exception ("before save for deleted record!");
        }

        if ($bean->parent_type !== 'AOS_Quotes') return;

        if ($bean->product_id === '0') return;

        if ($bean->product_qty === 0) return;

        if (!$bean->fetched_row) return;

        $recalc = false;
        if ($bean->fetched_row['wip_status'] === 'draft' &&  $bean->wip_status === 'plan') {
            $recalc = true;
            $factor = $bean->type_inout == 'in' ? 1 : -1;
            $plan = $bean->product_qty;
            $fact = 0;
        } else if ($bean->fetched_row['wip_status'] === 'plan' &&  $bean->wip_status === 'fact') {
            $recalc = true;
            $factor = $bean->type_inout == 'in' ? 1 : -1;
            $plan = 0;
            $fact = $bean->product_qty;
        }

        if ($recalc) {
            if ($prod = BeanFactory::getBean('AOS_Products', $bean->product_id)) {
                $prod->qty_plan += $factor * $plan;
                $prod->qty_fact += $factor * $fact;
                $prod->save();
            }
        }
    }

    static function recalculatePlan ($product_id) {
        global $db;

        $plan = $db->getOne("
          SELECT SUM(CASE type_inout 
                     WHEN 'in' THEN 1 
                     WHEN 'out' THEN -1
                     ELSE 0
                     END * product_qty) qty
          FROM aos_products_quotes
          WHERE deleted = 0
            AND wip_status = 'plan'
            AND product_id = '{$product_id}'
          ",
          false,
          "Cannot calculate plan remain for '{$product_id}' product"
        );

        if (!$plan) $plan = 0;

        $preferences = [];
        while ($row = $db->fetchByAssoc($result)) {
            $category = $row['category'];
            $preferences[$category] = unserialize(base64_decode($row['contents']));
        }

        if ($prod = BeanFactory::getBean('AOS_Products', $product_id)) {
            $prod->qty_plan = $plan + $prod->qty_fact;
            $prod->save();
        }
    }

}
