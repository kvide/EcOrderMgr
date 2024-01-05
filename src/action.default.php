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

//
// initialization
//
$max_destinations = - 1;
$thetemplate = \xt_param::get_string($params, 'template', $this->GetPreference('dflt_billingform_template'));
$tpl = $this->CreateSmartyTemplate($thetemplate, 'billingform_');

if (! ecomm::is_config_ok())
{
    $this->ShowFormattedMessage('EcommerceExt is not completely/properly configured... cannot continue',
                                    TRUE, 'CRITICAL ERROR');
    return;
}

try
{
    // double check that the user is logged in
    $order = null;
    $uid = orders_helper::is_valid_user();
    if ($uid === FALSE)
    {
        throw new \exception($this->Lang('error_notloggedin'));
    }

    // check if we have an order already saved.
    // this happens when we get to the confirmation stage, but then go back and continue shopping.
    // because of the way payment gateways are processed users can go directly there instead of talking to
    // the orders module again.
    $order_maker = $this->GetPreparedOrderFactory();
    $keyname = orders_helper::get_security_key();
    $order_id = \CMSMSExt\encrypted_store::get($keyname);
    if ($order_id)
    {
        $tmp = orders_ops::load_by_id($order_id);
        if (! is_object($tmp))
        {
            $order_id = null;
        }
        else if ($tmp->get_status() != \EcommerceExt\ORDERSTATUS_PROPOSED)
        {
            // only proposed orders can have their items modified.
            throw new \Exception($this->Lang('error_invalidorderstatus'));
        }
        else
        {
            $order_maker->set_order($tmp);
        }
    }

    if (isset($params['invoice_prefix']))
    {
        orders_helper::set_invoice_prefix($params['invoice_prefix']);
    }

    $policy = $this->GetPreference('address_retrieval', billing_address_retriever::ADDR_POLICY_NONE);
    $billing_address_retriever = new billing_address_retriever($this, $policy, $uid);

    // update/build get our basic order from the items in the cart.
    $order = $order_maker->get_basic_order();
    $tmp = $order->get_billing();
    if (! $tmp->is_valid())
    {
        $tmp_addr = $billing_address_retriever->get_address();
        if ($tmp_addr && $tmp_addr->is_valid())
        {
            $order_maker->set_billing_address($tmp_addr);
        }
    }
    if (! $order_maker->supports_different_shipping_locations())
    {
        $max_destinations = 1;
    }

    $errors = array();
    $status = '';
    if ($uid !== FALSE)
    {
        if (isset($params['submit']))
        {
            //
            // Fill from parameters
            // todo: put this in order factory
            //

            // fill in the billing address
            $billing_addr = $order->get_billing();
            if (isset($params['billing_firstname']))
            {
                $billing_addr = new Address();
                $billing_addr->from_array($params, 'billing_');
                $order_maker->set_billing_address($billing_addr);
                // $order->set_billing($billing_addr);
            }
            if (isset($params['order_notes']))
            {
                $notes = \xt_utils::clean_input_html(cms_html_entity_decode($params['order_notes']));
                $order_maker->set_order_notes($notes);
                // $order->set_order_notes($notes);
            }

            // fill in the destination addresses
            for ($i = 0; $i < 1000; $i ++)
            {
                if (! isset($params['shipping_' . $i . '_firstname']))
                {
                    break;
                }

                $addr = new Address();
                $addr->from_array($params, 'shipping_' . $i . '_');
                $order_maker->set_shipping_address($i, $addr);
                $order_maker->set_shipping_pickup($i, \xt_param::get_bool($params, "shipping_{$i}_pickup"));
                $order_maker->set_shipping_message($i,
                                    trim(strip_tags(\xt_utils::get_param($params, "shipping_{$i}_message"))));
            }
            // if we did not find any shipping address info in the form results
            // set the first shipping address to the billing address.
            if ($i == 1 && ! \xt_param::get_int($params, 'orders_shipdifferent'))
            {
                $order_maker->set_shipping_address(0, $billing_addr);
            }

            // handle extra params
            foreach ($params as $key => $val)
            {
                if (startswith($key, 'orders_extra_'))
                {
                    // clean html from $val
                    $key = substr($key, strlen('orders_extra_'));
                    $val = html_entity_decode($val);
                    $val = \xt_utils::clean_input_html($val);
                    $order_maker->set_order_extra($key, $val);
                }
            }

            //
            // Do Data Validation
            // can return multiple errors.
            //
            $errors = $order_maker->validate_addresses();
            if (! empty($errors))
            {
                $status = 'error';
            }

            // adjust the order and make sure shipping,handling and taxes are calculated.
            if (empty($status))
            {
                $order = $order_maker->adjust_for_shipping();

                // save the billing address somewhere.
                $billing_address_retriever->save_address($billing_addr);
            }

            //
            // Do database work
            //
            if (empty($status))
            {

                // If that works, then we handle the database stuff.
                $res = '';
                if ($order_id)
                {
                    // We've previously started an order. Let's use that.
                    // But delete anything in the database first, just incase
                    // The items have changed.

                    // reset all ids to null to force everything to be saved properly.
                    $order->reset_ids();
                    $order->set_id($order_id, true);

                    // and save.
                    $res = $order->save(true);
                }
                else
                {
                    $res = $order->save();
                    $order_id = $order->get_id();
                }

                // toss it in the session
                if (! $order->get_id())
                {
                    die('empty order id');
                }
                \CMSMSExt\encrypted_store::put($order->get_id(), $keyname);

                //
                // Order is submitted
                // Redirect to something that'll ask us to
                // pay for the crap we just ordered.
                //
                session_write_close();
                audit($order->get_id(), 'EcOrderMgr', 'Customer proceeding to confirm page');

                $destpage = $this->GetPreference('confirmpage', $returnid);
                if ($destpage < 1)
                {
                    $destpage = $returnid;
                }
                $this->Redirect($id, 'confirm', $destpage);
            }
        }
    }
}
catch (\Exception $e)
{
    $tpl->assign('status', 'error');
    $tpl->assign('errors', [$e->GetMessage()]);
}

if (! empty($status))
{
    $tpl->assign('status', $status);
    $tpl->assign('errors', $errors);
}

$shipping_different = false;
if ($order)
{
    $addr1 = $order->get_billing();
    if ($order->count_destinations() > 0)
    {
        $addr2 = $order->get_destination(0)->get_shipping_address();
        $shipping_different = ! ($addr1 == $addr2);
    }
}
$num_destinations = ($order) ? $order->count_destinations() : 0;
if ($max_destinations > 0 && $num_destinations > 0)
{
    $num_destinations = min($num_destinations, $max_destinations);
}
$tpl->assign('num_destinations', $num_destinations);
$tpl->assign('country_list', $this->get_country_list_options());
$tpl->assign('state_list', $this->get_state_list_options());
$tpl->assign('order', $order);
$tpl->assign('shipdifferent', $shipping_different);
$tpl->assign('formstart', $this->XTCreateFormStart($id, 'default', $returnid, $params));
$tpl->assign('formend', $this->CreateFormEnd());

$promotions_module = \cms_utils::get_module('EcPromotions');
if (isset($promotions_module))
{
    $tpl->assign('promotions_avail', 1);
}

$tpl->display();


// EOF
?>
