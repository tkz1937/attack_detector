<?php

/**
 * Attack detector summary view.
 *
 * @category   apps
 * @package    attack-detector
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/attack_detector/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('attack_detector');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('attack_detector_rule_name'),
    lang('base_description')
);

$anchors = array();

///////////////////////////////////////////////////////////////////////////////
// Ports
///////////////////////////////////////////////////////////////////////////////

foreach ($rules as $basename => $rule) {
    $state = ($rule['enabled']) ? 'disable' : 'enable';
    $add = ($rule['add']) ? 'delete' : 'add';
    $state_anchor = 'anchor_' . $state;
    $add_anchor='anchor_'.$add;

    $item['current_state'] = (bool)$rule['enabled'];
    $item['action'] = '/app/attack_detector/settings/edit/' . $basename;
    $item['anchors'] = button_set(
        array(
            $state_anchor('/app/attack_detector/settings/' . $state . '/' . $basename, 'high', $options),
            $add_anchor('/app/attack_detector/settings/' . $add . '/' . $basename, 'high', $options),
        )

    );
    $item['details'] = array(
        $basename,
        $rule['description']
    );
    $items[] = $item;
    

}

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array (
    'default_rows' => 25,
    'sort-default-col' => 1,
    'row-enable-disable' => TRUE

);



echo summary_table(
    lang('attack_detector_rules'),
    $anchors,
    $headers,
    $items,
    $options
);
