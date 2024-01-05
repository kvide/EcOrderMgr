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

namespace EcOrderMgr\Reports;

class report_subscr_picklist extends \EcOrderMgr\report_base
{

    public function get_name()
    {
        return $this->fixname(get_class($this));
    }

    public function get_description()
    {
        $mod = \cms_utils::get_module(\MOD_ECORDERMGR);
        return $mod->Lang('report_' . $this->get_name());
    }

    protected function get_query()
    {
        $query = 'SELECT oi.*,os.* FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items oi
                LEFT JOIN ' . cms_db_prefix() . 'module_ec_ordermgr oo
                  ON oi.order_id = oo.id
                LEFT JOIN ' . cms_db_prefix() . 'module_ec_ordermgr_shipping os
                  ON oi.shipping_id = os.id
               WHERE oo.status != \'proposed\'
                 AND oo.status != \'cancelled\'
                 AND oo.status != \'hold\'
                 AND oi.item_type = \'product\'
                 AND oi.subscr_payperiod != \'none\'
                 AND oi.subscr_delperiod != \'none\'
                 AND (oo.modified_date BETWEEN __START_DATE__ AND __END_DATE__)';

        $db = $this->get_db();
        $query = str_replace('__START_DATE__', trim($db->DbTimeStamp($this->get_startdate(), "'")), $query);
        $query = str_replace('__END_DATE__', trim($db->DbTimeStamp($this->get_enddate(), "'")), $query);
        return $query;
    }

}

// EOF
?>
