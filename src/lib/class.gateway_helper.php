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
use EcommerceExt\Payment;
use EcommerceExt\ecomm;

final class gateway_helper
{

    private function __construct()
    {
        // Static class
    }

    // given an order, find all of the gateways that are compatible with the items in the order
    // if any.
    static public function get_compatible_gateways(\EcOrderMgr\Order $order_obj)
    {
        $tmp_gateway_modules = ecomm::get_gateway_modules();
        if (! is_array($tmp_gateway_modules) || ! count($tmp_gateway_modules))
        {
            return;
        }

        $n_products = $order_obj->count_products();
        $n_services = $order_obj->count_services();
        $n_subscriptions = $order_obj->count_subscriptions();

        $valid_names = array();
        foreach ($tmp_gateway_modules as $mod_name)
        {
            $mod = \cms_utils::get_module($mod_name);
            if (! is_object($mod))
            {
                continue; // module in list, but could not be loaded.
            }

            $policy = $mod->get_cartitem_policy();
            if (! is_object($policy))
            {
                continue; // no policy (technically an error)
            }

            if ($policy->matches($n_products, $n_services, $n_subscriptions))
            {
                $valid_names[] = $mod_name;
            }
        }

        if (count($valid_names))
        {
            return $valid_names;
        }
    }

    static public function process_gateway_transaction(Payment\async_transaction $trans)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $orders_mod = \cms_utils::get_module(\MOD_ECORDERMGR);

        $order_id = $trans->get_order_id();
        if (! $order_id)
        {
            return $orders_mod->Lang('error_orderinvalid');
        }
        $order_obj = orders_ops::load_by_id($order_id);
        if (! is_object($order_obj))
        {
            return $this->Lang('error_orderinvalid');
        }

        $uid = $trans->get_user_id();
        $transaction_id = $trans->get_id();
        $amount = $trans->get_amount();
        $payment_id = $trans->get_payment_id();
        if ($uid && $order_obj->get_feu_user() != $uid)
        {
            return $orders_mod->Lang('error_orderinvalid');
        }

        $billing = $order_obj->get_billing();
        $email_addr = $billing->get_email();
        if (! $email_addr)
        {
            return $orders_mod->Lang('error_noemailaddress');
        }

        //
        // try to find a payment that matches
        //
        $payment = $order_obj->get_payment_by_txn_id($transaction_id);
        if (! $payment)
        {
            // so payment found, try to find one by it's id.
            if ($payment_id > 0)
            {
                $payment = $order_obj->get_payment_by_id($payment_id);
            }
        }
        if (! $payment)
        {
            // still no payment found
            // in which case we gotta create a new payment object.
            $payment = new Payment();
            $payment->set_payment_date(\xt_utils::db_time(time()));
            $payment->set_order_id($order_id);
            $order_obj->add_payment($payment);
        }

        // and update it from the transaction.
        $payment->set_txn_id($transaction_id);
        $payment->set_method(Payment::TYPE_ONLINE);
        $payment->set_gateway($trans->get_gateway());
        $payment->set_amount($amount);

        // add extra data to the payment.
        $tmp_keys = $trans->get_other_keys();
        if ($tmp_keys)
        {
            foreach ($tmp_keys as $tmp_key)
            {
                $payment->set_extra($tmp_key, $trans->get_other_val($tmp_key));
            }
        }

