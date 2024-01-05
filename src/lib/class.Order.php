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

use EcommerceExt\Shipping;
use EcommerceExt\Payment;

final class Order
{

    private $_id;
    private $_feu_user;
    private $_invoice;
    private $_billing;
    private $_status;
    private $_extra;
    private $_order_notes;
    private $_create_date;
    private $_modified_date;
    private $_shipping_recs;
    private $_payment_recs;
    private $_note_recs;

    public function get_id()
    {
        return $this->_id;
    }

    public function set_id($val, $set_invoice = false)
    {
        $this->_id = $val;
        if ($set_invoice)
        {
            $this->set_invoice(orders_helper::make_invoice_number($this->get_id()));
        }
    }

    public function get_invoice()
    {
        return $this->_invoice;
    }

    public function set_invoice($val)
    {
        $this->_invoice = $val;
    }

    public function get_feu_user()
    {
        return $this->_feu_user;
    }

    public function set_feu_user($val)
    {
        $this->_feu_user = $val;
    }

    public function get_billing()
    {
        return $this->_billing;
    }

    public function set_billing($val)
    {
        $this->_billing = $val;
    }

    public function get_status()
    {
        return $this->_status;
    }

    public function set_status($val)
    {
        $this->_status = $val;
    }

    public function get_order_notes()
    {
        return $this->_order_notes;
    }

    public function set_order_notes($val)
    {
        $this->_order_notes = $val;
    }

    public function get_create_date()
    {
        return $this->_create_date;
    }

    public function set_create_date($val)
    {
        $this->_create_date = $val;
    }

    public function get_modified_date()
    {
        return $this->_modified_date;
    }

    public function set_modified_date($val)
    {
        $this->_modified_date = $val;
    }

    public function reset_shipping()
    {
        if ($this->get_id())
        {
            $db = cmsms()->GetDb();
            $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE order_id = ?';
            $dbr = $db->Execute($query, array($this->get_id()));

            $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE order_id = ?';
            $dbr = $db->Execute($query, array($this->get_id()));
        }
        $this->_shipping_recs = array();
    }

    public function add_shipping(Destination &$shipping, $reset_id = true)
    {
        if ($reset_id)
        {
            $shipping->set_id(null);
        }
        $shipping->set_order_id($this->get_id());
        if (! is_array($this->_shipping_recs))
        {
            $this->_shipping_recs = array();
        }
        $this->_shipping_recs[] = $shipping;
    }

    public function del_shipping($idx)
    {
        $new_data = array();
        for ($i = 0; $i < count($this->_shipping_recs); $i ++)
        {
            if ($i != $idx)
            {
                $new_data[] = $this->_shipping_recs[$i];
            }
        }
        $this->_shipping_recs = $new_data;
    }

    public function &get_shipping($idx)
    {
        return $this->get_destination($idx);
    }

    public function get_destination_by_name($name)
    {
        if (empty($this->_shipping_recs))
        {
            return;
        }
        for ($i = 0; $i < count($this->_shipping_recs); $i ++)
        {
            if ($this->_shipping_recs[$i]->get_name() == $name)
            {
                return $this->_shipping_recs[$i];
            }
        }

        return;
    }

    public function &get_destination_by_id($id)
    {
        for ($i = 0; $i < count($this->_shipping_recs); $i ++)
        {
            if ($this->_shipping_recs[$i]->get_id() == $id)
            {
                return $this->_shipping_recs[$i];
            }
        }
        $out = null;

        return $out;
    }

    public function &get_destination($idx)
    {
        if ($idx >= 0 && $idx < count($this->_shipping_recs))
        {
            return $this->_shipping_recs[$idx];
        }
        $out = null;


        return $out;
    }

    public function get_destinations()
    {
        return $this->_shipping_recs;
    }

    public function count_destinations()
    {
        return count($this->_shipping_recs);
    }

    public function count_real_items()
    {
        $itemcount = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $itemcount += $shipping->count_real_items();
        }

