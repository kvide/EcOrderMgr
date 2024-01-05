<h3>{$mod->Lang('order')} #: {$order.id}</h3>

<fieldset style="float: left; width: 300px; margin-right: 25px;">
	<legend>&nbsp;{$mod->Lang('billing_info')}:&nbsp;</legend>
	{$mod->Lang('first_name')}: {$order.billing_first_name}<br />
	{$mod->Lang('last_name')}: {$order.billing_last_name}<br />
	{$mod->Lang('address1')}: {$order.billing_address1}<br />
	{$mod->Lang('address2')}: {$order.billing_address2}<br />
	{$mod->Lang('city')}: {$order.billing_city}<br />
	{$mod->Lang('state/province')}: {$order.billing_state}<br />
	{$mod->Lang('postal')}: {$order.billing_postal}<br />
	{$mod->Lang('country')}: {$order.billing_country}<br />
	{$mod->Lang('phone')}: {$order.billing_phone}<br />
</fieldset>

<fieldset style="width: 300px;">
	<legend>&nbsp;{$mod->Lang('shipping_info')}:&nbsp;</legend>
	{$mod->Lang('first_name')}: {$order.shipping_first_name}<br />
	{$mod->Lang('last_name')}: {$order.shipping_last_name}<br />
	{$mod->Lang('address1')}: {$order.shipping_address1}<br />
	{$mod->Lang('address2')}: {$order.shipping_address2}<br />
	{$mod->Lang('city')}: {$order.shipping_city}<br />
	{$mod->Lang('state/province')}: {$order.shipping_state}<br />
	{$mod->Lang('postal')}: {$order.shipping_postal}<br />
	{$mod->Lang('country')}: {$order.shipping_country}<br />
	{$mod->Lang('phone')}: {$order.shipping_phone}<br />
</fieldset>

<br/>

{$order_status_form_start}
	Order Status: {$order_status_dropdown} {$order_status_form_submit}<br />
	<br />
	{$back_to_orders}
{$order_status_form_end}