        // Update the payment status
        $message = $trans->get_message();
        $status = $trans->get_status(); // transaction status
        $retstatus = '';
        $do_orderstatus = TRUE;
        switch ($status)
        {
            case Payment\payment_gateway::PAYMENT_STATUS_NONE:
                $do_orderstatus = FALSE;
                return;

            case Payment\payment_gateway::PAYMENT_STATUS_AUTHORIZED:
                $do_orderstatus = FALSE;
                $payment->set_status(Payment::STATUS_AUTHORIZED);
                $orders_mod->Audit($order_id, $orders_mod->GetName(),
                    sprintf("%.2f authorized for order %d (transaction id: %s)", $amount, $order_id, $transaction_id));
                return;

            case Payment\payment_gateway::PAYMENT_STATUS_APPROVED:
                $payment->set_status(Payment::STATUS_APPROVED);
                $orders_mod->Audit($order_id, $orders_mod->GetName(),
                    sprintf("%.2f received for order %d (transaction id: %s)", $amount, $order_id, $transaction_id));
                break;

            case Payment\payment_gateway::PAYMENT_STATUS_ERROR:
                // bad address or something
                $payment->set_status(Payment::STATUS_ERROR);
                if (! empty($message))
                {
                    $retstatus = $message;
                }
                else
                {
                    $retstatus = $orders_mod->Lang('error_orderprocessing');
                }

                $orders_mod->Audit($order_id, $orders_mod->GetName(),
                    sprintf('Error occurred with payment for order %s (transaction id: %s)', $order_id, $transaction_id));
                break;

            case Payment\payment_gateway::PAYMENT_STATUS_OTHER:
                // bad address or something
                $payment->set_status(Payment::STATUS_OTHER);
                if (! empty($message))
                {
                    $retstatus = $message;
                }
                else
                {
                    $retstatus = $orders_mod->Lang('error_orderprocessing');
                }
                $orders_mod->Audit($order_id, $orders_mod->GetName(), $orders_mod->Lang('msg_payment_error', $transaction_id, $order_id));
                break;

            case Payment\payment_gateway::PAYMENT_STATUS_PENDING:
                // the gateway stuff worked, but we haven't had
                // final approval for the purchase yet.
                $cur_status = $order_obj->get_status();
                if ($cur_status == \EcommerceExt\ORDERSTATUS_PROPOSED
                    || $cur_status == \EcommerceExt\ORDERSTATUS_PENDING
                    || $cur_status == \EcommerceExt\ORDERSTATUS_CONFIRMED)
                {
                    $payment->set_status(Payment::STATUS_PENDING);
                    $orders_mod->Audit($order_id, $orders_mod->GetName(),
                        $orders_mod->Lang('msg_payment_pending', $order_id));
                }
                else
                {
                    // maybe the async stuff came in BEFORE we got here synchronously
                    $orders_mod->Audit($order_id, $orders_mod->GetName(),
                        $orders_mod->Lang('msg_payment_setbackwards', $order_id));
                    $retstatus = $orders_mod->Lang('msg_payment_setbackwards', $order_id);
                    $do_orderstatus = FALSE;
                }
                break;

            case Payment\payment_gateway::PAYMENT_STATUS_DECLINED:
                $payment->set_status(Payment::STATUS_DECLINED);
                $orders_mod->Audit($order_id, $orders_mod->GetName(),
                    $orders_mod->Lang('msg_payment_declined', $transaction_id, $order_id));
                $retstatus = $orders_mod->Lang('msg_payment_declined', $transaction_id, $order_id);
                $do_orderstatus = FALSE;
                break;

            case Payment\payment_gateway::PAYMENT_STATUS_CANCELLED:
                $payment->set_status(Payment::STATUS_CANCELLED);
                $orders_mod->Audit($order_id, $orders_mod->GetName(),
                    $orders_mod->Lang('msg_payment_cancelled', $transaction_id, $order_id));
                break;

            default:
                $retstatus = $orders_mod->Lang('error_unknownstatus');
                break;
        }

        // oops, we got an error.
        if ($retstatus)
        {
            return $retstatus;
        }

