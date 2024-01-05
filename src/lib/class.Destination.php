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

use EcommerceExt\Packaging;

/**
 * Contains destination info, and line items for the destination
 */
class Destination
{
    const OPTION_LOCALPICKUP = 'localPickup';
    const STATUS_PENDING = '_pending'; // order is being picked/prepared
    const STATUS_NOTSHIPPED = '_notshipped'; // order is prepared, but not shipped
    const STATUS_SHIPPED = '_shipped'; // shipped, or awaiting pickup from shipping company
    const STATUS_PICKUPREADY = '_pickupready'; // awaiting customer pickup
    const STATUS_DELAYED = '_delayed'; // delayed, some problem (backorder ?)
    const STATUS_OTHER = '_other'; // other
    const STATUS_DELIVERED = '_delivered'; // delivered
    const STATUS_COMPLETED = '_completed';

    // no further action necessary (digital orders)
    private $_id;
    private $_name;
    private $_order_id;
    private $_source_address;
    private $_shipping_address;
    private $_message;
    private $_create_date;
    private $_modified_date;
    private $_lineitems;
    private $_packing_list;
    private $_optional;
    private $_vendor_id;
    private $_pickup;
    // set for local pickup.
    private $_status;
    // these members are not saved to the database.
    private $_allow_pickup;

    // used for order selection only.
    public function get_id()
    {
        return $this->_id;
    }

    public function set_id($val)
    {
        $this->_id = $val;
    }

    public function get_name()
    {
        return $this->_name;
    }

    public function set_name($val)
    {
        $this->_name = $val;
    }

    public function get_order_id()
    {
        return $this->_order_id;
    }

    public function set_order_id($val)
    {
        $this->_order_id = (int) $val;
    }

    public function get_packing_list()
    {
        return $this->_packing_list;
    }

    public function set_packing_list(Packaging\packing_list $obj)
    {
        $this->_packing_list = $obj;
    }

    public function get_shipping_address()
    {
        return $this->_shipping_address;
    }

    public function set_shipping_address(\xt_address $val)
    {
        $this->_shipping_address = $val;
    }

    public function get_source_address()
    {
        return $this->_source_address;
    }

    public function set_source_address(\xt_address $val)
    {
        $this->_source_address = $val;
    }

    public function get_vendor_id()
    {
        return $this->_vendor_id;
    }

    public function get_status()
    {
        if (! $this->_status)
        {
            return self::STATUS_PENDING;
        }

        return $this->_status;
    }

    public function get_pickup()
    {
        return $this->_pickup;
    }

    /**
     *
     * @ignore
     */
    public function get_optional()
    {
        return $this->_optional;
    }

    public function get_extra($key)
    {
        $key = trim($key);
        if (! $key)
        {
            throw new \LogicException("Invalid key passed to " . __METHOD__);
        }
        if (is_array($this->_optional) && isset($this->_optional[$key]))
        {
            return $this->_optional[$key];
        }
    }

    public function set_extra($key, $val)
    {
        $key = trim($key);
        if (! $key)
        {
            throw new \LogicException("Invalid key passed to " . __METHOD__);
        }
        if (! is_array($this->_optional))
        {
            $this->_optional = [];
        }
        $this->_optional[$key] = $val;
    }

    public function set_vendor_id($id)
    {
        $id = (int) $id;
        if ($id < 1)
        {
            $id = null;
        }
        $this->_vendor_id = $id;
    }

    public function set_status($str)
    {
        switch ($str)
        {
            case self::STATUS_PENDING:
            case self::STATUS_NOTSHIPPED:
            case self::STATUS_SHIPPED:
            case self::STATUS_PICKUPREADY:
            case self::STATUS_DELAYED:
            case self::STATUS_OTHER:
            case self::STATUS_DELIVERED:
            case self::STATUS_COMPLETED:
                $this->_status = $str;
                break;
            default:
                throw new \LogicException('Invalid status passed to ' . __METHOD__);
        }
    }

    public function set_pickup($flag = true)
    {
        $this->_pickup = (bool) $flag;
    }

    public function get_message()
    {
        return $this->_message;
    }

