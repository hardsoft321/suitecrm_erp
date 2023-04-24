<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Leon Nikitin <nlv@lab321.ru>
 * @package hs321_erp
 */

 function createContract ($quote_id, DateTime $quoteAccdate) {
    global $timedate, $sugar_config, $db;

    if (!$quoteAccdate) $quoteAccdate = $timedate->now();
    $prodAccdate = clone $quoteAccdate;
    $prodAccdate->modify($sugar_config['erp']['quote']['product_accdate_modify']);
    $paymentAccdate = clone $quoteAccdate;
    $paymentAccdate->modify($sugar_config['erp']['quote']['payment_accdate_modify']);    

    require_once('modules/AOS_Quotes/AOS_Quotes.php');
    require_once('modules/AOS_Contracts/AOS_Contracts.php');

    //Setting values in Quotes
    $quote = BeanFactory::newBean('AOS_Quotes');
    $quote->retrieve($quote_id);

 
    //Setting Contract Values
    $contract = BeanFactory::newBean('AOS_Contracts');
    $contract->name = $quote->name;
    $contract->assigned_user_id = $quote->assigned_user_id;
    $contract->total_contract_value = format_number($quote->total_amount);
    $contract->contract_account_id = $quote->billing_account_id;
    $contract->contact_id = $quote->billing_contact_id;
    $contract->opportunity_id = $quote->opportunity_id;
    $contract->start_date = $timedate->asUserDate($quoteAccdate);
    $contract->end_date = $timedate->asUserDate($prodAccdate);

    $contract->total_amt = $quote->total_amt;
    $contract->subtotal_amount = $quote->subtotal_amount;
    $contract->discount_amount = $quote->discount_amount;
    $contract->tax_amount = $quote->tax_amount;
    $contract->shipping_amount = $quote->shipping_amount;
    $contract->shipping_tax = $quote->shipping_tax;
    $contract->shipping_tax_amt = $quote->shipping_tax_amt;
    $contract->total_amount = $quote->total_amount;
    $contract->currency_id = $quote->currency_id;

    $contract->save();

    $group_id_map = array();

    //Setting Group Line Items
    $sql = "SELECT * FROM aos_line_item_groups WHERE parent_type = 'AOS_Quotes' AND parent_id = '".$quote->id."' AND deleted = 0";

    $result = $db->query($sql);
    while ($row = $db->fetchByAssoc($result)) {
        $old_id = $row['id'];
        $row['id'] = '';
        $row['parent_id'] = $contract->id;
        $row['parent_type'] = 'AOS_Contracts';
        if ($row['total_amt'] != null) {
            $row['total_amt'] = format_number($row['total_amt']);
        }
        if ($row['discount_amount'] != null) {
            $row['discount_amount'] = format_number($row['discount_amount']);
        }
        if ($row['subtotal_amount'] != null) {
            $row['subtotal_amount'] = format_number($row['subtotal_amount']);
        }
        if ($row['tax_amount'] != null) {
            $row['tax_amount'] = format_number($row['tax_amount']);
        }
        if ($row['subtotal_tax_amount'] != null) {
            $row['subtotal_tax_amount'] = format_number($row['subtotal_tax_amount']);
        }
        if ($row['total_amount'] != null) {
            $row['total_amount'] = format_number($row['total_amount']);
        }
        $group_contract = BeanFactory::newBean('AOS_Line_Item_Groups');
        $group_contract->populateFromRow($row);
        $group_contract->save();
        $group_id_map[$old_id] = $group_contract->id;
    }

    //Setting Line Items
    $sql = "SELECT * FROM aos_products_quotes WHERE parent_type = 'AOS_Quotes' AND parent_id = '".$quote->id."' AND deleted = 0";
    $result = $db->query($sql);
    $grand_total_price = 0;
    while ($row = $db->fetchByAssoc($result)) {
        if ($row['product_id']) {
            $grand_total_price += $row['product_total_price'];
        }
        
        $row['id'] = '';
        $row['parent_id'] = $contract->id;
        $row['parent_type'] = 'AOS_Contracts';
        if ($row['product_cost_price'] != null) {
            $row['product_cost_price'] = format_number($row['product_cost_price']);
        }
        $row['product_list_price'] = format_number($row['product_list_price']);
        if ($row['product_discount'] != null) {
            $row['product_discount'] = format_number($row['product_discount']);
            $row['product_discount_amount'] = format_number($row['product_discount_amount']);
        }
        $row['product_unit_price'] = format_number($row['product_unit_price']);
        $row['vat_amt'] = format_number($row['vat_amt']);
        $row['product_total_price'] = format_number($row['product_total_price']);
        $row['product_qty'] = format_number($row['product_qty']);
        $row['group_id'] = $group_id_map[$row['group_id']];

        if ($row['product_id']) {
            $row['type_inout'] = 'out';
            $row['wip_status'] = 'draft';
            $row['accdate'] = $timedate->asUser($prodAccdate);
        }        

        $prod_contract = BeanFactory::newBean('AOS_Products_Quotes');
        $prod_contract->populateFromRow($row);
        $prod_contract->save();
        if ($row['product_id']) {
            $prod_contract2 = (BeanFactory::newBean('AOS_Products_Quotes'))->retrieve($prod_contract->id);
            $prod_contract2->wip_status = 'plan';
            $prod_contract2->save();
        }
    }

    $row = [];
    $row['id'] = '';
    $row['name'] = 'Оплата';
    $row['parent_id'] = $contract->id;
    $row['parent_type'] = 'AOS_Contracts';
    $row['total_amt'] = format_number($grand_total_price);
    $row['discount_amount'] = format_number(0);
    $row['subtotal_amount'] = format_number($grand_total_price);
    $row['tax_amount'] = format_number(0);
    $row['subtotal_tax_amount'] = format_number(0);
    $row['total_amount'] = format_number($grand_total_price);
    $group_pay = BeanFactory::newBean('AOS_Line_Item_Groups');
    $group_pay->populateFromRow($row);
    $group_pay->save();    

    $payment_product = $db->fetchByAssoc($db->retrieve(BeanFactory::newBean('AOS_Products'), ['part_number' => $sugar_config['erp']['quote']['payment_part_number']]));

    $row = [];
    $row['id'] = '';
    $row['parent_id'] = $contract->id;
    $row['product_id'] = $payment_product['id'];
    $row['part_number'] = $payment_product['part_number'];
    $row['name'] = $payment_product['name'];
    $row['parent_type'] = 'AOS_Contracts';
    $row['product_cost_price'] = format_number($grand_total_price);
    $row['product_list_price'] = format_number(1);
    $row['product_discount'] = format_number(0);
    $row['product_discount_amount'] = format_number(0);
    $row['product_unit_price'] = format_number(1);
    $row['vat_amt'] = format_number(0);
    $row['product_total_price'] = format_number($grand_total_price);
    $row['product_qty'] = format_number($grand_total_price);
    $row['wip_status'] = 'draft';
    $row['type_inout'] = 'in';
    $row['accdate'] = $timedate->asUser($paymentAccdate);
    $row['group_id'] = $group_pay->id;


    $prod_contract = BeanFactory::newBean('AOS_Products_Quotes');
    $prod_contract->populateFromRow($row);
    $prod_contract->save();
    $prod_contract2 = (BeanFactory::newBean('AOS_Products_Quotes'))->retrieve($prod_contract->id);
    $prod_contract2->wip_status = 'plan';
    $prod_contract2->save();

    //Setting contract quote relationship
    require_once('modules/Relationships/Relationship.php');
    $key = Relationship::retrieve_by_modules('AOS_Quotes', 'AOS_Contracts', $GLOBALS['db']);
    if (!empty($key)) {
        $quote->load_relationship($key);
        $quote->$key->add($contract->id);
    }

    return $contract->id;
}
