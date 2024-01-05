<h3>{$order.invoice} - {$mod->Lang('send_message')}</h3>
<h4>{$mod->Lang('to')}: {$order.email}</h4>
<br/>

{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('template')}:</p>
  <p class="pageinput">{$input_template}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('subject')}:</p>
  <p class="pageinput">{$input_subject}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('message')}:</p>
  <p class="pageinput">{$input_body}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput">{$input_send}{$input_cancel}</p>
</div>
{$formend}