    public function set_message($val)
    {
        $this->_message = trim($val);
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

    public function add_item(LineItem &$item, $new = true)
    {
        if ($new)
        {
            $item->set_id(null);
        }
        $item->set_order_id($this->get_order_id());
        $item->set_shipping_id($this->get_id());
        if (! is_array($this->_lineitems))
        {
            $this->_lineitems = array();
        }
        $this->_lineitems[] = $item;
    }

    public function set_items($items)
    {
        if (! is_array($items) || count($items) == 0)
        {
            return; // @todo throw an exception
        }

        $this->_lineitems = array();
        foreach ($items as $item)
        {
            if (! is_object($item))
            {
                continue; // @todo throw an exception
            }
            if (! $item instanceof LineItem)
            {
                continue; // @todo throw an exception
            }
            $item->set_id(null);
            $item->set_order_id($this->get_order_id());
            $item->set_shipping_id($this->get_id());
            $this->_lineitems[] = $item;
        }
    }

    public function del_item($idx)
    {
        $new = array();
        for ($i = 0; $i < count($this->_lineitems); $i ++)
        {
            if ($i != $idx)
            {
                $new[] = $this->_lineitems[$i];
            }
        }
        $this->_lineitems = $new;
    }

    public function get_items()
    {
        return $this->_lineitems;
    }

    public function &get_item($idx)
    {
        if ($idx >= 0 && $idx < $this->count_all_items())
        {
            return $this->_lineitems[$idx];
        }
        $out = null;

        return $out;
    }

    public function &get_item_by_sku($skus)
    {
        if (! is_array($skus) && is_string($skus))
        {
            $skus = array($skus);
        }
        for ($i = 0; $i < count($this->_lineitems); $i ++)
        {
            if (in_array($this->_lineitems[$i]->get_sku(), $skus))
            {
                return $this->_lineitems[$i];
            }
        }
        $out = null;

        return $out;
    }

    public function set_item($idx, LineItem $item)
    {
        if ($idx < $this->count_all_items() && $idx >= 0)
        {
            $this->_lineitems[$idx] = $item;
        }
    }

    public function count_real_items()
    {
        $count = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            $type = $item->get_item_type();
            switch ($type)
            {
                case LineItem::ITEMTYPE_TAX:
                case LineItem::ITEMTYPE_SHIPPING:
                case LineItem::ITEMTYPE_DISCOUNT:
                    break;
                default:
                    $count += $item->get_quantity();
                    break;
            }
        }

        return $count;
    }

    public function count_items()
    {
        $count = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            $type = $item->get_item_type();
            switch ($type)
            {
                case LineItem::ITEMTYPE_TAX:
                case LineItem::ITEMTYPE_SHIPPING:
                case LineItem::ITEMTYPE_DISCOUNT:
                    break;
                default:
                    $count ++;
                    break;
            }
        }

