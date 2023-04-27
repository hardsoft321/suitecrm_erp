<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

 function createPurchaseContracts ($seller_id, $product_id = null) {
    global $timedate, $sugar_config, $db;

    require_once('modules/AOS_Contracts/AOS_Contracts.php');

    if (!isset($sugar_config['erp']['module'])) {
        throw new Exception ("ERP module not set!");
    }
    $erp_module = $sugar_config['erp']['module'];   
    
    $payment_product = $db->fetchByAssoc($db->retrieve(BeanFactory::newBean('AOS_Products'), ['part_number' => $sugar_config['erp']['quote']['payment_part_number']]));

    $productSql = $product_id ? " AND prod.id = '$product_id'" : "";

    $sql = "
    SELECT 
      prod.id,
      prod.name,
      prod.part_number,
      prod.cost, 
      prod.qty_plan
    FROM aos_products prod
    WHERE deleted = 0
      AND qty_plan < 0
      AND prod.type != 'Money'
      $productSql
    ";
    $sqlres = $db->query($sql, false);
    $res = [];
    $accdateField = $db->convert($db->convert('pos.accdate', 'date_format',array('%Y-%m-%d')), 'date');
    $nowDbDate = $timedate->nowDbDate();

    while ($prodRow = $db->fetchByAssoc($sqlres)) {
        $sql2 = "
            SELECT 
              $accdateField accdate
            FROM aos_products_quotes pos
            WHERE pos.deleted = 0
              AND pos.product_id = '{$prodRow['id']}'
              AND pos.type_inout = 'out'
              AND pos.parent_type = '{$erp_module}'
            ORDER BY pos.accdate  
        ";

        $outAccdate = $db->getOne($sql2);
        if ($outAccdate === FALSE) $ourAccdate = $nowDbDate;
        $outAccdate = new DateTime($outAccdate);
        $inAccdate = clone $outAccdate;

        $inAccdate = $inAccdate->modify($sugar_config['erp']['supplying']['income_accdate_modify']);
        
        $now = new DateTime();
        if ($inAccdate <= $now)  $inAccdate = $now->modify('+1 day');

        $total_amount = -$prodRow['qty_plan']*$prodRow['cost'];

        $contract = BeanFactory::newBean('AOS_Contracts');
        $contract->name = "Закуп товара \"{$prodRow['name']}\"";
        $contract->total_contract_value = format_number($total_amount);
        $contract->contract_account_id = $seller_id;

        $contract->total_amt = $total_amount;
        $contract->subtotal_amount = $total_amount;
        $contract->discount_amount = 0;
        $contract->tax_amount = 0;
        $contract->shipping_amount = $total_amount;
        $contract->shipping_tax = 0;
        $contract->shipping_tax_amt = 0;
        $contract->total_amount = $total_amount;
        $contract->total_contact_value = $total_amount;
        $contract->currency_id = $payment_product['id'];

        $contract->save();

        $row = [];
        $row['id'] = '';
        $row['name'] = 'Покупка';
        $row['parent_id'] = $contract->id;
        $row['parent_type'] = 'AOS_Contracts';
        $row['total_amt'] = format_number($total_amount);
        $row['discount_amount'] = format_number(0);
        $row['subtotal_amount'] = format_number($total_amount);
        $row['tax_amount'] = format_number(0);
        $row['subtotal_tax_amount'] = format_number(0);
        $row['total_amount'] = format_number($total_amount);
        $group_income = BeanFactory::newBean('AOS_Line_Item_Groups');
        $group_income->populateFromRow($row);
        $group_income->save();

        $row = [];
        $row['id'] = '';
        $row['parent_id'] = $contract->id;
        $row['parent_type'] = 'AOS_Contracts';
        $row['product_cost_price'] = format_number($prodRow['cost']);
        $row['product_list_price'] = format_number($prodRow['cost']);
        $row['product_discount'] = format_number(0);
        $row['product_discount_amount'] = format_number(0);
        $row['product_unit_price'] = format_number($prodRow['cost']);
        $row['vat_amt'] = format_number(0);
        $row['vat'] = format_number(0);
        $row['product_total_price'] = format_number($total_amount);
        $row['product_qty'] = format_number(-$prodRow['qty_plan']);
        $row['group_id'] = $group_income->id;
        $row['product_id'] = $prodRow['id'];
        $row['part_number'] = $prodRow['part_number'];
        $row['name'] = $prodRow['name'];

        $row['type_inout'] = 'in';
        $row['wip_status'] = 'draft';
        $row['accdate'] = $timedate->asUser($inAccdate);

        $prod_contract = BeanFactory::newBean('AOS_Products_Quotes');
        $prod_contract->populateFromRow($row);
        $prod_contract->save();
        $prod_contract2 = (BeanFactory::newBean('AOS_Products_Quotes'))->retrieve($prod_contract->id);
        $prod_contract2->wip_status = 'plan';
        $prod_contract2->save();

        $row = [];
        $row['id'] = '';
        $row['name'] = 'Оплата';
        $row['parent_id'] = $contract->id;
        $row['parent_type'] = 'AOS_Contracts';
        $row['total_amt'] = format_number($total_amount);
        $row['discount_amount'] = format_number(0);
        $row['subtotal_amount'] = format_number($total_amount);
        $row['tax_amount'] = format_number(0);
        $row['subtotal_tax_amount'] = format_number(0);
        $row['total_amount'] = format_number($total_amount);
        $group_pay = BeanFactory::newBean('AOS_Line_Item_Groups');
        $group_pay->populateFromRow($row);
        $group_pay->save();

        $row = [];
        $row['id'] = '';
        $row['parent_id'] = $contract->id;
        $row['product_id'] = $payment_product['id'];
        $row['part_number'] = $payment_product['part_number'];
        $row['name'] = $payment_product['name'];
        $row['parent_type'] = 'AOS_Contracts';
        $row['product_cost_price'] = format_number($total_amount);
        $row['product_list_price'] = format_number(1);
        $row['product_discount'] = format_number(0);
        $row['product_discount_amount'] = format_number(0);
        $row['product_unit_price'] = format_number(1);
        $row['vat_amt'] = format_number(0);
        $row['vat'] = format_number(0);
        $row['product_total_price'] = format_number($total_amount);
        $row['product_qty'] = format_number($total_amount);
        $row['wip_status'] = 'draft';
        $row['type_inout'] = 'out';
        $row['accdate'] = $timedate->asUser($inAccdate);
        $row['group_id'] = $group_pay->id;


        $prod_contract = BeanFactory::newBean('AOS_Products_Quotes');
        $prod_contract->populateFromRow($row);
        $prod_contract->save();
        $prod_contract2 = (BeanFactory::newBean('AOS_Products_Quotes'))->retrieve($prod_contract->id);
        $prod_contract2->wip_status = 'plan';
        $prod_contract2->save();
    }
}
