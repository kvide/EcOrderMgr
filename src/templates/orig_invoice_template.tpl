{* invoice template *}
<h4>{$mod->Lang('order_number')}:&nbsp;{$ordernumber}</h4>
{if isset($invoice_message)}
<div>{eval var=$invoice_message}</div>
{/if}

{if $order_obj->count_payments() }
<h4>{$mod->Lang('payment_history')}</h4>
<table width="100%">
  <thead>
    <tr>
      <th>{$mod->Lang('id')}</th>
      <th>{$mod->Lang('date')}</th>
      <th>{$mod->Lang('method')}</th>
      <th>{$mod->Lang('details')}
      <th>{$mod->Lang('status')}</th>
      <th>{$mod->Lang('amount')}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$order_obj->get_payments() item='payment'}
  <tr>
   <td>{$payment->get_id()}</td>
   <td>{$payment->get_payment_date()|cms_date_format}</td>
   <td>{assign var='tmp' value=$payment->get_method()}{$mod->Lang($tmp)}</td>
   <td>
     {if $tmp == 'creditcard'}
       {$payment->get_cc_number_masked()}
     {else if $tmp == 'online'}
       {$payment->get_gateway()}: {$payment->get_txn_id()}
     {/if}
   </td>
   <td>{assign var='tmp' value=$payment->get_status()}{$mod->Lang($tmp)}</td>
   <td>{$payment->get_amount()|as_num:2}</td>
  </tr>
  {/foreach}
  </tbody>
</table>
<br/>
{/if}

<h4>{$mod->Lang('bill_to')}</h4>
{assign var='billing' value=$order_obj->get_billing()}
<table>
  <tr>
   <td>{$mod->Lang('first_name')}:</td>
   <td>{$billing->get_firstname()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('last_name')}:</td>
   <td>{$billing->get_lastname()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('address1')}:</td>
   <td>{$billing->get_address1()}</td>
  </tr>
  {if $billing->get_address2() != ''}
  <tr>
   <td>{$mod->Lang('address2')}:</td>
   <td>{$billing->get_address2()}</td>
  </tr>
  {/if}
  <tr>
   <td>{$mod->Lang('city')}:</td>
   <td>{$billing->get_city()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('state/province')}:</td>
   <td>{$billing->get_state()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('postal')}:</td>
   <td>{$billing->get_postal()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('country')}:</td>
   <td>{$billing->get_country()}</td>
  </tr>
  {if $billing->get_phone() != ''}
  <tr>
   <td>{$mod->Lang('phone')}:</td>
   <td>{$billing->get_phone()}</td>
  </tr>
  {/if}
  {if $billing->get_fax() != ''}
  <tr>
   <td>{$mod->Lang('fax')}:</td>
   <td>{$billing->get_fax()}</td>
  </tr>
  {/if}
  {if $billing->get_email() != ''}
  <tr>
   <td>{$mod->Lang('email_address')}:</td>
   <td>{$billing->get_email()}</td>
  </tr>
  {/if}
</table>
<br/>

{if $order_obj->get_order_notes() != ''}
<h4>{$mod->Lang('special_instructions')}</h4>
{$order_obj->get_order_notes()}<br/>
{/if}

{$extra=$order_obj->get_all_extra()}
{if $extra}
  <h4>{$mod->Lang('order_extra')}</h4>
  {foreach $extra as $fldname => $fldval}
    <p>Field: {$fldname} is {$fldval}</p>
  {/foreach}
{/if}

{foreach from=$order_obj->get_destinations() item='shipping'}
   {assign var='shipping_addr' value=$shipping->get_shipping_address()}
<h4>{$mod->Lang('ship_to')}: {$shipping_addr->get_lastname()}, {$shipping_addr->get_firstname()}</h4>
  <p>
    {$shipping_addr->get_address1()}<br/>
    {if $shipping_addr->get_address2() != ''}{$shipping_addr->get_address2()}<br/>{/if}
    {$shipping_addr->get_city()}, {$shipping_addr->get_state()}<br/>
    {$shipping_addr->get_country()}<br/>
    {$shipping_addr->get_postal()}<br/>
    {if $shipping_addr->get_phone() != ''}{$mod->Lang('phone')}:&nbsp;{$shipping_addr->get_phone()}<br/>{/if}
    {if $shipping_addr->get_fax() != ''}{$mod->Lang('fax')}:&nbsp;{$shipping_addr->get_fax()}<br/>{/if}
    {if $shipping_addr->get_email() != ''}{$mod->Lang('email_address')}:&nbsp;{$shipping_addr->get_email()}<br/>{/if}
  </p>
  <p><strong>&nbsp;{$mod->Lang('items')}:</strong></p>
  <table align="center" border="1" width="100%">
    <thead>
      <tr>
        <th>{$mod->Lang('num')}</th>
        <th>{$mod->Lang('type')}</th>
        <th>{$mod->Lang('sku')}</th>
        <th>{$mod->Lang('description')}</th>
        <th>{$mod->Lang('unit_weight')}</th>
        <th>{$mod->Lang('unit_price')}</th>
        <th>{$mod->Lang('discount')}</th>
        <th>{$mod->Lang('net_price')}</th>
        <th>{$mod->Lang('quantity')}</th>
        <th>{$mod->Lang('master_price')}</th>
        <th>{$mod->Lang('total')}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$shipping->get_items() item='item' name='items'}
      <tr>
        <td align="right">{$smarty.foreach.items.iteration}</td>
        <td>{$mod->Lang($item->get_item_type())}</td>
        <td>{$item->get_sku()}</td>
        <td>{$item->get_description()}</td>
        <td align="right">{if $item->get_weight() != ''}{$item->get_weight()|as_num:2}{$weightunits}{/if}</td>
        <td align="right">{$currencysymbol}{$item->get_unit_price()|as_num:2}</td>
        <td align="right">{if $item->get_discount() != ''}{$currencysymbol}{$item->get_discount()|as_num:2}{/if}</td>
        <td align="right">{$currencysymbol}{$item->get_net_price()|as_num:2}</td>
        <td align="right">{$item->get_quantity()}</td>
	<td align="right">{$currencysymbol}{$item->get_master_price()|as_num:2}</td>
        <td align="right">{$currencysymbol}{$item->get_net_total()|as_num:2}</td>
      </tr>
    {/foreach}
      <td colspan="10" align="right">{$mod->Lang('total')}</td>
      <td align="right">{$currencysymbol}{$shipping->get_total()|as_num:2}</td>
    </tr>
    <tr>
      <td colspan="10" align="right">{$mod->Lang('weight')}</td>
      <td align="left">{$shipping->get_weight()|as_num:2}{$weightunits}</td>
    </tr>
    </tbody>
  </table>
</table>
<br/>
{/foreach}
<br/>

<h4>{$mod->Lang('order_summary')}</h4>
<table align="center" border="1" width="100%">
  <tr>
    <td width="80%" align="right">{$mod->Lang('total')}</td>
    <td align="right">{$currencysymbol}{$order_obj->get_total()|as_num:2}</td>
  </tr>
</table>

{if isset($allow_reorder) && $allow_reorder && isset($reorder_url) && isset($EcOrderViewer)}
<a href="{$reorder_url}">{$EcOrderViewer->Lang('reorder_this')}</a>
{/if}