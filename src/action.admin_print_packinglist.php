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
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS)
    && ! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    exit();
}

$order_id = \xt_param::get_int($params, 'orderid');
$shipping_id = \xt_param::get_int($params, 'shipping_id');
if ($order_id < 1 || $shipping_id < 1)
{
    throw new \LogicException('Invalid parameters passed to admin_print_packinglist action');
}

$order = orders_ops::load_by_id($order_id);
if (! $order)
{
    throw new \LogicException('Could not find order with the specified id');
}

$dest = $order->get_destination_by_id($shipping_id);
if (! $dest)
{
    throw new \LogicException('Could not find the destination object specified by id ' . $shipping_id);
}

// NOTE: packing list dimensions and weight are in metric (grams and mm)
$packing_list = $dest->get_packing_list();
$total_weight = 0;
$total_value = 0;
$boxes = $packing_list->get_boxes();
foreach ($boxes as $box)
{
    $total_value += $box->total_value;
    $total_weight += $box->total_weight;
}
$tpl = $this->CreateSmartyTemplate('admin_print_packinglist.tpl');
$tpl->assign('order', $order);
$tpl->assign('packing_list', $packing_list);
$tpl->assign('total_value', $total_value);
$tpl->assign('total_weight', $total_weight);
$tpl->assign('shipping_id', $shipping_id);

$tpl->display();

?>
