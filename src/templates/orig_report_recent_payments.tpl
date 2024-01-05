{* shipping costs report *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
{* Change lang="en" to the language of your site *}

<head>
<title>{$mod->Lang($report_name)}</title>
<link rel="stylesheet" type="text/css" href="style.php" />
{$EcOrderMgr->GetHeaderHTML()}
</head>

<body>
<div id="clean-container">
  <div id="MainContent">
    <div class="pagecontainer">

{* the print option *}
<div class="print_pageheader"><a href="#" onclick="window.print(); return false;">{$mod->Lang('print_invoice')}</a></div>

{* begin body of main report *}
<div>

<h3>{$mod->Lang($report_name)}</h3>
<h4>{$mod->Lang('from')}: {$start_date|cms_date_format}<h4>
<h4>{$mod->Lang('to')}: {$end_date|cms_date_format}<h4>
{if isset($user)}
{if $oneuser->firstname != '' && $oneuser->lastname != ''}
  {capture assign='created_by'}{$oneuser->lastname}, {$oneuser->firstname}{/capture}
{else}
  {assign var='created_by' value=$user->username}
{/if}
<p>{$mod->Lang('created_by')}: {$created_by}</p>
{/if}

{if $report_data|default:array()|@count == 0}
<h4 style="color: red;">{$mod->Lang('no_report_data')}</h4>
{else}

<table width="100%;" border="1">
 <thead>
  <tr>
    <td>{$mod->Lang('invoice')}</td>
    <td>{$mod->Lang('date')}</td>
    <td>{$mod->Lang('status')}</td>
    <td>{$mod->Lang('method')}</td>
    <td>{$mod->Lang('gateway')}</td>
    <td>{$mod->Lang('transaction_id')}</td>
    <td>{$mod->Lang('amount')}</td>
  </tr>
 </thead>
 <tbody>
 {foreach from=$report_data item='row'}
  <tr>
    <td>{$row.invoice}</td>
    <td>{$row.payment_date|cms_date_format}</td>
    <td>{$mod->Lang($row.status)}</td>
    <td>{if $row.method}{$mod->Lang($row.method)}{/if}</td>
    <td>{$row.gateway}</td>
    <td>{$row.txn_id}</td>
    <td>{$currency_symbol}{$row.amount}</td>
  </tr>
 {/foreach}
 </tbody>
</table>

{/if}

</div>
{* end of report stuff *}

    {* pagecontainer *}</div>
  {* MainContent *}</div>
{* clear-container *}</div>
</body>
</html>