        return $count;
    }

    public function count_all_items()
    {
        $count = 0;
        if (is_array($this->_lineitems))
        {
            $count = count($this->_lineitems);
        }

        return $count;
    }

    public function remove_all_items()
    {
        $this->_lineitems = [];
    }

    public function get_weight()
    {
        $weight = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            switch ($item->get_item_type())
            {
                case LineItem::ITEMTYPE_PRODUCT:
                    $weight += $item->get_weight() * $item->get_quantity();
                    break;
                default:
                    break;
            }
        }

        return $weight;
    }

    public function get_subtotal()
    {
        $subtotal = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            switch ($item->get_item_type())
            {
                case LineItem::ITEMTYPE_TAX:
                case LineItem::ITEMTYPE_SHIPPING:
                case LineItem::ITEMTYPE_DISCOUNT:
                    break;
                default:
                    $subtotal += max($item->get_quantity() * $item->get_unit_price(), $item->get_master_price());
                    break;
            }
        }

        return $subtotal;
    }

    public function get_shipping_cost()
    {
        $shipping = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            switch ($item->get_item_type())
            {
                case LineItem::ITEMTYPE_SHIPPING:
                    $shipping += $item->get_net_price();
                    break;
                default:
                    break;
            }
        }

        return $shipping;
    }

    public function get_tax_cost()
    {
        $tax = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            switch ($item->get_item_type())
            {
                default:
                    break;

                case LineItem::ITEMTYPE_TAX:
                    $tax += $item->get_net_price();
                    break;
            }
        }

        return $tax;
    }

    public function get_discount()
    {
        $discount = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            switch ($item->get_item_type())
            {
                case LineItem::ITEMTYPE_TAX:
                case LineItem::ITEMTYPE_SHIPPING:
                    break;

                case LineItem::ITEMTYPE_DISCOUNT:
                default:
                    $discount += ($item->get_unit_discount() * $item->get_quantity());
                    break;
            }
        }

        return $discount;
    }

    public function get_total()
    {
        return $this->get_subtotal() + $this->get_shipping_cost() + $this->get_tax_cost() + $this->get_discount();
    }

    public function is_all_shipped()
    {
        if ($this->get_status() == self::STATUS_SHIPPED || $this->get_status() == self::STATUS_DELIVERED
            || $this->get_status() == self::STATUS_PICKUPREADY)
        {
            return TRUE;
        }

        return FALSE;
        /*
         * for( $i = 0; $i < $this->count_all_items(); $i++ ) {
         * $item =& $this->_lineitems[$i];
         * if( $item->get_status()
         *   != \EcommerceExt\ITEMSTATUS_SHIPPED && $item->get_item_type() == LineItem::ITEMTYPE_PRODUCT ) return false;
         * }
         * return TRUE;
         */
    }

    public function is_partially_shipped()
    {
        // remove me
        $have_shipped = false;
        $have_unshipped = false;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            if ($item->get_item_type() == LineItem::ITEMTYPE_PRODUCT)
            {
                if ($item->get_status() == \EcommerceExt\ITEMSTATUS_SHIPPED)
                {
                    $have_shipped = true;
                }
                else if ($item->get_status() == \EcommerceExt\ITEMSTATUS_NOTSHIPPED)
                {
                    $have_unshipped = true;
                }
            }
        }

        return ($have_shipped && $have_unshipped);
    }

    public function allow_pickup($flag = true)
    {
        $this->_allow_pickup = (bool) $flag;
    }

    public function allows_pickup()
    {
        return $this->_allow_pickup;
    }

    public function count_services()
    {
        $n_s = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            if ($item->get_item_type() == LineItem::ITEMTYPE_SERVICE)
            {
                $n_s ++;
            }
        }

        return $n_s;
    }

    public function has_services()
    {
        return $this->count_services() > 0;
    }

    public function count_subscriptions()
    {
        $n_s = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            if ($item->is_subscription())
            {
                $n_s ++;
            }
        }

        return $n_s;
    }

    public function has_subscriptions()
    {
        return $this->count_subscriptions() > 0;
    }

    public function is_subscription_only()
    {
        $have_other = FALSE;
        $have_subscr = FALSE;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            if ($item->is_subscription())
            {
                $have_subscr = TRUE;
            }
            else if ($item->get_item_type() == LineItem::ITEMTYPE_PRODUCT || $item->get_item_type() == LineItem::ITEMTYPE_SERVICE)
            {
                // make sure we exclude discount, shipping, and tax items.
                $have_other = TRUE;
            }
        }

        return ($have_subscr && ! $have_other);
    }

    public function count_products()
    {
        $n_p = 0;
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->_lineitems[$i];
            if ($item->get_item_type() == LineItem::ITEMTYPE_PRODUCT)
            {
                $n_p ++;
            }
        }

        return $n_p;
    }

    public function has_products()
    {
        return $this->count_products() > 0;
    }

    public function save()
    {
        if ($this->get_id() > 0)
        {
            return shipping_ops::update($this);
        }

        return shipping_ops::insert($this);
    }

    public function from_array($data)
    {
        $this->set_id($data['id']);
        $this->set_name($data['name']);
        $this->set_order_id($data['order_id']);
        $this->set_message($data['shipping_message']);
        if (! empty($data['option']))
        {
            $this->set_optional(unserialize($data['option']));
        }
        if (! empty($data['packing_list']))
        {
            // packing list can be null.
            $tmp = unserialize($data['packing_list']);
            if ($tmp)
            {
                $this->set_packing_list($tmp);
            }
        }
        $this->set_pickup($data['pickup']);
        $this->set_status($data['status']);
        $this->set_create_date($data['create_date']);
        $this->set_modified_date($data['modified_date']);
        $addr = new Address();
        $addr->from_array($data, 'shipping_');
        $this->set_shipping_address($addr);
        $addr = new Address();
        $addr->from_array($data, 'source_');
        $this->set_source_address($addr);
        $this->set_vendor_id($data['vendor_id']);
    }

    public function to_array()
    {
        // for backward compatibilty... remove me soon.
        $result = array();
        $result['id'] = $this->get_id();
        $result['name'] = $this->get_name();
        $result['order_id'] = $this->get_order_id();
        $result['message'] = $this->get_message();
        $result['create_date'] = $this->get_create_date();
        $result['modified_date'] = $this->get_modified_date();
        $result['packing_list'] = null;
        $obj = $this->get_packing_list();
        if ($obj)
        {
            $result['packing_list'] = serialize($obj);
        }

        $addr = $this->get_shipping_address();
        $tmp = $addr->to_array('shipping_');
        $result = array_merge($tmp, $result);

        $addr = $this->get_source_address();
        if ($addr)
        {
            $tmp = $addr->to_array('source_');
            $result = array_merge($tmp, $result);
        }

        $result['items'] = array();
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = $this->get_item($i);
            $result['items'][] = $item->to_array();
        }

        $result['vendor_id'] = $this->get_vendor_id();
        $result['option'] = $this->get_optional();
        $result['subtotal'] = $this->get_subtotal();
        $result['discount'] = $this->get_discount();
        $result['tax'] = $this->get_tax_cost();
        $result['shipping_total'] = $this->get_shipping_cost();
        $result['total'] = $this->get_total();

        return $result;
    }

    public function reset_ids()
    {
        $this->set_id(null);
        $this->set_order_id(null);
        for ($i = 0; $i < $this->count_all_items(); $i ++)
        {
            $item = &$this->get_item($i);
            $item->reset_ids();
        }
    }

} // end of class

// EOF
?>
