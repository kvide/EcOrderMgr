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
if (! $this->CheckPermission('Modify Templates'))
{
    echo $this->ShowErrors($this->Lang('error_permissiondenied'));
    return;
}
$this->SetCurrentTab('adminemail');

if (isset($params['email_subject']))
{
    $this->SetPreference('adminemail_subject', trim($params['email_subject']));
}

$this->RedirectToTab($id, '', '', 'admin_templates');

// EOF
?>
