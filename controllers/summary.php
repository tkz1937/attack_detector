<?php

/**
 * Attack detector log controller.
 *
 * @category   apps
 * @package    attack-detector
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Attack detector log controller.
 *
 * @category   apps
 * @package    attack-detector
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/attack_detector/
 */

class Summary extends ClearOS_Controller
{
    /**
     * Attack detector summary view.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('attack_detector/Fail2ban');
        $this->lang->load('attack_detector');

        // Load view data
        //---------------

        try {
            $data['entries'] = $this->fail2ban->get_log();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
 
        // Load views
        //-----------

        $this->page->view_form('attack_detector/summary', $data, lang('base_summary'));
    }
        /**
     * Delete log.
     *
     * @param string $log log
     *
     * @return view
     */
        function delete()
    {
        try {

           $this->load->library('attack_detector/Fail2ban');

           $this->fail2ban->get_filters();

           $this->page->get_log();
            
            redirect('/attack_detector/summary');
            
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
        /**
     * Editlog.
     *
     * @param string $log log
     *
     * @return view
     */
    
    function edit()
    {
        try {
  
           $this->load->library('attack_detector/Fail2ban');

           $this->fail2ban->get_jails();

           $this->page->get_log();

          redirect('/attack_detector/summary');

        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
