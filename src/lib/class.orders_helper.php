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
use EcommerceExt\utils;

class orders_helper extends \module_helper
{

    private static $_encryption_key;
    private static $_invoice_prefix;
    private static $_security_key;

    protected function __construct()
    {
        // Static class
    }

    static public function instance()
    {
        return self::get_instance('EcOrderMgr');
    }

    static public function get_encryption_key()
    {
        if (is_null(self::$_encryption_key))
        {
            $module = self::instance();
            self::$_encryption_key = $module->_getRealEncryptionKey();
        }

        return self::$_encryption_key;
    }

    static public function can_manage_orders()
    {
        $module = self::instance();
        if ($module->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
        {
            return TRUE;
        }

        return FALSE;
    }

    static public function get_destination_statuses()
    {
        $mod = self::instance();
        $out = null;
        $out[Destination::STATUS_PENDING] = $mod->Lang('deststatus_pending');
        $out[Destination::STATUS_NOTSHIPPED] = $mod->Lang('deststatus_notshipped');
        $out[Destination::STATUS_SHIPPED] = $mod->Lang('deststatus_shipped');
        $out[Destination::STATUS_PICKUPREADY] = $mod->Lang('deststatus_pickupready');
        $out[Destination::STATUS_DELAYED] = $mod->Lang('deststatus_delayed');
        $out[Destination::STATUS_OTHER] = $mod->Lang('deststatus_other');
        $out[Destination::STATUS_DELIVERED] = $mod->Lang('deststatus_delivered');
        $out[Destination::STATUS_COMPLETED] = $mod->Lang('deststatus_completed');

        return $out;
    }

    static public function postal_required()
    {
        $module = self::instance();

        return $module->GetPreference('require_postalcode', 1);
    }

    static public function state_required()
    {
        $module = self::instance();

        return $module->GetPreference('require_state', 1);
    }

    static public function set_invoice_prefix($txt)
    {
        self::$_invoice_prefix = $txt;
    }

    static public function make_invoice_number($order_id, $prefix = '')
    {
        if (! $prefix)
        {
            $module = self::instance();
            $prefix = $module->GetPreference('ordernum_prefix', 'INV');
            if (! is_null(self::$_invoice_prefix))
            {
                $prefix = self::$_invoice_prefix;
            }
        }

        return $prefix . sprintf('%05d', $order_id);
    }

    static public function is_creditcard_number($str)
    {
        return utils::is_creditcard_number($str);
    }

    static public function requires_creditcard_number()
    {
        stack_trace();
        die('__fixme__');
        $gateway_module = ecomm::get_payment_module();
        if (is_object($gateway_module))
        {
            return $gateway_module->RequiresCreditCardInfo();
        }

        return FALSE;
    }

    static public function creditcard_requires_ssl()
    {
        stack_trace();
        die('__fixme__');
        $module = self::instance();
        $force_ssl = $module->GetPreference('force_ssl');
        $requires_creditcard = self::requires_creditcard_number();
        $gateway_module = ecomm::get_payment_module();
        $gateway_requires_ssl = FALSE;
        if (is_object($gateway_module))
        {
            $gateway_requires_ssl = $gateway_module->RequiresSSL();
        }

        return ($force_ssl || $gateway_requires_ssl);
    }

    static public function is_valid_user()
    {
        $module = self::instance();
        $uid = - 1;

        $feu = \cms_utils::get_module(\MOD_MAMS);
        $uid = $feu->LoggedInId();
        if ((! $feu || ! $uid))
        {
            if ($module->GetPreference('allow_anon_checkout', 0))
            {
                return - 1;
            }

            return FALSE;
        }

        // user is logged in.
        $grpid = $module->GetPreference('require_membership', - 1);
        if ($grpid <= 0)
        {
            return $uid;
        }

        // we require membership in a specific group
        $data = $feu->GetMemberGroupsArray($uid);
        $gids = \xt_array::extract_field($data, 'groupid');
        if (! in_array($grpid, $gids))
        {
            return FALSE;
        }

        return $uid;
    }

    static public function get_security_key()
    {
        if (is_null(self::$_security_key))
        {
            $uid = self::is_valid_user();
            if ($uid !== FALSE)
            {
                $config = cmsms()->GetConfig;
                self::$_security_key = 'c_' . md5(__FILE__ . $config['root_url'] . $uid . session_id());
            }
        }

        return self::$_security_key;
    }

    /**
     * estimate packages for shipping
     *
     * returns mixed ... error message or array of \xt_ecomm_packaging_box items.
     */
    public static function estimate_packages(Order &$order)
    {
        $generator = new \xt_ecomm_package_generator();

        // for each destination
        $dests = $order->get_destinations();
        foreach ($dests as $one_dest)
        {
            $items = $one_dest->get_items();
            foreach ($items as $one_item)
            {
                // only products are shippable.
                if ($one_item->get_item_type() != LineItem::ITEMTYPE_PRODUCT)
                {
                    continue;
                }

                // todo: check for unit conversion to metric
                $box = new xt_ecomm_package($one_item->get_id(), $one_item->get_weight(), 0);
                $info = ecomm::get_product_info($one_item->get_source(), $one_item->get_item_id());
                if (! is_object($info))
                {
                    throw new \Exception('Could not get product info for a product');
                }

                $dimensions = $info->get_dimensions();
                if (! $dimensions || count($dimensions) != 3 || $dimensions[0] <= 0
                    || $dimensions[1] <= 0 || $dimensions[2] <= 0)
                {
                    audit($one_item->get_item_id(), $one_item->get_source(), 'Invalid dimensions for packaging');
                    continue;
                }

                $box['length'] = $dimensions[0];
                $box['width'] = $dimensions[1];
                $box['height'] = $dimensions[2];
                $box['weight'] = $one_item->get_weight();

                // box dimensions need to be in metric (cm and kg)
                if (! \xt_units::is_weight_metric(ecomm::get_weight_units()))
                {
                    $box['weight'] *= \xt_units::KG_TO_LBS;
                }

                if (! \xt_units::is_length_metric(ecomm::get_length_units()))
                {
                    $box['width'] *= \xt_units::KG_TO_LBS;
                    $box['height'] *= \xt_units::KG_TO_LBS;
                    $box['length'] *= \xt_units::KG_TO_LBS;
                }

                $generator->add_item($box);
            }
        }

        $res = $generator->calculate();
        if (! $res)
        {
            return $generator->get_error(); // error ooccurred
        }

        return $generator->get_packages();
    }

    public static function &get_feu_address($uid = null)
    {
        $badresult = NULL;
        if ($uid <= 0)
        {
            $uid = self::is_valid_user();
        }
        if ($uid <= 0)
        {
            return $badresult;
        }

        $serialized = self::instance()->GetPreference('address_map');
        if (! $serialized)
        {
            return $badresult;
        }

        // get the map from the preference and unserialize.
        $addr_map = unserialize($serialized);

        // now, get the feu users properties.
        $feu = \cms_utils::get_module(\MOD_MAMS);
        if (! $feu && ! $module->GetPreference('allow_anon_checkout', 0))
        {
            return $badresult;
        }

        $tmp = $feu->GetUserProperties($uid);
        if (! $tmp)
        {
            return $badresult;
        }
        $userprops = \xt_array::to_hash($tmp, 'title');

        $new_addr_array = array();
        foreach ($addr_map as $key => $value)
        {
            if ($key == '' || $key == - 1)
            {
                continue;
            }
            if ($value == - 1 || $value == '')
            {
                $new_addr_array[$key] = '';
            }
            if ($value == '__USERNAME__')
            {
                // the username is the email address.
                $uinfo = $feu->GetUserInfo($uid);
                $new_addr_array[$key] = $uinfo[1]['username'];
            }
            else if ($value == '__EMAIL__')
            {
                $new_addr_array[$key] = $feu->GetEmail($uid);
            }
            else if (isset($userprops[$value]))
            {
                $new_addr_array[$key] = $userprops[$value]['data'];
            }
        }

        $address = new Address();
        $address->from_array($new_addr_array, '');

        return $address;
    }

    public static function get_gateway_confirm_form($order, $gwmod, $returnid, $destpage = 0)
    {
        $mod = \cms_utils::get_module(\MOD_ECORDERMGR);

        if (! $order instanceof Order)
        {
            throw new \CmsInvalidDataException('Expecting an order object in parameter 1 of ' . __METHOD__);
        }
        if (! $gwmod instanceof \EcPaymentExt)
        {
            throw new \CmsInvalidDataException('Expecting a payment gateway module in parameter 2 of ' . __METHOD__);
        }
        if ($returnid < 1)
        {
            $returnid = \cms_utils::get_current_pageid();
        }
        if ($destpage < 1)
        {
            $destpage = (int) $mod->GetPreference('invoicepage', - 1);
            if ($destpage < 1)
            {
                $destpage = $returnid;
            }
        }

        $gwmod->Reset();
        $gwmod->SetOrderObject($order);

        // do not include the order id in the URL (security... make sure it's in the session somewhere.
        $url = $mod->create_url('__orders', 'gateway_complete', $destpage);
        $gwmod->SetDestination($url);
        $billing = $order->get_billing();
        $gwmod->SetBillingAddress($billing);
        $shipment = $order->get_shipping(0);
        $shipping_addy = $shipment->get_shipping_address();
        $gwmod->SetShippingAddress($shipping_addy);

        $str = $mod->GetPreference('gateway_description');
        $str = $mod->ProcessTemplateFromData($str);
        $str = html_entity_decode($str);
        $gwmod->SetOrderDescription($str);

        // give the items to the gateway module
        for ($i = 0; $i < $order->count_destinations(); $i ++)
        {
            $shipping = $order->get_shipping($i);
            for ($j = 0; $j < $shipping->count_all_items(); $j ++)
            {
                $item = $shipping->get_item($j);
                $item_num = $item->get_item_id();
                $sku = $item->get_sku();
                if (empty($sku))
                {
                    $sku = $item->get_item_id();
                }
                $gwmod->AddItem($item->get_description(), $sku, $item->get_quantity(), $item->get_unit_weight(), $item->get_net_price());
            }
        }

        // get the output from the module
        $smarty = cmsms()->GetSmarty();

        return $gwmod->GetForm($returnid);
    }

}

#
# EOF
#
?>
