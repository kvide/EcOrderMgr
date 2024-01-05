{math equation='x-y' x=$order->get_total() y=$order->get_amount_paid() assign='amt_due'}

<script type="text/javascript"><!--
function openwindow(url) {
  window.open(url,'_blank',config='height=600,width=800,scrollbars=yes,toolbar=no,menubar=yes,location=no,directories=no,status=no');
}

$(function() {
  {if !$can_manage_orders}
    $("[name$='submit']").each(function(){
       $(this).hide();
    });
  {/if}

  $('#print_packinglist').click(function(ev){
     ev.preventDefault();
     var shipping_id = $(this).data('shipping-id');
     if( !shipping_id ) return;
     var url = '{cms_action_url module='EcOrderMgr' action=admin_print_packinglist orderid=$order->get_id() shipping_id=XXX forjs=1}&showtemplate=false';
     url = url.replace('XXX',shipping_id);
     openwindow( url );
  });

  jQuery("[name$='submit']").click(function() {
    // if the amount due is negliglble
    // set the status to paid
    // if the amount due is not, set the
    // status to balance due
    var orderstatus = jQuery('#input_status').val();
    var amt_due = {$amt_due};
    var want_status = orderstatus;
    if( orderstatus == 'invoiced' || orderstatus == 'balancedue' ||
        orderstatus == 'hold' || orderstatus == 'confirmed') {
       if( amt_due > 0.01 && orderstatus != 'paid' ) {
         want_status = 'balancedue';
       }
       else {
         want_status = 'paid';
       }
    }
    if( orderstatus != want_status ) {
	var res = confirm('{$mod->Lang('ask_setorderstatus')}'+want_status);
	if( res ) {
            jQuery('#order_status').val(want_status);
            return false;
          }
      }
  });

  var c = $('#destinations > h3').length;
  if( c > 1 ) {
    $('#destinations > h3').click(function() {
      $('#destinations > div').hide();
      $(this).next().show('slow');
    });
    $('#destinations > h3:first').trigger('click');
  }
});
--></script>

{$formstart}

<h3>{$mod->Lang('title_manage_order')}</h3>
<fieldset>
<legend>{$mod->Lang('order_statistics')}: </legend>
<div class="c_full cf">
  <div class="grid_6">
    <label class="grid_2 text-right">{$mod->Lang('order_number')}:</label>
    <span class="grid_10">{$order->get_invoice()}</span>
    <label class="grid_2 text-right">{$mod->Lang('status')}:</label>
    {if !empty($order_statuses)}
      <div class="grid_10">
      <select class="grid_12" id="input_status" name="{$actionid}input_status">
      {html_options options=$order_statuses selected=$order->get_status()}
      </select>
      <input class="cms_submit" type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')} "/>
      </div>
    {else}
      <span>{$mod->Lang($order->get_status())}</span>
    {/if}

    {if $can_manage_orders}
    <div class="clearb"></div>
    <div class="c_full cf" style="margin-top: 0.5em;">
      <a class="grid_6" href="javascript:void()" onclick="openwindow('{$print_url}'); return false;">{xtimage image='printer.png'} {$mod->Lang('print_invoice')}</a>
      <a class="grid_6" href="{cms_action_url module='EcOrderMgr' action=admin_createinvoice orderid=$order->get_id()}">{xtimage image='report.png'} {$mod->Lang('create_invoice')}</a>
    </div>
    {/if}
  </div>{* column 1 *}

  <div class="grid_5" style="border-left: 1px solid gray;">
    <label class="grid_3 text-right">{$mod->Lang('username')}:</label>
    {capture assign='username'}{$EcOrderMgr->GetUsername($order->get_feu_user())}{/capture}
    {if $username == ''}
      <span class="grid_9" style="color: red;">{$mod->Lang('unknown')}</span>
    {else}
      <span class="grid_9">{$username}</span>
    {/if}

    <label class="grid_3 text-right">{$mod->Lang('created')}:</label>
    <span class="grid_9">{$order->get_create_date()|cms_date_format}</span>

    <label class="grid_3 text-right">{$mod->Lang('last_modified')}:</label>
    <span class="grid_9">{$order->get_modified_date()|cms_date_format}</span>

    <label class="grid_3 text-right">{$mod->Lang('subtotal')}:</label>
    <span class="grid_9">{$currencysymbol}{$order->get_subtotal()|number_format:2}</span>

    <label class="grid_3 text-right">{$mod->Lang('discount')}:</label>
    <span class="grid_9">{$currencysymbol}{$order->get_discount()|number_format:2}</span>

    <label class="grid_3 text-right">{$mod->Lang('shipping_total')}:</label>
    <span class="grid_9">{$currencysymbol}{$order->get_shipping_cost()|number_format:2}</span>

    <label class="grid_3 text-right">{$mod->Lang('tax')}:</label>
    <span class="grid_9">{$currencysymbol}{$order->get_tax_cost()|number_format:2}</span>

    <label class="grid_3 text-right">{$mod->Lang('total')}:</label>
    <span class="grid_9">{$currencysymbol}{$order->get_total()|number_format:2}</span>

  </div>{* column 2 *}
