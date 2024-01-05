<h3>{$mod->Lang('create_invoice_for',$ordernum)}</h3>

{literal}
<script type='text/javascript'>
var opopupurl = {/literal}'{$popupurl}'{literal};
var oemailurl = {/literal}'{$popupurl}'{literal};
var actionid = {/literal}'{$actionid}'{literal};
var oreturnurl = {/literal}'{$returnurl}'{literal};

opopupurl = opopupurl.replace(/&amp;/g,'&');
oemailurl = oemailurl.replace(/&amp;/g,'&');
oreturnurl = oreturnurl.replace(/&amp;/g,'&');

function submit_clicked(elem)
{
  var idx = document.getElementById('sel_template').selectedIndex;
  var template = document.getElementById('sel_template').options[idx].value;
  idx = document.getElementById('sel_action').selectedIndex;
  var action = document.getElementById('sel_action').options[idx].value;

  popupurl = opopupurl + '&suppress_output=1&' + actionid + 'template=' + template;
  emailurl = oemailurl + '&' + actionid + 'template=' + template + '&'+actionid+'email=1';

  if( action == 'popup' )
  {
    window.open(popupurl,'_blank',config='height=300,width=800,scrollbars=yes,toolbar=no,menubar=yes,location=no,directories=no,status=no');
    window.location = oreturnurl;
  }
  else
  {
    window.location = emailurl;
  }
}
</script>
{/literal}

{$formstart}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('select_invoice_template')}:</p>
  <p class="pagetext">
    <select id="sel_template" name="{$actionid}template">
      {html_options options=$templates selected=$dflttemplate}
    </select>
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('select_action')}:</p>
  <p class="pagetext">
    <select id="sel_action" name="{$actionid}action">
      {html_options options=$options}
    </select>
  </p>
</div>
<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput"><input type="submit" value="{$mod->Lang('submit')}" onclick="submit_clicked(this); return false;"/>{$cancel}</p>
</div>
{$formend}