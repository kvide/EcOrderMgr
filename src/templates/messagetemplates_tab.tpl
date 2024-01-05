{if isset($templates)}
<table class="pagetable" cellspacing="0">
  <thead>
   <tr>
     <th>{$mod->Lang('name')}</th>
     <th>{$mod->Lang('html')}</th>
     <th>{$mod->Lang('created')}</th>
     <th>{$mod->Lang('modified')}</th>
     <th class="pageicon">&nbsp;</th>
     <th class="pageicon">&nbsp;</th>
   </tr>
  </thead>
  <tbody>
  {foreach from=$templates item='one'}
    {cycle values="row1,row2" assign='rowclass'}
    <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
      <td>{mod_action_link module='EcOrderMgr' action='admin_addmsgtemplate' templateid=$one.id text=$one.name}</td>
      <td>{if $one.is_html}{$mod->Lang('yes')}{else}{$mod->Lang('no')}{/if}</td>
      <td>{$one.create_date|cms_date_format}</td>
      <td>{$one.modified_date|cms_date_format}</td>
      <td>{mod_action_link module='EcOrderMgr' action='admin_addmsgtemplate' templateid=$one.id text=$mod->Lang('edit') image='icons/system/edit.gif' imageonly=true}</td>
      <td>{mod_action_link module='EcOrderMgr' action='admin_delmsgtemplate' templateid=$one.id text=$mod->Lang('delete') image='icons/system/delete.gif' imageonly=true confmessage=$mod->Lang('ask_delete')}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

<div class="pageoverflow">
  {$link_newtemplate}
</div>