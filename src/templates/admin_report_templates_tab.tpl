<table class="pagetable" cellspacing="0">
<thead>
  <tr>
    <th>{$mod->Lang('name')}</td>
    <th class="pageicon">
    </th>
  </tr>
</thead>
<tbody>
{foreach from=$report_opts key='classname' item='one'}
  {cycle values="row1,row2" assign="rowclass"}
  <tr class="{$rowclass}" onmouseover="this.className='{$rowclass}hover';" onmouseout="this.className='{$rowclass}';">
    <td>{$one}</td>
    <td>
      {mod_action_link module=EcOrderMgr action='admin_edit_report_template' template=$classname image='icons/system/edit.gif' imageonly=1}
    </td>
  </tr>
{/foreach}
</tbody>
</table>