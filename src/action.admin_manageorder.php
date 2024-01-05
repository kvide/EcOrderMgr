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

use EcommerceExt\ecomm;

if (! isset($gCms))
{
    exit();
}
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS)
    && ! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    return;
}

$this->SetCurrentTab('orders');
$statuses = array();
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_PENDING)] = \EcommerceExt\ITEMSTATUS_PENDING;
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_DELIVERED)] = \EcommerceExt\ITEMSTATUS_DELIVERED;
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_SHIPPED)] = \EcommerceExt\ITEMSTATUS_SHIPPED;
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_NOTSHIPPED)] = \EcommerceExt\ITEMSTATUS_NOTSHIPPED;
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_HOLD)] = \EcommerceExt\ITEMSTATUS_HOLD;
$statuses[$this->Lang(\EcommerceExt\ITEMSTATUS_BACKORDER)] = \EcommerceExt\ITEMSTATUS_BACKORDER;

// display a report and form that provides information about this order
// people with authorized permission can edit certain details
// provide links to send users an email
if (! isset($params['orderid']))
{
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}
$order_id = (int) $params['orderid'];

if (isset($params['cancel']))
{
    $this->RedirectToTab($id);
}

$order = orders_ops::load_by_id($order_id);
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    $order->hide_ccinfo();
}

//
// Handle submit
//
if (isset($params['submit']))
{
    if (isset($params['input_status']))
    {
        $order->set_status(trim($params['input_status']));
    }
    if (isset($params['input_confirmnum']))
    {
        $order->set_confirmation_num(trim($params['input_confirmnum']));
    }

    // Update the items
    foreach ($params as $key => $value)
    {
        if (! startswith($key, 'input_itemstatus_'))
        {
            continue;
        }

        $itemid = (int) substr($key, strlen('input_itemstatus_'));
        for ($j = 0; $j < $order->count_destinations(); $j ++)
        {
            $shipping = &$order->get_shipping($j);
            for ($i = 0; $i < $shipping->count_all_items(); $i ++)
            {
                $item = &$shipping->get_item($i);
                if ($item->get_id() != $itemid)
                {
                    continue;
                }
                $item->set_status($value);
            }
        }
    }

    $order->save();

    // and redirect
    $this->RedirectToTab($id);
}

// and do the smarty stuff
if ($this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    $smarty->assign('canmanage', 1);
    $smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_manageorder', $returnid, $params));
    $smarty->assign('formend', $this->CreateFormEnd());

    $statuses = array();
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_PROPOSED)] = \EcommerceExt\ORDERSTATUS_PROPOSED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_SUBMITTED)] = \EcommerceExt\ORDERSTATUS_SUBMITTED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_PAID)] = \EcommerceExt\ORDERSTATUS_PAID;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_BALANCEDUE)] = \EcommerceExt\ORDERSTATUS_BALANCEDUE;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_INVOICED)] = \EcommerceExt\ORDERSTATUS_INVOICED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_CANCELLED)] = \EcommerceExt\ORDERSTATUS_CANCELLED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_HOLD)] = \EcommerceExt\ORDERSTATUS_HOLD;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_INCOMPLETE)] = \EcommerceExt\ORDERSTATUS_INCOMPLETE;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_CONFIRMED)] = \EcommerceExt\ORDERSTATUS_CONFIRMED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_COMPLETED)] = \EcommerceExt\ORDERSTATUS_COMPLETED;
    $statuses[$this->Lang(\EcommerceExt\ORDERSTATUS_SUBSCRIBED)] = \EcommerceExt\ORDERSTATUS_SUBSCRIBED;
    $statuses = array_flip($statuses);
    $smarty->assign('order_statuses', $statuses);
    $smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));
    $smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel')));
}
if ($this->CheckPermission(\EcOrderMgr\ORDERS_PERM_CONTACT_CUSTOMERS))
{
    $smarty->assign('sendmail_link', $this->CreateImageLink($id, 'admin_sendmail', $returnid,
                    $this->Lang('send_message'), 'email_add.png', array('orderid' => $order_id), '', '', false));
}
if ($this->CheckPermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS))
{
    $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages
               WHERE order_id = ? LIMIT 1';
    $res = $db->GetOne($query, array($order_id));
    if ($res)
    {
        $smarty->assign('viewmail_link',
                    $this->CreateImageLink($id, 'admin_viewmessages', $returnid,
                        $this->Lang('view_messages'), 'email.png', array('orderid' => $order_id), '', '', false));
    }
}

$print_url = $this->create_url($id, 'admin_printinvoice', $returnid, array('orderid' => $order_id));
$print_url .= '&suppress_output=1';
$smarty->assign('print_url', $print_url);
$statuses = array();
$statuses[\EcommerceExt\ITEMSTATUS_PENDING] = $this->Lang(\EcommerceExt\ITEMSTATUS_PENDING);
$statuses[\EcommerceExt\ITEMSTATUS_DELIVERED] = $this->Lang(\EcommerceExt\ITEMSTATUS_DELIVERED);
$statuses[\EcommerceExt\ITEMSTATUS_SHIPPED] = $this->Lang(\EcommerceExt\ITEMSTATUS_SHIPPED);
$statuses[\EcommerceExt\ITEMSTATUS_NOTSHIPPED] = $this->Lang(\EcommerceExt\ITEMSTATUS_NOTSHIPPED);
$statuses[\EcommerceExt\ITEMSTATUS_HOLD] = $this->Lang(\EcommerceExt\ITEMSTATUS_HOLD);
$statuses[\EcommerceExt\ITEMSTATUS_BACKORDER] = $this->Lang(\EcommerceExt\ITEMSTATUS_BACKORDER);
$smarty->assign('statuses', $statuses);
$config = $gCms->GetConfig();
$smarty->assign('expand', $config['root_url'] . '/modules/' . $this->GetName() . '/icons/bullet_toggle_plus.png');
$smarty->assign('contract', $config['root_url'] . '/modules/' . $this->GetName() . '/icons/bullet_toggle_minus.png');
$smarty->assign('currencysymbol', ecomm::get_currency_symbol());
$smarty->assign('weightunits', ecomm::get_weight_units());
$smarty->assign('order', $order);
$smarty->assign('print_img', $this->DisplayImage('printer.png', $this->Lang('print_invoice')));
$smarty->assign('invoice_img', $this->DisplayImage('invoice_lg.gif', $this->Lang('create_invoice')));
$can_manage_orders = $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS);
$can_manual_process = 0;
$ccprocessing_gateway = $this->GetPreference('ccprocessing_module', - 1);
if ($ccprocessing_gateway && $ccprocessing_gateway != - 1)
{
    $module = \cms_utils::get_module($ccprocessing_gateway);
    if ($module && $module->RequiresCreditCardInfo())
    {
        $can_manual_process = 1;
    }
}
$smarty->assign('can_manage_orders', $can_manage_orders);
$smarty->assign('can_manual_process', $can_manual_process);

echo $this->ProcessTemplate('admin_manageorder.tpl');

#
# EOF
#
?>