</div>
</fieldset>

<fieldset class="c_full cf">
    {capture assign='tmp'}<em>({$mod->Lang('edit')})</em>{/capture}
    <legend>{$mod->Lang('bill_to')}: {if isset($can_manage_orders)}{mod_action_link module='EcOrderMgr' action='admin_editorderbilling' text=$tmp orderid=$order->get_id()}{/if}</legend>
    {assign var='billing' value=$order->get_billing()}
    <strong class="grid_12">{$billing->get_firstname()} {$billing->get_lastname()}</strong>
    {if $billing->get_company() != ''}<span class="grid_12">{$billing->get_company()}</span>{/if}
    <span class="grid_12">{$billing->get_address1()}</span>
    {if $billing->get_address2() != ''}<span class="grid_12">{$billing->get_address2()}</span>{/if}
    <span class="grid_12">{$billing->get_city()} {$billing->get_state()}</span>
    <span class="grid_12">{$billing->get_country()}</span>
    <span class="grid_12">{$billing->get_postal()}</span>
    <span class="grid_12">{$mod->Lang('phone')}:&nbsp;{$billing->get_phone()}</span>
    <span class="grid_12">{$mod->Lang('fax')}:&nbsp;{$billing->get_fax()}</span>
    <span class="grid_12">{$mod->Lang('email_address')}:&nbsp;{$billing->get_email()}</span>
    {if isset($sendmail_link)}<span class="grid_12">{$sendmail_link}</span>{/if}
    {if isset($viewmail_link)}<span class="grid_12">{$viewmail_link}</span>{/if}
</fieldset>

{* begin second column of second row *}
<fieldset>
  <legend>{$mod->Lang('payment_info')}:</legend>
  <strong class="grid_12">
    {* amount due *}
    {$mod->Lang('amt_due')}:&nbsp;
    {if $order->get_status() == 'pending'}
      N/A:
    {else}
      {if $amt_due < -0.01}
        <span style='color: red;'>{$currencysymbol}{$amt_due|number_format:2}</span>
      {else}
        {$currencysymbol}{$amt_due|number_format:2}</span>
      {/if}
    {/if}
  </strong>

  {if $can_manage_orders}
  <a class="grid_12" href="{cms_action_url module='EcOrderMgr' action=admin_manualprocess orderid=$order->get_id()}">{xtimage image='money_add.png'} {$mod->Lang('process_manual_payment')}</a>
  {/if}

  {if $order->count_payments()}
    {* payments table *}
    <table class="pagetable" cellspacing="0">
     <thead>
       <tr>
         <th>{$mod->Lang('date')}</th>
         <th>{$mod->Lang('status')}</th>
         <th>{$mod->Lang('method')}</th>
         <th>{$mod->Lang('transaction_id')}</th>
         <th align="right">{$mod->Lang('amount')}</th>
         <th class="pageicon">&nbsp;</th>
         <th class="pageicon">&nbsp;</th>
         <th class="pageicon">&nbsp;</th>
       </tr>
     </thead>
     <tbody>
     {foreach from=$order->get_payments() item='payment' name='payments'}
       <tr>
         <td>{$payment->get_payment_date()|cms_date_format}</td>
	 <td>{$mod->Lang($payment->get_status())}</td>
         <td>
	   {$mod->Lang($payment->get_method())}
	   {if $payment->get_gateway()}/ {$payment->get_gateway()}{/if}
	 </td>
         <td>{$payment->get_txn_id()}</td>
         <td>{$currencysymbol}{$payment->get_amount()|number_format:2}</td>
         <td>
          {if $can_manage_orders && $payment->get_status() == 'payment_notprocessed' && $payment->get_method() == 'creditcard'}
            {mod_action_link module='EcOrderMgr' action='admin_manualprocess' imageonly=1 image='creditcard.png' text=$mod->Lang('prompt_process_cc_now') orderid=$order->get_id() payment_id=$payment->get_id() process_only=1}
          {/if}
         </td>
         <td>
          {if $can_manage_orders}
            {mod_action_link module='EcOrderMgr' action='admin_manualprocess' imageonly=1 image='icons/system/edit.gif' text=$mod->Lang('edit') orderid=$order->get_id() payment_id=$payment->get_id()}
          {/if}
         </td>
         <td>
          {if $can_manage_orders}
            {mod_action_link module='EcOrderMgr' action='admin_deletepayment' imageonly=1 image='icons/system/delete.gif' text=$mod->Lang('delete') orderid=$order->get_id() payment_id=$payment->get_id() confmessage=$mod->Lang('ask_delete_payment')}
          {/if}
         </td>
       </tr>
     {/foreach}
     </tbody>
    </table>
  {/if}
