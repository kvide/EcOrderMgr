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

$gateway_module = ecomm::get_payment_module();
if (! is_object($gateway_module))
{
    // should never get here... display some weird message.
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}
if (! $gateway_module->RequiresCreditCardInfo())
{
    // should never get here... display some weird message.
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}
// redirect to https if required, include weird check for IIS. SjG
if ($gateway_module->RequiresSSL() && (! isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
{
    \xt_redirect::redirect_https();
    exit();
}

// double check that the user is logged in
$uid = orders_helper::is_valid_user();
if ($uid === FALSE)
{
    $this->DisplayErrorMessage($this->Lang('error_notloggedin'));
    return;
}
$keyname = orders_helper::get_security_key();
$order_id = \xt_param::get_int($params, 'order_id');
$order_id = \CMSMSExt\encrypted_store::get($keyname);
if ($order_id < 1)
{
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}

$thetemplate = \xt_param::get_string($params, 'template', $this->GetPreference('dflt_paymentform_template'));
$tpl = $this->CreateSmartyTemplate($thetemplate, 'paymentform_');

$tpl->assign('logged_in', $uid);
$tpl->assign('order_id', $order_id);

if (! $this->GetPreference('allow_anon_checkout'))
{
    if ($uid <= 0)
    {
        // not logged in, do the default action
        $destpage = $this->GetPreference('billingpage', $returnid);
        if ($destpage < 1)
        {
            $destpage = $returnid;
        }
        $this->Redirect($id, 'default', $destpage);
        return;
    }

    // Make sure someone isn't pulling a fast one by just trolling for order id's
    $found_uid = $db->GetOne('SELECT feu_user_id FROM ' . cms_db_prefix()
                                . 'module_ec_ordermgr WHERE id = ?', array($order_id));
    if ($uid != $found_uid)
    {
        echo $this->DisplayErrorMessage($this->Lang('error_possible_hack', $uid, $found_uid, $order_id));
        return;
    }
}

//
// Get the data out of the order
//
$order = orders_ops::load_by_id($order_id);

if (isset($params['submit']))
{
    if (\CMSMSExt\encrypted_store::get($keyname) != $order_id)
    {
        echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
        return;
    }

    // submit was pressed
    // record and validate credit card information
    // and encrypt it in the database
    // and move to the invoice page
    $ccnumber = trim(\xt_param::get_string($params, 'orders_ccnumber'));
    $ccnumber = preg_replace('/\D/', '', $ccnumber);
    $lastday = date('t', mktime(0, 0, 0, (int) $params['orders_ccexp_Month'], 1, (int) $params['orders_ccexp_Year']));
    $ccexp = mktime(0, 0, 0, (int) $params['orders_ccexp_Month'],
        (isset($params['orders_ccexp_Day'])) ? (int) $params['orders_ccexp_Day'] : $lastday,
        (int) $params['orders_ccexp_Year']);
    $ccv = trim(\xt_param::get_string($params, 'orders_ccverifycode'));
    if (! $ccnumber)
    {
        $tpl->assign('error', $this->Lang('error_insufficientparams'));
    }
    else if (strlen($ccnumber) > 16 || strlen($ccnumber) < 15)
    {
        $tpl->assign('error', $this->Lang('error_invalidfield', $this->Lang('creditcard')));
    }
    else if (! $ccv)
    {
        $tpl->assign('error', $this->Lang('error_insufficientparams'));
    }
    else if ($ccexp < time())
    {
        $tpl->assign('error', $this->Lang('error_creditcard_expired'));
    }
    else
    {
        // all looks good

        //
        // generate temporary encrypted creditcard data.
        //

        // generate a key and store it as a session cookie
        $key = str_shuffle(md5(time() + $order_id + $this->_getRealEncryptionKey() + $uid + session_id()));
        $cname = 'c' . orders_helper::get_security_key();
        $res = setcookie($cname, $key, 0, '/');

        // build a payment object
        $payment = new Payment();
        $payment->set_status(Payment::STATUS_NOTPROCESSED);
        $payment->set_method(Payment::TYPE_CREDITCARD);
        $payment->set_payment_date(time());
        $payment->set_amount($order->get_total());
        $payment->set_cc_expiry($ccexp);
        $payment->set_cc_number($ccnumber);
        $payment->set_cc_verifycode($ccv);

        if ($this->GetPreference('store_creditcard_data', 0))
        {
            // and save the payment
            $order->add_payment($payment);
            $res = $order->save();
        }

        // save the payment object temporarily
        \CMSMSExt\encrypted_store::put_special(serialize($payment), $key, $cname);

        // All done.
        $destpage = $this->GetPreference('confirmpage', $returnid);
        if ($destpage < 1)
        {
            $destpage = $returnid;
        }
        $sig = md5(serialize($order->to_array()));
        $this->Redirect($id, 'confirm', $destpage, array('order_id' => $order_id));
    }
}

$tpl->assign('back_link_url', $this->CreateLink($id, 'default', $returnid, '', array(), '', true));
$tpl->assign('back_url', $this->CreateLink($id, 'default', $returnid, '', array(), '', true));

//
// And give everything to smarty
//
$tpl->assign('order_obj', $order);
$tpl->assign('order', $order->to_array());

// Get credit card information
// or select a payment
$tpl->assign('formstart', $this->XTCreateFormStart($id, 'payment', $returnid, array('order_id' => $order_id)));
$tpl->assign('ccnumber', $this->CreateInputText($id, 'orders_ccnumber', '', 25, 25));
$tpl->assign('ccverifycode', $this->CreateInputText($id, 'orders_ccverifycode', '', 5, 5));
$tpl->assign('ccdateprefix', $id . 'orders_ccexp_');
$tpl->assign('formend', $this->CreateFormEnd());

$tpl->display();

// EOF
?>
