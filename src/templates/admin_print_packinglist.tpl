<html>
  <head>
    <title>{$mod->Lang('packing_list')} -- {$order->get_invoice()} -- {$mod->Lang('shipping')} {$shipping_id}</title>
    <style>
    h3, h4 { text-align: center; }
    .address h4 {
        text-align: left;
	margin-top: 0.5em;
	margin-bottom: 0;
    }
    .address p {
        margin-top: 0;
        margin-bottom: 0;
    }
    fieldset {
        margin-bottom: 0.75em;
    }
    hr {
        margin-top: 0.75em;
        margin-bottom: 0.75em;
    }
    table.itemtable {
        width: 100%;
    }
    th {
        text-align: left;
    }
    th.desc {
        max-width: 50%;
    }
    h4.box {
       page-break-before: always;
    }
    </style>
  </head>
  <body>
    <h3>{$mod->Lang('packing_list')}</h3>
    <h4>{$mod->Lang('order')} {$order->get_invoice()} <em>({$order->get_id()})</em></h4>
    <hr/>

{function currency}
  <span class="currency">{\EcommerceExt\ecomm::get_currency_symbol()}{$in|as_num:2} {\EcommerceExt\ecomm::get_currency_code()}</span>
{/function}
{function addr_line}
  {if !empty($in)}<p>{$in}</p>{/if}
{/function}
{function addr_line_prompt}
  {if !empty($in)}
    {if !empty($prompt)}{$in="{$prompt}: {$in}"}{/if}
    {addr_line in=$in}
  {/if}
{/function}

    {$boxes=$packing_list->get_boxes()}
    <fieldset class="address">
       <legend>{$mod->Lang('deliver_to')}:</legend>
       {$addr=$packing_list->get_destination()}
       <h4>{$addr->get_firstname()} {$addr->get_lastname()}</h4>
       {addr_line in=$addr->get_company()}
       {addr_line in=$addr->get_address1()}
       {addr_line in=$addr->get_address2()}
       {addr_line in="{$addr->get_city()} {$addr->get_state()}"}
       {addr_line in=$addr->get_address1()}
       {addr_line in=$addr->get_address2()}
       {addr_line in=$addr->get_country()}
       {addr_line in=$addr->get_postal()}
       {addr_line_prompt in=$addr->get_phone() prompt=$mod->Lang('phone')}
       {addr_line_prompt in=$addr->get_fax() prompt=$mod->Lang('fax')}
       {addr_line_prompt in=$addr->get_email() prompt=$mod->Lang('email_address')}
    </fieldset>

    <fieldset class="stats">
       <legend>{$mod->Lang('totals')}:</legend>
       <table>
          <tr>
	     <th>{$mod->Lang('total_weight')}:</th>
	     <td>{$total_weight=$total_weight/1000.0}{$total_weight|as_num:3} kg</td>
	  </tr>
          <tr>
	     <th>{$mod->Lang('total_value')}:</th>
	     <td>{currency in=$total_value}</td>
	  </tr>
          <tr>
	     <th>{$mod->Lang('pieces')}:</th>
	     <td>{count($boxes)}</td>
	  </tr>
       </table>
    </fieldset>

    {foreach $boxes as $box}
      <h4 class="box">{$mod->Lang('order')} {$order->get_invoice()} - {$mod->Lang('shipping')} {$shipping_id} - {$mod->Lang('package')} {$box@iteration} {$mod->Lang('of')} {count($boxes)}</h4>
      <fieldset class="box">
        <legend>{$box->name}</legend>
	<table>
	  <tr>
	    <th>{$mod->Lang('dimensions')}</th>
	    <td>{$box->width} mm x {$box->length} mm x {$box->depth} mm</td>
	  </tr>
	  <tr>
	    <th>{$mod->Lang('weight')}</th>
	    <td>{$num=$box->total_weight/1000}{$num|as_num:2} kg</td>
	  </tr>
	  <tr>
	    <th>{$mod->Lang('value')}</th>
	    <td>{currency in=$box->total_value}</td>
	  </tr>
	</table>

	<hr/>
	<h5>{$mod->Lang('inthispackage')}:</h5>
	{$items=$box->get_items()}
	<table class="itemtable">
	   <thead>
	     <tr>
	       <th>{$mod->Lang('sku')}</th>
	       <th class="desc">{$mod->Lang('description')}</th>
	       <th>{$mod->Lang('weight')}</th>
	       <th>{$mod->Lang('value')}</th>
	     </tr>
	   </thead>
	   <tbody>
	   {foreach $items as $item}
	     <tr>
	       <td>{$item->sku}</td>
	       <td>{$item->description}</td>
	       <td>{$item->weight|as_num:3} g</td>
	       <td>{currency in=$item->value}</td>
	     </tr>
	   {/foreach}
	   </tbody>
	</table>
      </fieldset>
    {/foreach}
</body>
</html>
