<?php

# BEGIN_LICENSE
# -------------------------------------------------------------------------
# Module: EcOrderMgr (c) 2023 by CMS Made Simple Foundation
#
# An addon module for CMS Made Simple to allow users to create, manage
# and display orders made through the Ecommerce extensions.
# -------------------------------------------------------------------------
# A fork of:
#
# Module: Orders (c) 2008-2019 by Robert Campbell
# (calguy1000@cmsmadesimple.org)
#
# -------------------------------------------------------------------------
#
# CMSMS - CMS Made Simple is (c) 2006 - 2023 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: http://www.cmsmadesimple.org
#
# -------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple. You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
# -------------------------------------------------------------------------
# END_LICENSE

namespace EcOrderMgr;

if (! isset($gCms))
{
    exit();
}
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    echo $this->DisplayErrorMessage('error_permissiondenied');
    return;
}
if (! isset($params['orderid']))
{
    echo $this->DisplayErrorMessage('error_insufficientparams');
    return;
}

// //////////////////////////////////////////
// An action to allow an authorized admin
// to display an invoice for an order in a
// new window, for printing or emailing.
// allow the admin to select the invoice
// template, and optionally send it to
// the customer, or display in a new
// window for printing.
// //////////////////////////////////////////

//
// Initialization
//
$orderid = (int) $params['orderid'];

//
// Setup
//
$templates = $this->ListTemplatesWithPrefix('invoice_');

//
// Handle form submit
//
if (isset($params['cancel']))
{
    $this->Redirect($id, 'admin_manageorder', $returnid, array('orderid' => $orderid));
    return;
}

//
// Give everything to smarty
//
$tmp = array();
foreach ($templates as $one)
{
    $xx = substr($one, strlen('invoice_'));
    $tmp[$one] = $xx;
}

$order_obj = orders_ops::load_by_id($orderid);
$opts = array();
$opts['popup'] = $this->Lang('popup_new_window');
$opts['email'] = $this->Lang('email_invoice');
$smarty->assign('options', $opts);
$smarty->assign('ordernum', $order_obj->get_invoice());
$smarty->assign('orderid', $orderid);
$smarty->assign('templates', $tmp);
$smarty->assign('dflttemplate', 'invoice_' . $this->GetPreference('dflt_invoice_template'));
$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_createinvoice', $returnid, $params));
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel')));
$smarty->assign('popupurl', $this->create_url($id, 'admin_printinvoice', $returnid, array('orderid' => $orderid)));
$smarty->assign('emailurl', $this->create_url($id, 'admin_emailinvoice', $returnid, array('orderid' => $orderid)));
$smarty->assign('returnurl', $this->create_url($id, 'admin_manageorder', $returnid, array('orderid' => $orderid)));

//
// Process template
//
echo $this->ProcessTemplate('admin_createinvoice.tpl');

// EOF
?>