        if ($do_orderstatus)
        {
            // figure out what the order status should be.
            // there prolly should be some preferences to help in decision making.
            $all_shipped = $order_obj->is_all_shipped();
            $part_shipped = $order_obj->is_partially_shipped();
            $none_shipped = (! $all_shipped && ! $part_shipped) ? TRUE : FALSE;
            $all_paid = ($order_obj->get_amount_due() > 0.01) ? FALSE : TRUE;
            $cur_status = $order_obj->get_status();
            $has_services = $order_obj->has_services();
            $subscr_only = $order_obj->is_subscription_only();

            switch ($cur_status)
            {
                // finished checking out... we can adjust it
                case \EcommerceExt\ORDERSTATUS_PROPOSED:
                case \EcommerceExt\ORDERSTATUS_CONFIRMED:
                case \EcommerceExt\ORDERSTATUS_PENDING:
                // statuses set automagically.. we can adjust it.
                case \EcommerceExt\ORDERSTATUS_PAID:
                case \EcommerceExt\ORDERSTATUS_BALANCEDUE:
                case \EcommerceExt\ORDERSTATUS_INCOMPLETE:
                    if ($status == Payment\payment_gateway::PAYMENT_STATUS_CANCELLED)
                    {
                        $order_obj->set_status(\EcommerceExt\ORDERSTATUS_CANCELLED);
                    }
                    else if ($status == Payment\payment_gateway::PAYMENT_STATUS_PENDING)
                    {
                        $order_obj->set_status(\EcommerceExt\ORDERSTATUS_PENDING);
                    }
                    else if (! $all_paid)
                    {
                        $order_obj->set_status(\EcommerceExt\ORDERSTATUS_BALANCEDUE);
                    }
                    else if (! $has_services)
                    {
                        // only products
                        if ($all_shipped)
                        {
                            $order_obj->set_status(\EcommerceExt\ORDERSTATUS_COMPLETE);
                        }
                        else if ($part_shipped)
                        {
                            $order_obj->set_status(\EcommerceExt\ORDERSTATUS_INCOMPLETE);
                        }
                        else
                        {
                            $order_obj->set_status(\EcommerceExt\ORDERSTATUS_PAID);
                        }
                    }
                    else if ($subscr_only)
                    {
                        $order_obj->set_status(\EcommerceExt\ORDERSTATUS_SUBSCRIBED);
                    }
                    else
                    {
                        // it's a service... but not a subscription
                        // and it's all paid.
                        $order_obj->set_status(\EcommerceExt\ORDERSTATUS_PAID);
                    }
                    break;

                case \EcommerceExt\ORDERSTATUS_SUBSCRIBED:
                    // for subscriptions, we cancel the subscription on a CANCELLED transaction.
                    switch ($status)
                    {
                        case Payment\payment_gateway::PAYMENT_STATUS_CANCELLED:
                            $order_obj->set_status(\EcommerceExt\ORDERSTATUS_CANCELLED);
                            break;
                        case Payment\payment_gateway::PAYMENT_STATUS_DECLINED:
                            // ADD A NOTE
                            $order_obj->set_status(\EcommerceExt\ORDERSTATUS_HOLD);
                            $msg = new order_message();
                            $msg->set_subject('Payment declined');
                            $msg->set_body('We received notification that a payment was declined for this subscription');
                            $order_obj->add_note($msg);
                            break;
                    }
                    break;

                // user was invoiced... why are we even getting here?
                case \EcommerceExt\ORDERSTATUS_INVOICED:
                // completed already, shouldn't even get here.
                case \EcommerceExt\ORDERSTATUS_COMPLETED:
                // admin set statuses... don't adjust.

                case \EcommerceExt\ORDERSTATUS_CANCELLED:
                case \EcommerceExt\ORDERSTATUS_HOLD:
                default:
                    break;
            }

            $orders_mod->Audit($order_id, $orders_mod->GetName(), 'Order status set to '
                                . $orders_mod->Lang($order_obj->get_status()));

            // save the order
            $order_obj->save();
        }

        //
        // we're done with processing the order.
        // maybe we need to send an email.
        // actually, we could use a different template for each status... but we won't for now.
        //
        $sendemail = false;
        switch ($order_obj->get_status())
        {
            case \EcommerceExt\ORDERSTATUS_COMPLETED:
            case \EcommerceExt\ORDERSTATUS_PAID:
            case \EcommerceExt\ORDERSTATUS_INVOICED:
            case \EcommerceExt\ORDERSTATUS_CONFIRMED:
            case \EcommerceExt\ORDERSTATUS_SUBSCRIBED:
                $sendemail = true;
                break;
        }
        if (! $sendemail)
        {
            return; // we're done.
        }

