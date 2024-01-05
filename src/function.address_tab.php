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

$feu_group = $this->GetPreference('require_membership', '-1');
if (! $this->GetPreference('allow_anon_checkout') && $feu_group == - 1)
{
    echo $this->ShowErrors($this->Lang('error_norequiredgroup'));
}

$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_save_address'));
$smarty->assign('formend', $this->CreateFormEnd());

$address_options = array();
$address_options[EcOrderMgr\billing_address_retriever::ADDR_POLICY_NONE] = $this->Lang('none');
$address_options[EcOrderMgr\billing_address_retriever::ADDR_POLICY_COOKIE] = $this->Lang('cookie');
$address_options[EcOrderMgr\billing_address_retriever::ADDR_POLICY_LAST] = $this->Lang('remember_last_address_used');
$address_options[EcOrderMgr\billing_address_retriever::ADDR_POLICY_FEU] = $this->Lang('get_address_from_feu');
$smarty->assign('address_options', $address_options);
$smarty->assign('address_retrieval',
                    $this->GetPreference('address_retrieval', EcOrderMgr\billing_address_retriever::ADDR_POLICY_NONE));
$smarty->assign('require_state', $this->GetPreference('require_state', 1));
$smarty->assign('require_postalcode', $this->GetPreference('require_postalcode', 1));
$smarty->assign('dflt_state', $this->GetPreference('dflt_state'));
$smarty->assign('dflt_country', $this->GetPreference('dflt_country'));
$smarty->assign('input_dflt_state',
                    $this->CreateInputText($id, 'dflt_state', $this->GetPreference('dflt_state', ''), 2, 2));
$smarty->assign('input_dflt_country',
                    $this->CreateInputText($id, 'dflt_country', $this->GetPreference('dflt_country', ''), 2, 2));

$serialized = $this->GetPreference('address_map', '');
$address_map = new EcOrderMgr\Address();
$tmp = $address_map->to_array();
foreach ($tmp as $key => $value)
{
    $tmp[$key] = - 1;
}
$address_map->from_array($tmp, '');
if ($serialized)
{
    $tmp = unserialize($serialized);
    $address_map->from_array($tmp, '');
}
$smarty->assign('map', $address_map);
$smarty->assign('valid_countries', $this->GetPreference('valid_countries', ''));
$smarty->assign('valid_states', $this->GetPreference('valid_states', ''));

if ($feu_group != '-1')
{
    // get the list of properties for this group
    $feu = $this->GetModuleInstance(\MOD_MAMS);
    if ($feu)
    {
        // get all the property definitions
        $defns = $feu->GetPropertyDefns();

        // get all the properties for this group.
        $relns = $feu->GetGroupPropertyRelations($feu_group);

        // build an array of all of the property values
        $props = array();
        $props[- 1] = $this->Lang('none');
        $props['__USERNAME__'] = $this->Lang('prompt_username');
        $props['__EMAIL__'] = $this->Lang('prompt_email');
        for ($i = 0; $i < count($relns); $i ++)
        {
            $name = $relns[$i]['name'];
            $props[$name] = $defns[$name]['prompt'] . " ($name)";
        }

        $smarty->assign('properties', $props);
    }
}

echo $this->ProcessTemplate('address_tab.tpl');
// EOF
?>
