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

class Payment
{
    const STATUS_APPROVED = 'payment_approved';
    const STATUS_AUTHORIZED = 'payment_authorized';
    const STATUS_DECLINED = 'payment_declined';
    const STATUS_ERROR = 'payment_error';
    const STATUS_CANCELLED = 'payment_cancelled';
    const STATUS_OTHER = 'payment_other';
    const STATUS_PAID = 'payment_paid';
    const STATUS_PENDING = 'payment_pending';
    const STATUS_NOTPROCESSED = 'payment_notprocessed';
    const TYPE_UNKNOWN = 'unknown';
    const TYPE_ONLINE = 'online';
    const TYPE_CASH = 'cash';
    const TYPE_CREDITCARD = 'creditcard';

    private $_id;
    private $_order_id;
    private $_amount;
    private $_payment_date;
    private $_method;
    private $_status;
    private $_gateway;
    private $_cc_number;
    private $_cc_expiry;
    private $_cc_verifycode;
    private $_confirmation_num;
    private $_txn_id;
    private $_notes;
    private $_assocdata;

    public function get_id()
    {
        return $this->_id;
    }

    public function set_id($val)
    {
        $this->_id = $val;
    }

    public function get_order_id()
    {
        return $this->_order_id;
    }

    public function set_order_id($val)
    {
        $this->_order_id = $val;
    }

    public function get_amount()
    {
        return $this->_amount;
    }

    public function set_amount($val)
    {
        $this->_amount = $val;
    }

    public function get_payment_date()
    {
        return $this->_payment_date;
    }

    public function set_payment_date($val)
    {
        $this->_payment_date = \xt_utils::unix_time($val);
    }

    public function get_method()
    {
        return $this->_method;
    }

    public function set_method($val)
    {
        $this->_method = $val;
    }

    public function get_status()
    {
        return $this->_status;
    }

    public function set_status($val)
    {
        $this->_status = $val;
    }

    public function get_gateway()
    {
        return $this->_gateway;
    }

    public function set_gateway($val)
    {
        $this->_gateway = $val;
    }

    public function get_cc_number_masked($numchars = - 4, $mask = '*')
    {
        return \xt_string::mask_string($this->get_cc_number, - 4, $mask);
    }

    public function get_cc_number()
    {
        return $this->_cc_number;
    }

    public function set_cc_number($val)
    {
        $this->_cc_number = $val;
    }

    public function get_cc_expiry()
    {
        return $this->_cc_expiry;
    }

    public function set_cc_expiry($val)
    {
        $this->_cc_expiry = $val;
    }

    public function get_cc_verifycode()
    {
        return $this->_cc_verifycode;
    }

    public function set_cc_verifycode($val)
    {
        $this->_cc_verifycode = $val;
    }

    public function get_confirmation_num()
    {
        return $this->_confirmation_num;
    }

    public function set_confirmation_num($val)
    {
        $this->_confirmation_num = $val;
    }

    public function get_txn_id()
    {
        return $this->_txn_id;
    }

    public function set_txn_id($val)
    {
        $this->_txn_id = $val;
    }

    public function get_notes()
    {
        return $this->_notes;
    }

    public function set_notes($val)
    {
        $this->_notes = $val;
    }

    public function set_extra($key, $val)
    {
        if (! is_array($this->_assocdata))
        {
            $this->_assocdata = array();
        }
        $this->_assocdata[$key] = $val;
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

    public function get_assocdata()
    {
        return $this->_assocdata;
    }

    public function save()
    {
        if ($this->get_id() > 0)
        {
            return payment_ops::update($this);
        }

        return payment_ops::insert($this);
    }

    public function from_array($data)
    {
        if (isset($data['id']))
        {
            $this->set_id($data['id']);
        }
        if (isset($data['order_id']))
        {
            $this->set_order_id($data['order_id']);
        }
        $this->set_amount($data['amount']);
        $this->set_payment_date($data['payment_date']);
        $this->set_method($data['method']);
        $this->set_status($data['status']);
        $this->set_gateway($data['gateway']);
        $this->set_cc_number($data['cc_number']);
        $this->set_cc_expiry($data['cc_expiry']);
        $this->set_cc_verifycode($data['cc_verifycode']);
        $this->set_confirmation_num($data['confirmation_num']);
        $this->set_txn_id($data['txn_id']);
        $this->set_notes($data['notes']);
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
        $result['amount'] = $this->get_amount();
        $result['payment_date'] = $this->get_payment_date();
        $result['method'] = $this->get_method();
        $result['status'] = $this->get_status();
        $result['gateway'] = $this->get_gateway();
        $result['cc_number'] = $this->get_cc_number();
        $result['cc_expiry'] = $this->get_cc_expiry();
        $result['cc_verifycode'] = $this->get_cc_verifycode();
        $result['confirmation_num'] = $this->get_confirmation_num();
        $result['txn_id'] = $this->get_txn_id();
        $result['notes'] = $this->get_notes();
        $result['assocdata'] = $this->get_assocdata();

        return $result;
    }

} // end of class

#
# EOF
#
?>
