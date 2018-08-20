<?php

/**
 * Attack detector log.
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

$this->lang->load('base');
$this->lang->load('network');
$this->lang->load('attack_detector');

///////////////////////////////////////////////////////////////////////////////
// Anchor
///////////////////////////////////////////////////////////////////////////////

$buttons = array();

$headers = array(
    lang('network_ip'),
    lang('attack_detector_rule'),
    lang('base_date') . '/' . lang('base_time')
    //lang('attack_detector_options');
);

$rows = array();

foreach ($entries as $entry) {
    $row = array();
    $row['details'] = array(
        $entry['ip'],
        $entry['rule'],
        $entry['date'] . ' - ' . $entry['time'],
        $entry['options']

    );
    $rows[] = $row;
}

///////////////////////////////////////////////////////////////////////////////
// Table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'default_rows' => 15,
    'no_action' => FALSE,
    'sort-default-col' => 3,
);

echo summary_table(
     lang('attack_detector_log'),
     $buttons,
     $headers,
     $rows,
     $options
);
