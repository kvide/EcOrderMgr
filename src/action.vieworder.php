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

use EcommerceExt\OrderMgr;

if (! isset($gCms))
{
    exit();
}

if (isset($params['back_to_orders_submit']))
{
    $this->Redirect($id, 'defaultadmin', $returnid, array('order_status' => $params['order_status_filter']));
}

// require_once(dirname(__FILE__) . '/lib/class.orders_ops.php');
$order_obj = orders_ops::load_by_id($params['order_id']);

if (isset($params['order_status_submit']))
{
    $order_obj->set_status($params['order_status']);
    $order_obj->save();
}

$smarty->assign('order', $order->to_array());
$smarty->assign('mod', $this);

$smarty->assign('order_status_form_start', $this->CreateFormStart($id, 'vieworder', $returnid));
$smarty->assign('order_status_dropdown', $this->CreateInputDropdown($id, 'order_status', array(
    $this->Lang('proposed') => 'proposed',
    $this->Lang('submitted') => 'submitted',
    $this->Lang('paid') => 'paid',
    $this->Lang('shipped') => 'shipped',
    $this->Lang('cancelled') => 'cancelled'
), - 1, $order->get_status()));
$smarty->assign('order_status_form_submit', $this->CreateInputSubmit($id, 'order_status_submit',
    $this->Lang('update_status')) . $this->CreateInputHidden($id, 'order_id', $params['order_id'])
        . $this->CreateInputHidden($id, 'order_status_filter', $params['order_status_filter']));
$smarty->assign('back_to_orders', $this->CreateInputSubmit($id, 'back_to_orders_submit',
    $this->Lang('back_to_orders')));
$smarty->assign('order_status_form_end', $this->CreateFormEnd());

echo $this->ProcessTemplate('vieworder.tpl');

// EOF
?>
