<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
{* Change lang="en" to the language of your site *}

<head>
<title>{$EcOrderMgr->GetFriendlyName()} {$mod->Lang('invoice')}: {$ordernumber}</title>
<link rel="stylesheet" type="text/css" href="style.php" />
{$headerhtml}
</head>

<body>
<div id="clean-container">
  <div id="MainContent">
    <div class="pagecontainer">
      <div class="print_pageheader"><a href="#" onclick="window.print(); return false;">{$mod->Lang('print_invoice')}</a></div>
      <div>
      {* the invoice report *}
      {$invoice}
      </div>
    {* pagecontainer *}</div>
  {* MainContent *}</div>
{* clear-container *}</div>
</body>
</html>
