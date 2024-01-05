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

class message_ops
{

    private function __construct()
    {
        // Static class
    }

    static public function &load_by_id($id)
    {
        $tmp = null;
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'SELECT id,order_id,sender_name,subject,is_html,body,sent
                FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages
               WHERE id = ?';
        $row = $db->GetRow($query, array($id));
        if (! $row)
        {
            return $tmp;
        }

        $message = new order_message();
        $message->from_array($row);

        return $message;
    }

    static public function insert(order_message &$message)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $now = $db->DbTimeStamp(time());
        if ($message->get_sent() == '')
        {
            $message->set_sent(trim($now, "'"));
        }
        $query = 'INSERT INTO ' . cms_db_prefix() . 'module_ec_ordermgr_messages
              (order_id,sender_name,subject,is_html,body,sent)
              VALUES (?,?,?,?,?,?)';
        $dbr = $db->Execute($query, array(
            $message->get_order_id(),
            $message->get_sender_name(),
            $message->get_subject(),
            $message->get_is_html(),
            $message->get_body(),
            trim($message->get_sent(), '"')
        ));
        if (! $dbr)
        {
            throw new \xt_sql_error($db->sql . "\n" . $db->ErrorMsg());
        }
        $message->set_id($db->Insert_Id());

        return TRUE;
    }

    static public function update(order_message &$message)
    {
        $gCms = cmsms();
        $db = $gCms->GetDb();
        $query = 'UPDATE ' . cms_db_prefix() . 'module_ec_ordermgr_messages
                 SET order_id = ?, sender_name = ?, subject = ?,
                     is_html = ?, body = ?, sent = ?
               WHERE id = ?';
        $dbr = $db->Execute($query, array(
            $message->get_order_id(),
            $message->get_sender_name(),
            $message->get_subject(),
            $message->get_is_html(),
            $message->get_body(),
            trim($message->get_sent(), '"')
        ));
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
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_ec_ordermgr_messages
               WHERE id = ?';
        $dbr = $db->Execute($query, array($id));
        if (! $dbr)
        {
            return FALSE;
        }
        return TRUE;
    }

} // end of class

#
# EOF
#
?>
