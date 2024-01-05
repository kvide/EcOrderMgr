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
$uid = get_userid();

$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_report',
                                $returnid, array('showtemplate' => 'false'), FALSE, 'post', '', '', 'target="blank"'));
$smarty->assign('formend', $this->CreateFormEnd());

$date_opts = array();
$date_opts['exact_dates'] = $this->Lang('dateopt_exactdates');
$date_opts['7days'] = $this->Lang('dateopt_7days');
$date_opts['14days'] = $this->Lang('dateopt_14days');
$date_opts['thismonth'] = $this->Lang('dateopt_thismonth');
$date_opts['30days'] = $this->Lang('dateopt_30days');
$date_opts['thisquarter'] = $this->Lang('dateopt_thisquarter');
$date_opts['3months'] = $this->Lang('dateopt_3months');
$date_opts['6months'] = $this->Lang('dateopt_6months');
$date_opts['thisyear'] = $this->Lang('dateopts_thisyear');
$date_opts['1year'] = $this->Lang('dateopt_1year');
$smarty->assign('date_opts', $date_opts);
$smarty->assign('sel_dateopt', get_preference($uid, $this->GetName() . '_sel_dateopt', '30days'));
$smarty->assign('startdate', get_preference($uid, $this->GetName() . '_startdate', time()));
$smarty->assign('enddate', get_preference($uid, $this->GetName() . '_enddate', strtotime('-1 month')));

// get a list of the reports, later this will be dynamic.
$files = glob(dirname(__FILE__) . '/lib/reports/class.report_*.php');
$report_opts = array();
for ($i = 0; $i < count($files); $i ++)
{
    $tmp = explode('.', basename($files[$i]));
    $classname = '\\EcOrderMgr\\reports\\' . $tmp[1];
    if (is_array($tmp) && count($tmp) == 3)
    {
        $report_opts[$classname] = $this->Lang($tmp[1]);
    }
}
$smarty->assign('report_opts', $report_opts);

$smarty->assign('sel_reportopt', get_preference($uid, $this->GetName() . '_sel_reportopt', 'subscr_picklist'));

echo $this->ProcessTemplate('admin_reports_tab.tpl');

// EOF
?>
