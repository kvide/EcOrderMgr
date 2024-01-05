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

class order_message
{

    private $_id;
    private $_order_id;
    private $_sender_name;
    private $_subject;
    private $_is_html;
    private $_body;
    private $_sent;

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

    public function get_sender_name()
    {
        return $this->_sender_name;
    }

    public function set_sender_name($val)
    {
        $this->_sender_name = $val;
    }

    public function get_subject()
    {
        return $this->_subject;
    }

    public function set_subject($val)
    {
        $this->_subject = $val;
    }

    public function get_is_html()
    {
        return $this->_is_html;
    }

    public function set_is_html($val = true)
    {
        $this->_is_html = $val;
    }

    public function get_body()
    {
        return $this->_body;
    }

    public function set_body($val)
    {
        $this->_body = $val;
    }

    public function get_sent()
    {
        return $this->_sent;
    }

    public function set_sent($val)
    {
        $this->_sent = $val;
    }

    public function save()
    {
        if ($this->get_id() > 0)
        {
            return message_ops::update($this);
        }

        return message_ops::insert($this);
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
        $this->set_sender_name($data['sender_name']);
        $this->set_subject($data['subject']);
        $this->set_is_html($data['is_html']);
        $this->set_body($data['body']);
        $this->set_sent($data['sent']);
    }

    public function to_array()
    {
        $data = array();
        $data['id'] = $this->get_id();
        $data['order_id'] = $this->get_order_id();
        $data['sender_name'] = $this->get_sender_name();
        $data['subject'] = $this->get_subject();
        $data['is_html'] = $this->get_is_html();
        $data['body'] = $this->get_body();
        $data['sent'] = $this->get_sent();

        return $data;

    }

} // end of class

#
# EOF
#
?>
