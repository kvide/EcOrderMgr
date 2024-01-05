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

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$taboptarray = ['mysql' => 'TYPE=InnoDB'];

$flds = "
	id I KEY AUTO,
	feu_user_id I NOTNULL,
  	invoice C(50) NOTNULL,
   	billing_company C(255),
	billing_first_name C(50),
	billing_last_name C(50),
	billing_address1 C(100),
	billing_address2 C(100),
	billing_city C(50),
	billing_state C(25),
	billing_postal C(25),
	billing_country C(50),
	billing_phone C(25),
	billing_fax C(25),
	billing_email C(255),
	status C(25),
   	order_notes X,
   	extra X2,
	create_date " . CMS_ADODB_DT . ",
	modified_date " . CMS_ADODB_DT . "
";

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    id I KEY AUTO,
    order_id I NOTNULL,
    name C(255) NOTNULL,
    shipping_company C(255),
    shipping_first_name C(50),
    shipping_last_name C(50),
    shipping_address1 C(100),
    shipping_address2 C(100),
    shipping_city C(50),
    shipping_state C(25),
    shipping_postal C(25),
    shipping_country C(50),
    shipping_phone C(25),
    shipping_fax C(25),
    shipping_email C(255),
    shipping_message  X,
    source_company C(255),
    source_first_name C(50),
    source_last_name C(50),
    source_address1 C(100),
    source_address2 C(100),
    source_city C(50),
    source_state C(25),
    source_postal C(25),
    source_country C(50),
    source_phone C(25),
    source_fax C(25),
    source_email C(255),
    packing_list X2,
    vendor_id I,
    status C(50),
    optional C(255),
    pickup I1 DEFAULT 0,
    create_date " . CMS_ADODB_DT . ",
    modified_date " . CMS_ADODB_DT . "
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr_shipping", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
	id I KEY AUTO,
	order_id I NOTNULL,
   	shipping_id I NOTNULL,
	item_id I,
	quantity I,
	product_name C(255),
	details X,
	unit_price F,
	weight F,
   	discount F,
   	status C(25),
	create_date " . CMS_ADODB_DT . ",
	modified_date " . CMS_ADODB_DT . ",
   	item_type C(25),
   	sku C(25),
   	source C(50),
   	master_price F,
   	subscr_payperiod C(50),
   	subscr_delperiod C(50),
   	subscr_expires " . CMS_ADODB_DT . ",
   	assocdata X
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr_items", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    id I KEY AUTO,
    order_id I NOTNULL,
    sender_name C(255),
    subject C(255),
    is_html I1,
    body X,
    sent " . CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr_messages", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "id I KEY AUTO,
         name C(255),
         is_html I1,
         subject C(255),
         template X,
         create_date " . CMS_ADODB_DT . ",
         modified_date " . CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr_message_templates", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "id I KEY AUTO,
         order_id      I NOTNULL,
         amount        F,
         payment_date  I,
         status        C(50),
         method        C(50),
         gateway       C(50),
         cc_number     B,
         cc_expiry     I,
         cc_verifycode B,
	     confirmation_num C(255),
         txn_id        C(255),
         notes         X,
         assocdata     X
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_ordermgr_payments", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

// Indexes
$sqlarray = $dict->CreateIndexSQL('orders_feu_id', cms_db_prefix() . 'module_ec_ordermgr', 'feu_user_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_order_modified', cms_db_prefix() . 'module_ec_ordermgr', 'modified_date');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_order_created', cms_db_prefix() . 'module_ec_ordermgr', 'create_date');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_order_status', cms_db_prefix() . 'module_ec_ordermgr', 'status');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_shipping_orderid', cms_db_prefix() . 'module_ec_ordermgr_shipping', 'order_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_items_to_shipping', cms_db_prefix() . 'module_ec_ordermgr_items', 'order_id,shipping_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL('orders_messages', cms_db_prefix() . 'module_ec_ordermgr_messages', 'order_id');
$dict->ExecuteSQLArray($sqlarray);

