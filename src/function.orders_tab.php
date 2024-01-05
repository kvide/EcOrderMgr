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

if (! isset($gCms))
{
    exit();
}
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS)
    && ! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    exit();
}

//
// Initialization
//
$config = $gCms->GetConfig();
$status_filter = get_preference(get_userid(), 'order_status_filter', '');
$datelimit_type = get_preference(get_userid(), 'datelimit_type', 'any');
$datelimit_interval = get_preference(get_userid(), 'datelimit_interval', 'any');
$order_number_filter = get_preference(get_userid(), 'order_number_filter', '');
$pagelimit = min(get_preference(get_userid(), 'orders_pagelimit', 500), 500);
$item_string = get_preference(get_userid(), 'orders_item_string', '');
$pagenum = \xt_param::get_int($params, 'pagenum', 1);

//
// Handle param data
//

//
// Handle form submit
//
if (\xt_param::exists($params, 'order_status_submit'))
{
    if (isset($params['order_status']))
    {
        $status_filter = $params['order_status'];
        set_preference(get_userid(), 'order_status_filter', $status_filter);
    }

    if (isset($params['datelimit_type']))
    {
        $datelimit_type = $params['datelimit_type'];
        set_preference(get_userid(), 'datelimit_type', $datelimit_type);
    }

    if (isset($params['datelimit_interval']))
    {
        $datelimit_interval = $params['datelimit_interval'];
        set_preference(get_userid(), 'datelimit_interval', $datelimit_interval);
    }

    if (isset($params['order_number_filter']))
    {
        $order_number_filter = $params['order_number_filter'];
        set_preference(get_userid(), 'order_number_filter', $order_number_filter);
    }

    if (isset($params['item_string']))
    {
        $item_string = $params['item_string'];
        set_preference(get_userid(), 'orders_item_string', $item_string);
    }

    if (isset($params['pagelimit']))
    {
        $pagelimit = (int) $params['pagelimit'];
        set_preference(get_userid(), 'orders_pagelimit', $pagelimit);
    }

    $pagenum = 1;
}

//
// Build the query
//
$where = array();
$qparms = array();
$cquery = 'SELECT count(a.id) AS count FROM ' . cms_db_prefix() . 'module_ec_ordermgr a';

$query = 'SELECT a.id,a.feu_user_id, a.invoice,
                 a.billing_first_name, a.billing_last_name,
                 a.status,
                 a.create_date, a.modified_date,
                 c.total AS total,
                 d.items AS items,
                 p.amt_paid AS amt_paid
          FROM ' . cms_db_prefix() . 'module_ec_ordermgr a ';
$tmp = ' LEFT JOIN
              (SELECT order_id, SUM(GREATEST(COALESCE(master_price,0),quantity*COALESCE(unit_price,0)))+SUM(quantity*COALESCE(discount,0)) AS total
                 FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
                GROUP BY order_id) AS c
          ON a.id = c.order_id
          LEFT JOIN
              (SELECT order_id, SUM(quantity) AS items FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
                WHERE item_type = \'' . LineItem::ITEMTYPE_PRODUCT . '\' OR item_type = \'' . LineItem::ITEMTYPE_SERVICE . '\'
                GROUP BY order_id) AS d
          ON a.id = d.order_id
          LEFT JOIN
              (SELECT order_id, SUM(quantity) AS items FROM ' . cms_db_prefix() . 'module_ec_ordermgr_items
                WHERE item_type = \'' . LineItem::ITEMTYPE_PRODUCT . '\' OR item_type = \'' . LineItem::ITEMTYPE_SERVICE . '\'
                GROUP BY order_id) AS e
          ON a.id = e.order_id
          LEFT JOIN
              (SELECT order_id, SUM(amount) AS amt_paid
                 FROM ' . cms_db_prefix() . 'module_ec_ordermgr_payments
                WHERE status = \'payment_approved\'
                GROUP BY order_id) AS p
          ON a.id = p.order_id';
$query .= $tmp;
// $cquery .= $tmp;

if ($order_number_filter != '')
{
    $where[] = 'a.id = ?';
    $qparms[] = intval($order_number_filter);
}
if ($item_string != '')
{
    $str = ' LEFT JOIN ' . cms_db_prefix() . 'module_ec_ordermgr_items f ON a.id = f.order_id';
    $query .= $str;
    $cquery .= $str;
    $where[] = 'f.product_name LIKE ?';
    $qparms[] = '%' . $item_string . '%';
}
if ($status_filter != '' && $status_filter != 'any')
{
    switch ($status_filter)
    {
        case 'notcancelledshipped':
            $where[] = 'a.status != ?';
            $where[] = 'a.status != ?';
            $qparms[] = 'cancelled';
            $qparms[] = 'shipped';
            break;
        default:
            $where[] = 'a.status = ?';
            $qparms[] = $status_filter;
            break;
    }
}
if ($datelimit_type != 'any' && $datelimit_interval != 'any')
{
    $start = '';
    switch ($datelimit_interval)
    {
        case 'oneday':
            $start = strtotime('-24 hours');
            break;
        case 'oneweek':
            $start = strtotime('-1 week');
            break;
        case 'twoweeks':
            $start = strtotime('-2 weeks');
            break;
        case 'onemonth':
            $start = strtotime('-1 month');
            break;
        case 'onequarter':
            $start = strtotime('-3 months');
            break;
        case 'oneyear':
            $start = strtotime('-1 year');
            break;
    }

    $start = $db->DbTimeStamp($start);
    $str = '';
    switch ($datelimit_type)
    {
        case 'created':
            $str = "a.create_date > $start";
            break;
        case 'modified':
            $str = "a.modified_date > $start";
            break;
    }
    $where[] = $str;
}

