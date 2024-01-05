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
    echo $this->ShowErrors($this->Lang('error_permissiondenied'));
    return;
}
$this->SetCurrentTab('prefs');

if (isset($params['setup_events']))
{
    $this->AddEventHandler('EcPaymentExt', 'on_incoming_event', false);
    $this->RedirectToTab($id, '', '', 'admin_preferences');
    return;
}

if (isset($params['admin_email']))
{
    $this->SetPreference('admin_email', trim($params['admin_email']));
}
$this->SetPreference('require_membership', $params['require_membership']);

if (isset($params['ccprocessing_module']))
{
    $this->SetPreference('ccprocessing_module', $params['ccprocessing_module']);
}
else
{
    $this->SetPreference('ccprocessing_module', - 1);
}

if (isset($params['ordernum_prefix']))
{
    $this->SetPreference('ordernum_prefix', trim($params['ordernum_prefix']));
}
else
{
    $this->SetPreference('ordernum_prefix', '');
}

$this->SetPreference('allow_manual_checkout', (int) $params['allow_manual_checkout']);
$this->SetPreference('admin_invoice_template', trim($params['admin_invoice_template']));
$this->SetPreference('invoice_message', cms_html_entity_decode($params['invoice_message']));
$this->SetPreference('encryption_key', $params['encryption_key']);
$this->SetPreference('billingpage', $params['billingpage']);
$this->SetPreference('shippingpage', $params['shippingpage']);
$this->SetPreference('paymentpage', $params['paymentpage']);
$this->SetPreference('confirmpage', $params['confirmpage']);
$this->SetPreference('invoicepage', $params['invoicepage']);
$this->SetPreference('gateway_description', $params['gateway_description']);
$this->SetPreference('allow_anon_checkout', $params['allow_anon_checkout']);

$num = (int) $params['datastore_timeout'];
$num = max(1, $num);
$num = min(1000, $num);
$this->SetPreference('datastore_timeout', $num);

$this->RedirectToTab($id, '', '', 'admin_preferences');

// EOF
?>
