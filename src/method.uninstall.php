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
if (! isset($gCms))
{
    exit();
}
if (version_compare(phpversion(), '8.0') < 0)
{
    return "Minimum PHP version of 8.0 required";
}

$this->DeleteTemplate();
$this->RemovePreference();
$this->RemovePermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS);
$this->RemovePermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS);
$this->RemovePermission(\EcOrderMgr\ORDERS_PERM_CONTACT_CUSTOMERS);
$this->RemovePermission(\EcOrderMgr\ORDERS_PERM_DELETE_ORDERS);

$db = &$this->GetDb();
$dict = NewDataDictionary($db);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr_message_templates");
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr_messages");
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr_items");
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr_shipping");
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr_payments");
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . "module_ec_ordermgr");
$dict->ExecuteSQLArray($sqlarray);

// events
$this->RemoveEvent('PostGatewayComplete');
$this->RemoveEvent('CalculateDiscounts');
$this->RemoveEvent('OrderDeletedPre');
$this->RemoveEvent('OrderDeletedPost');
$this->RemoveEventHandler('EcPaymentExt', 'on_incoming_event');

// EOF
?>
