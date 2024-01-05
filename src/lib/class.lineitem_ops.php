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

class lineitem_ops
{

    static private function mysql_datetime($input)
    {
        if (! $input)
        {
            return NULL;
        }

        $db = cmsms()->GetDb();

        return trim($db->DbTimeStamp($input), "'");
    }

    static public function &load_by_id($id)
    {
        $res = null;
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items WHERE id = ?';
        $tmp = $db->GetRow($query, array($id));
        if (! $tmp)
        {
            return $res;
        }

        if ($tmp['assocdata'])
        {
            $tmp['assocdata'] = unserialize($tmp['assocdata']);
        }

        $line_item = new LineItem();
        $line_item->from_array($tmp);

        return $line_item;
    }

    static public function insert(LineItem &$line_item)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        $query = 'INSERT INTO ' . cms_db_prefix() . "module_ec_ordermgr_items
               (order_id,shipping_id,item_id,quantity,
                product_name,details,unit_price,discount,weight,status,
                create_date,modified_date,
                item_type,sku,source,master_price,
                subscr_payperiod,subscr_delperiod,subscr_expires,assocdata)
              VALUES (?,?,?,?,?,?,?,?,?,?,$now,$now,?,?,?,?,?,?,?,?)";

        $assocdata = $line_item->get_assocdata();
        if ($assocdata)
        {
            $assocdata = serialize($assocdata);
        }
        $dbr = $db->Execute($query, array(
            $line_item->get_order_id(),
            $line_item->get_shipping_id(),
            $line_item->get_item_id(),
            $line_item->get_quantity(),
            $line_item->get_description(),
            $line_item->get_details(),
            $line_item->get_unit_price(),
            $line_item->get_unit_discount(),
            $line_item->get_unit_weight(),
            $line_item->get_status(),
            $line_item->get_item_type(),
            $line_item->get_sku(),
            $line_item->get_source(),
            $line_item->get_master_price(),
            $line_item->get_subscr_payperiod(),
            $line_item->get_subscr_delperiod(),
            self::mysql_datetime($line_item->get_subscr_expires()),
            $assocdata
        ));

        $line_item->set_id($db->Insert_Id());
        $line_item->set_create_date(trim($now, "'"));
        $line_item->set_modified_date(trim($now, "'"));

        if (! $dbr)
        {
            die($db->sql . '<br/>' . $db->ErrorMsg());
            return FALSE;
        }

        return TRUE;
    }

    static public function update(LineItem &$line_item)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        $query = 'UPDATE ' . cms_db_prefix() . "module_ec_ordermgr_items
               SET order_id = ?, shipping_id = ?, item_id = ?,
                   quantity = ?, product_name = ?, details = ?,
                   unit_price = ?, discount = ?, weight = ?, status = ?,
                   modified_date = $now, item_type = ?, sku = ?, source = ?, master_price = ?,
                   subscr_payperiod = ?, subscr_delperiod = ?, subscr_expires = ?,
                   assocdata = ?
               WHERE id = ?";

        $assocdata = $line_item->get_assocdata();
        if ($assocdata)
        {
            $assocdata = serialize($assocdata);
        }
        $dbr = $db->Execute($query, array(
            $line_item->get_order_id(),
            $line_item->get_shipping_id(),
            $line_item->get_item_id(),
            $line_item->get_quantity(),
            $line_item->get_description(),
            $line_item->get_details(),
            $line_item->get_unit_price(),
            $line_item->get_discount(),
            $line_item->get_weight(),
            $line_item->get_status(),
            $line_item->get_item_type(),
            $line_item->get_sku(),
            $line_item->get_source(),
            $line_item->get_master_price(),
            $line_item->get_subscr_payperiod(),
            $line_item->get_subscr_delperiod(),
            self::mysql_datetime($line_item->get_subscr_expires()),
            $assocdata,
            $line_item->get_id()
        ));
        $line_item->set_modified_date(trim($now, "'"));
        if (! $dbr)
        {
            throw new \xt_sql_error($db->sql . "\n" . $db->ErrorMsg());
        }

        return TRUE;
    }

    static public function delete_by_id($id)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
               WHERE id = ?';
        $dbr = $db->Execute($query, array($id));
        if (! $dbr)
        {
            return FALSE;
        }

        return TRUE;
    }

} // end class.

// EOF
?>
