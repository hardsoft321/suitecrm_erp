<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class GenerationERPData
{
    var $object_name = 'GenerationERPData';
    var $module_dir = 'GenerationERPData';
    var $module_name = 'GenerationERPData';
    var $disable_vardefs = true;

    var $numbers = array('0','1','2','3','4','5','6','7','8','9');
    var $current_user_id = null;
    var $metadataFile = null;
    var $view_form = 'edit';
    var $gender;
    var $current_language;
    var $id_prefix = 'erpgenr';
    var $name = '';
    var $city = '';
    var $state = '';
    var $street = '';
    var $postalcode = '';
    var $country = '';
    var $orgf;
    var $orgf_ip = 'ИП';
    var $now_date_db;
    
    function genFields()//временно//__construct()
    {
        global $sugar_config;
        global $timedate;
        global $db;
        $this->gender = rand(0,1);
        $this->current_language = 'ru_ru';//in_array($sugar_config['default_language'], $this->supportDefaultLanguage()) ? $sugar_config['default_language'] : 'en_us';
        $this->now_date_db = $timedate->nowDb();
        $city_arr = $this->read_dict($this->current_language.'.city.txt');
        $city_r = count($city_arr)-1;
        $this->city = trim(trim($city_arr[rand(0,$city_r)]),"\n");

        $genNameLang = 'genName_'. $this->current_language;
        if (method_exists($this, $genNameLang))
            $this->name = $this->$genNameLang();
        else
            $this->name = $this->genName_ru_ru();

        
        $this->postalcode = $this->generateNumber(6);

        $this->country = $this->gen_address_country();

        $state = $this->read_dict($this->current_language.'.state.txt');
        if (!empty($state)) {
            $state_r = count($state)-1;
            $this->state = trim(trim($state[rand(0,$state_r)]),"\n");
        }

        $street = $this->read_dict($this->current_language.'.street.txt');
        $street_r = count($street)-1;
        $this->street = trim(trim($street[rand(0,$street_r)]),"\n");

        $this->orgf = $this->generate_org_form_name();
    }

    function ACLAccess($view,$is_owner='not_set',$in_group='not_set')
    {
        return true;
    }

    public function genRun() {
        global $db;
        global $sugar_config;
        global $timedate;
        $data = [];
        if ((int)$_POST["count_gen_orders"] < 1 || (int)$_POST["max_count_gen_products"] < 1 || (int)$_POST["count_gen_contacts"] < 1) {
            return ['error' => 'Incorrect value: count_gen_orders or max_count_gen_products or count_gen_contacts'];
        }
        if ($_POST['create_contracts'] == 'true' && !file_exists('modules/AOS_ERP/createContractFunction.php')) {
            return ['error' => 'File "modules/AOS_ERP/createContractFunction.php" not found'];
        } else {
            require_once 'modules/AOS_ERP/createContractFunction.php';
        }

        $count_gen_contacts = $_POST['new_contact'] == 'true' ? (int)$_POST['count_gen_contacts'] : 1;

        while($count_gen_contacts > 0) {
            $this->genFields();
            if ($_POST['new_contact'] == 'true') {
                $contact = $this->beanSave('Contacts');
                $account = $this->beanSave('Accounts');
                if ($contact->load_relationship('accounts'))
                    $contact->accounts->add($account->id);

                $password = !empty($sugar_config['erp']['genData']['password']) ? $sugar_config['erp']['genData']['password'] : '';
                $user_pass = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                $insert_fields = [
                    'l_users' => [
                        'name' => $contact->full_name,
                        'email' => $contact->email1,
                        'password' => $user_pass,
                        'verify_token' => create_guid(),
                        'suite_id' => $contact->id,
                        'created_at' => $this->now_date_db,
                        'updated_at' => $this->now_date_db
                    ]
                ];
                $res = $this->insertToDB($insert_fields);
                if ($res) {
                    //странные перекрестные ссылки l_users - contacts, ну ладно!
                    $l_user_id = $db->getOne("SELECT id FROM l_users WHERE email = '{$contact->email1}'"); //тк email UNIQUE
                    if (!empty($l_user_id)) {
                        $contact->l_user_id = $l_user_id;
                        $contact->save();
                    }

                    $insert_fields = [
                        'l_order' => [
                            'l_user_id' => $l_user_id,
                            'suite_user' => $contact->id,
                            'created_at' => $this->now_date_db,
                            'updated_at' => $this->now_date_db
                        ]
                    ];
                    $res = $this->insertToDB($insert_fields);
                }
            } else {
                if (empty($_POST['current_contacts'])) {
                    return ['error' => 'Empty value: Current contact'];
                }
                $current_contacts = htmlspecialchars($_POST['current_contacts']);
                $contact = BeanFactory::getBean('Contacts', $current_contacts);
                if (empty($contact->id)) {
                    return ['error' => 'Contact not found'];
                }
                if ($contact->load_relationship('accounts')) {
                    if (!empty($contact->accounts->getBeans())) {
                        $account = reset($contact->accounts->getBeans());
                    }
                }
            }

            $data[$count_gen_contacts]['Contacts'] = ['id' => $contact->id, 'name' => $contact->full_name];
            $data[$count_gen_contacts]['Accounts'] = ['id' => $account->id, 'name' => $account->name];

            $count_gen_orders = (int)$_POST["count_gen_orders"];

            if ($_POST['random_products'] != 'true' && is_array($_POST['select_products']) && count($_POST['select_products']) > 0) {
                $sql_end = "AND id IN ('".implode("', '", $_POST['select_products']) . "')";
            } else {
                $max_count_gen_products = (int)$_POST["max_count_gen_products"];
                $max_count_gen_products = rand(1, $max_count_gen_products);
                $sql_end = "ORDER BY RAND() LIMIT {$max_count_gen_products}";
            }
            $enable_groups = $sugar_config['aos']['lineItems']['enableGroups'];
            $filling_fields = [];
            $number = $db->getOne('SELECT MAX(number) as number FROM aos_quotes');
            while($count_gen_orders > 0) {
                $number++;
                $sql = "SELECT * FROM aos_products WHERE deleted = 0 {$sql_end}";
                $res = $db->query($sql);
                $c = 1;
                $total_amt = 0;
                $total_amt_usdollar = 0;
                $subtotal_amount = 0;
                $subtotal_amount_usdollar = 0;
                $total_amount = 0;
                $total_amount_usdollar = 0;
                $currency_id = -99;
                $rand_date = date($timedate->get_db_date_time_format(), strtotime("+" . rand(0,7) . " days"));
                while($row = $db->fetchByAssoc($res)) {
                    $count_item = rand(1,3);
                    $currency_id = $row['currency_id'];
                    $filling_fields[$count_gen_orders]['AOS_Products_Quotes'][$c] = [
                        'name' => $row['name'],
                        'product_id' => $row['id'],
                        'item_description' => $row['description'],
                        'part_number' => $row['part_number'],
                        'currency_id' => $row['currency_id'],
                        'product_cost_price' => $row['cost'],
                        'product_cost_price_usdollar' => $row['cost_usdollar'],
                        'product_list_price' => $row['price'],
                        'product_list_price_usdollar' => $row['price_usdollar'],
                        'product_unit_price' => $row['price'],
                        'product_unit_price_usdollar' => $row['price_usdollar'],
                        'product_qty' => $count_item,
                        'product_discount' => 0,
                        'product_discount_usdollar' => 0,
                        'product_discount_amount' => 0,
                        'product_discount_amount_usdollar' => 0,
                        'discount' => 'Percentage',
                        'vat_amt' => 0,
                        'vat_amt_usdollar' => 0,
                        'product_total_price' => $count_item * $row['price'],
                        'product_total_price_usdollar' => $count_item * $row['price_usdollar'],
                        'vat' => 0,
                        'parent_type' => 'AOS_Quotes',
                        'wip_status' => 'draft',
                        'type_inout' => 'out',
                    ];
                    $c++;
                    $total_amt += $count_item * $row['price'];
                    $total_amt_usdollar += $count_item * $row['price_usdollar'];
                    $subtotal_amount += $count_item * $row['price'];
                    $subtotal_amount_usdollar += $count_item * $row['price_usdollar'];
                    $total_amount += $count_item * $row['price'];
                    $total_amount_usdollar += $count_item * $row['price_usdollar'];

                }
                $filling_fields[$count_gen_orders]['AOS_Quotes'] = [
                    'name' => $number,
                    'number' => $number,
                    'billing_account_id' => $account->id,
                    'billing_contact_id' => $contact->id,
                    'total_amt' => $total_amt,
                    'total_amt_usdollar' => $total_amt_usdollar,
                    'subtotal_amount' => $subtotal_amount,
                    'subtotal_amount_usdollar' => $subtotal_amount_usdollar,
                    'total_amount' => $total_amount,
                    'total_amount_usdollar' => $total_amount_usdollar,
                    'order_id' => $number,
                    'currency_id' => $currency_id, //TODO
                    'subtotal_tax_amount_usdollar' => '0.000000',
                    'approval_status' => 'Approved',
                    'term' => 'Net 15',
                    'discount_amount' => '0.000000',
                    'discount_amount_usdollar' => '0.000000',
                    'tax_amount' => '0.000000',
                    'tax_amount_usdollar' => '0.000000',
                    'shipping_amount' => '0.000000',
                    'shipping_amount_usdollar' => '0.000000',
                    'shipping_tax' => '0.0',
                    'shipping_tax_amt' => '0.000000',
                    'shipping_tax_amt_usdollar' => '0.000000',
                ];
                if ($enable_groups) {
                    $filling_fields[$count_gen_orders]['AOS_Line_Item_Groups'] = [
                        'subtotal_tax_amount_usdollar' => '0.000000',
                        'parent_type' => 'AOS_Quotes',
                        'currency_id' => '-99',
                        'created_by' => 1,
                        'modified_user_id' => 1,
                        'deleted' => 0,
                        'discount_amount' => '0.000000',
                        'discount_amount_usdollar' => '0.000000',
                        'tax_amount' => '0.000000',
                        'tax_amount_usdollar' => '0.000000',
                        'number' => '1',
                        'total_amt' => $total_amt,
                        'total_amt_usdollar' => $total_amt_usdollar,
                        'subtotal_amount' => $subtotal_amount,
                        'subtotal_amount_usdollar' => $subtotal_amount_usdollar,
                        'total_amount' => $total_amount,
                        'total_amount_usdollar' => $total_amount_usdollar,
                        'currency_id' => $currency_id,
                    ];
                }
                $count_gen_orders--;
            }
            $c_quote = 0;
            foreach($filling_fields as $filling_field) {
                $c_quote++;
                $AOS_Quotes = new AOS_Quotes();
                foreach($filling_field['AOS_Quotes'] as $field => $value) {
                    $AOS_Quotes->$field = $value;
                }
                $AOS_Quotes->save();
                $data[$count_gen_contacts]['AOS_Quotes'][$c_quote] = ['id' => $AOS_Quotes->id, 'name' => $AOS_Quotes->name];
                $AOS_Line_Item_Groups = new AOS_Line_Item_Groups();
                foreach ($filling_field['AOS_Line_Item_Groups'] as $field => $value) {
                    $AOS_Line_Item_Groups->$field = $value;
                }
                $AOS_Line_Item_Groups->parent_id = $AOS_Quotes->id;
                $AOS_Line_Item_Groups->save();
                $c_products_quote = 0;
                foreach ($filling_field['AOS_Products_Quotes'] as $fields) {
                    $c_products_quote++;
                    $AOS_Products_Quotes = new AOS_Products_Quotes();
                    foreach ($fields as $field => $value) {
                        $AOS_Products_Quotes->$field = $value;
                    }
                    $AOS_Products_Quotes->parent_id = $AOS_Quotes->id;
                    $AOS_Products_Quotes->group_id = $AOS_Line_Item_Groups->id;
                    $AOS_Products_Quotes->save();
                }
                $contract_id = createContract($AOS_Quotes->id, new DateTime($rand_date));
                $data[$count_gen_contacts]['AOS_Contracts'][$c_quote] = ['id' => $contract_id, 'name' => $AOS_Quotes->name];
            }
            $count_gen_contacts--;
        }
        return $data;
    }

    /**
     * @param
     */
    public function beanSave($module_name)
    {
        $bean = BeanFactory::newBean($module_name);
        $funcRun = "beanSave" . $bean->module_name;
        if(!method_exists($this, $funcRun)) {
            $funcRun = "beanSaveDefault";
        }
        return $this->$funcRun($bean);
    }

    /**
     * создание или редактирование контакта
     * @param  $contact bean контакта
     */
    function beanSaveContacts($contact) {
        $contact = $this->fillingFieldsModule($contact);
        $contact->contact_active_status = '10';
        $contact->save();
        return $contact;
    }

    /**
     * создание или редактирование контрагентра
     * @param  $account bean контрагентра
     * @param  $action
     */
    function beanSaveAccounts($account) {
        $account = $this->fillingFieldsModule($account, array('name'));
        $account->name = $this->name;
        $account->save();
        return $account;
    }

    /**
     * создание или редактирование записи модуля
     * @param  $bean модуля
     */
    function beanSaveDefault($bean) {
        $bean = $this->fillingFieldsModule($bean, ['account_name']);
        $bean->save();
        return $bean;
    }

    /**
     * Заполняем рандомно поля модуля
     * @param  $bean бин модуля
     * @param  $currentUser пользователь, от которого создается запись
     * @param  $fields, массив дополниткльных полей для рандомного заполнения
     * @return $bean бин модуля
     */
    function fillingFieldsModule($bean, $fieldsRem = array(), $fieldsAdd = array()) {
        global $timedate;
        global $current_user;
        $app_list_strings = return_app_list_strings_language($this->current_language);
        $mod_strings = return_module_language($this->current_language, $bean->module_dir);
        $dateUser = $timedate->get_date_time_format($current_user);
        $minutes = rand(100,1000);
        if (empty($bean->id)) {
            $bean->new_with_id = true;
            $bean->id = $this->id_prefix.substr(create_guid(), -(36-strlen($this->id_prefix)));
        }
        $fields = array_merge($fieldsAdd, $this->getListFieldsForm($bean->module_name, $this->metadataFile));
        foreach ($fields as $field) {
            if (in_array($field, $fieldsRem))
                continue;
            $func = 'gen_'.$field;
            if(method_exists($this, $func)){
                $bean->$field = $this->$func();
                continue;
            }
            switch ($bean->field_name_map[$field]['type']) {
                case 'date':
                case 'datetime':
                case 'datetimecombo':
                    $bean->$field = date($dateUser, strtotime("+". $minutes." minutes"));
                    break;
                case 'enum':
                case 'dynamicenum':
                case 'multienum':
                    $enum = array();
                    if (!empty($bean->field_name_map[$field]['options']) && !empty($app_list_strings[$bean->field_name_map[$field]['options']])) {
                        $enum = array_keys(array_diff($app_list_strings[$bean->field_name_map[$field]['options']], array('' => '')));
                        $bean->$field = $enum[array_rand($enum)];
                    }
                    else {
                        $label = $mod_strings[$bean->field_name_map[$field]['vname']];
                        $bean->$field = $label[StrLen($label)-1] == ':' ? substr($label, 0, -1) : $label;
                    }
                    break;
                case 'parent':
                    /* $parent = $bean->field_name_map[$bean->field_name_map[$field]['type_name']]['name'];
                    if (!empty($parent)){
                        if (!empty($bean->field_name_map[$field]['options'])) {
                            $parent_val = array_keys(array_diff($app_list_strings[$bean->field_name_map[$field]['options']], array('' => '')));
                            for ($i=0; $i < 10; $i++) { //если не может найти parent_id, повторяет еще раз
                                $parent_module = $parent_val[array_rand($parent_val)];
                                $parent_bean = BeanFactory::newBean($parent_module);
                                if (!empty($parent_bean)) {
                                    $parent_id = $this->getModuleRecordList($parent_bean->module_name, $bean->module_name, $bean->id);
                                    $parent_bean->retrieve($parent_id);
                                    if (!empty($parent_id)){
                                        $id_field = $bean->field_name_map[$field]['id_name'];
                                        if (!empty($id_field)) {
                                            $bean->$parent = $parent_module;
                                            $bean->$id_field = $parent_id;
                                            $bean->$field = $parent_bean->name;
                                        }
                                    }
                                }
                                if (!empty($parent_id))
                                    break;
                            }
                        }
                    } else {
                        if (empty($bean->name))
                            $bean->name = $this->now_date;
                    } */
                    break;
                case 'relate':
                    /* $relate = $bean->field_name_map[$bean->field_name_map[$field]['id_name']]['name'];
                    if ($relate == 'assigned_user_id') {
                        if (empty($bean->$relate)) {
                            $bean->$relate = $this->current_user_id;
                        }
                    } else {
                        if(!empty($bean->field_name_map[$field]['module']))
                            $parent_bean = BeanFactory::newBean($bean->field_name_map[$field]['module']);
                        elseif (!empty($bean->field_name_map[$bean->field_name_map[$field]['id_name']]['module']))
                            $parent_bean = BeanFactory::newBean($bean->field_name_map[$bean->field_name_map[$field]['id_name']]['module']);
                        if (empty($parent_bean))
                            continue 2;
                        $parent_id = $this->getModuleRecordList($parent_bean->module_name, $bean->module_name, $bean->id);
                        $id_field = $bean->field_name_map[$field]['id_name'];
                        if (empty($parent_id) && !empty($bean->$id_field))
                            continue 2;
                        if(!empty($parent_id))
                            $parent_bean->retrieve($parent_id);
                        $bean->$id_field = $parent_id;
                        $bean->$field = $parent_bean->name;
                    } */
                    break;
                case 'int':
                case 'double':
                case 'currency':
                case 'decimal':
                case 'float':
                        $count = 10;
                        $bean->$field = (isset($bean->field_name_map[$field]['len']) && $bean->field_name_map[$field]['len'] < $count) ?
                            $this->generateNumber($bean->field_name_map[$field]['len']) :
                            $this->generateNumber($count);
                    break;
                case 'text':
                case 'varchar':
                    $label = $mod_strings[$bean->field_name_map[$field]['vname']];
                    $bean->$field = $label[StrLen($label)-1] == ':' ? substr($label, 0, -1) : $label;
                    break;
                case 'phone':
                    $bean->$field = $this->generate_phone();
                    break;
                case 'bool':
                    $bean->$field = rand(0,1);
                    break;
                case 'url':
                    $bean->$field = $this->genSimbolMask('******.com', false);
                    break;
                default:
                    break;
            }
        }
        return $bean;
    }

    /**
    * Функции для генерации отдельных полей
    */
    function gen_first_name() {
        $fields = $this->genFIO($this->gender);
        return $fields['first_name'];
    }
    function gen_last_name() {
        $fields = $this->genFIO($this->gender);
        return $fields['last_name'];
    }
    function gen_passport_serial() {
        return rand(50,99).' '.rand(10,99);
    }
    function gen_passport_number() {
        return $this->generateNumber(6);
    }
    function gen_passport_where_issued() {
        $city = $this->read_dict($this->current_language.'.city.txt');
        $city_r = count($city)-1;
        $n_account = trim(trim($city[rand(0,$city_r)]),"\n");
        return ($this->current_language == 'ru_ru' ? "УФМС России по г. " : '') . $n_account;
    }
    function gen_inn() {
        return str_pad($this->generateNumber(10)+15,(rand(0,1) == 1 ? 12 : 10),"0",STR_PAD_LEFT);
    }
    function gen_kpp() {
        return str_pad($this->generateNumber(10)+21,9,"0",STR_PAD_LEFT);
    }
    function gen_ogrn() {
        return str_pad($this->generateNumber(10)+35,($this->orgf['val'] == $this->orgf_ip ? 15 : 13),"0",STR_PAD_LEFT);
    }
    function gen_email1() {
        return $this->genSimbolMask('*****@***.***', false);
    }
    function gen_billing_address_street() {
        return  $this->street;
    }
    function gen_billing_address_city() {
        return $this->city;
    }
    function gen_billing_address_state() {
        return $this->state;
    }
    function gen_billing_address_postalcode() {
        return $this->postalcode;
    }
    function gen_billing_address_country() {
        return $this->country;
    }
    function gen_shipping_address_street() {
        return  $this->street;
    }
    function gen_shipping_address_city() {
        return $this->city;
    }
    function gen_shipping_address_state() {
        return $this->state;
    }
    function gen_shipping_address_postalcode() {
        return $this->postalcode;
    }
    function gen_shipping_address_country() {
        return $this->country;
    }
    function gen_primary_address_street() {
        return  $this->street;
    }
    function gen_primary_address_city() {
        return $this->city;
    }
    function gen_primary_address_state() {
        return $this->state;
    }
    function gen_primary_address_postalcode() {
        return $this->postalcode;
    }
    function gen_primary_address_country() {
        return $this->country;
    }
    function gen_alt_address_street() {
        return  $this->street;
    }
    function gen_alt_address_city() {
        return $this->city;
    }
    function gen_alt_address_state() {
        return $this->state;
    }
    function gen_alt_address_postalcode() {
        return $this->postalcode;
    }
    function gen_alt_address_country() {
        return $this->country;
    }
    function gen_employees() {
        return $this->generateNumber(8);
    }

    /**
     * Получаем список полей модуля в editviewdefs.
     * @param  $module_name имя модуля
     * @param  $view_form $view форма edit,detail
     * @return массив полей
     */
    function getListFieldsForm($module_name, $metadataFile = null) {
        require_once('include/MVC/View/SugarView.php');
        $this->module = $module_name;
        $view = strtolower($this->view_form);
        $v = new SugarView(null,array());
        $v->type = $view;
        $v->module = $module_name;
        $fullView = ucfirst($view) . 'View';
        if (empty($metadataFile))
            $metadataFile = $v->getMetaDataFile();
        if (empty($metadataFile))
            return array();
        require($metadataFile);
        $results = $viewdefs[$module_name][$fullView];
        $fieldsEditForm = array();
        if (!empty($results['panels'])) {
            foreach ($results['panels'] as $fields_panel) {
                foreach ($fields_panel as $fields) {
                    foreach ($fields as $field) {
                        if (is_array($field)) {
                            if (empty($field['hideLabel']) && !empty($field['name']))
                                $fieldsEditForm[] = $field['name'];
                            elseif (isset($field['type']) && $field['type'] == 'address') {
                                $addr = $this->getAddressFields($module_name, $field);
                                if (!empty($addr))
                                    $fieldsEditForm = array_merge($fieldsEditForm, $addr);
                            }
                        }
                        else
                            if (!empty($field))
                                $fieldsEditForm[] = $field;
                    }
                }
            }
        }
        return $fieldsEditForm;
    }

    /**
     * Данные с файла в массив
     * @param  $filename имя файла
     * @return сгенерированный массив
     */
    function read_dict($filename) {
        $file = __DIR__ .'/directories/'.$filename;
        if (!file_exists($file))
            return '';
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if(!$lines)
            return '';
        return $lines;
    }

     /**
     * генерация номера телефона
     * @return сгенерированный номер
     */
    function generate_phone () {
        $phone = '('.$this->generateNumber(4).')'.$this->generateNumber(3).'-'.$this->generateNumber(3);
        return $phone;
    }

    /**
     * генерирует ФИО
     * @param  $gender - 'Mr.'(1) || 'Ms.'(1)
     * @return сгенерированный массив имени фамилии и отчества
     */
    function genFIO ($gender = 0) {
        $mfn = $this->read_dict($this->current_language.'.first_names_men.txt');
        $mln = $this->read_dict($this->current_language.'.last_names_men.txt');
        $msn = $this->read_dict($this->current_language.'.second_names_men.txt');
        $wfn = $this->read_dict($this->current_language.'.first_names_women.txt');
        $wln = $this->read_dict($this->current_language.'.last_names_women.txt');
        $wsn = $this->read_dict($this->current_language.'.second_names_women.txt');
        $cmf = count($mfn)-1; //TODO: проверка на пустоту
        $cml = count($mln)-1;
        $cms = count($msn)-1;
        $cwf = count($wfn)-1;
        $cwl = count($wln)-1;
        $cws = count($wsn)-1;
        if ($gender == 0) {
            $fname = trim(trim($mfn[rand(0,$cmf)]),"\n");
            $lname = trim(trim($mln[rand(0,$cml)]),"\n");
            $sname = !empty($msn) ? trim(trim($msn[rand(0,$cms)]),"\n") : '';
        } else {
            $fname = trim(trim($wfn[rand(0,$cwf)]),"\n");
            $lname = trim(trim($wln[rand(0,$cwl)]),"\n");
            $sname = !empty($wsn) ? trim(trim($wsn[rand(0,$cws)]),"\n") : '';
        }
        return array ('last_name' => $lname, 'first_name' => $fname, 'patronymic' => $sname);
    }

    function supportDefaultLanguage() {
        $supportLang = [
            'ru_ru',
            'en_us',
            'de_DE',
        ];
        return $supportLang;
    }

        /**
     * генерация числа
     * @param $count количество цифр
     * @return сгенерированное число
     */
    function generateNumber($count) {
        $numeric = '';
        for ($c=0; $c < $count; $c++) {
            $numeric .= array_rand($this->numbers);
        }
        return $numeric;
    }

    /**
     * Получает запись модуля в той же группе, что и текущая запись текущего модуля,
     *     либо в группе текущего пользователя
     * @param  $getModule нужный модуль
     * @param  $currenModule текущий модуль
     * @param  $currentModuleId id текущего записи
     * @return id записи нужного модуля
     */
    function getModuleRecordList($getModule, $currenModule, $currentModuleId) {
        global $db;
        $sql = "SELECT id FROM {$getmodule} WHERE deleted = 0 ORDER BY RAND()";
        $getModuleId = $db->getOne($sql);
        return (!empty($getModuleId)) ? $getModuleId : '';
    }

    /**
     * Проверяем, есть ли у записи группы
     * @param  $module модуль
     * @param  $record id записи
     * @return если есть, true
     */
    function checkRecordingSecurityGroup($module, $record) {
        global $db;
        return $db->getOne("
            SELECT 1
            FROM securitygroups_records
            WHERE
                    deleted = 0
                AND module = '{$module}'
                AND record_id = '{$record}'
        ");
    }

    /**
     * Генерируем строку по маске
     * @param  $mask маска строки. Пример '***-***.**'.
     *               Звездочки заменяются, остальные символы остаются
     * @param  $onlyNumber Если true - только цифры, false - цифры + другие символы
     * @return $fields_arr массив полей с группой адреса
     */
    public function genSimbolMask($mask, $onlyNumber = true) {
        if ($onlyNumber)
            $chars = "1234567890";
        else
            $chars = "1234567890abcdefghijkmnopqrstuvwxyz";
        $max = StrLen($mask);
        $size = StrLen($chars) - 1;
        $gen = '';
        $c = 0;
        while($c < $max) {
            if ($mask[$c] == '*')
                $gen .= $chars[rand(0,$size)];
            else
                $gen .= $mask[$c];
            $c++;
        }
        return $gen;
    }

    /**
     * генерация name контрагента в зависимости от огрформы
     * @param  $orgf - оргформа
     * @return поле name
     */
    function genName_ru_ru() {
        $n_account = $this->orgf['fullname'] . ' ';
        if ($this->orgf['val'] == $this->orgf_ip)
            $n_account .= implode(" ", $this->genFIO());
        else {
            $abbreviation = $this->read_dict($this->current_language.'.abbreviation.txt');
            $abbreviation_r = count($abbreviation)-1;
            $n_account .= $this->city;
            for ($i = 0; $i <= rand(0,3); $i++) {
                $n_account .= trim(trim($abbreviation[rand(0,$abbreviation_r)]),"\n");
            }
        }
        return $n_account;
    }

     /**
     * генерация name контрагента в зависимости от огрформы
     * @param  $orgf - оргформа
     * @return поле name
     */
    function genName_en_us() {
        $name_gen = rand(0,1);
        if ($this->orgf['val'] == $this->orgf_ip) {
            $name = '';
            $count = rand(0,1);
            $i = 0;
            while ($i <= $count) {
                $full_name = $this->genFIO();
                if (!empty($name)) {
                    $name .= empty($name_gen) ? ' & ' : ', ';
                }
                $name .= $full_name['last_name'];
                $i++;
            }
            if (!empty($name_gen) || empty($count)) {
                $org_form = array('Company', 'Sons');
                $name .= ' & ' . $org_form[array_rand($org_form)];
            }
        } else {
            if (!empty($name_gen)) {
                $name = $this->city;
            } else {
                $full_name = $this->genFIO();
                $name = $full_name['last_name'];
            }
            $name .= $this->orgf['fullname'];
        }
        return $name;
    }

    /**
     * генерация name контрагента в зависимости от огрформы
     * @param  $orgf - оргформа
     * @return поле name
     */
    function genName_de_DE() {
        $name_gen = rand(0,1);
        if ($this->orgf['val'] == $this->orgf_ip) {
            $name = '';
            $full_name = $this->genFIO();
            $name .= $full_name['last_name'];
            $name .= $this->orgf['fullname'];
        } else {
            if (!empty($name_gen)) {
                $name = $this->city;
            } else {
                $full_name = $this->genFIO();
                $name = $full_name['last_name'];
            }
            $name .= $this->orgf['fullname'];
        }
        return $name;
    }

    /**
     * Получаем все поля адресов
     * @param  $module_name имя модуля
     * @param  $field поле адреса
     * @return $fields_arr массив полей с группой адреса
     */
    function getAddressFields($module_name, $field) {
        $bean = BeanFactory::newBean($module_name);
        $fields_arr = array();
        if (!empty($bean->field_name_map[$field['name']]['group'])){
            foreach ($bean->field_name_map as $key => $value) {
                if (!empty($value['group']) && $value['group'] == $bean->field_name_map[$field['name']]['group']) {
                    $fields_arr[] = $key;
                }
            }
        }
        unset($bean);
        return $fields_arr;
    }


    function gen_address_country() {
        if ($this->current_language == 'ru_ru')
            return 'Россия';
            if ($this->current_language == 'de_DE')
            return 'BRD';
        $country = array(
            'USA',
            'AUS',
            'GBR',
            'DEU',
        );
        return $country[array_rand($country)];
    }

    /**
     * генерация имени по огр формы (ВРЕМЕННО)
     * @return массив
     */
    function generate_org_form_name() {
        global $app_list_strings;
        $org_forms = $this->read_dict($this->current_language.'.org_form.txt');
        $c = 0;
        foreach ($org_forms as $org_form) {
            $form = explode("^" , $org_form);
            //if(isset($app_list_strings['org_form_dom'])) {
                $orgf[$c]['name'] = $form[1];
                $orgf[$c]['fullname'] = $form[2];
                $orgf[$c]['val'] = $form[1];//!empty($app_list_strings['org_form_dom'][$form[0]]) ? $app_list_strings['org_form_dom'][$form[0]] : $form[0];
                /* if(isset($app_list_strings['org_form_dom'][$form[0]]))
                    $orgf[$c]['val'] = $app_list_strings['org_form_dom'][$form[0]];
                else {
                    $org_form_dom = $this->getValueInDom($orgf[$c]['name'], 'org_form_dom');
                    $orgf[$c]['val'] = !empty($org_form_dom) ? $org_form_dom : $form[0];
                } */
            //}
            $c++;
        }
        if ($this->current_language == 'en_us' || $this->current_language == 'de_DE') {
            if (rand(0, 10) > 3) { //почаще вероятность выпадения Sole proprietorship(ИП)
                $abbreviation = $this->read_dict($this->current_language.'.abbreviation.txt');
                $c = 1;
                foreach ($abbreviation as $abbr) {
                    $c++;
                    $orgf[] = array(
                        'val' => $c,
                        'name' => $abbr,
                        'fullname' => $abbr,
                    );
                }
            }
        }
        if (empty($orgf))
            return '';
        return $orgf[array_rand($orgf)];
    }

    private function insertToDB($fields)
    {
        global $db;
        foreach ($fields as $table => $values) {
            $sql = $db->query("SHOW TABLES LIKE '{$table}'");
            if (!$db->fetchByAssoc($sql)) {
                return false;
            }
            $sql = "INSERT INTO {$table} (".implode(array_keys($values), ',').") VALUES ('".implode(array_values($values), "','")."')";
            return $db->query($sql, true);
        }
        
    }
}
?>
