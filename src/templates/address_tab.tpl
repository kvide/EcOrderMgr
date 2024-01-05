{* address tab template *}
{$formstart}
<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_address_retrieval')}</label>
  <select class="grid_8" name="{$actionid}address_retrieval">
    {html_options options=$address_options selected=$address_retrieval}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_requirepostal')}</label>
  <select class="grid_8" name="{$actionid}require_postalcode">{cms_yesno selected=$require_postalcode}</select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_requirestate')}</label>
  <select class="grid_8" name="{$actionid}require_state">{cms_yesno selected=$require_state}</select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_dflt_state')}</label>
  <input class="grid_2" name="{$actionid}dflt_state" value="{$dflt_state}" maxlength="2"/>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_dflt_country')}</label>
  <input class="grid_2" name="{$actionid}dflt_country" value="{$dflt_country}" maxlength="2"/>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_validcountries')}</label>
  <div class="grid_8">
    <textarea class="grid_12" rows="5" name="{$actionid}valid_countries">{$valid_countries}</textarea>
    <div class="grid_12">{$mod->Lang('info_validcountries')}</div>
  </div>
</div>
<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('prompt_validstates')}</label>
  <div class="grid_8">
    <textarea class="grid_12" rows="5" name="{$actionid}valid_states">{$valid_states}</textarea>
    <div class="grid_12">{$mod->Lang('info_validstates')}</div>
  </div>
</div>



{if isset($properties)}
<hr/>
<h4>{$mod->Lang('prompt_address_property_map')}:</h4>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('company')}:</label>
  <select name="{$actionid}map_company" class="grid_8">
    {html_options options=$properties selected=$map->get_company()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('first_name')}:</label>
  <select name="{$actionid}map_firstname" class="grid_8">
    {html_options options=$properties selected=$map->get_firstname()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('last_name')}:</label>
  <select name="{$actionid}map_lastname" class="grid_8">
    {html_options options=$properties selected=$map->get_lastname()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('address1')}:</label>
  <select name="{$actionid}map_address1" class="grid_8">
    {html_options options=$properties selected=$map->get_address1()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('address2')}:</label>
  <select name="{$actionid}map_address2" class="grid_8">
    {html_options options=$properties selected=$map->get_address2()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('city')}:</label>
  <select name="{$actionid}map_city" class="grid_8">
    {html_options options=$properties selected=$map->get_city()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('state/province')}:</label>
  <select name="{$actionid}map_state" class="grid_8">
    {html_options options=$properties selected=$map->get_state()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('postal')}:</label>
  <select name="{$actionid}map_postal" class="grid_8">
    {html_options options=$properties selected=$map->get_postal()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('country')}:</label>
  <select name="{$actionid}map_country" class="grid_8">
    {html_options options=$properties selected=$map->get_country()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('phone')}:</label>
  <select name="{$actionid}map_phone" class="grid_8">
    {html_options options=$properties selected=$map->get_phone()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('fax')}:</label>
  <select name="{$actionid}map_fax" class="grid_8">
    {html_options options=$properties selected=$map->get_fax()}
  </select>
</div>

<div class="c_full cf">
  <label class="grid_3">{$mod->Lang('email')}:</label>
  <select name="{$actionid}map_email" class="grid_8">
    {html_options options=$properties selected=$map->get_email()}
  </select>
</div>
{/if}

<hr/>
<div class="c_full cf">
  <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
</div>

{$formend}