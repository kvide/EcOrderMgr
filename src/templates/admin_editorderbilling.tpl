<h3>{$mod->Lang('edit_billing_info')}
<h3>{$mod->Lang('order_number')}: {$ordernum}</h3>

{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('company')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_company" value="{$order.billing_company}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('first_name')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_firstname" value="{$order.billing_first_name}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('last_name')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_lastname" value="{$order.billing_last_name}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('address1')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_address1" value="{$order.billing_address1}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('address2')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_address2" value="{$order.billing_address2}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('city')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_city" value="{$order.billing_city}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('state/province')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_state" value="{$order.billing_state}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('postal')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_postal" value="{$order.billing_postal}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('country')}:</p>
  <p class="pageinput">{$input_billing_country}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('phone')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_phone" value="{$order.billing_phone}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('fax')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_fax" value="{$order.billing_fax}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('email_address')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}billing_email" value="{$order.billing_email}" size="30"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput">
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}" onclick="return confirm('{$mod->Lang('ask_edit_billing')}');" />
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}"/>
  </p>
</div>
{$formend}



