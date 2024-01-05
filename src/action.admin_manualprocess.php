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
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    return;
}

// Redirect to https?
if ($this->GetPreference('force_ssl', '0') == 1 && (! isset($_SERVER['HTTPS'])
    || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
{
    \xt_redirect::redirect_https();
}

//
// initialization
//
if (! isset($params['orderid']))
{
    echo $this->DisplayErrorMessage($this->Lang('error_insufficientparams'));
    return;
}
$orderid = (int) $params['orderid'];
$pmt_id = - 1;
$payment = '';
$warnings = array($this->Lang('warning_manual_process'));
$errors = array();
$expires = time();
$ccnumber = '';
$ccv = '';
$amount = '';
$url = '';
$gateway_module = '';
$ccprocessing_gateway = $this->GetPreference('ccprocessing_module', - 1);

//
// setup
//
$order_obj = orders_ops::load_by_id($orderid);

if (! isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')
{
    $warnings[] = $this->Lang('warning_https');
}
if ($order_obj->get_status() == \EcommerceExt\ORDERSTATUS_PAID)
{
    $warnings[] = $this->Lang('warning_orderstatus_paid');
}
else if ($order_obj->get_status() == \EcommerceExt\ORDERSTATUS_CANCELLED)
{
    $warnings[] = $this->Lang('warning_orderstatus_cancelled');
}
if ($ccprocessing_gateway == - 1)
{
    $warnings[] = $this->Lang('warning_no_ccprocessing_gateway');
}

//
// get the data
//
if (isset($params['payment_id']))
{
    $pmt_id = (int) $params['payment_id'];
    $payment = $order_obj->get_payment_by_id($pmt_id);
    if (! $payment)
    {
        echo $this->DisplayErrorMessage($this->Lang('error_paymentnotfound'));
        return;
    }
    if (isset($params['process_only']))
    {
        $payment->set_method(Payment::TYPE_CREDITCARD);
    }
}
else
{
    $payment = new Payment();
    $amt = max(0, $order_obj->get_total() - $order_obj->get_amount_paid());
    if ($amt == 0)
    {
        $warnings[] = $this->Lang('warning_order_paid_in_full');
    }
    else
    {
        $payment->set_amount($amt);
    }
    $payment->set_status(Payment::STATUS_NOTPROCESSED);
    $payment->set_order_id($orderid);
}

//
// process form data
//
if (isset($params['cancel']))
{
    unset($params['cancel']);
    $this->XTRedirect($id, 'admin_manageorder', $returnid, $params);
}
else if (isset($params['submit']))
{
    //
    // rebuild the payment object from params.
    //
    $tmp = mktime($params['payment_date_Hour'], $params['payment_date_Minute'], 0, $params['payment_date_Month'], $params['payment_date_Day'], $params['payment_date_Year']);
    $params['payment_date'] = $tmp;

    $lastday = date('t', mktime(0, 0, 0, $params['expires_Month'], 1, $params['expires_Year']));
    $tmp = mktime(0, 0, 0, $params['expires_Month'], (isset($params['expires_Day'])) ? $params['expires_Day'] : $lastday, $params['expires_Year']);
    $params['cc_expiry'] = $tmp;

    $payment->from_array($params);
    $payment->set_order_id($params['orderid']);

    $url = trim($params['url']);

    if ($payment->get_cc_number() == '' && $payment->get_method() == Payment::TYPE_CREDITCARD)
    {
        $errors[] = $this->Lang('error_invalidfield', $this->Lang('creditcard_number'));
    }
    if ($payment->get_cc_verifycode() == '' && $payment->get_method() == Payment::TYPE_CREDITCARD)
    {
        $errors[] = $this->Lang('error_invalidfield', $this->Lang('creditcard_verifycode'));
    }
    if ($payment->get_method() == Payment::TYPE_CREDITCARD && $payment->get_cc_expiry() < time())
    {
        $errors[] = $this->Lang('error_creditcard_expired');
    }
    if ((float) $payment->get_amount() <= 0)
    {
        $errors[] = $this->Lang('error_invalidfield', $this->Lang('amount'));
    }

    if (! count($errors))
    {
        // save this payment.
        $res = $payment->save();
        if (! $res)
        {
            $errors[] = $this->Lang('error_save', 'payment');
        }
    }

    if ($payment->get_method() == Payment::TYPE_CREDITCARD && isset($params['process_cc_new']) && $params['process_cc_new'] == 1)
    {
        // get our gateway module setup. and make sure it's valid.
        if (! count($errors) && empty($ccprocessing_gateway) || $ccprocessing_gateway == '-1')
        {
            // no processing gateway, and
            $errors[] = $this->Lang('error_nopaymentgateway');
        }

        $gateway_module = $this->GetModuleInstance($ccprocessing_gateway);
        if (! count($errors) && ! $gateway_module)
        {
            $errors[] = $this->Lang('error_nopaymentgateway');
        }
        if (! count($errors) && ! $gateway_module->RequiresCreditCardInfo())
        {
            $errors[] = $this->Lang('error_invalidgateway');
        }
        if (! count($errors) && $gateway_module->RequiresSSL()
            && (! isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
        {
            // problem.
            $errors[] = $this->Lang('error_requiressl');
        }

        if (! count($errors))
        {
            $gateway_module->SetCreditCardInfo($payment->get_cc_number(), $payment->get_cc_expiry(), $payment->get_cc_verifycode());
            $gateway_module->SetCurrencyCode(ecomm::get_currency_code());
            $gateway_module->SetWeightUnits(ecomm::get_weight_units());
            $gateway_module->SetInvoice($order_obj->get_invoice());
            $gateway_module->SetPaymentId($payment->get_id());
            $gateway_module->SetOrderID($orderid);
            $gateway_module->SetOrderDescription($this->GetPreference('gateway_description'));
            $gateway_module->SetDestination($url);

            // add the items.
            foreach ($order_obj->get_destinations() as $shipment)
            {
                foreach ($shipment->get_items() as $item)
                {
                    $gateway_module->AddItem($item->get_description(), $item->get_item_id(), $item->get_quantity(),
                                                $item->get_weight(), $item->get_net_price());
                }
            }

            // now do the deed.
            $gateway_module->ProcessTransaction();
        }
    }
    else if (! count($errors))
    {
        // we're done.
        $parms = array('orderid' => $orderid);
        $this->XTRedirect($id, 'admin_manageorder', $returnid, $parms);
    }
}
else if (isset($_GET['cntnt01datakey']))
{
    // gateway module has returned.
    $gateway_module_name = $this->GetPreference('ccprocessing_module', - 1);
    $gateway_module = $this->GetModuleInstance($gateway_module_name);

    // get gateway return details
    $datakey = '';
    if (isset($_GET['cntnt01datakey']))
    {
        $datakey = $_GET['cntnt01datakey'];
    }

    $gateway_module->RestoreState($datakey);
    if ($gateway_module->GetOrderId() != $orderid)
    {
        $errors[] = $this->Lang('error_gateway_invalid_data');
    }

    // get the saved payment information... we know it exists.
    $payment = payment_ops::load_by_id($gateway_module->GetPaymentId());
    if (! $payment)
    {
        // oops, couldn't find the payment.
        $errors[] = $this->Lang('error_gateway_invalid_data');
    }

    $status = $gateway_module->GetTransactionStatus();
    $transaction_id = $gateway_module->GetTransactionID();
    $message = $gateway_module->GetMessage();

    // we're done with the gateway now
    // so reset it (just in case)
    $gateway_module->Reset();

    if (! count($errors))
    {
        // now process the results.
        switch ($status)
        {
            case PAYMENT_STATUS_APPROVED:
                {
                    // update the order
                    $payment->set_status(Payment::STATUS_APPROVED);
                    $payment->set_txn_id($transaction_id);
                    $payment->set_gateway($gateway_module->GetFriendlyName());
                    if ($message)
                    {
                        $notes = $payment->get_notes();
                        $notes .= $message . '<br/>';
                        $payment->set_notes($notes);
                    }
                    $payment->save();

                    // redirect to manage order page.
                    $this->SetMessage($this->Lang('transaction_successful'));
                    $this->XTRedirect($id, 'admin_manageorder', $returnid, $params);
                }
                break;

            case PAYMENT_STATUS_ERROR:
            case PAYMENT_STATUS_OTHER:
            case PAYMENT_STATUS_DECLINED:
            case PAYMENT_STATUS_CANCELLED:
                {
                    $errors[] = $message;
                }
                break;
        }
    }
}

//
// give verything to smarty
//
if (count($warnings))
{
    $smarty->assign('warnings', $warnings);
}
if (count($errors))
{
    $smarty->assign('errors', $errors);
}

$statuses = array();
$statuses[Payment::STATUS_APPROVED] = $this->Lang(Payment::STATUS_APPROVED);
$statuses[Payment::STATUS_DECLINED] = $this->Lang(Payment::STATUS_DECLINED);
$statuses[Payment::STATUS_ERROR] = $this->Lang(Payment::STATUS_ERROR);
$statuses[Payment::STATUS_CANCELLED] = $this->Lang(Payment::STATUS_CANCELLED);
$statuses[Payment::STATUS_OTHER] = $this->Lang(Payment::STATUS_OTHER);
$statuses[Payment::STATUS_PENDING] = $this->Lang(Payment::STATUS_PENDING);
$statuses[Payment::STATUS_NOTPROCESSED] = $this->Lang(Payment::STATUS_NOTPROCESSED);
$smarty->assign('statuses', $statuses);

$pmt_types = array();
$pmt_types[Payment::TYPE_ONLINE] = $this->Lang(Payment::TYPE_ONLINE);
$pmt_types[Payment::TYPE_CASH] = $this->Lang(Payment::TYPE_CASH);
$pmt_types[Payment::TYPE_CREDITCARD] = $this->Lang(Payment::TYPE_CREDITCARD);
$pmt_types[Payment::TYPE_UNKNOWN] = $this->Lang(Payment::TYPE_UNKNOWN);
$smarty->assign('pmt_types', $pmt_types);

if (isset($params['process_only']))
{
    $smarty->assign('process_only', 1);
}
$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_manualprocess', $returnid, $params));
$smarty->assign('url', \xt_url::current_url());
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('order_obj', $order_obj);
$tmp = $payment->get_assocdata();
if (is_array($tmp) && count($tmp))
{
    $smarty->assign('payment_assoc', $tmp);
}
$smarty->assign('payment', $payment);
$smarty->assign('currencysymbol', ecomm::get_currency_symbol());
$smarty->assign('have_cc_gateway', ($ccprocessing_gateway != - 1));

//
// process the template
//
echo $this->ProcessTemplate('admin_manualprocess.tpl');

// EOF
?>
