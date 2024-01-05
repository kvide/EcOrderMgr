<h3>{$mod->Lang('title_manual_process')}</h3>

<script type="text/javascript">
var pmt_id = "{$payment->get_id()}";
var pmt_method = "{$payment->get_method()}";
var have_cc_gateway = "{$have_cc_gateway}";
{literal}
jQuery(document).ready(function(){
  var val = jQuery('#pmt_method').val();
  jQuery('#ccinfo').hide();
  if( typeof(val) != 'string' || val == 'creditcard')
  {
    jQuery('#ccinfo').show();
  }

  jQuery('#pmt_method').change(function(){
    var val = jQuery(this).val();
    if( val == 'creditcard' )
    {
      jQuery('#ccinfo').show();
    }
    else
    {
      jQuery('#ccinfo').hide();
    }

  });

  jQuery('#submit_btn').click(function(){
    var method = jQuery('#pmt_method').val();
    if( pmt_id != '' )
    {
       // we have a valid payment id... we're just editing this payment.
       if( method == 'creditcard' && pmt_method != 'creditcard' )
       {
          // and the new method is creditcard
	  return confirm("{/literal}{$mod->Lang('inform_no_cc_processing')}{literal}");
       }
    }
    else
    {
       // this is a new payment, if we're doing a creditcard transaction
       // and we have a gateway for instant creditcard transactions
       // and the process now option is checked
       var checked = jQuery('#process_new').val();
       if( checked && have_cc_gateway && method == 'creditcard' )
	{
           return confirm("{/literal}{$mod->Lang('ask_manual_payment')}{literal}");
        }
       else if( checked && method == 'creditcard' )
	{
	  return confirm("{/literal}{$mod->Lang('inform_no_cc_processing')}{literal}");
        }
    }
  });
});
{/literal}
</script>

{if isset($errors)}
  {$EcOrderMgr->ShowErrors($errors)}
{/if}

{if isset($warnings)}
<div class="pagewarning">
  <ul>
  {foreach from=$warnings item='one'}
    <li>{$one}</li>
  {/foreach}
  </ul>
</div>
{/if}

<table border="0" width="100%">
<tr>
  <td valign="top">
   <fieldset>
   <legend>{$mod->Lang('order_summary')}:&nbsp;</legend>
   <table border="0">
      <tr>
        <td>{$mod->Lang('order_number')}:</td>
        <td>{$order_obj->get_invoice()}</td>
      </tr>
      <tr>
        <td>{$mod->Lang('status')}:</td>
        <td>{$mod->Lang($order_obj->get_status())}</td>
      </tr>
      <tr>
        <td>{$mod->Lang('created')}:</td>
        <td>{$order_obj->get_create_date()|cms_date_format}</td>
      </tr>
      <tr>
        <td>{$mod->Lang('modified')}:</td>
        <td>{$order_obj->get_modified_date()|cms_date_format}</td>
      </tr>
      <tr>
        <td>{$mod->Lang('total')}:</td>
        <td>{$currencysymbol} {$order_obj->get_total()|number_format:2}</td>
      </tr>
      <tr>
        <td>{$mod->Lang('paid')}:</td>
        <td>{$currencysymbol} {$order_obj->get_amount_paid()|number_format:2}</td>
      </tr>
    {if isset($payment_assoc)}
    {foreach from=$payment_assoc key='key' item='val'}
      <tr>
        <td>{$key}:</td>
        <td>{$val}</td>
      </tr>
    {/foreach}
    {/if}
    </table>
   </fieldset>
   <fieldset>
   <legend>{$mod->Lang('order_notes')}:</legend>
      {$order_obj->get_order_notes()}
   </fieldset>
 </td>

 <td style="padding-left: 5px;">{* second column *}
{$formstart}
<div>
<input type="hidden" name="{$actionid}url" value="{$url}"/>
<input type="hidden" name="{$actionid}gateway" value="{$payment->get_gateway()}"/>
</div>

{if $payment->get_method() != 'creditcard' && !isset($process_only)}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('payment_method')}</p>
  <p class="pageinput">
    <select id='pmt_method' name="{$actionid}method">
    {html_options options=$pmt_types selected=$payment->get_method()}
    </select>
  </p>
</div>
{else}
<div>
  <input type="hidden" name="{$actionid}method" value="{$payment->get_method()}"/>
</div>
{/if}

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('payment_date')}</p>
  <p class="pageinput">
    {capture assign='tmp'}{$actionid}payment_date_{/capture}
    {html_select_date prefix=$tmp time=$payment->get_payment_date() start_year='-1' end_year='+1'}
    {html_select_time prefix=$tmp time=$payment->get_payment_date() display_seconds=false}
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('payment_amount')}</p>
  <p class="pageinput">
    {$currencysymbol}<input type="text" name="{$actionid}amount" value="{$payment->get_amount()|number_format:2}" size="10"/>
  </p>
</div>

<div id="ccinfo">
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('creditcard_number')}:</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}cc_number" value="{$payment->get_cc_number()}" size="20" maxlength="40"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('creditcard_verifycode')}:</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}cc_verifycode" value="{$payment->get_cc_verifycode()}" size="4" maxlength="4"/>
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('creditcard_expiry')}:</p>
  <p class="pageinput">
    {capture assign='tmp'}{$actionid}expires_{/capture}
    {html_select_date prefix=$tmp time=$payment->get_cc_expiry() end_year=+5 display_days=false}
  </p>
</div>

{if !isset($process_only)}
{if $payment->get_id() == "" && $have_cc_gateway}
<div class="pageoverflow"">
  <p class="pagetext">{$mod->Lang('process_new_transaction')}:</p>
  <p class="pageinput">
    <input type="hidden" name="{$actionid}process_cc_new" value="0"/>
    <input type="checkbox" id="process_new" name="{$actionid}process_cc_new" value="1"/>
  </p>
</div>
{/if}
{else}
<div><input type="hidden" name="{$actionid}process_cc_new" value="1"/></div>
{/if}

</div>

{if !isset($process_only)}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('payment_status')}</p>
  <p class="pageinput">
    <select name="{$actionid}status">
    {html_options options=$statuses selected=$payment->get_status()}
    </select>
  </p>
</div>
{else}
<div><input type="hidden" name="{$actionid}status" value="payment_notprocessed"/></div>
{/if}

{if !isset($process_only)}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('transaction_id')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}txn_id" size="30" maxlength="255" value="{$payment->get_txn_id()}"/>
  </p>
</div>
{else}
<div><input type="hidden" name="{$actionid}txn_id" value=""/></div>
{/if}

{if !isset($process_only)}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('confirmation_num')}</p>
  <p class="pageinput">
    <input type="text" name="{$actionid}confirmation_num" size="30" maxlength="255" value="{$payment->get_confirmation_num()}"/>
  </p>
</div>
{else}
<div><input type="hidden" name="{$actionid}confirmation_num" value=""/></div>
{/if}

<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('notes')}</p>
  <p class="pageinput">
    {strip}<textarea name="{$actionid}notes" rows="5">{$payment->get_notes()}</textarea>{/strip}
  </p>
</div>

<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput">
    <input id="submit_btn" type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}"/>
  </p>
</div>
{$formend}
  </td>{* second column*}
</tr>
</table>