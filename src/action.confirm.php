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

use EcommerceExt\Payment;
use EcommerceExt\ecomm;

if (! isset($gCms))
{
    exit();
}

//
// Initialization
//

// Connect with FrontEndUsers
$feu = $this->GetModuleInstance(MOD_MAMS);
if (! $feu)
{
    echo $this->DisplayErrorMessage($this->Lang('error_nofeumodule'));
    return;
}
$cart_module = ecomm::get_cart_module();
if (! is_object($cart_module))
{
    echo $this->DisplayErrorMessage($this->Lang('error_nocartmodule'));
    return;
}

$uid = orders_helper::is_valid_user();
if ($uid === FALSE)
{
    echo $this->DisplayErrorMessage($this->Lang('error_notloggedin'));
    return;
}
$keyname = orders_helper::get_security_key();
$order_id = \CMSMSExt\encrypted_store::get($keyname);
if ($order_id < 1)
{
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}

//
// Setup
//
// double check that the user is logged in
if (! $this->GetPreference('allow_anon_checkout'))
{
    if ($uid <= 0)
    {
        // not logged in, do the default action
        audit($order_id, $this->GetName(), 'Could not display confirm page (user not loggedin)');
        $destpage = $this->GetPreference('billingpage', $returnid);
        if ($destpage < 1)
        {
            $destpage = $returnid;
        }
        $this->Redirect($id, 'default', $destpage);
        return;
    }

    // Make sure someone isn't pulling a fast one by just trolling for order id's
    $found_uid = $db->GetOne('SELECT feu_user_id FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE id = ?',
        array($order_id));
    if ($uid != $found_uid)
    {
        audit($order_id, $this->GetName(), 'Could not display confirm page (invalid FEU userid)');
        $destpage = $this->GetPreference('billingpage', $returnid);
        if ($destpage < 1)
        {
            $destpage = $returnid;
        }
        $this->Redirect($id, 'default', $destpage);
        return;
    }
}

//
// Get the Data
//
$order = orders_ops::load_by_id($order_id);
if (! $order)
{
    // invalid order id.
    audit($order_id, $this->GetName(), 'Could not display confirm page (incorrect order id)');
    $destpage = $this->GetPreference('billingpage', $returnid);
    if ($destpage < 1)
    {
        $destpage = $returnid;
    }
    $this->Redirect($id, 'default', $destpage);
    return;
}
if ($order->get_status() != \EcommerceExt\ORDERSTATUS_PROPOSED)
{
    // uhm... make sure that the order has the right status.
    audit($order_id, $this->GetName(), 'Could not display confirm page (incorrect order status)');
    $destpage = $this->GetPreference('billingpage', $returnid);
    if ($destpage < 1)
    {
        $destpage = $returnid;
    }
    $this->Redirect($id, 'default', $destpage);
    return;
}

//
// find compatible gateway modules that can handle all of the items in our order.
//
$gateway_modules = gateway_helper::get_compatible_gateways($order);

//
// Handle form submission
//
if (isset($params['orders_confirmorder']) && $this->GetPreference('allow_manual_checkout', 0))
{
    if (\CMSMSExt\encrypted_store::get($keyname) != $order_id)
    {
        audit($order_id, $this->GetName(), 'Could not retrieve encrpted data.');
        echo $this->DisplayErrorMessage($this->Lang('error_retrieve_data'));
        return;
    }

    $order->set_status(\EcOrderMgr\ORDERSTATUS_CONFIRMED);
    $order->save();

    $destpage = $this->GetPreference('invoicepage', $returnid);
    if ($destpage < 1)
    {
        $destpage = $returnid;
    }
    $this->Redirect($id, 'invoice', $destpage, array('order_id' => $order_id));
}

$thetemplate = \xt_param::get_string($params, 'template', $this->GetPreference('dflt_confirmorder_template'));
$tpl = $this->CreateSmartyTemplate($thetemplate, 'confirmorder_');

$forms = array();
if (is_array($gateway_modules) && count($gateway_modules))
{
    // already validated that we can load these modules
    $tpl->assign('gateway_modules', $gateway_modules);
    foreach ($gateway_modules as $module_name)
    {
        $gwmod = \cms_utils::get_module($module_name);
        if (! $gwmod->IsConfigured())
        {
            continue;
        }
        $forms[$module_name] = orders_helper::get_gateway_confirm_form($order, $gwmod, $returnid);
    }
}
if ($this->GetPreference('allow_manual_checkout', 0))
{
    // todo: template for this.
    $out = $this->XTCreateFormStart($id, 'confirm', $returnid, array('orders_confirmorder' => 1));
    $out .= '<input type="submit" name="' . $id . 'orders_confirmorder" value="'
                . $this->lang('procede_manual_checkout') . '"/>';
    $out .= '</form>';
    $forms['__invoice__'] = $out;
}

$tpl->assign('gw_forms', $forms);

