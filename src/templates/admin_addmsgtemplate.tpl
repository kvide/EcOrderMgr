{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('template_name')}:</p>
  <p class="pageinput">{$input_name}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('html_email')}:</p>
  <p class="pageinput">{$input_html}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('email_subject')}:</p>
  <p class="pageinput">{$input_subject}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_template')}:</p>
  <p class="pageinput">{$input_template}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput">{$submit}{$cancel}</p>
</div>
{$formend}