// Build the query
if (count($where))
{
    $query .= ' WHERE ' . implode(' AND ', $where);
    $cquery .= ' WHERE ' . implode(' AND ', $where);
}
$query .= ' GROUP by a.id';
$query .= ' ORDER BY a.modified_date DESC';

// Get the count
$recordcount = $db->GetOne($cquery, $qparms);

// build pagination data
$numpages = (int) ceil($recordcount / $pagelimit);
$startelement = ($pagenum - 1) * $pagelimit;

// Get the data
$dbr = $db->SelectLimit($query, $pagelimit, $startelement, $qparms);

$orders = array();
while ($dbr && ($order = $dbr->FetchRow()))
{
    $ordernum = $order['invoice'];
    $order['ordernum'] = $ordernum;
    $order['ordernum_link'] = $this->CreateLink($id, 'vieworder', $returnid, $ordernum, $params = array(
        'order_id' => $order['id'],
        'order_status_filter' => $status_filter
    ));
    if ($this->CheckPermission(\EcOrderMgr\ORDERS_PERM_DELETE_ORDERS))
    {
        $order['delete_link'] = $this->CreateImageLink($id, 'deleteorder', $returnid, $this->Lang('delete'), 'icons/system/delete.gif', array(
            'order_id' => $order['id'],
            'order_status_filter' => $status_filter
        ), 'systemicon', $this->Lang('ask_delete_order'), true);
    }
    $order['amt_due'] = $order['total'] - $order['amt_paid'];
    $orders[] = $order;
}

if ($this->CheckPermission(\EcOrderMgr\ORDERS_PERM_DELETE_ORDERS))
{
    $smarty->assign('allowdelete', 1);
}
$smarty->assign('currencysymbol', ecomm::get_currency_symbol());
$smarty->assign('weightunits', ecomm::get_weight_units());
$smarty->assign('formstart', $this->CreateFormStart($id, 'defaultadmin', $returnid));
$smarty->assign('order_number', $this->CreateInputText($id, 'order_number_filter', $order_number_filter, 20, 20));
$smarty->assign('order_status_dropdown', $this->CreateInputDropdown($id, 'order_status', array(
    $this->Lang('any') => '',
    $this->Lang('proposed') => 'proposed',
    $this->Lang('submitted') => 'submitted',
    $this->Lang('invoiced') => 'invoiced',
    $this->Lang('paid') => 'paid',
    $this->Lang('shipped') => 'shipped',
    $this->Lang('cancelled') => 'cancelled',
    $this->Lang('notcancelledshipped') => 'notcancelledshipped'
), - 1, $status_filter));
$smarty->assign('submit', $this->CreateInputSubmit($id, 'order_status_submit', lang('submit')));
$smarty->assign('formend', $this->CreateFormEnd());
$datelimits1 = array();
$datelimits1[$this->Lang('any')] = 'any';
$datelimits1[$this->Lang('created')] = 'created';
$datelimits1[$this->Lang('modified')] = 'modified';
$smarty->assign('datelimit_type_dropdown',
                    $this->CreateInputDropdown($id, 'datelimit_type', $datelimits1, - 1, $datelimit_type));
$datelimits1 = array();
$datelimits1[$this->Lang('any')] = 'any';
$datelimits1[$this->Lang('interval_oneday')] = 'oneday';
$datelimits1[$this->Lang('interval_oneweek')] = 'oneweek';
$datelimits1[$this->Lang('interval_twoweeks')] = 'twoweeks';
$datelimits1[$this->Lang('interval_onemonth')] = 'onemonth';
$datelimits1[$this->Lang('interval_onequarter')] = 'onequarter';
$datelimits1[$this->Lang('interval_oneyear')] = 'oneyear';
$smarty->assign('datelimit_interval_dropdown',
                    $this->CreateInputDropdown($id, 'datelimit_interval', $datelimits1, - 1, $datelimit_interval));

$pagelimits = array();
$pagelimits[10] = 10;
$pagelimits[25] = 25;
$pagelimits[50] = 50;
$pagelimits[100] = 100;
$pagelimits[250] = 250;
$pagelimits[500] = 500;
$smarty->assign('pagelimits', $pagelimits);
$smarty->assign('val_pagelimit', $pagelimit);

if (count($orders) && $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    $parms = array('query' => base64_encode($query));
    $export_link = $this->CreateImageLink($id, 'admin_export_orders',
        $returnid, $this->Lang('export_orders'), 'icons/system/export.gif', $parms, 'systemicon', '', false);
    $smarty->assign('export_link', $export_link);
}

$smarty->assign('orders', $orders);
$smarty->assign('mod', $this);

$smarty->assign('item_string', $item_string);
$smarty->assign('pagenum', $pagenum);
$smarty->assign('pagecount', $numpages);
$smarty->assign('recordcount', $recordcount);
if ($pagenum > 1)
{
    $parms = array();
    $parms['pagenum'] = 1;
    $smarty->assign('firstpage_url', $this->create_url($id, 'defaultadmin', $returnid, $parms));
    $parms['pagenum'] = $pagenum - 1;
    $smarty->assign('prevpage_url', $this->create_url($id, 'defaultadmin', $returnid, $parms));
}
if ($pagenum < $numpages)
{
    $parms = array();
    $parms['pagenum'] = $pagenum + 1;
    $smarty->assign('nextpage_url', $this->create_url($id, 'defaultadmin', $returnid, $parms));
    $parms['pagenum'] = $numpages;
    $smarty->assign('lastpage_url', $this->create_url($id, 'defaultadmin', $returnid, $parms));
}
echo $this->ProcessTemplate('orders_tab.tpl');

// EOF
?>