/*
 * removed
 * $gateway_module = ecomm::get_payment_module();
 * if( is_object($gateway_module) ) {
 * $tpl->assign('gateway_module',$gateway_module->Getname()); // old stuff.
 *
 * // if this module requires ssl, we had better make sure that we're using it.
 * if( $gateway_module->RequiresSSL() &&
 * (! isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
 * // problem.
 * echo $this->DisplayErrorMessage($this->Lang('error_requiresssl'));
 * return;
 * }
 *
 * if( $gateway_module->RequiresCreditCardInfo() ) {
 * // get the credit card info from the cookie.
 * $cname = 'c'.OrderMgr\orders_helper::get_security_key();
 * if( !isset($_COOKIE[$cname]) ) {
 * // uh-oh
 * audit($order_id,$this->GetName(),'Could not retrieve security key from the cookie.');
 * echo $this->DisplayErrorMessage($this->Lang('error_security_key'));
 * return;
 * }
 * $key = $_COOKIE[$cname];
 * $stuff = setcookie($cname,'',time()-3600,'/');
 *
 * $tmp2 = \CMSMSExt\encrypted_store::get_special($key,$cname);
 * if( !$tmp2 ) {
 * // decryption problem
 * audit($order_id,$this->GetName(),'Decryption problem.');
 * echo $this->DisplayErrorMessage($this->Lang('error_encryption_problem'));
 * return;
 * }
 *
 * $payment = unserialize($tmp2);
 * $gateway_module->SetPaymentId($payment->get_id());
 * $gateway_module->SetCreditCardInfo($payment->get_cc_number(), $payment->get_cc_expiry(), $payment->get_cc_verifycode());
 * }
 *
 * // Setup the basics.
 * $gateway_module->SetCurrencyCode(ecomm::get_currency_code());
 * $gateway_module->SetWeightUnits(ecomm::get_weight_units());
 *
 * $destpage = $this->GetPreference('invoicepage',-1);
 * if( $destpage == -1 || $destpage == '' ) $destpage = $returnid;
 * $url = $this->CreateURL($id,'gateway_complete',$destpage, array('order_id'=>$order_id));
 * $gateway_module->SetDestination($url);
 * $gateway_module->SetInvoice($order->get_invoice());
 * $billing = $order->get_billing();
 * $gateway_module->SetBillingAddress($billing);
 * $shipment = $order->get_shipping(0);
 * $shipping_addy = $shipment->get_shipping_address();
 * $gateway_module->SetShippingAddress($shipping_addy);
 * $gateway_module->SetOrder($order->to_array()); // deprecated
 * $gateway_module->SetOrderObject($order);
 * $gateway_module->SetOrderId($order_id);
 *
 * $str = $this->GetPreference('gateway_description');
 * $str = $this->ProcessTemplateFromData($str);
 * $str = html_entity_decode($str);
 * $gateway_module->SetOrderDescription($str);
 *
 * // Setup the items
 * for( $i = 0; $i < $order->count_destinations(); $i++ ) {
 * $shipping =& $order->get_shipping($i);
 * for( $j = 0; $j < $shipping->count_all_items(); $j++ ) {
 * $item =& $shipping->get_item($j);
 * $item_num = $item->get_item_id();
 * $sku = $item->get_sku();
 * if( empty($sku) ) $sku = $item->get_item_id();
 * $gateway_module->AddItem($item->get_description(), $sku, $item->get_quantity(), $item->get_weight(), $item->get_net_price());
 * }
 * }
 *
 * // Get the output from the module
 * $tpl->assign('gateway',$gateway_module);
 * $formdata = $gateway_module->GetForm($returnid);
 * $tpl->assign('payment_gateway_form',$formdata);
 * }
 * end removed
 */

//
// And give everything to smarty
//
$tpl->assign('currencysymbol', ecomm::get_currency_symbol());
$tpl->assign('weightunits', ecomm::get_weight_units());

$billingpage = $this->GetPreference('billingpage', - 1);
if ($billingpage < 1)
{
    $billingpage = $returnid;
}

$back_url = $this->CreateLink($id, 'default', $billingpage, '', array(), '', true);
$edit_url = $back_url;

$tpl->assign('edit_url', $edit_url);
$tpl->assign('editurl', $edit_url); // for safety
$tpl->assign('back_url', $back_url);
$tpl->assign('back_link_url', $back_url); // for safety
$tpl->assign('backurl', $back_url); // for safety.

$tpl->assign('logged_in', $uid);
$tpl->assign('order_id', $order_id);
$tpl->assign('order_obj', $order);
$tpl->assign('billing', $order->get_billing());
$tpl->assign('cart_module', $cart_module);
$tpl->assign('message', (isset($params['billing_message']) ? htmlspecialchars($params['billing_message']) : ''));

// Create another simple form
// with a button to proceed to the invoice/credit-card payment stuff

//
// Process the template
//
$tpl->display();

// EOF
?>
