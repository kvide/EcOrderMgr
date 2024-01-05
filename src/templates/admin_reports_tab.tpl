{literal}
<script type="text/javascript">
jQuery(document).ready(function(){
  var tmp = jQuery('#sel_dateopt').val();
  if( tmp != 'exact_dates' )
  {
     jQuery('#exact_dates').hide();
  }

  jQuery('#sel_dateopt').change(function(){
    var val=jQuery(this).val();
    if( val == 'exact_dates' )
    {
       jQuery('#exact_dates').fadeIn();
    }
    else
    {
       jQuery('#exact_dates').fadeOut();
    }
  });
});
</script>
{/literal}

{$formstart}
<table width="100%">
<tr>
  <td>{* column 1 *}
  <fieldset>
  <legend>{$mod->Lang('legend_dates')}:&nbsp;</legend>
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('date_range')}:</p>
    <p class="pageinput">
      <select name="{$actionid}sel_dateopt" id="sel_dateopt">
      {html_options options=$date_opts selected=$sel_dateopt}
      </select>
    </p>
  </div>

  <div id="exact_dates">
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('start_date')}</p>
    <p class="pageinput">
      {capture assign='prefix'}{$actionid}startdate_{/capture}
      {html_select_date prefix=$prefix time=$startdate start_year='-5'}
    </p>
  </div>
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('end_date')}</p>
    <p class="pageinput">
      {capture assign='prefix'}{$actionid}enddate_{/capture}
      {html_select_date prefix=$prefix time=$enddate start_year='-5'}
    </p>
  </div>
  </div>
  </fieldset>
  </td>

  <td valign="top">{* column 2 *}
  <fieldset>
  <legend>{$mod->Lang('legend_reports')}:&nbsp;</legend>
  <div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('report')}:</p>
    <p class="pageinput">
      <select name="{$actionid}sel_reportopt">
      {html_options options=$report_opts selected=$sel_reportopt}
      </select>
    </p>
  </div>
  </fieldset>
  </td>
</tr>
</table>

<div class="pageoverflow">
  <p class="pageinput">
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
  </p>
</div>
{$formend}