// setup foreign key relationships
$sql = [];
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_payments ADD FOREIGN KEY (order_id) REFERENCES '
    . cms_db_prefix() . 'module_ec_ordermgr (id)';
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_messages ADD FOREIGN KEY (order_id) REFERENCES '
    . cms_db_prefix() . 'module_ec_ordermgr (id)';
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_items ADD FOREIGN KEY (order_id) REFERENCES '
    . cms_db_prefix() . 'module_ec_ordermgr (id)';
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_items ADD FOREIGN KEY (shipping_id) REFERENCES
' . cms_db_prefix() . 'module_ec_ordermgr_shipping (id)';
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_shipping ADD FOREIGN KEY (order_id) REFERENCES
' . cms_db_prefix() . 'module_ec_ordermgr (id)';
$sql[] = 'ALTER TABLE ' . cms_db_prefix() . 'module_ec_ordermgr_shipping ADD FOREIGN KEY (vendor_id) REFERENCES
' . cms_db_prefix() . 'module_mams_users (id)';
foreach ($sql as $one)
{
    $db->Execute($one);
}

// Templates
$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_shippingform_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetPreference('systemdflt_shippingform_template', $template);
    $this->SetTemplate('shippingform_Sample', $template);
    $this->SetPreference('dflt_shippingform_template', 'Sample');
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_invoice_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetPreference('systemdflt_invoice_template', $template);
    $this->SetTemplate('invoice_Sample', $template);
    $this->SetPreference('dflt_invoice_template', 'Sample');
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_gateway_complete_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetPreference('systemdflt_gateway_complete_template', $template);
    $this->SetTemplate('gateway_complete_Sample', $template);
    $this->SetPreference('dflt_gateway_complete_template', 'Sample');
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_billingform_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetPreference('systemdflt_billingform_template', $template);
    $this->SetTemplate('billingform_Sample', $template);
    $this->SetPreference('dflt_billingform_template', 'Sample');
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_confirmorder_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetPreference('systemdflt_confirmorder_template', $template);
    $this->SetTemplate('confirmorder_Sample', $template);
    $this->SetPreference('dflt_confirmorder_template', 'Sample');
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_adminemail_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetTemplate('adminemail_template', $template);
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_useremail_template.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetTemplate('useremail_template', $template);
}

$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_postorder_msg.tpl');
if (file_exists($fn))
{
    $template = file_get_contents($fn);
    $this->SetTemplate('postorder_msg', $template);
}

$fn = cms_join_path(__DIR__, 'templates', 'orig_report_shipping_costs.tpl');
if (is_file($fn))
{
    $tpl = file_get_contents($fn);
    $this->SetTemplate('report_shipping_costs', $tpl);
}
$fn = cms_join_path(__DIR__, 'templates', 'orig_report_recent_payments.tpl');
if (is_file($fn))
{
    $tpl = file_get_contents($fn);
    $this->SetTemplate('report_recent_payments', $tpl);
}
$fn = cms_join_path(__DIR__, 'templates', 'orig_report_subscr_picklist.tpl');
if (is_file($fn))
{
    $tpl = file_get_contents($fn);
    $this->SetTemplate('report_subscr_picklist', $tpl);
}

// events
Events::CreateEvent('OrderMgr', 'PostGatewayComplete');
Events::CreateEvent('OrderMgr', 'CalculateDiscounts');
Events::CreateEvent('OrderMgr', 'OrderDeletedPre');
Events::CreateEvent('OrderMgr', 'OrderDeletedPost');
$this->AddEventHandler('EcPaymentExt', 'on_incoming_event', false);

// preferences
$this->SetPreference('invoice_message', '<p>' . $this->Lang('dflt_invoice_message') . '</p>');
$this->SetPreference('adminemail_subject', $this->Lang('order') . ': {$ordernumber}');
$this->SetPreference('useremail_subject', $this->Lang('thank_you_for_order'));
$str = md5(uniqid(rand(), true));
$this->SetPreference('encryption_key', $str);
$this->SetPreference('force_ssl', 0);
$this->SetPreference('gateway_description', get_site_preference('sitename'));
$this->SetPreference('address_retrieval', 'remember_last');

// permissions
$this->CreatePermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS, \EcOrderMgr\ORDERS_PERM_VIEWORDERS);
$this->CreatePermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS, \EcOrderMgr\ORDERS_PERM_MANAGEORDERS);
$this->CreatePermission(\EcOrderMgr\ORDERS_PERM_CONTACT_CUSTOMERS, \EcOrderMgr\ORDERS_PERM_CONTACT_CUSTOMERS);
$this->CreatePermission(\EcOrderMgr\ORDERS_PERM_DELETE_ORDERS, \EcOrderMgr\ORDERS_PERM_DELETE_ORDERS);
// EOF
?>
