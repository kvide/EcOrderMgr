{strip}
{$order->get_invoice()}|{$order->get_create_date()}|{$order->get_modified_date()}|{$order->get_weight()} {$weight_units}|{$currency_symbol}{$order->get_total()}|{$currency_symbol}{$order->get_amount_due()}|
{foreach from=$order->get_destinations() item='shipping'}
  {assign var='addr' value=$shipping->get_shipping_address()}
  {$addr->get_company()}|{$addr->get_firstname()}|{$addr->get_lastname()}|{$addr->get_email()}|{$addr->get_address1()}|{$addr->get_address2()}|{$addr->get_city()}|{$addr->get_state()}|{$addr->get_country()}|{$addr->get_phone()}|{$addr->get_fax()}|{$shipping->get_weight()}{$weight_units}|{$currency_symbol}{$shipping->get_total()}  
{/foreach}
{/strip}
