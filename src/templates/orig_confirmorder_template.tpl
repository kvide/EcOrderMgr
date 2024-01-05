{* confirmation report template *}
<h3>{$mod->Lang('confirm_order')}</h3>
{if $message != ''}<div class="alert alert-info">{$message}</div>{/if}

{function do_address}
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('address1')}:</p>
    <p class="col-sm-10">{$address->get_address1()}</p>
  </div>

  {if $address->get_address2() != ''}
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('address2')}:</p>
    <p class="col-sm-10">{$address->get_address2()}</p>
  </div>
  {/if}

  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('city')}:</p>
    <p class="col-sm-10">{$address->get_city()}</p>
  </div>
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('state/province')}:</p>
    <p class="col-sm-10">{$address->get_state()}</p>
  </div>
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('postal')}:</p>
    <p class="col-sm-10">{$address->get_postal()}</p>
  </div>
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('country')}:</p>
    <p class="col-sm-10">{$address->get_country()}</p>
  </div>

  {if $address->get_phone() != ''}
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('phone')}:</p>
    <p class="col-sm-10">{$address->get_phone()}</p>
  </div>
  {/if}

  {if $address->get_fax() != ''}
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('fax')}:</p>
    <p class="col-sm-10">{$address->get_fax()}</p>
  </div>
  {/if}

  {if $address->get_email() != ''}
  <div class="row">
    <p class="col-sm-2 text-right">{$mod->Lang('email_address')}:</p>
    <p class="col-sm-10">{$address->get_email()}</p>
  </div>
  {/if}
{/function}

{function full_name}
{strip}{if $addr->firstname}{$addr->firstname} {/if}{$addr->lastname}{/strip}
{/function}

{function do_dual_address_line}
  {if $a || $b}
  <div class="row">
    <p class="col-sm-4 text-right"><strong>{$lbl}:</strong></p>
    <p class="col-sm-4">{$a|default:''}</p>
    <p class="col-sm-4">{$b|default:''}</p>
  </div>
  {/if}
{/function}

{function do_dual_address}
  <div class="row">
     <p class="col-sm-4">&nbsp;</p>
     <p class="col-sm-4"><strong>{$mod->Lang('from')}</strong></p>
     <p class="col-sm-4"><strong>{$mod->Lang('to')}</strong></p>
  </div>
  {do_dual_address_line lbl=$mod->Lang('company') a=$from->company b=$to->company}
  {$tmp1="{full_name addr=$from}"}{$tmp2="{full_name addr=$to}"}
  {do_dual_address_line lbl=$mod->Lang('attn') a=$tmp1 b=$tmp2}
  {do_dual_address_line lbl=$mod->Lang('address2') a=$from->address2 b=$to->address2}
  {do_dual_address_line lbl=$mod->Lang('city') a=$from->city b=$to->city}
  {do_dual_address_line lbl=$mod->Lang('state/province') a=$from->state b=$to->state}
  {do_dual_address_line lbl=$mod->Lang('postal') a=$from->postal b=$to->postal}
  {do_dual_address_line lbl=$mod->Lang('country') a=$from->country b=$to->country}
  {do_dual_address_line lbl=$mod->Lang('phone') a=$from->phone b=$to->phone}
  {do_dual_address_line lbl=$mod->Lang('fax') a=$from->fax b=$to->fax}
  {do_dual_address_line lbl=$mod->Lang('email_address') a=$from->email b=$to->email}
{/function}

{* display the billing info *}
<fieldset>
   <legend><strong>{$mod->Lang('bill_to')}:</strong> (<a href="{$back_url}">Edit</a>):</legend>
   {do_address address=$billing}
</fieldset>

{foreach from=$order_obj->get_destinations() item='destination'}
  <fieldset>
    <legend>{$mod->Lang('addresses')}:</legend>
    {do_dual_address from=$destination->get_source_address() to=$destination->get_shipping_address()}

    <fieldset>
      <legend><strong>{$mod->Lang('items')}:</strong></legend>
      <table class="table" width="100%">
        <thead>
          <tr>
            <th>{$mod->Lang('type')}</th>
            <th>{$mod->Lang('sku')}</th>
            <th>{$mod->Lang('description')}</th>
            <th class="text-right">{$mod->Lang('quantity')}</th>
            <th class="text-right">{$mod->Lang('unit_weight')}</th>
            <th class="text-right">{$mod->Lang('unit_price')}</th>
            <th class="text-right">{$mod->Lang('discount')}</th>
            <th class="text-right">{$mod->Lang('net_price')}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$destination->get_items() item='item'}
          <tr>
            <td>{$mod->Lang($item->get_item_type())}</td>
            <td>{$item->get_sku()}</td>
            <td>{$item->get_description()}</td>
            <td class="text-right">{$item->get_quantity()}</td>
            <td class="text-right">{if $item->get_unit_weight() != ''}{$item->get_unit_weight()|as_num:2} {$weightunits}{/if}</td>
	    <td class="text-right">{$currencysymbol}{$item->get_unit_price()|as_num:2}</td>
	    <td class="text-right">{if $item->get_unit_discount() != ''}{$currencysymbol}{$item->get_discount()|as_num:2}{/if}</td>
            <td class="text-right">{$currencysymbol}{$item->get_net_total()|as_num:2}</td>
          </tr>
          {/foreach}
          <tr>
            <td colspan="7" align="right">{$mod->Lang('total')}:</td>
            <td align="right">{$currencysymbol}{$destination->get_total()|as_num:2}</td>
          </tr>
        </tbody>
      </table>
    </fieldset>
  </fieldset>
{/foreach}

{* display any extra information *}
{$extra=$order_obj->get_all_extra()}
{if $extra}
  <fieldset>
    <legend>{$mod->Lang('order_extra')}</legend>
    {foreach $extra as $key => $val}
    <div class="row">
      <p class="col-sm-2 text-right">Field: {$key}:</p>
      <p class="col-sm-10">{$val}</p>
    </div>
    {/foreach}
  </fieldset>
{/if}

{* display the order notes *}
{if $order_obj->get_order_notes() != '' }
<fieldset>
  <legend><strong>{$mod->Lang('order_notes')}:</strong> (<a href="{$edit_url}">Edit</a>):</legend>
  {$order_obj->get_order_notes()}
</fieldset>
{/if}

{if isset($gw_forms)}
<fieldset>
  <legend><strong>{$mod->Lang('payment_options')}:</strong></legend>
  {foreach $gw_forms as $gateway => $html}
    <div class="gateway_form">{$html}</div>
  {/foreach}
</fieldset>
{/if}

<div class="well">
  <a href="{$backurl}" title="{$mod->Lang('back')}">{$mod->Lang('back')}</a>
</div>
