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

class billing_address_retriever
{
    const SESSION_KEY = '__mybillingaddr__';
    const ADDR_POLICY_COOKIE = 'addr::cookie';
    const ADDR_POLICY_FEU = 'addr::from_feu';
    const ADDR_POLICY_LAST = 'addr::from_last';
    const ADDR_POLICY_NONE = 'addr::none';

    private $_mod;
    private $_policy;
    private $_uid;

    public function __construct(\EcOrderMgr $mod, $policy, $uid)
    {
        $this->_mod = $mod;
        $this->_policy = $policy;
        $this->_uid = (int) $uid;
    }

    /**
     * Returns Address object, or null
     */
    public function get_address()
    {
        // prefer session address... if it exists.
        if (isset($_SESSION[self::SESSION_KEY]))
        {
            $data = $_SESSION[self::SESSION_KEY];
            if ($data)
            {
                $data = unserialize($data);
            }
            if (is_object($data))
            {
                return $data;
            }
        }

        $out = null;
        switch ($this->_policy)
        {
            case self::ADDR_POLICY_FEU:
                if ($this->_uid > 0)
                {
                    $out = orders_helper::get_feu_address();
                }
                break;

            case self::ADDR_POLICY_LAST:
                if ($this->_uid > 0)
                {
                    $last_order_id = orders_ops::find_last_feu_order($this->_uid);
                    if ($last_order_id)
                    {
                        $tmp_order = orders_ops::load_by_id($last_order_id);
                        $out = $tmp_order->get_billing();
                    }
                }
                break;

            case self::ADDR_POLICY_COOKIE:
                $tmp = \cms_cookies::get('my_address');
                if ($tmp)
                {
                    $tmp = unserialize(base64_decode($tmp));
                    if ($tmp instanceof Address)
                    {
                        $out = $tmp;
                    }
                }
                break;

            case self::ADDR_POLICY_NONE:
                break;
        }

        if (! $out)
        {
            $out = new Address();
            $out->state = $this->_mod->GetPreference('dflt_state');
            $out->country = $this->_mod->GetPreference('dflt_country');
        }

        return $out;
    }

    public function save_address(Address $address)
    {
        // save the address in the session.
        $_SESSION[self::SESSION_KEY] = serialize($address);

        if ($this->_policy == self::ADDR_POLICY_COOKIE)
        {
            $data = base64_encode(serialize($address));
            @setcookie('my_address', $data, time() + (365 * 24 * 60 * 60));
        }
    }

} // end of class

?>
