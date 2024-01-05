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

class LineItem
{
    const ITEMTYPE_PRODUCT = '0_product';
    const ITEMTYPE_SERVICE = '1_service';
    const ITEMTYPE_DISCOUNT = '2_discount';
    const ITEMTYPE_SHIPPING = '3_shipping';
    const ITEMTYPE_TAX = '4_tax';

    private $_id;
    private $_order_id;
    private $_shipping_id;
    private $_item_id;
    private $_quantity;
    private $_description;
    private $_details;
    private $_unit_price;
    private $_weight;
    private $_discount = 0;
    private $_status;
    private $_create_date;
    private $_modified_date;
    private $_item_type;
    private $_sku;
    private $_source;
    private $_master_price;
    private $_subscr_payperiod = - 1;
    // subscription info.
    private $_subscr_delperiod = - 1;
    private $_subscr_expires;
    private $_assocdata;

    public function get_id()
    {
        return $this->_id;
    }

    public function set_id($num)
    {
        $this->_id = $num;
    }

    public function get_item_type()
    {
        return $this->_item_type;
    }

    public function set_item_type($num)
    {
        $this->_item_type = $num;
    }

    public function is_digital()
    {
        $tmp = $this->get_extra('__digital__');
        if ($tmp)
        {
            return TRUE;
        }
    }

    public function set_digital($flag = true)
    {
        $flag = (bool) $flag;
        $this->set_extra('__digital__', $flag);
    }

    public function get_sku()
    {
        return $this->_sku;
    }

    public function set_sku($num)
    {
        $this->_sku = $num;
    }

    public function get_source()
    {
        return $this->_source;
    }

    public function set_source($num)
    {
        $this->_source = $num;
    }

    /**
     * The master price is the total price for this line item before discounts
     * if not specified, it is calculated by unit_price * quantity
     */
    public function get_master_price()
    {
        return is_null($this->_master_price) ? 0 : (float) $this->_master_price;
    }

    public function set_master_price($num)
    {
        $this->_master_price = $num;
    }

    public function get_order_id()
    {
        return $this->_order_id;
    }

    public function set_order_id($num)
    {
        $this->_order_id = $num;
    }

    public function get_shipping_id()
    {
        return $this->_shipping_id;
    }

    public function set_shipping_id($num)
    {
        $this->_shipping_id = $num;
    }

    public function get_item_id()
    {
        return $this->_item_id;
    }

    public function set_item_id($num)
    {
        $this->_item_id = $num;
    }

    public function get_quantity()
    {
        return is_null($this->_quantity) ? 0 : (int) $this->_quantity;
    }

    public function set_quantity($num)
    {
        $this->_quantity = (int) $num;
    }

    public function get_description()
    {
        return $this->_description;
    }

    public function set_description($num)
    {
        $this->_description = $num;
    }

    public function get_details()
    {
        return $this->_details;
    }

    public function set_details($num)
    {
        $this->_details = $num;
    }

    public function get_unit_price()
    {
        return $this->_unit_price;
    }

    public function set_unit_price($num)
    {
        $this->_unit_price = $num;
    }

    /**
     *
     * @deprecated
     */
    public function get_weight()
    {
        return $this->get_unit_weight();
    }

    public function get_unit_weight()
    {
        return $this->_weight;
    }

    public function set_unit_weight($num)
    {
        $this->_weight = $num;
    }

    public function get_status()
    {
        return $this->_status;
    }

    public function set_status($num)
    {
        $this->_status = $num;
    }

    /**
     *
     * @deprecated
     */
    public function get_discount()
    {
        return $this->get_unit_discount();
    }

    public function get_unit_discount()
    {
        return is_null($this->_discount) ? 0 : (float) $this->_discount;
    }

    public function set_unit_discount($num)
    {
        $this->_discount = $num;
    }

    public function get_create_date()
    {
        return $this->_create_date;
    }

    public function set_create_date($num)
    {
        $this->_create_date = $num;
    }

    public function get_modified_date()
    {
        return $this->_modified_date;
    }

    public function set_modified_date($num)
    {
        $this->_modified_date = $num;
    }

    public function get_net_price()
    {
        return $this->get_unit_price() + $this->get_unit_discount();
    }

    /**
     * This gets the highest of the master price, or the net unit price * quantity
     * then adjusts for discounts
     */
    public function get_net_total()
    {
        return max($this->get_master_price(), $this->get_unit_price()
                    * $this->get_quantity()) + $this->get_unit_discount() * $this->get_quantity();
    }

