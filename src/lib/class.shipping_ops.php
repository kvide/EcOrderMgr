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

final class shipping_ops
{

    private function __construct()
    {
        // Static class
    }

    static public function load_by_id($id)
    {
        $id = (int) $id;
        if ($id < 1)
        {
            throw new \LogicException('Invalid destination id passed to ' . __METHOD__);
        }
        // load the shipping record itself
        $gCms = \CmsApp::get_instance();
        $db = $gCms->GetDb();
        $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE id = ?';
        $row = $db->GetRow($query, array($id));
        if (! $row)
        {
            return FALSE;
        }

        $shipping = new Destination();
        $shipping->from_array($row);

        // load all the items.
        $query = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE shipping_id = ? ORDER BY item_type ASC';
        $tmp = $db->GetCol($query, array($id));
        foreach ($tmp as $item_id)
        {
            $line_item = lineitem_ops::load_by_id($item_id);
            $shipping->add_item($line_item, false);
        }

        return $shipping;
    }

    static public function save(Destination &$dest)
    {
        if ($dest->get_id() < 1)
        {
            return self::insert($dest);
        }

        return self::update($dest);
    }

    static public function insert(Destination &$shipping)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        $query = 'INSERT INTO ' . cms_db_prefix() . "module_ec_ordermgr_shipping (order_id,name,
                shipping_company, shipping_first_name,shipping_last_name,shipping_address1,
                shipping_address2,shipping_city,shipping_state,shipping_postal,
                shipping_country,shipping_phone,shipping_fax,shipping_email,
                source_company, source_first_name,source_last_name,source_address1,
                source_address2,source_city,source_state,source_postal,
                source_country,source_phone,source_fax,source_email,
                shipping_message,vendor_id,status,pickup,optional,packing_list,create_date,modified_date)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";

        $src_address = $shipping->get_source_address();
        if (! $src_address)
        {
            $src_address = new Address(); // / ugly hack
        }
        $dest_address = $shipping->get_shipping_address();
        if (! $dest_address)
        {
            $dest_address = new Address(); // / ugly hack
        }
        $dbr = $db->Execute($query, [
            $shipping->get_order_id(),
            $shipping->get_name(),
            $dest_address->get_company(),
            $dest_address->get_firstname(),
            $dest_address->get_lastname(),
            $dest_address->get_address1(),
            $dest_address->get_address2(),
            $dest_address->get_city(),
            $dest_address->get_state(),
            $dest_address->get_postal(),
            $dest_address->get_country(),
            $dest_address->get_phone(),
            $dest_address->get_fax(),
            $dest_address->get_email(),
            $src_address->get_company(),
            $src_address->get_firstname(),
            $src_address->get_lastname(),
            $src_address->get_address1(),
            $src_address->get_address2(),
            $src_address->get_city(),
            $src_address->get_state(),
            $src_address->get_postal(),
            $src_address->get_country(),
            $src_address->get_phone(),
            $src_address->get_fax(),
            $src_address->get_email(),
            $shipping->get_message(),
            $shipping->get_vendor_id(),
            $shipping->get_status(),
            $shipping->get_pickup(),
            serialize($shipping->get_optional()),
            serialize($shipping->get_packing_list())
        ]);

        if (! $dbr)
        {
            throw new \xt_sql_error($db->sql . "\n" . $db->ErrorMsg());
        }
        $shipping->set_id($db->Insert_Id());
        $shipping->set_create_date(trim($now, "'"));
        $shipping->set_modified_date(trim($now, "'"));

        // insert the items.
        for ($i = 0; $i < $shipping->count_all_items(); $i ++)
        {
            $line_item = $shipping->get_item($i);
            if ($line_item)
            {
                $line_item->set_order_id($shipping->get_order_id());
                $line_item->set_shipping_id($shipping->get_id());
                $line_item->set_id(null);
                $res = $line_item->save();
                if (! $res)
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    static public function update(Destination &$shipping)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'UPDATE ' . cms_db_prefix() . "module_ec_ordermgr_shipping
                SET order_id = ?,
                    shipping_company = ?,
                    shipping_first_name = ?,
                    shipping_last_name = ?,
                    shipping_address1 = ?,
                    shipping_address2 = ?,
                    shipping_city = ?,
                    shipping_state = ?,
                    shipping_postal = ?,
                    shipping_country = ?,
                    shipping_phone = ?,
                    shipping_fax = ?,
                    shipping_email = ?,
                    source_company = ?,
                    source_first_name = ?,
                    source_last_name = ?,
                    source_address1 = ?,
                    source_address2 = ?,
                    source_city = ?,
                    source_state = ?,
                    source_postal = ?,
                    source_country = ?,
                    source_phone = ?,
                    source_fax = ?,
                    source_email = ?,
                    shipping_message = ?,
                    vendor_id = ?,
                    status = ?,
                    pickup = ?,
                    optional = ?,
                    packing_list = ?,
                    modified_date = NOW()
              WHERE id = ?";

        $src_address = $shipping->get_source_address();
        $dest_address = $shipping->get_shipping_address();
        $dbr = $db->Execute($query, array(
            $shipping->get_order_id(),
            $dest_address->get_company(),
            $dest_address->get_firstname(),
            $dest_address->get_lastname(),
            $dest_address->get_address1(),
            $dest_address->get_address2(),
            $dest_address->get_city(),
            $dest_address->get_state(),
            $dest_address->get_postal(),
            $dest_address->get_country(),
            $dest_address->get_phone(),
            $dest_address->get_fax(),
            $dest_address->get_email(),
            $src_address->get_company(),
            $src_address->get_firstname(),
            $src_address->get_lastname(),
            $src_address->get_address1(),
            $src_address->get_address2(),
            $src_address->get_city(),
            $src_address->get_state(),
            $src_address->get_postal(),
            $src_address->get_country(),
            $src_address->get_phone(),
            $src_address->get_fax(),
            $src_address->get_email(),
            $shipping->get_message(),
            $shipping->get_vendor_id(),
            $shipping->get_status(),
            $shipping->get_pickup(),
            serialize($shipping->get_optional()),
            serialize($shipping->get_packing_list()),
            $shipping->get_id()
        ));
        if (! $dbr)
        {
            throw new \xt_sql_error($db->sql . "\n" . $db->ErrorMsg());
        }

        // insert the items.
        for ($i = 0; $i < $shipping->count_all_items(); $i ++)
        {
            $line_item = $shipping->get_item($i);
            $res = $line_item->save();
            if (! $res)
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    static public function delete_by_id($id)
    {
        $db = \CmsApp::get_instance()->GetDb();

        // a. delete the items
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE shipping_id = ?';
        $dbr = $db->Execute($query, array($id));
        if (! $dbr)
        {
            return FALSE;
        }

        // b. delete the shipping.
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE id = ?';
        $dbr = $db->Execute($query, array($id));
        if (! $dbr)
        {
            return FALSE;
        }

        return TRUE;
    }

    static public function load_bulk(array $idlist)
    {
        $db = \CmsApp::get_instance()->GetDb();

        // get the shipping recs, order unspecified.
        $sql = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr_shipping WHERE id IN ('
                    . implode(',', $idlist) . ') ORDER BY id';
        $dest_recs = $db->GetArray($sql);

        // get the line items
        $sql = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE shipping_id IN ('
                    . implode(',', $idlist) . ') ORDER BY shipping_id,id';
        $line_recs = $db->GetArray($sql);

        $out = null;
        foreach ($dest_recs as $dest_rec)
        {
            $list = null;
            for ($i = 0, $n = count($line_recs); $i < $n; $i ++)
            {
                $rec = $line_recs[$i];
                if ($rec['shipping_id'] < $dest_rec['id'])
                {
                    continue;
                }
                if ($rec['shipping_id'] > $dest_rec['id'])
                {
                    break;
                }
                $obj = new LineItem();
                $obj->from_array($rec);
                $list[] = $obj;
            }
            $dest = new Destination();
            $dest->from_array($dest_rec);
            $dest->set_items($list);
            $out[] = $dest;
        }

        return $out;
    }

} // end class.

// EOF

?>