</fieldset>


{function display_shipping}
  {assign var='addr' value=$dest->get_shipping_address()}
  <div class="c_full cf">
     {$company=$addr->get_company()}
     <h3 style="padding-left: 0.75em; margin-bottom: 0.25em;">{$addr->get_firstname()} {$addr->get_lastname()} {if $company} @ {$company}{/if}</h3>
     <div class="grid_6" style="border-right: 1px solid gray">
        {if $company != ''}<div class="grid_12">{$company}</div>{/if}
	<div class="grid_12">{$addr->get_address1()}</div>
        {if $addr->get_address2() != ''}<div class="grid_12">{$addr->get_address2()}</div>{/if}
	<div class="grid_12">{$addr->get_city()} {$addr->get_state()}</div>
	<div class="grid_12">{$addr->get_country()}</div>
	<div class="grid_12">{$addr->get_postal()}</div>
	<div class="grid_12">{$mod->Lang('phone')}: {$addr->get_postal()}</div>
        {if $addr->get_fax() != ''}<div class="grid_12">{$mod->Lang('fax')}: {$addr->get_fax()}</div>{/if}
        {if $addr->get_email() != ''}<div class="grid_12">{$mod->Lang('email_address')}: {$addr->get_email()}</div>{/if}
     </div>
     <div class="grid_5">
       {$packing_list=$dest->get_packing_list()}
       {if $packing_list}
          <a class="grid_12" href="#" id="print_packinglist" data-shipping-id="{$dest->get_id()}">{xtimage image='printer.png'} {$mod->Lang('print_packing_list')}</a>
       {/if}
       {if $dest->get_message() != ''}
       <fieldset>
         <legend>{$mod->Lang('shipping_message')}:</legend>
         <p>{$dest->get_message()}</p>
       </fieldset>
       {/if}
     </div>
  </div>


     {if $dest->count_items() }
     <fieldset>
       <legend>{$mod->Lang('items')}: </legend>
       <table class="pagetable" cellspacing="0">
         <thead>
           <tr>
             <th>{$mod->Lang('source')}</th>
             <th>{$mod->Lang('id')}</th>
             <th>{$mod->Lang('name')}</th>
             <th>{$mod->Lang('type')}</th>
             <th>{$mod->Lang('subscription')}</th>
             <th>{$mod->Lang('quantity')}</th>
             <th>{$mod->Lang('unit_weight')}</th>
             <th>{$mod->Lang('unit_price')}</th>
             <th>{$mod->Lang('master_price')}</th>
             <th>{$mod->Lang('status')}</th>
             <th class="pageicon">&nbsp;</th>
           </tr>
         </thead>
         <tbody>
         {foreach from=$dest->get_items() item='item'}
            <tr>
              <td>{$item->get_source()}</td>
              <td>{$item->get_item_id()}</td>
              <td>{$item->get_description()}</td>
	      <td>{$mod->Lang($item->get_item_type())}</td>
	      <td>{if $item->is_subscription()}{$mod->Lang('yes')}{else}{$mod->Lang('no')}{/if}</td>
              <td>{$item->get_quantity()}</td>
              <td>
                {if $item->get_item_type() == '0_product'}
                {$item->get_weight()|number_format:2}{$weightunits}
                {/if}
              </td>
              <td>{$currencysymbol}{$item->get_net_price()|number_format:2}</td>
              <td>{$currencysymbol}{$item->get_master_price()|number_format:2}</td>
              <td>
                {if $item->get_item_type() == '0_product'}
                  <select name="{$actionid}input_itemstatus_{$item->get_id()}">
                    {html_options options=$statuses selected=$item->get_status()}
                  </select>
		{else}
		  n/a
                {/if}
              </td>
              <td>&nbsp;</td>
            </tr>
          {/foreach}
          </tbody>
        </table>
    </fieldset>
    {/if}

  </div>
{/function}

{* display the order items *}
<fieldset>
  <legend>{$mod->Lang('destinations')}:</legend>

  <div id="destinations">
    {foreach $order->get_destinations() as $dest}
      {display_shipping dest=$dest}
    {/foreach}
  </div>{* #destinations *}
</fieldset>

<div class="pageoverflow">
  <p class="pageinput">
   <input type="submit" name="{$actionid}submit" id="submit" value="{$mod->Lang('submit')}"/>
   {$cancel}
  </p>
</div>
{$formend}
