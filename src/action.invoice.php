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
if (! $this->GetPreference('allow_manual_checkout', 0))
{
    exit();
}

try
{
    // Connect with FrontEndUsers
    $feu = $this->GetModuleInstance(\MOD_MAMS);
    if (! $feu)
    {
        echo $this->DisplayErrorMessage($this->Lang('error_nofeumodule'));
        return;
    }
    $order_id = '';

    if (! isset($params['order_id']))
    {
        echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
        return;
    }
    $order_id = (int) $params['order_id'];

    // double check that the user is logged in
    $uid = orders_helper::is_valid_user();
    if ($uid === FALSE)
    {
        $this->DisplayErrorMessage($this->Lang('error_notloggedin'));
        return;
    }
    $logged_in = ($uid <= 0) ? 0 : 1;
    $keyname = orders_helper::get_security_key();

    $thetemplate = \xt_param::get_string($params, 'invoicetemplate', $this->GetPreference('dflt_invoice_template'));

    $smarty->assign('logged_in', $logged_in);

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
    }

    $cart_module = \EcommerceExt\ecomm::get_cart_module();
    if (! is_object($cart_module))
    {
        echo $this->DisplayErrorMessage($this->Lang('error_nocartmodule'));
        return;
    }

    if (! $this->GetPreference('allow_anon_checkout'))
    {
        // Make sure someone isn't pulling a fast one by just trolling for order id's
        $found_uid = $db->GetOne('SELECT feu_user_id FROM ' . cms_db_prefix()
                                    . 'module_ec_ordermgr WHERE id = ?', array($order_id));

        if ($uid != $found_uid)
        {
            $destpage = $this->GetPreference('billingpage', $returnid);
            if ($destpage < 1)
            {
                $destpage = $returnid;
            }
            $this->Redirect($id, 'default', $destpage);
            return;
        }
    }

    if (\CMSMSExt\encrypted_store::get($keyname) != $order_id)
    {
        echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
        return;
    }

    //
    // Get the data out of the order
    //
    $order_obj = orders_ops::load_by_id($order_id);
    $order_arr = $order_obj->to_array();

    // Get this users email address
    $billing_addr = $order_obj->get_billing();
    $email_addr = $billing_addr->get_email();
    if (! $email_addr)
    {
        $this->Audit('', $this->GetName(), $this->Lang('error_noemailaddress'));
        echo $this->DisplayErrorMessage($this->Lang('error_noemailaddress'));
        return;
    }

    //
    // And give everything to smarty
    //
    $smarty->assign('order_id', $order_id);
    $smarty->assign('ordernumber', $order_obj->get_invoice());
    $smarty->assign('currencysymbol', ecomm::get_currency_symbol());
    $smarty->assign('weightunits', ecomm::get_weight_units());
    $smarty->assign('order', $order_arr);
    $smarty->assign('order_obj', $order_obj);
    $smarty->assign('orders_formstart', $this->XTCreateFormStart($id, 'complete', $returnid,
                                                                    array('order_id' => $order_id)));
    $smarty->assign('orders_formend', $this->CreateFormEnd());
    $smarty->assign('invoice_message', $this->GetPreference('invoice_message'));
    $smarty->assign('email_address', $email_addr);
    $smarty->assign('status', 'INVOICED');

    //
    // Send an email to the customer
    //
    $subject = $this->ProcessTemplateFromData($this->GetPreference('useremail_subject'));
    $body = $this->ProcessTemplateFromDatabase('useremail_template');
    if ($body)
    {
        $cmsmailer = new \cms_mailer();
        $cmsmailer->IsHTML(true);
        $cmsmailer->AddAddress($email_addr);
        $cmsmailer->SetSubject($subject);
        $cmsmailer->SetBody($body);
        $cmsmailer->Send();
        $cmsmailer->reset();
    }

    //
    // Send an email to the administrator(s)
    //
    $tmp = $this->GetPreference('admin_email');
    if ($tmp != '')
    {
        $addresses = explode(',', $tmp);
        if (is_array($addresses) && count($addresses) > 0)
        {
            $subject = $this->ProcessTemplateFromData($this->GetPreference('adminemail_subject'));
            $body = $this->ProcessTemplateFromDataBase('adminemail_template');
            if ($body)
            {
                $cmsmailer = new \cms_mailer();
                $cmsmailer->IsHTML(true);
                foreach ($addresses as $addr)
                {
                    $cmsmailer->AddAddress($addr);
                }
                $cmsmailer->SetSubject($subject);
                $cmsmailer->SetBody($body);
                $cmsmailer->Send();
                $cmsmailer->reset();
            }
        }
    }

    //
    // Unset the session's order id
    //
    $cname = 'c' . orders_helper::get_security_key();
    \CMSMSExt\encrypted_store::erase($keyname);
    \CMSMSExt\encrypted_store::erase($cname);

    //
    // Update the order
    //
    $order_obj->set_status(\EcommerceExt\ORDERSTATUS_INVOICED);
    $order_obj->save();

    //
    // Process the template
    //
    $rsrc = $this->XTGetTemplateResource($thetemplate, 'invoice_');
    echo $smarty->fetch($rsrc);

    //
    // Clear the cart
    //
    $cart_module->EraseCart();

    //
    audit($order_obj->get_id(), 'EcOrderMgr', 'Customer viewed the invoice (cart cleared)');
}
catch (\Exception $e)
{
    \xt_utils::log_exception($e);
}

// EOF
?>
