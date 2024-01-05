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
if (! $this->CheckPermission(\EcOrderMgr\ORDERS_PERM_VIEWORDERS))
{
    exit();
}
if (! isset($params['submit']))
{
    return;
}
$uid = get_userid();

// save the settings as user preferences.
set_preference($uid, $this->GetName() . '_sel_dateopt', $params['sel_dateopt']);
set_preference($uid, $this->GetName() . '_sel_reportopt', $params['sel_reportopt']);

$startdate = mktime(0, 0, 0, $params['startdate_Month'], $params['startdate_Day'], $params['startdate_Year']);
set_preference($uid, $this->GetName() . '_startdate', $startdate);

$enddate = mktime(0, 0, 0, $params['enddate_Month'], $params['enddate_Day'], $params['enddate_Year']);
set_preference($uid, $this->GetName() . '_enddate', $enddate);

// now we can generate the report.
$dateopt = $params['sel_dateopt'];
$classname = $params['sel_reportopt'];

// need a template to popup a new window.
if (! class_exists($classname))
{
    die('class ' . $classname . ' could not be found');
}

// handle the dates
switch ($dateopt)
{
    case 'exact_dates':
        // already have the dates.
        break;

    case '7days':
        $enddate = time();
        $startdate = strtotime('-7 days', $enddate);
        break;

    case '14days':
        $enddate = time();
        $startdate = strtotime('-14 days', $enddate);
        break;

    case 'thismonth':
        $enddate = mktime(23, 59, 0, date('m'), date('t'), date('Y'));
        $startdate = mktime(0, 0, 0, date('m'), 1, date('Y'));
        break;

    case '30days':
        $enddate = time();
        $startdate = strtotime('-30 days', $enddate);
        break;

    case 'thisquarter':
        // quarters are from jan thru mar, apr thru jun
        // july thru sep, oct thru dec.
        // first find out which quarter we're in
        $m = date('m');
        $q = 1;
        if ($m <= 3)
        {
            $q = 1;
        }
        else if ($m <= 6)
        {
            $q = 2;
        }
        else if ($m <= 9)
        {
            $q = 3;
        }
        else
        {
            $q = 4;
        }

        // now determine start and end month.
        $sm = 1;
        $em = 3;
        switch ($q)
        {
            case 1:
                $sm = 1;
                $em = 3;
                break;
            case 2:
                $sm = 4;
                $em = 6;
                break;
            case 3:
                $sm = 7;
                $em = 9;
                break;
            case 4:
                $sm = 10;
                $em = 12;
                break;
        }

        // now calculate start and end time
        // use midnight on the first day of the end month (+1)
        // instead of figuring out the number of days in the month
        $enddate = mktime(0, 0, 0, $em + 1, 1, date('Y'));
        $startdate = mktime(0, 0, 0, $sm, 1, date('Y'));
        break;

    case '3months':
        $enddate = time();
        $startdate = strtotime('-3 months', $enddate);
        break;

    case '6months':
        $enddate = time();
        $startdate = strtotime('-6 months', $enddate);
        break;

    case 'thisyear':
        $enddate = mktime(23, 59, 0, 12, 31, date('Y'));
        $startdate = mktime(0, 0, 0, 1, 1, date('Y'));
        break;

    case '1year':
        $enddate = time();
        $startdate = strtotime('-1 year', $enddate);
        break;
}

$report = new $classname($db);
$report->set_startdate($startdate);
$report->set_enddate($enddate);
$res = $report->generate();
echo $res;

// EOF
?>
