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
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_MANAGEORDERS))
{
    exit();
}
$this->SetCurrentTab('orders');

if (! isset($params['query']))
{
    $this->SetError($this->Lang('error_insufficientparams'));
    $this->RedirectToTab($id);
}

$query = base64_decode($params['query']);
$tmp = $db->GetArray($query);
if (! is_array($tmp))
{
    $this->SetError($this->Lang('error_nomatches'));
    $this->RedirectToTab($id);
}

$order_ids = \xt_array::extract_field($tmp, 'id');
if (! is_array($order_ids))
{
    $this->SetError($this->Lang('error_nomatches'));
    $this->RedirectToTab($id);
}

$smarty->assign('currency_symbol', ecomm::get_currency_symbol());
$smarty->assign('weight_units', ecomm::get_weight_units());
$text = '';
set_time_limit(9999);
$fn = tempnam(TMP_CACHE_LOCATION, 'oe') . '.csv';
$fh = fopen($fn, "w");
foreach ($order_ids as $one_id)
{
    $tmp = orders_ops::load_by_id($one_id);
    $smarty->assign('order', $tmp);
    $text = $this->ProcessTemplate('admin_export_format.tpl');
    if (! endswith($text, "\n"))
    {
        $text .= "\n";
    }
    fwrite($fh, $text);
}
fclose($fh);

\xt_utils::send_file_and_exit($fn);
// \xt_utils::send_data_and_exit($text,'text/csv','orders_export.csv');

@unlink($fn);
#
# EOF
#
?>
