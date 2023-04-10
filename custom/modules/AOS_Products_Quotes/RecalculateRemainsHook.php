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
        $factor = $bean->type_inout == 'in' ? 1 : -1;
        if ($bean->fetched_row['wip_status'] === 'draft' &&  $bean->wip_status === 'plan') {
            $recalc = true;
            $plan = $bean->product_qty;
            $fact = 0;

            $bean->qty_plan = self::getPlan ($bean->product_id, $bean->accdate)
                            + $factor * $bean->product_qty;
        } else if ($bean->fetched_row['wip_status'] === 'plan' &&  $bean->wip_status === 'fact') {
            $recalc = true;
            
            $plan = 0;
            $fact = $bean->product_qty;
            $bean->qty_plan = null;            
        }

        if ($recalc) {
            if ($prod = BeanFactory::getBean('AOS_Products', $bean->product_id)) {
                $prod->qty_plan += $factor * $plan;
                $prod->qty_fact += $factor * $fact;
                $prod->save();
                if ($bean->qty_plan !== null) $bean->qty_plan += $prod->qty_fact;
            }
        }
    }

    static function recalculatePlan ($product_id, $accdate = null) {
        global $db;

        if ($prod = BeanFactory::getBean('AOS_Products', $product_id)) {
            $plan = self::getPlan($product_id, $accdate);
            $prod->qty_plan = $plan + $prod->qty_fact;
            $prod->save();
        }
    }

    static function getPlan ($product_id, $accdate = null) {
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
          " . ($accdate == null ? "" : " AND accdate <= '$accdate'"),
          false,
          "Cannot calculate plan remain for '{$product_id}' product"
        );
        if (!$plan) $plan = 0;

        return $plan;
    }    

    static function getForecast ($product_id) {
        global $db;

        $accdate = $db->convert($db->convert('accdate', 'date_format',array('%Y-%m-%d')), 'date');

        $sql = "
          SELECT
            s.accdate,
            s.product_name,
            s.product_id,
            s.product_qty,
            coalesce(sum(s.product_qty) over (order by s.accdate, s.product_name, s.product_id 
            rows between unbounded preceding and current row), 
            0)  + s.qty_fact running_product_qty
          FROM (
            SELECT pos.accdate,
                   prod.name product_name,
                   prod.id product_id,
                   prod.qty_fact qty_fact,
                   SUM(CASE type_inout 
                   WHEN 'in' THEN 1 
                   WHEN 'out' THEN -1
                   ELSE 0
                   END * product_qty) product_qty
            FROM aos_products_quotes pos
            INNER JOIN aos_products prod ON prod.id = pos.product_id
            WHERE pos.deleted = 0
              AND pos.wip_status = 'plan'
              AND pos.product_id = '{$product_id}'
            GROUP BY pos.accdate, prod.name, prod.id, prod.qty_fact
          ) s    
          ORDER BY product_id, accdate
        ";

        $res = $db->query($sql, false, "Cannot get forecast data for '{$product_id}' product");
        $result = array ();
        while ($row = $db->fetchByAssoc($res)) {
            $result[] = $row;
        }

        return $result;

        if (!$plan) $plan = 0;

        return $plan;
    }    

}