        // update smarty.
        $smarty = $gCms->GetSmarty();
        $smarty->assign('transaction_id', $transaction_id);
        $smarty->assign('orders', $orders_mod);
        $smarty->assign('message', $message);
        $smarty->assign('gateway_status', $status);
        $smarty->assign('email_address', $email_addr);
        $smarty->assign('order_obj', $order_obj);
        $smarty->assign('ordernumber', $order_obj->get_invoice()); // deprecated.
        $smarty->assign('currencysymbol', ecomm::get_currency_symbol());
        $smarty->assign('weightunits', ecomm::get_weight_units());
        if ($status == Payment\payment_gateway::PAYMENT_STATUS_APPROVED)
        {
            $smarty->assign('status', 'SUCCESS');
        }
        else if ($status == Payment\payment_gateway::PAYMENT_STATUS_PENDING)
        {
            $smarty->assign('status', 'PENDING');
        }

        // send it if we need to.
        if ($sendemail)
        {
            $cmsmailer = new \cms_mailer();

            // Send an email to the administrator
            $addresses = explode(',', $orders_mod->GetPreference('admin_email'));
            if (is_array($addresses) && count($addresses) > 0)
            {
                $subject = $orders_mod->ProcessTemplateFromData($orders_mod->GetPreference('adminemail_subject'));
                $tpl = $orders_mod->CreateSmartyTemplate('adminemail_template');
                $tpl->assign('transaction_id', $transaction_id);
                $tpl->assign('orders', $orders_mod);
                $tpl->assign('message', $message);
                $tpl->assign('gateway_status', $status);
                $tpl->assign('email_address', $email_addr);
                $tpl->assign('order_obj', $order_obj);
                $tpl->assign('ordernumber', $order_obj->get_invoice()); // deprecated.
                $tpl->assign('currencysymbol', ecomm::get_currency_symbol());
                $tpl->assign('weightunits', ecomm::get_weight_units());
                if ($status == Payment\payment_gateway::PAYMENT_STATUS_APPROVED)
                {
                    $tpl->assign('status', 'SUCCESS');
                }
                else if ($status == Payment\payment_gateway::PAYMENT_STATUS_PENDING)
                {
                    $tpl->assign('status', 'PENDING');
                }
                $body = $tpl->fetch();
                $n = 0;
                foreach ($addresses as $addr)
                {
                    $addr = trim($addr);
                    if (! $addr)
                        continue;
                    $cmsmailer->AddAddress($addr);
                    $n ++;
                }
                if ($n)
                {
                    $cmsmailer->SetSubject($subject);
                    $cmsmailer->SetBody($body);
                    $cmsmailer->IsHTML(true);
                    $cmsmailer->Send();
                    $cmsmailer->reset();
                }
            }

            // Send an email to the user
            {
                $subject = $orders_mod->ProcessTemplateFromData($orders_mod->GetPreference('useremail_subject'));
                $tpl = $orders_mod->CreateSmartyTemplate('useremail_template');
                $tpl->assign('transaction_id', $transaction_id);
                $tpl->assign('orders', $orders_mod);
                $tpl->assign('message', $message);
                $tpl->assign('gateway_status', $status);
                $tpl->assign('email_address', $email_addr);
                $tpl->assign('order_obj', $order_obj);
                $tpl->assign('ordernumber', $order_obj->get_invoice()); // deprecated.
                $tpl->assign('currencysymbol', ecomm::get_currency_symbol());
                $tpl->assign('weightunits', ecomm::get_weight_units());
                if ($status == Payment\payment_gateway::PAYMENT_STATUS_APPROVED)
                {
                    $tpl->assign('status', 'SUCCESS');
                }
                else if ($status == Payment\payment_gateway::PAYMENT_STATUS_PENDING)
                {
                    $tpl->assign('status', 'PENDING');
                }
                $body = $tpl->fetch();
                $cmsmailer->AddAddress($email_addr);
                $cmsmailer->SetSubject($subject);
                $cmsmailer->SetBody($body);
                $cmsmailer->IsHTML(true);
                $cmsmailer->Send();
                $cmsmailer->reset();
            }
        }

        // and we're done.
        return;
    }

    // function
} // end of class.

#
# EOF
#
?>
