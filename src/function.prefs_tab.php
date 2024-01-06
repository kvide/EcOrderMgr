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
if (! $this->CheckPermission('Modify Site Preferences'))
{
    return;
}

$ssl_opts = array(
    $this->Lang('yes') . '&nbsp;&nbsp;' => 1,
    $this->Lang('no') . '&nbsp;&nbsp;' => 0
);
$tmp = $this->GetModulesWithCapability('payment_gateway', array('baseversion' => '0.98.0'));
$ccprocessing_modules = [-1 => $this->Lang('none')];
if ($tmp)
{
    $ccprocessing_modules = array_flip(array_merge($ccprocessing_modules, $tmp));
}

$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_saveprefs', $returnid));
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('prompt_adminemail', $this->Lang('admin_email_addresses'));
$smarty->assign('input_adminemail',
                    $this->CreateInputText($id, 'admin_email', $this->GetPreference('admin_email', ''), 100, 1024));

$smarty->assign('prompt_require_membership', $this->Lang('require_group_membership'));

$feu = &$this->GetModuleInstance(\MOD_MAMS);
$grouplist = $feu->GetGroupList();

$grouplist = \xt_array::hash_prepend($grouplist, $this->Lang('none'), '-1');
$smarty->assign('input_require_membership',
                $this->CreateInputDropdown($id, 'require_membership', $grouplist, - 1,
                                                $this->GetPreference('require_membership')));
$smarty->assign('submit',
                $this->CreateInputSubmit($id, 'submit', $this->Lang('submit'), '', '', $this->Lang('ask_saveprefs')));

$smarty->assign('input_ordernum_prefix',
                $this->CreateInputText($id, 'ordernum_prefix', $this->GetPreference('ordernum_prefix', 'INV'), 5, 5));

$smarty->assign('input_encryption_key',
                $this->CreateInputText($id, 'encryption_key', $this->GetPreference('encryption_key'), 40, 40));

$contentops = &$gCms->GetContentOperations();
$smarty->assign('input_billingpage',
    $contentops->CreateHierarchyDropdown('', $this->GetPreference('billingpage'), $id . 'billingpage'));
$smarty->assign('input_paymentpage',
    $contentops->CreateHierarchyDropdown('', $this->GetPreference('paymentpage'), $id . 'paymentpage'));
$smarty->assign('input_shippingpage',
    $contentops->CreateHierarchyDropdown('', $this->GetPreference('shippingpage'), $id . 'shippingpage'));
$smarty->assign('input_confirmpage',
    $contentops->CreateHierarchyDropdown('', $this->GetPreference('confirmpage'), $id . 'confirmpage'));
$smarty->assign('input_invoicepage',
    $contentops->CreateHierarchyDropdown('', $this->GetPreference('invoicepage'), $id . 'invoicepage'));
$smarty->assign('input_invoice_message',
    $this->CreateTextArea(true, $id, $this->GetPreference('invoice_message'), 'invoice_message'));
$smarty->assign('input_ccprocessing_module',
    $this->CreateInputDropdown($id, 'ccprocessing_module', $ccprocessing_modules, - 1,
                                    $this->GetPreference('ccprocessing_module', - 1)));

$tmp = 'invoice_' . $this->GetPreference('dflt_invoice_template');
$smarty->assign('input_admin_invoice_template',
    \xt_template_utils::create_template_dropdown($id, 'admin_invoice_template', 'invoice_',
                                                    $this->GetPreference('admin_invoice_template', $tmp)));

$smarty->assign('input_gateway_description',
    $this->CreateInputText($id, 'gateway_description', $this->GetPreference('gateway_description'), 40, 255));
$smarty->assign('datastore_timeout', $this->GetPreference('datastore_timeout', 10));
$smarty->assign('input_allow_anon_checkout',
    $this->CreateInputYesNoDropdown($id, 'allow_anon_checkout', $this->GetPreference('allow_anon_checkout', 0)));
$smarty->assign('allow_manual_checkout', $this->GetPreference('allow_manual_checkout', 0));

echo $this->ProcessTemplate('prefs_tab.tpl');

// EOF
?>
