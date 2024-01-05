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

use EcommerceExt\Cart;
use EcommerceExt\Shipping;
use EcommerceExt\Tax;
use EcommerceExt\Packaging;
use EcommerceExt\Handling;

interface order_factory
{

    // set the order into
    public function __construct(Order $order = null);

    public function set_billing_address(\xt_address $address);

    public function set_order_notes($notes);

    public function set_shipping_address($dest_idx, \xt_address $addr);

    public function set_shipping_pickup($dest_idx, $flag);

    public function set_shipping_message($dest_idx, $message);

    public function set_order_extra($key, $val);

    public function validate_addresses();

    public function set_order(Order $order);

    public function set_cart_module(Cart\shopping_cart_mgr $module);

    public function set_shipping_module(Shipping\shipping_assistant $module = null);

    public function set_tax_module(Tax\tax_calculator $module = null);

    public function set_packaging_module(Packaging\packaging_calculator $module = null);

    public function set_handling_module(Handling\handling_calculator $module = null);

    public function set_user_id($uid);

    // public function set_address_policy($policy);

    // talks to the cart, and makes a basic order (one or more destinations)
    // but does not have shipping, handling, or taxes.
    // @return order
    public function get_basic_order();

    // takes the order and adds line items for packaging, shipping, handling, taxes, promotions
    // @return order
    public function adjust_for_shipping();

    // return boolean if a single order can come from multiple different vendors
    public function supports_multiple_vendors();

    // returns a boolean if different shipping locations can be specified for each shipping/sub-order/destination
    public function supports_different_shipping_locations();

}

?>
