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
use EcommerceExt\Shipping;

final class orders_ops
{

    private function __construct()
    {
        // Static class
    }

    static public function find_last_feu_order($feu_uid)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE feu_user_id = ? ORDER by id DESC LIMIT 1';
        $tmp = $db->GetOne($query, array($feu_uid));
        if ($tmp)
        {
            return $tmp;
        }
    }

    static public function find_by_billing_email($email)
    {
        $db = cmsms()->GetDb();
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE billing_email = ? ORDER BY id DESC';
        $tmp = $db->GetCol($query, array(trim($email)));
        if (is_array($tmp) && count($tmp))
        {
            return $tmp;
        }
    }

    static public function find_by_feu_id($uid)
    {
        $db = cmsms()->GetDb();
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE feu_user_id = ? ORDER by id DESC';
        $tmp = $db->GetCol($query, array((int) $uid));
        if (is_array($tmp) && count($tmp))
        {
            return $tmp;
        }
    }

    static public function get_summary($order_id)
    {
        $qparms = array();
        $query = 'SELECT O.id,O.invoice,O.status,I2.items,I1.weight,I1.total,(I1.total - P1.paid) AS amtdue,O.create_date,O.modified_date
              FROM ' . cms_db_prefix() . 'module_ec_ordermgr O
              LEFT JOIN (SELECT order_id, SUM(weight) AS weight,
                SUM(GREATEST(COALESCE(master_price,0),quantity*COALESCE(unit_price,0)))+SUM(quantity*COALESCE(discount,0)) AS total
                FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
                GROUP BY order_id)
                  AS I1 ON O.id = I1.order_id
              LEFT JOIN (SELECT order_id, SUM(quantity) AS items
                FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
                WHERE item_type = ? OR item_type = ?
                GROUP BY order_id)
                AS I2 ON O.id = I2.order_id
              LEFT JOIN (SELECT order_id,SUM(amount) AS paid
                FROM ' . cms_db_prefix() . 'module_ec_ordermgr_payments
                  GROUP BY order_id)
                  AS P1 ON O.id = P1.order_id';
        $qparms[] = '0_product';
        $qparms[] = '1_service';

        if (is_array($order_id))
        {
            $tmp = array();
            for ($i = 0; $i < count($order_id); $i ++)
            {
                if ($order_id[$i] > 0)
                {
                    $tmp[] = $order_id[$i];
                }
            }
            if (! is_array($tmp) || count($tmp) == 0)
            {
                // nothing to query, so nuke the query var.
                $query = '';
            }
            else
            {
                $query .= ' WHERE O.id IN (' . implode(',', $tmp) . ')';
            }
        }
        else if (is_int($order_id) && $order_id > 0)
        {
            $query .= ' WHERE O.id = ?';
            $qparms[] = $order_id;
        }
        else
        {
            // something wierd
            return;
        }

        if (! $query)
        {
            return;
        }

        $db = cmsms()->GetDb();

        $tmp = $db->GetArray($query, $qparms);
        if (is_array($tmp) && count($tmp))
        {
            $results = \xt_array::to_hash($tmp, 'id');
            return $results;
        }
    }

    static public function &load_by_invoice($invoice)
    {
        // load the order record itself
        $bad = null;
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT id, feu_user_id, invoice, billing_company, billing_first_name, billing_last_name, billing_address1,
              billing_address2, billing_city, billing_state, billing_postal, billing_country, billing_phone, billing_fax, billing_email,
              status, order_notes, extra, create_date, modified_date
              FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE invoice = ?';
        $row = $db->GetRow($query, array($invoice));
        if (! $row)
        {
            return $bad;
        }

        $order = new Order();
        $order->from_array($row);
        $id = $order->get_id();

        // now fill the shipping stuff.
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE order_id = ?';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $shipping_id)
            {
                $shipping = shipping_ops::load_by_id($shipping_id);
                $order->add_shipping($shipping, false);
            }
        }

        // now fill in the payments stuff
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_payments WHERE order_id = ?';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $payment_id)
            {
                $payment = payment_ops::load_by_id($payment_id);
                $order->add_payment($payment, false);
            }
        }

        // now fill in the notes stuff
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages WHERE order_id = ? ORDER BY sent ASC';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $note_id)
            {
                $message = message_ops::load_by_id($note_id);
                $order->add_note($message, false);
            }
        }

        return $order;
    }

    static public function &load_by_id($id)
    {
        // load the order record itself
        $bad = null;
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT id, feu_user_id, invoice, billing_company, billing_first_name, billing_last_name, billing_address1,
              billing_address2, billing_city, billing_state, billing_postal, billing_country, billing_phone, billing_fax, billing_email,
              status, order_notes, extra, create_date, modified_date
              FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE id = ?';
        $row = $db->GetRow($query, array($id));
        if (! $row)
        {
            return $bad;
        }

        $order = new Order();
        $order->from_array($row);

        // now fill the shipping stuff.
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE order_id = ?';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $shipping_id)
            {
                $shipping = shipping_ops::load_by_id($shipping_id);
                $order->add_shipping($shipping, false);
            }
        }

        // now fill in the payments stuff
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_payments WHERE order_id = ?';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $payment_id)
            {
                $payment = payment_ops::load_by_id($payment_id);
                $order->add_payment($payment, false);
            }
        }

        // now fill in the notes stuff
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages WHERE order_id = ? ORDER BY sent ASC';
        $tmp = $db->GetCol($query, array($id));
        if (is_array($tmp))
        {
            foreach ($tmp as $note_id)
            {
                $message = message_ops::load_by_id($note_id);
                $order->add_note($message, false);
            }
        }

        return $order;
    }

    static public function insert(Order &$order)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        $query = 'INSERT INTO ' . cms_db_prefix() . "module_ec_ordermgr (feu_user_id,invoice, billing_company,billing_first_name,billing_last_name,
              billing_address1,billing_address2,billing_city, billing_state,billing_postal,billing_country,
              billing_phone,billing_fax,billing_email, status, order_notes, extra, create_date, modified_date)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,$now,$now)";

        $extra = null;
        if (($tmp = $order->get_all_extra()))
        {
            $extra = serialize($tmp);
        }

        $billing = $order->get_billing();
        $dbr = $db->Execute($query, array(
            $order->get_feu_user(),
            'NOTSET',
            $billing->get_company(),
            $billing->get_firstname(),
            $billing->get_lastname(),
            $billing->get_address1(),
            $billing->get_address2(),
            $billing->get_city(),
            $billing->get_state(),
            $billing->get_postal(),
            $billing->get_country(),
            $billing->get_phone(),
            $billing->get_fax(),
            $billing->get_email(),
            $order->get_status(),
            $order->get_order_notes(),
            $extra
        ));

        if (! $dbr)
        {
            echo "DEBUG: " . $db->sql . '<br/>' . $db->ErrorMsg();
            die();
            return FALSE;
        }

        $order->set_id($db->Insert_Id());
        $invoice = $order->get_invoice();
        if (! $invoice)
        {
            // generate an invoice number.
            $invoice = orders_helper::make_invoice_number($order->get_id());
            $order->set_invoice($invoice);
            $query = 'UPDATE ' . cms_db_prefix() . 'module_ec_ordermgr SET invoice = ? WHERE id = ? and invoice = ?';
            $db->Execute($query, array($invoice, $order->get_id(), 'NOTSET'));
        }
        $order->set_create_date(trim($now, "'"));
        $order->set_modified_date(trim($now, "'"));

        try
        {
            // save the shipping stuff.
            for ($i = 0; $i < $order->count_destinations(); $i ++)
            {
                $shipping = $order->get_shipping($i);
                $shipping->set_id(null);
                $shipping->set_order_id($order->get_id());
                $res = $shipping->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            // save the payments stuff
            for ($i = 0; $i < $order->count_payments(); $i ++)
            {
                $payment = $order->get_payment($i);
                $payment->set_id(null);
                $payment->set_order_id($order->get_id());
                $res = $payment->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            // save the notes
            for ($i = 0; $i < $order->count_notes(); $i ++)
            {
                $message = $order->get_note($i);
                $message->set_id(null);
                $message->set_order_id($order->get_id());
                $res = $message->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            OrderMgr\order::on_order_created($order->get_id());

            return TRUE;
        }
        catch (\xt_exception $e)
        {
            self::delete_by_id($order->get_id());
            return FALSE;
        }
    }

    static public function update(Order &$order, $delete_items = false)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        $query = 'UPDATE ' . cms_db_prefix() . "module_ec_ordermgr SET
                feu_user_id = ?, invoice = ?,
                billing_company = ?, billing_first_name = ?, billing_last_name = ?,
                billing_address1 = ?, billing_address2 = ?, billing_city = ?,
                billing_state = ?, billing_postal = ?, billing_country = ?,
                billing_phone = ?, billing_fax = ?, billing_email = ?,
                status = ?, order_notes = ?, extra = ?,
                modified_date = $now
              WHERE id = ?";

        $billing = $order->get_billing();
        $extra = '';
        if (($tmp = $order->get_all_extra()))
        {
            $extra = serialize($tmp);
        }

        $dbr = $db->Execute($query, array(
            $order->get_feu_user(),
            $order->get_invoice(),
            $billing->get_company(),
            $billing->get_firstname(),
            $billing->get_lastname(),
            $billing->get_address1(),
            $billing->get_address2(),
            $billing->get_city(),
            $billing->get_state(),
            $billing->get_postal(),
            $billing->get_country(),
            $billing->get_phone(),
            $billing->get_fax(),
            $billing->get_email(),
            $order->get_status(),
            $order->get_order_notes(),
            $extra,
            $order->get_id()
        ));
        if (! $dbr)
        {
            echo "DEBUG: " . $db->sql . '<br/>' . $db->ErrorMsg();
            die();
            return FALSE;
        }

        try
        {
            $order->set_modified_date(trim($now, "'"));
            if ($delete_items)
            {
                $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE order_id = ?';
                $dbr = $db->Execute($query, array($order->get_id()));

                $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE order_id = ?';
                $dbr = $db->Execute($query, array($order->get_id()));
            }
            for ($i = 0; $i < $order->count_destinations(); $i ++)
            {
                $shipping = $order->get_shipping($i);
                $shipping->set_order_id($order->get_id());
                $res = $shipping->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            // save the payments stuff
            for ($i = 0; $i < $order->count_payments(); $i ++)
            {
                $payment = $order->get_payment($i);
                $payment->set_order_id($order->get_id());
                $res = $payment->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            // save the notes
            for ($i = 0; $i < $order->count_notes(); $i ++)
            {
                $message = $order->get_note($i);
                $message->set_order_id($order->get_id());
                $res = $message->save();
                if (! $res)
                {
                    return FALSE;
                }
            }

            OrderMgr\order::on_order_updated($order->get_id());

            return TRUE;
        }
        catch (\xt_exception $e)
        {
            die($e->__toString());
            // now what to do.
            return FALSE;
        }
    }

    static public function delete_by_id($order_id)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages WHERE order_id = ?';
        $db->Execute($query, array($order_id));

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE order_id = ?';
        $db->Execute($query, array($order_id));

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE order_id = ?';
        $db->Execute($query, array($order_id));

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_payments WHERE order_id = ?';
        $db->Execute($query, array($order_id));

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE id = ?';
        $db->Execute($query, array($order_id));

        // delete all associated data.
        $assoc = new \CMSMSExt\AssocData($db, 'EcOrderMgr');
        $assoc->Delete($order_id);

        OrderMgr\order::on_order_deleted($order_id);
    }

} // end of class

// EOF
?>
