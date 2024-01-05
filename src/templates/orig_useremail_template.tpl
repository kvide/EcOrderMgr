{* useremail template *}
<h3>Order number &quot;{$ordernumber}&quot;</h3>
<p>Hello: {$email_address}  Thank you for placing an order with {sitename}.  The details of your order are as follows:</p>

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
   <td>{$currencysymbol}{$payment->get_amount()|number_format:2}</td>
  </tr>
  {/foreach}
  </tbody>
</table>
<br/>
{/if}

{assign var='billing' value=$order_obj->get_billing()}
<h4>{$mod->Lang('bill_to')}</h4>
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
  <tr>
   <td>{$mod->Lang('address2')}:</td>
   <td>{$billing->get_address2()}</td>
  </tr>
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
  <tr>
   <td>{$mod->Lang('phone')}:</td>
   <td>{$billing->get_phone()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('fax')}:</td>
   <td>{$billing->get_fax()}</td>
  </tr>
  <tr>
   <td>{$mod->Lang('email_address')}:</td>
   <td>{$billing->get_email()}</td>
  </tr>
</table>
<br/>

{if $order_obj->get_order_notes() != ''}
<h4>{$mod->Lang('special_instructions')}</h4>
{$order_obj->get_order_notes()}<br/>
<br/>
{/if}

{foreach from=$order_obj->get_destinations() item='shipping'}
  {assign var='shipping_addr' value=$shipping->get_shipping_address()}
<h4>{$mod->Lang('ship_to')}: {$shipping_addr->get_lastname()}, {$shipping_addr->get_firstname()}</h4>
  <p>
    {$shipping_addr->get_address1()}<br/>
    {$shipping_addr->get_address2()}<br/>
    {$shipping_addr->get_city()}, {$shipping_addr->get_state()}<br/>
    {$shipping_addr->get_country()}<br/>
    {$shipping_addr->get_postal()}<br/>
    {$mod->Lang('phone')}:&nbsp;{$shipping_addr->get_phone()}<br/>
    {$mod->Lang('fax')}:&nbsp;{$shipping_addr->get_fax()}<br/>
    {$mod->Lang('email_address')}:&nbsp;{$shipping_addr->get_email()}<br/>
  </p>
  <p><strong>&nbsp;{$mod->Lang('items')}:</strong></p>
  <table align="center" border="1" width="100%">
    <thead>
      <tr>
        <th>{$mod->Lang('num')}</th>
        <th>{$mod->Lang('sku')}</th>
        <th>{$mod->Lang('description')}</th>
        <th>{$mod->Lang('unit_weight')}</th>
        <th>{$mod->Lang('unit_price')}</th>
        <th>{$mod->Lang('quantity')}</th>
        <th>{$mod->Lang('master_price')}</th>
        <th>{$mod->Lang('total')}</th>
      </tr>
    </thead>
    <tbody>
    {foreach from=$shipping->get_items() item='item' name='items'}
      <tr>
        <td>{$smarty.foreach.items.iteration}</td>
        <td>
          {if $item->get_item_type() == 'product'}
            {$item->get_sku()}
          {/if}
        </td>
        <td>{$item->get_description()}</td>
        <td align="right">
          {if $item->get_item_type() == 'product'}
          {$item->get_weight()|number_format:2}{$weightunits}
          {/if}
        </td>
        <td align="right">{$currencysymbol}{$item->get_net_price()|number_format:2}</td>
        <td align="right">{$item->get_quantity()}</td>
	<td align="right">{$currencysymbol}{$item->get_master_price()|number_format:2}</td>
        <td align="right">{$currencysymbol}{$item->get_net_total()|number_format:2}</td>
      </tr>
    {/foreach}
    <tr>
      <td colspan="7" align="right">{$mod->Lang('subtotal')}</td>
      <td align="right">{$currencysymbol}{$shipping->get_subtotal()|number_format:2}</td>
    </tr>
    <tr>
      <td colspan="7" align="right">{$mod->Lang('tax')}</td>
      <td align="right">{$currencysymbol}{$shipping->get_tax_cost()|number_format:2}</td>
    </tr>
    <tr>
      <td colspan="7" align="right">{$mod->Lang('shipping')}</td>
      <td align="right">{$currencysymbol}{$shipping->get_shipping_cost()|number_format:2}</td>
    </tr>
    <tr>
      <td colspan="7" align="right">{$mod->Lang('total')}</td>
      <td align="right">{$currencysymbol}{$shipping->get_total()|number_format:2}</td>
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
    <td width="80%" align="right">{$mod->Lang('subtotal')}</td>
    <td align="right">{$currencysymbol}{$order_obj->get_subtotal()|number_format:2}</td>
  </tr>
  <tr>
    <td width="80%" align="right">{$mod->Lang('tax')}</td>
    <td align="right">{$currencysymbol}{$order_obj->get_tax_cost()|number_format:2}</td>
  </tr>
  <tr>
    <td width="80%" align="right">{$mod->Lang('shipping')}</td>
    <td align="right">{$currencysymbol}{$order_obj->get_shipping_cost()|number_format:2}</td>
  </tr>
  <tr>
    <td width="80%" align="right">{$mod->Lang('total')}</td>
    <td align="right">{$currencysymbol}{$order_obj->get_total()|number_format:2}</td>
  </tr>
</table>

<p>Orders normally ship within 24 hours.  Please watch your email for tracking information.  If you have any questions please feel free to contact us.</p>

<p>Thank you for your time.</p>
