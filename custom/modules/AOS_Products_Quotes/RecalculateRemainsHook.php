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


}
