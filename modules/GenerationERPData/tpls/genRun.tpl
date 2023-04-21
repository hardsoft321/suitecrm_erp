{*
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Denis Antonenko <ads@lab321.ru>
*}
<div class="moduleTitle">
<h2>{$MOD.LBL_MODULE_TITLE}</h2>
<div class="clear"></div></div>
<form name="GenerationERPRun" method="POST" action="index.php">
<input type='hidden' name='action' value='GenRun'/>
<input type='hidden' name='module' value='GenerationERPData'/>
        {if !empty($data.error)}
            <div class="col-xs-12 col-sm-6 detail-view-row-item">
                <div class="col-xs-12 col-sm-4 label col-1-label">
                    {$MOD.LBL_ERROR}
                </div>
                <div class="col-xs-12 col-sm-8 detail-view-field" type="name" field="name">
                    <span class="sugar_field" id="error">{$data.error}</span>
                </div>
            </div>
        {else}
            {foreach from=$data item=lists}
            <table class="other view" style="margin-top:20px">
            <tbody>
                {foreach from=$lists item=field key=key}
                    {if $key == 'AOS_Quotes' || $key == 'AOS_Contracts'}
                       {foreach from=$field item=f}
                        <tr>
                            <td width="30%">{$APP_LIST.moduleList.$key}</td>
                            <td width="20%" scope="row">
                                <span class="link"></span>
                                <a href="index.php?module={$key}&action=DetailView&record={$f.id}">{$f.name}</a>
                            </td>
                        </tr>
                        {/foreach}
                    {else}
                    <tr>
                        <td width="30%">{$APP_LIST.moduleList.$key}</td>
                        <td width="20%" scope="row">
                            <span class="link"></span>
                            <a href="index.php?module={$key}&action=DetailView&record={$field.id}">{$field.name}</a>
                        </td>
                    </tr>
                    {/if}
                {/foreach}
            </tbody>
            {/foreach}
        {/if}
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-bottom: 2px;">
           <td>
                <input title="{$MOD.LBL_PREV_BUTTON_TITLE}" id="gen_prev_button" class="button" onclick="document.location.href='index.php?module=GenerationERPData&action=GenView'" class="button" type="button" name="prev" value=" {$MOD.LBL_PREV_BUTTON_LABEL}  " >
                &nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}" id="gen_run_cancel_button" onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " >
            </td>
        </td>
    </tr>
</table>
</form>