    public function is_subscription()
    {
        if (empty($this->_subscr_payperiod) || $this->_subscr_payperiod == '-1' || $this->_subscr_payperiod == 'none')
        {
            return FALSE;
        }

        return TRUE;
    }

    public function get_subscr_payperiod()
    {
        return $this->_subscr_payperiod;
    }

    public function set_subscr_payperiod($period)
    {
        $this->_subscr_payperiod = $period;
    }

    public function get_subscr_delperiod()
    {
        return $this->_subscr_delperiod;
    }

    public function set_subscr_delperiod($period)
    {
        $this->_subscr_delperiod = $period;
    }

    public function get_subscr_expires()
    {
        return $this->_subscr_expires;
    }

    public function set_subscr_expires($date)
    {
        $this->_subscr_expires = $date;
    }

    public function get_extra($key)
    {
        $ret = null;
        if (is_array($this->_assocdata))
        {
            if (isset($this->_assocdata[$key]))
            {
                $ret = $this->_assocdata[$key];
            }
        }

        return $ret;
    }

    public function set_extra($key, $val)
    {
        if (! is_array($this->_assocdata))
        {
            $this->_assocdata = array();
        }
        if (is_null($val) && array_key_exists($key, $this->_associdata))
        {
            unset($this->_assocdata[$key]);
        }
        else
        {
            $this->_assocdata[$key] = $val;
        }
    }

    public function get_assocdata()
    {
        return $this->_assocdata;
    }

    public function save()
    {
        if ($this->get_id() > 0)
        {
            return lineitem_ops::update($this);
        }
        return lineitem_ops::insert($this);
    }

    public function from_array($data)
    {
        $this->set_id($data['id']);
        $this->set_order_id($data['order_id']);
        $this->set_shipping_id($data['shipping_id']);
        $this->set_item_id($data['item_id']);
        $this->set_quantity($data['quantity']);
        $this->set_description($data['product_name']);
        if (isset($data['description']))
        {
            $this->set_description($data['description']);
        }
        $this->set_details($data['details']);
        $this->set_unit_price($data['unit_price']);
        $this->set_unit_discount($data['discount']);
        $this->set_unit_weight($data['weight']);
        $this->set_status($data['status']);
        $this->set_create_date($data['create_date']);
        $this->set_modified_date($data['modified_date']);
        $this->set_item_type($data['item_type']);
        $this->set_sku($data['sku']);
        $this->set_source($data['source']);
        $this->set_master_price($data['master_price']);
        $this->set_subscr_payperiod($data['subscr_payperiod']);
        $this->set_subscr_delperiod($data['subscr_delperiod']);
        $this->set_subscr_expires($data['subscr_expires']);
        if (isset($data['assocdata']) && is_array($data['assocdata']))
        {
            $this->_assocdata = $data['assocdata'];
        }
    }

    public function to_array()
    {
        $result = array();
        $result['id'] = $this->get_id();
        $result['order_id'] = $this->get_order_id();
        $result['shipping_id'] = $this->get_shipping_id();
        $result['item_id'] = $this->get_item_id();
        $result['quantity'] = $this->get_quantity();
        $result['product_name'] = $this->get_description();
        $result['details'] = $this->get_details();
        $result['unit_price'] = $this->get_unit_price();
        $result['discount'] = $this->get_unit_discount();
        $result['weight'] = $this->get_weight();
        $result['status'] = $this->get_status();
        $result['create_date'] = $this->get_create_date();
        $result['modified_date'] = $this->get_modified_date();
        $result['item_type'] = $this->get_item_type();
        $result['sku'] = $this->get_sku();
        $result['source'] = $this->get_source();
        $result['master_price'] = $this->get_master_price();
        $result['net_price'] = $this->get_net_price();
        $result['price'] = $this->get_net_price(); // for backwards compatibility.
        $result['subscr_payperiod'] = $this->get_subscr_payperiod();
        $result['subscr_delperiod'] = $this->get_subscr_delperiod();
        $result['subscr_expires'] = $this->get_subscr_expires();
        $result['assocdata'] = $this->get_assocdata();

        return $result;
    }

    public function reset_ids()
    {
        $this->set_id(null);
        $this->set_shipping_id(null);
        $this->set_order_id(null);
    }

} // end of class

#
# EOF
#
?>