        return $itemcount;
    }

    public function add_payment(Payment &$payment, $reset_id = true)
    {
        if ($reset_id)
        {
            $payment->set_id(null);
        }
        $payment->set_order_id($this->get_id());
        if (! is_array($this->_payment_recs))
        {
            $this->_payment_recs = array();
        }
        $this->_payment_recs[] = $payment;
    }

    public function count_payments()
    {
        if (! empty($this->_payment_recs))
        {
            return count($this->_payment_recs);
        }
    }

    public function &get_payments()
    {
        return $this->_payment_recs;
    }

    public function &get_payment($idx)
    {
        if ($idx >= 0 && $idx < count($this->_payment_recs))
        {
            return $this->_payment_recs[$idx];
        }
    }

    public function &get_payment_by_id($pmt_id)
    {
        $res = null;
        for ($i = 0; $i < count($this->_payment_recs); $i ++)
        {
            if ($this->_payment_recs[$i]->get_id() == $pmt_id)
            {
                $res = &$this->_payment_recs[$i];
            }
        }

        return $res;
    }

    public function get_payment_by_txn_id($txn_id)
    {
        $res = null;
        for ($i = 0; ! is_null($this->_payment_recs) && $i < count($this->_payment_recs); $i ++)
        {
            if ($this->_payment_recs[$i]->get_txn_id() == $txn_id)
            {
                $res = &$this->_payment_recs[$i];
            }
        }

        return $res;
    }

    public function add_note(order_message &$message, $reset_id = true)
    {
        if ($reset_id)
            $message->set_id(null);
        $message->set_order_id($this->get_id());
        if (! is_array($this->_note_recs))
            $this->_note_recs = array();
        $this->_note_recs[] = $message;
    }

    public function count_notes()
    {
        if (! empty($this->_note_recs))
        {
            return count($this->_note_recs);
        }
    }

    public function &get_notes()
    {
        return $this->_note_recs;
    }

    public function &get_note($idx)
    {
        if ($idx >= 0 && $idx < count($this->_note_recs))
        {
            return $this->_note_recs[$idx];
        }
    }

    public function get_note_by_id($note_id)
    {
        for ($i = 0; $i < count($this->_note_recs); $i ++)
        {
            if ($this->_note_recs[$i]->get_id() == $note_id)
            {
                return $this->_note_recs[$i];
            }
        }
    }

    public function get_weight()
    {
        $weight = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $weight += $shipping->get_weight();
        }

        return $weight;
    }

    public function get_subtotal()
    {
        $subtotal = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $subtotal += $shipping->get_subtotal();
        }

        return max(0.0, $subtotal);
    }

    public function get_shipping_cost()
    {
        $shipping_cost = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $shipping_cost += $shipping->get_shipping_cost();
        }

        return $shipping_cost;
    }

    public function get_tax_cost()
    {
        $tax_cost = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $tax_cost += $shipping->get_tax_cost();
        }

        return $tax_cost;
    }

    public function get_discount()
    {
        $discount = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $discount += $shipping->get_discount();
        }

        // discount can never exceed the subtotal.
        $subtotal = $this->get_subtotal() * - 1;
        $discount = max($subtotal, $discount);

        return $discount;
    }

    public function get_total()
    {
        // this should be done in a post processing step.
        $val = max(0, $this->get_subtotal()) + $this->get_shipping_cost() + max(0, $this->get_tax_cost()) + $this->get_discount();
        $val = max(0, $val);

        return $val;
    }

    public function get_amount_paid()
    {
        $sum = 0;
        for ($i = 0; $i < $this->count_payments(); $i ++)
        {
            $payment = $this->_payment_recs[$i];
            if ($payment->get_status() != Payment::STATUS_APPROVED)
            {
                continue;
            }
            $sum += $payment->get_amount();
        }

        return $sum;
    }

    public function get_amount_due()
    {
        return $this->get_total() - $this->get_amount_paid();
    }

    public function save($del_children = false)
    {
        if ($this->get_id() > 0)
        {
            return orders_ops::update($this, $del_children);
        }

        return orders_ops::insert($this);
    }

    public function is_all_shipped()
    {
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            if (! $shipping->is_all_shipped())
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function is_partially_shipped()
    {
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            if (! $shipping->is_partially_shipped())
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function count_services()
    {
        $n_s = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $n_s += $shipping->count_services();
        }

        return $n_s;
    }

    public function has_services()
    {
        return ($this->count_services() > 0);
    }

    public function count_subscriptions()
    {
        $n_s = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $n_s += $shipping->count_subscriptions();
        }

        return $n_s;
    }

    public function has_subscriptions()
    {
        return ($this->count_subscriptions() > 0);
    }

    public function is_subscription_only()
    {
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            if (! $shipping->is_subscription_only())
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function count_products()
    {
        $n_p = 0;
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $n_p += $shipping->count_products();
        }

        return $n_p;
    }

    public function get_extra($key, $dflt = null)
    {
        if (! is_array($this->_extra))
        {
            return $dflt;
        }
        if (isset($this->_extra[$key]))
        {
            return $this->_extra[$key];
        }

        return $dflt;
    }

    public function set_extra($key, $value)
    {
        $key = trim($key);
        if (! $key)
        {
            return;
        }
        if (! is_array($this->_extra))
        {
            $this->_extra = array();
        }
        $this->_extra[$key] = $value;
    }

    public function isset_extra($key)
    {
        if (! is_array($this->_extra))
        {
            return FALSE;
        }

        return isset($this->_extra[$key]);
    }

    public function unset_extra($key)
    {
        if ($this->isset_extra($key))
        {
            unset($this->_exttra[$key]);
        }
    }

    public function get_all_extra()
    {
        return $this->_extra;
    }

    public function from_array($data, $prefix = 'billing_')
    {
        $this->set_id($data['id']);
        $this->set_feu_user($data['feu_user_id']);
        $this->set_invoice($data['invoice']);
        $this->set_status($data['status']);
        $this->set_order_notes($data['order_notes']);
        $this->set_create_date($data['create_date']);
        $this->set_modified_date($data['modified_date']);
        if (isset($data['extra']))
        {
            if (is_string($data['extra']))
            {
                $data['extra'] = unserialize($data['extra']);
            }
            if (count($data['extra']))
            {
                foreach ($data['extra'] as $key => $val)
                {
                    $this->set_extra($key, $val);
                }
            }
        }

        $addr = new Address();
        $addr->from_array($data, $prefix);
        $this->set_billing($addr);
    }

    public function to_array()
    {
        $result = array();
        $result['id'] = $this->get_id();
        $result['feu_user_id'] = $this->get_feu_user();
        $result['invoice'] = $this->get_invoice();
        $result['status'] = $this->get_status();
        $result['order_notes'] = $this->get_order_notes();
        $result['create_date'] = $this->get_create_date();
        $result['modified_date'] = $this->get_modified_date();
        if (is_array($this->_extra) && count($this->_extra))
        {
            $result['extra'] = $this->_extra;
        }

        $billing_addr = $this->get_billing();
        $tmp = $billing_addr->to_array('billing_');
        $result = array_merge($tmp, $result);

        $result['shipping'] = array();
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $result['shipping'][] = $shipping->to_array();
        }

        $result['payments'] = array();
        for ($i = 0; $i < $this->count_payments(); $i ++)
        {
            $payment = $this->get_payment($i);
            $result['payments'][] = $payment->to_array();
        }

        // for backward compatibilty... remove me soon.
        $result['subtotal'] = $this->get_subtotal();
        $result['tax'] = $this->get_tax_cost();
        $result['shipping_total'] = $this->get_shipping_cost();
        $result['total'] = $this->get_total();

        return $result;
    }

    public function reset_ids()
    {
        $this->set_id(null);
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $shipping->reset_ids();
        }
    }

    public function has_item_by_sku($sku)
    {
        for ($i = 0; $i < $this->count_destinations(); $i ++)
        {
            $shipping = $this->get_shipping($i);
            $item = $shipping->get_item_by_sku($sku);
            if (is_object($item))
            {
                return TRUE;
            }
        }

        return FALSE;
    }

} // end of class

// EOF
?>
