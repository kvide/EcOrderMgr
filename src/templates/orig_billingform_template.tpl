{* billing form template *}
<script>
{* relies on jquery being enabled already *}
$(function(){
   $('#btn_showshipping').change(function(ev){
       ev.preventDefault();
       var val = $(this).is(':checked');
       var vfor = $(this).data('for');
       if( !vfor ) return;
       vfor = '#'+vfor;
       if( val ) {
         $(vfor).show();
       }
       else {
         $(vfor).hide();
       }
   }).trigger('change');
})
</script>

{* this is a frontend template *}
<h3>{$mod->Lang('order_processing')}:</h3>
{if !empty($status)}
  {* there is an error of some type.... you can check the $status variable for the type of error *}
  <div class="alert alert-danger">
  {if !empty($errors) && count($errors) == 1}
    {$errors[0]}
  {else}
    <ul>
    {foreach $errors as $error}
      <li>{$error}</li>
    {/foreach}
    </ul>
  {/if}
  </div>
{/if}

{if isset($order)}
  {$formstart}
  <div class="billing_info">
    <fieldset>
      <legend>&nbsp;{$mod->Lang('billing_info')}:&nbsp;</legend>
      {$billing=$order->get_billing()}
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('company')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_company" value="{$billing->get_company()}" size="50"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('first_name')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_firstname" value="{$billing->get_firstname()}" size="30"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('last_name')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_lastname" value="{$billing->get_lastname()}" size="30"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('address1')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_address1" value="{$billing->get_address1()}" size="30"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('address2')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_address2" value="{$billing->get_address2()}" size="30"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('city')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_city" value="{$billing->get_city()}" size="30"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('state/province')}:</p>
        <p class="col-sm-8">
          <select name="{$actionid}billing_state">
	    {orders_state_options selected=$billing->get_state()}
	  </select>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right">{$mod->Lang('postal')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_postal" value="{$billing->get_postal()}" size="20"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('country')}:</p>
        <p class="col-sm-8">
          <select name="{$actionid}billing_country">
	    {orders_country_options options=$billing->get_country()}
	  </select>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('phone')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_phone" value="{$billing->get_phone()}" size="20"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right">{$mod->Lang('fax')}:</p>
        <p class="col-sm-8">
          <input type="text" name="{$actionid}billing_fax" value="{$billing->get_fax()}" size="20"/>
        </p>
      </div>
      <div class="row">
        <p class="col-sm-4 text-right required">{$mod->Lang('email')}:</p>
        <p class="col-sm-8">
          <input type="email" name="{$actionid}billing_email" value="{$billing->get_email()}" size="40"/>
        </p>
      </div>
    </fieldset>
  </div>

  {* depending on your application you may use javascript/css to show/hide this information.
     if a destination does not have a shipping address, the billing address will be used
     Note:  Using the GiftBaskets module a single order may have multiple destination with different shipping addresses
     Note:  Using ProductsByVendor module a single order may have multiple destinations with different source addresses
  *}
  {for $i = 0 to $num_destinations - 1}
    {$dest=$order->get_destination($i)}
    {$addr=$dest->get_shipping_address()}
    <div class="shipping_info">
      <fieldset>
        <legend>{if $num_destinations == 1}{$mod->Lang('shipping_info_if_different')}:{else}{$mod->Lang('shipping_info_for')}: {$dest->get_name()}{/if}
	{if $num_destinations == 1}
	   &nbsp;&nbsp;
                <input id="btn_showshipping" data-for="destination_{$i}" name="{$actionid}orders_shipdifferent" type="checkbox" value="1" {if $shipdifferent}checked{/if}/>
	{/if}
        </legend>

	<div class="shipping_address" id="destination_{$i}" {if $num_destinations == 1}style="display: none;"{/if}>
	{if $dest->allows_pickup()}
        <div class="row">
           <label class="col-sm-4 text-right" for="do_pickup">{$mod->Lang('local_pickup')}</label>
           <select class="col-sm-8" id="do_pickup" name="{$actionid}shipping_{$i}_pickup">
              {xt_yesno_options selected="{$dest->get_pickup()}"}
           </select>
        </div>
        {/if}

        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('company')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_company" value="{$addr->get_company()}" size="50"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('first_name')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_firstname" value="{$addr->get_firstname()}" size="30"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('last_name')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_lastname" value="{$addr->get_lastname()}" size="30"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('address1')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_address1" value="{$addr->get_address1()}" size="30"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('address2')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_address2" value="{$addr->get_address2()}" size="30"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('city')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_city" value="{$addr->get_city()}" size="30"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('state/province')}:</p>
          <p class="col-sm-8">
            <select name="{$actionid}shipping_{$i}_state">{orders_state_options selected=$addr->get_state()}</select>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('postal')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_postal" value="{$addr->get_postal()}" size="20"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('country')}:</p>
          <p class="col-sm-8">
            <select name="{$actionid}shipping_{$i}_country">
	    {orders_country_options selected=$addr->get_country()}
	    </select>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('phone')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_phone" value="{$addr->get_phone()}" size="20"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('fax')}:</p>
          <p class="col-sm-8">
            <input type="text" name="{$actionid}shipping_{$i}_fax" value="{$addr->get_fax()}" size="20"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('email')}:</p>
          <p class="col-sm-8">
            <input type="email" name="{$actionid}shipping_{$i}_email" value="{$addr->get_email()}" size="40"/>
          </p>
        </div>
        <div class="row">
          <p class="col-sm-4 text-right required">{$mod->Lang('dest_notes')}:</p>
          <p class="col-sm-8">
	    <textarea name="{$actionid}shipping_{$i}_message" rows="3" cols="50">{$dest->get_message()}</textarea>
          </p>
        </div>
        </div>{* .shipping_address *}
     </fieldset>
    </div>
  {/for}

  <fieldset>
    {* extra data to associate with the order.
       This section allows creating numerous fields to associate with the data.
       The input field names must be of the form {$actionid}orders_extra_NAME
       The NAME must contain only alphanumeric characters, and underscore. No spaces, no unprintable or characters that need encoding.
       The system does no validation on these fields, other than to clean all input html.
       The extra data is usable in any template where the order object is available by using $order->get_extra('NAME') or by using the $order->get_all_extra() method.
       ** see the default invoice template for an example **
     *}
    <legend>&nbsp;{$mod->Lang('order_extra')}:&nbsp;</legend>
    <div class="row">
      <p class="col-sm-4 text-right">User Field 1</p>
      <p class="col-md-8">
         <select name="{$actionid}orders_extra_fld1">{xt_yesno_options selected=$order->get_extra('fld1')}</select>
      </p>
    </div>
    <div class="row">
      <p class="col-sm-4 text-right">User Field 2</p>
      <p class="col-md-8">
         <input type="text" name="{$actionid}orders_extra_fld2" placeholder="some user data" value="{$order->get_extra('fld2')}"/>
      </p>
    </div>
  </fieldset>

  <fieldset>
    <legend>&nbsp;{$mod->Lang('order_notes')}:&nbsp;</legend>
    <div class="alert alert-info">{$mod->Lang('info_order_notes')}</div>
    <div class="row">
      <p class="col-sm-4 text-right required">{$mod->Lang('order_notes')}:</p>
      <p class="col-sm-8">
        <textarea name="{$actionid}order_notes" rows="3" cols="50"/>{$order->get_order_notes()}</textarea>
      </p>
    </div>
  </fieldset>

  <div class="row">
    <p class="col-sm-4 text-right required"></p>
    <p class="col-sm-8">
      <input type="submit" name="{$actionid}submit" value="{$mod->Lang('next')}"/>
    </p>
  </div>
  {$formend}
{/if}
