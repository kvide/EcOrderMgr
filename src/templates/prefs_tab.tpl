{$formstart}
<fieldset>
<legend>{$mod->Lang('general_settings')}: </legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_allow_anon_checkout')}:</p>
  <p class="pageinput">
    {$input_allow_anon_checkout}
    <br/>
    {$mod->Lang('info_allow_anon_checkout')}
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$prompt_require_membership}:</p>
  <p class="pageinput">{$input_require_membership}
  <br/>{$mod->Lang('info_require_membergroup')}
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_allow_manual_checkout')}:</p>
  <p class="pageinput">
    <select name="{$actionid}allow_manual_checkout">
    {xt_yesno_options selected=$allow_manual_checkout}
    </select>
    <br/>
    {$mod->Lang('info_allow_manual_checkout')}
  </p>
</div>
<hr/>

<div class="pageoverflow">
  <p class="pagetext">{$prompt_adminemail}:</p>
  <p class="pageinput">{$input_adminemail}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_ordernum_prefix')}:</p>
  <p class="pageinput">{$input_ordernum_prefix}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_billingpage')}:</p>
  <p class="pageinput">{$input_billingpage}<br/>
    {$mod->Lang('info_billingpage')}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_paymentpage')}:</p>
  <p class="pageinput">{$input_paymentpage}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_shippingpage')}:</p>
  <p class="pageinput">{$input_shippingpage}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_confirmpage')}:</p>
  <p class="pageinput">{$input_confirmpage}</p>
</div>
</fieldset>

<fieldset>
<legend>{$mod->Lang('security_settings')}: </legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_datastore_timeout')}:</p>
  <p class="pageinput"><input type="text" name="{$actionid}datastore_timeout" value="{$datastore_timeout}" size="3" maxlength="3"/></p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_encryption_key')}:</p>
  <p class="pageinput">{$input_encryption_key}<br/>{$mod->Lang('info_encryption_key')}</p>
</div>
</fieldset>

<fieldset>
<legend>{$mod->Lang('invoice_settings')}: </legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_admin_invoice_template')}:</p>
  <p class="pageinput">{$input_admin_invoice_template}<br/>{$mod->Lang('info_admin_invoice_template')}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_invoicepage')}:</p>
  <p class="pageinput">{$input_invoicepage}<br/>{$mod->Lang('info_invoicepage')}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_invoice_message')}:</p>
  <p class="pageinput">{$input_invoice_message}</p>
</div>
</fieldset>


<fieldset>
<legend>{$mod->Lang('payment_gateway_settings')}: </legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_gateway_description')}:</p>
  <p class="pageinput">{$input_gateway_description}<br/>
    {$mod->Lang('info_gateway_description')}
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_ccprocessing_module')}:</p>
  <p class="pageinput">{$input_ccprocessing_module}
    <br/>
    {$mod->Lang('info_ccprocessing_module')}
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_setup_events')}:</p>
  <p class="pageinput">
    <input type="submit" name="{$actionid}setup_events" value="{$mod->Lang('setup')}"/>
    <br/>
    {$mod->Lang('info_setup_events')}
  </p>
</div>
</fieldset>

<div class="pageoverflow">
  <p class="pageinput">{$submit}</p>
</div>

{$formend}