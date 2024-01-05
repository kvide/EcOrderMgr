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
    echo $this->DisplayErrormessage($this->Lang('error_insufficientparams'));
    return;
}

//
// Initialization
//
require_once (dirname(__FILE__) . '/lib/class.orders_ops.php');
$order_id = (int) $params['orderid'];

//
// Get Data
//
$order_obj = orders_ops::load_by_id($order_id);
$billing_addr = &$order_obj->get_billing();

//
// Handle Form Submission
//
if (isset($params['cancel']))
{
    $this->Redirect($id, 'admin_manageorder', $returnid, array('orderid' => $order_id));
    return;
}
else if (isset($params['submit']))
{
    $status = '';
    foreach ($params as $key => $value)
    {
        if (startswith($key, 'billing'))
        {
            $nkey = substr($key, 8);
            $fn = 'set_' . $nkey;
            $billing_addr->$fn($value);
        }
    }

    if (! $billing_addr->is_valid())
    {
        echo $this->ShowErrors($this->Lang('error_invalidfield'));
        $status = 'error';
    }

    if (empty($status))
    {
        $order_obj->save();

        // and get out of here
        $this->Redirect($id, 'admin_manageorder', $returnid, array('orderid' => $order_id));
    }
}

//
// Give Everything to smarty
//
$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_editorderbilling', $returnid, $params));
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('input_billing_country', $this->CreateInputCountryDropdown($id, 'billing_country',
                                                                            $billing_addr->get_country()));
$smarty->assign('order', $order_obj->to_array());
$smarty->assign('ordernum', $order_obj->get_invoice());

//
// Process Template
//
echo $this->ProcessTemplate('admin_editorderbilling.tpl');
// EOF
?>
