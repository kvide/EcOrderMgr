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

final class smarty_plugins
{

    protected function __construct()
    {
        // Static class
    }

    public static function init($smarty)
    {
        $smarty->register_function('EcOrderMgr::country_options', '\\EcOrderMgr\\smarty_plugins::country_options');
        $smarty->register_function('EcOrderMgr::state_options', '\\EcOrderMgr\\smarty_plugins::state_options');
    }

    public static function country_options($params, $tpl)
    {
        $mod = \cms_utils::get_module(\MOD_ECORDERMGR);
        $valid_countries = $mod->get_country_list_options();
        $dflt_country = $mod->GetPreference('dflt_country');
        $selected = \xt_param::get_string($params, 'selected', $dflt_country);

        $options = \CmsFormUtils::create_options($valid_countries, $selected);
        if (($assign = \xt_param::get_string($params, 'assign')))
        {
            $tpl->assign($assign, $options);
        }
        else
        {
            return $options;
        }
    }

    public static function state_options($params, $tpl)
    {
        $mod = \cms_utils::get_module(\MOD_ECORDERMGR);
        $valid = $mod->get_state_list_options();
        $dflt_state = $mod->GetPreference('dflt_state');
        $require_state = $mod->GetPreference('require_state');
        $selected = \xt_param::get_string($params, 'selected', $dflt_state);

        if (! $require_state)
        {
            $valid = array_merge(['' => $mod->Lang('nostate')], $valid);
        }
        $options = \CmsFormUtils::create_options($valid, $selected);

        if (($assign = \xt_param::get_string($params, 'assign')))
        {
            $tpl->assign($assign, $options);
        }
        else
        {
            return $options;
        }
    }

}

?>
