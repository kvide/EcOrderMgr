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

if (! isset($gCms))
{
    exit();
}
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_CONTACT_CUSTOMERS))
{
    return;
}
if (! isset($params['orderid']))
{
    echo $this->ShowErrors($this->Lang('error_insufficientparams'));
    return;
}
$orderid = (int) $params['orderid'];

//
// setup
//
$curtemplate = '';
$subject = '';
$ishtml = 0;
$body = '';
$templates = array();
$query = 'SELECT id,name FROM ' . cms_db_prefix() . 'module_ec_ordermgr_message_templates
           ORDER by modified_date DESC';
$results = $db->GetArray($query);
if (! is_array($results) || count($results) == 0)
{
    echo $this->DisplayErrorMessage($this->Lang('error_nomsgtemplates'));
    return;
}

foreach ($results as $one)
{
    $templates[$one['name']] = $one['id'];
}

if (isset($params['cancel']))
{
    $this->Redirect($id, 'admin_manageorder', '', array('orderid' => $orderid));
}

//
// get input parameters
//
if (isset($params['input_template']))
{
    $curtemplate = (int) $params['input_template'];
}
if ($curtemplate == '')
{
    $curtemplate = $results[0]['id'];
}

// Get the template data
$query = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr_message_templates WHERE id = ?';
$row = $db->GetRow($query, array($curtemplate));
if (is_array($row))
{
    $ishtml = $row['is_html'];
    $subject = $row['subject'];
    $body = $row['template'];
}

// give the order to smarty
$query = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_ordermgr WHERE id = ?';
$order = $db->GetRow($query, array($orderid));
if (! $order)
{
    echo $this->DisplayErrorMessage($this->Lang('error_ordernotfound'));
    return;
}
unset($order['cc_number']);
unset($order['cc_verifycode']);
unset($order['cc_expiry']);
$feu = &$this->GetModuleInstance(\MOD_MAMS);
if ($order['feu_user_id'] >= 0)
{
    $order['email'] = $feu->GetEmail($order['feu_user_id']);
}
else
{
    $order['email'] = $order['billing_email'];
}

$smarty->assign('order', $order);

if (isset($params['send']))
{
    // get more input parameters
    if (isset($params['input_subject']))
    {
        $subject = trim($params['input_subject']);
    }
    if (isset($params['input_body']))
    {
        $body = cms_html_entity_decode(trim($params['input_body']));
    }

    // do smarty
    $now = $db->DbtimeStamp(time());
    $body = $this->ProcessTemplateFromData($body);
    $subject = $this->ProcessTemplateFromData($subject);

    // send the message
    $cmsmailer = new \cms_mailer();
    $cmsmailer->AddAddress($order['email']);
    $cmsmailer->SetSubject($subject);
    $cmsmailer->SetBody($body);
    $cmsmailer->IsHtml($ishtml);
    $cmsmailer->Send();

    // save the message
    $userops = $gCms->GetUserOperations();
    $me = $userops->LoadUserByID(get_userid(false));
    $query = 'INSERT INTO ' . cms_db_prefix() . "module_ec_ordermgr_messages
               (order_id, sender_name, subject, is_html, body, sent)
              VALUES (?,?,?,?,?,$now)";
    $db->Execute($query, array(
        $orderid,
        $me->username,
        $subject,
        $ishtml,
        $body
    ));

    // return to the order form.
    $this->Redirect($id, 'admin_manageorder', '', array('orderid' => $orderid));
}

//
// build the form
//
$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_sendmail', '', $params));
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('input_template', $this->CreateInputDropdown($id, 'input_template', $templates, - 1, $curtemplate, 'onChange=\'this.form.submit()\''));
$smarty->assign('input_subject', $this->CreateInputText($id, 'input_subject', $subject, 80, 255));
$smarty->assign('input_body', $this->CreateTextArea($ishtml, $id, $body, 'input_body'));
$smarty->assign('input_send', $this->CreateInputSubmit($id, 'send', $this->Lang('send')));
$smarty->assign('input_cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel')));

echo $this->ProcessTemplate('admin_sendmail.tpl');

// EOF
?>
