{*
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
*}
<div class="moduleTitle">
<h2>{$MOD.LBL_MODULE_TITLE}</h2>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="clear"></div></div>
<form name="GenerationERPData" enctype='multipart/form-data' method="POST" action="index.php" onSubmit="return (check_form('GenerationERPData'));">
<input type='hidden' name='action' value='GenRun'/>
<input type='hidden' name='module' value='GenerationERPData'/>
<input type='hidden' name='module' value='GenerationERPData'/>
<input type="hidden" name="return_module" value="GenerationERPData">
<input type="hidden" name="return_action" value="GenView">
<table width="100%" cellpadding="0" cellspacing="1" border="0" class="actionsContainer">
    <br>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view" style="margin-top:20px">
        <tr>
            <td  scope="row">{$MOD.LBL_NEW_CONTACT} {sugar_help text=$MOD.LBL_NEW_CONTACT_INFO} </td>
            <td>
                <input type="hidden" name='new_contact' value='false'>
                <input type='checkbox' name='new_contact' value='true' id='new_contact' onclick='hiddenSelectContactsFields()'>
            </td>
            <td  scope="row"></td>
            <td></td>
        </tr>
        <tr>
            <td scope="row" width='15%' nowrap>{$MOD.LBL_COUNT_GEN_CONTACTS}&nbsp;{sugar_help text=$MOD.LBL_COUNT_GEN_CONTACTS_INFO}</td>
            <td width='35%'>
                <input type="number" name='count_gen_contacts' id="count_gen_contacts" value='1' />
            </td>
        </tr>
        <tr>
            <td  scope="row">{$MOD.LBL_CURRENT_CONTACTS}: <span class="required">*</span>{sugar_help text=$MOD.LBL_CURRENT_CONTACTS_INFO} </td>
            <td>
                {html_options name="current_contacts" id="current_contacts" options=$CURRENT_CONTACTS}
            </td>
            <td  scope="row"></td>
            <td></td>
        </tr>
        <tr>
            <td scope="row" width='15%' nowrap>{$MOD.LBL_MAX_COUNT_GEN_ORDERS}&nbsp; <span class="required">*</span>{sugar_help text=$MOD.LBL_MAX_COUNT_GEN_ORDERS_INFO}</td>
            <td width='35%'>
                <input type="number" name='count_gen_orders' value='1' />
            </td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view" style="margin-top:20px">
        <tr>
            <td scope="row" width='15%' nowrap>{$MOD.LBL_RANDOM_PRODUCTS} {sugar_help text=$MOD.LBL_RANDOM_PRODUCT_INFO} </td>
            <td width='35%'>
                <input type="hidden" name='random_products' value='false'>
                <input type='checkbox' name='random_products' value='true' id='random_products' onclick='hiddenSelectProductsFields()'>
            </td>
            <td  scope="row"></td>
            <td></td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
        <tr>
            <td scope="row" width='15%' nowrap>{$MOD.LBL_MAX_COUNT_GEN_PRODUCTS}&nbsp; <span class="required">*</span>{sugar_help text=$MOD.LBL_MAX_COUNT_GEN_PRODUCTS_INFO}</td>
            <td width='35%'>
                <input type="number" name='max_count_gen_products' id='max_count_gen_products' value='1' />
            </td>
        </tr>
        <tr>
            <td  scope="row" width='15%' nowrap>{$MOD.LBL_SELECT_PRODUCTS}: <span class="required">*</span>{sugar_help text=$MOD.LBL_SELECT_PRODUCTS_INFO} </td>
            <td>
                {html_options id="select_products" name="select_products[]" size="6" select2="" style="width: 100%" multiple="true" options=$SELECT_PRODUCTS}
            </td>
            <td  scope="row"></td>
            <td></td>
        </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-bottom: 2px;">
           <td>
                <input title="{$MOD.LBL_RUN_BUTTON_TITLE}" id="gen_run_button" class="button"  type="submit" name="run" value=" {$MOD.LBL_GENRUN_BUTTON_LABEL}  " >
                &nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}" id="gen_run_cancel_button" onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " >
            </td>
        </td>
    </tr>
</table>
</form>
<script type='text/javascript'>
SUGAR.util.doWhen('document.readyState == "complete" && (typeof validate != "undefined")', function () {ldelim}
    $('[class*="select2 select2-container select2-container"]').css('width','100%')
    $(function(){ldelim}
        $("#select_products").select2();
    {rdelim});
    hiddenSelectContactsFields();
    hiddenSelectProductsFields();
{rdelim});
function hiddenSelectContactsFields() {ldelim}
    if ($('#new_contact').is(':checked')) {ldelim}
        $('#current_contacts').closest('tr').hide();
        $('#count_gen_contacts').closest('tr').show();
    {rdelim} else {ldelim}
        $('#current_contacts').closest('tr').show();
        $('#count_gen_contacts').closest('tr').hide();
    {rdelim}
{rdelim}

function hiddenSelectProductsFields() {ldelim}
    if ($('#random_products').is(':checked')) {ldelim}
        $('#select_products').closest('tr').hide();
        $('#max_count_gen_products').closest('tr').show();
        removeFromValidate('GenerationERPData', 'select_products');
    {rdelim} else {ldelim}
        $('#select_products').closest('tr').show();
        $('#max_count_gen_products').closest('tr').hide();
        addToValidate('GenerationERPData', 'select_products', 'varchar', true, SUGAR.language.get('GenerationERPData','LBL_SELECT_PRODUCTS'));
    {rdelim}
{rdelim}
</script>
