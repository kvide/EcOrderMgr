<script type="text/javascript">
$(document).ready(function(){
  $('#filter_btn').click(function(){
     $('#filter_dlg').dialog({ modal: true, width: 'auto' });
     return false;
  })
});
</script>
<div id="filter_dlg" title="{$mod->Lang('filter')}" style="display: none;">
{$formstart}
<table width="100%">
  <tr>
  <td valign="top">
    <div class="pageoverflow">
       <p class="pagetext">{$mod->Lang('status')}:</p>
       <p class="pageinput">{$order_status_dropdown}</p>
    </div>
    <div class="pageoverflow">
       <p class="pagetext">{$mod->Lang('prompt_date_search_type')}:</p>
       <p class="pageinput">{$datelimit_type_dropdown} {$mod->Lang('within')} {$datelimit_interval_dropdown}</p>
    </div>
    <div class="pageoverflow">
       <p class="pagetext">{$mod->Lang('prompt_pagelimit')}:</p>
       <p class="pageinput">
         <select name="{$actionid}pagelimit">
           {html_options options=$pagelimits selected=$val_pagelimit}
         </select>
       </p>
    </div>
  </td>
  <td valign="top">
    <div class="pageoverflow">
       <p class="pagetext">{$mod->Lang('order_number')}:</p>
       <p class="pageinput">{$order_number} <em>{$mod->Lang('info_use_integer')}</em></p>
    </div>
    <div class="pageoverflow">
       <p class="pagetext">{$mod->Lang('item_string')}:</p>
       <p class="pageinput">
          <input type="text" name="{$actionid}item_string" value="{$item_string}" size="40" maxlength="255"/>
          <br/>
          {$mod->Lang('info_item_string')}
       </p>
    </div>
  </td>
  </tr>
</table>
  <div class="pageoverflow">
     <p class="pagetext">&nbsp;</p>
     <p class="pageinput">{$submit}</p>
  </div>
{$formend}
</div>

<div class="pageoptions">
<a href="#" id="filter_btn" class="pointer">{xtimage image='icons/system/view.gif' alt=''} {$mod->Lang('filter')}</a>
{if isset($orders) && count($orders)}
{if isset($export_link)}{$export_link}{/if}
{/if}
</div>

{if !isset($orders) || count($orders) == 0}
  <div class="red" style="width: 98%; text-align: center;">{$mod->Lang('records_matching_query')}: {$recordcount}</div>
{else}
<div class="pageoverflow">
   <div style="float: left; width: 24%;">{$mod->Lang('records_matching_query')}: {$recordcount}</div>
   <div style="float: left; width: 50%; text-align: center;">
     {if isset($prevpage_url)}
       <a href="{$firstpage_url}" title="{$mod->Lang('title_first_page')}">{$mod->Lang('firstpage')}</a>&nbsp;
       <a href="{$prevpage_url}" title="{$mod->Lang('title_prev_page')}">{$mod->Lang('prevpage')}</a>&nbsp;
     {/if}
     {$mod->Lang('prompt_page')}&nbsp;{$pagenum}&nbsp;{$mod->Lang('prompt_of')}&nbsp;{$pagecount}
     {if isset($nextpage_url)}
       &nbsp;<a href="{$nextpage_url}" title="{$mod->Lang('title_next_page')}">{$mod->Lang('nextpage')}</a>
       &nbsp;<a href="{$lastpage_url}" title="{$mod->Lang('title_last_page')}">{$mod->Lang('lastpage')}</a>
     {/if}
   </div>
   <div style="float: right; width: 24%;">&nbsp;</div>
</div>
<table cellspacing="0" class="pagetable cms_sortable tablesorter">
  <thead>
    <tr>
	<th>{$mod->Lang('order_number')}</th>
	<th>{$mod->Lang('customer_name')}</th>
	<th>{$mod->Lang('status')}</th>
	<th>{$mod->Lang('items')}</th>
	<th>{$mod->Lang('created')}</th>
	<th>{$mod->Lang('modified')}</th>
	<th>{$mod->Lang('total')}</th>
	<th>{$mod->Lang('amt_due')}</th>
	<th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
        {if isset($allowdelete)}
	<th class="pageicon {literal}{sorter: false}{/literal}">&nbsp;</th>
        {/if}
    </tr>
  </thead>
  <tbody>
  {foreach from=$orders item='order'}
    <tr>
      <td>{capture assign='tmp'}{$order.ordernum}{/capture}{mod_action_link module='EcOrderMgr' action='admin_manageorder' orderid=$order.id text=$tmp}</td>
      <td>{$order.billing_first_name} {$order.billing_last_name}</td>
      <td>
        {if $order.status == 'paid'}
          <span style="color: green;">{$mod->Lang($order.status)}</span>
	{else}
	   {$mod->Lang($order.status)}
	{/if}
      </td>
      <td>{$order.items}</td>
      <td>{$order.create_date|cms_date_format}</td>
      <td>{$order.modified_date|cms_date_format}</td>
      <td>{$currencysymbol}{$order.total|as_num:2}</td>
      <td>
        {if $order.amt_due < -0.01}
          <span style="color: red;">{$currencysymbol}{$order.amt_due|as_num:2}</span>
        {else}
        {$currencysymbol}{$order.amt_due|as_num:2}
        {/if}
      </td>
      <td>{mod_action_link module='EcOrderMgr' action='admin_manageorder' text=$tmp image='icons/system/view.gif' imageonly=1 orderid=$order.id}</td>
      {if isset($order.delete_link)}
      <td>{$order.delete_link}</td>
      {/if}
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}
