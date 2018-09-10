<?php

/**
 * Fail2ban class.
 *
 * @category   apps
 * @package    attack-detector
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/attack_detector/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\attack_detector;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('attack_detector');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
//use \clearos\apps\attack_detector\Fail2ban\delete as delete;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Fail2ban class.
 *
 * @category   apps
 * @package    attack-detector
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/attack_detector/
 */

class Fail2ban extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_LOG = '/var/log/fail2ban.log';
    const COMMAND_FAIL2BAN_CLIENT = '/bin/fail2ban-client';
    const PATH_FILTERS =  '';
    const PATH_JAILS =  '/etc/fail2ban/jail.d';
    const DEFAULT_BAN_TIME = 600; // TODO: lookup default ban time in /etc/fail2ban/jail.conf

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $filters_loaded = FALSE;
    protected $filters = FALSE;
    protected $jails_loaded = FALSE;
    protected $jails = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Fail2ban constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('fail2ban');
    }

    /**
     * Returns list of available filters.
     *
     * @return array list of available filters
     * @throws Engine_Exception
     */

    public function get_filters()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->filters_loaded)
            $this->_load_filters();

        return $this->filters;
    }

    /**
     * Returns list of configured jails.
     *
     * @return array list of configured jails
     * @throws Engine_Exception
     */

    public function get_jails()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->jails_loaded)
            $this->_load_jails();

        return $this->jails;
    }

    /**
     * Returns log data.
     *
     * @param int $max maximum number of entries to return
     *
     * @return array software updates
     * @throws Engine_Exception
     */

    public function get_log($max = 20)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file = new File(self::FILE_LOG);

        if (!$file->exists())
            return array();

        $lines = $file->get_tail(30000);
        $log = array();

        foreach ($lines as $line) {

            $pieces = preg_split('/\s+/', $line);

            if ($pieces[6] !== 'Ban')
                continue;

            $output['date'] = $pieces[0];
            $output['time'] = preg_replace('/,.*/', '', $pieces[1]);
            $output['rule'] = preg_replace('/[\[\]]/', '', $pieces[5]);
            $output['ip'] = $pieces[7];

            $log[] = $output;
        }

        return $log;
    }

    /**
     * Sets state of rule.
     *
     * @param string $rule rule
     * @param boolean $state state
     *
     * @return void
     * @throws Engine_Exception, Validation_Exception
     */

    public function set_state($rule, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_rule($rule));

        $file = new File(self::PATH_JAILS . '/clearos-' . $rule . '.conf');
        $state_value = ($state) ? 'true' : 'false';

        $file->replace_lines('/^enabled\s*=.*/', "enabled = $state_value\n");

        if ($this->get_running_state()) {
            $shell = new Shell();
            $options['validate_exit_code'] = FALSE;

            $shell->execute('/bin/fail2ban-client','reload ' . $rule, TRUE, $options);
            $shell->execute(self::COMMAND_FAIL2BAN_CLIENT, 'reload ' . $rule, TRUE, $options);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation for rule.
     *
     * @param string $rule rule
     *
     * @return string error message if rule is invalid
     */

    public function validate_rule($rule)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->jails_loaded)
            $this->_load_jails();

        if (!array_key_exists($rule, $this->jails))
            return lang('attack_detector_rule_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads the list of available jails.
     *
     * @access private
     * @return array list of available jails
     * @throws Engine_Exception
     */

    protected function _load_filters()
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder = new Folder(self::PATH_FILTERS);

        if (! $folder->exists())
            return;

        $installed = $folder->get_listing();

        foreach ($installed as $configlet_file) {
            if (! preg_match('/\.php$/', $configlet_file))
                continue;

            $basename = preg_replace('/\.php$/', '', $configlet_file);

            include self::PATH_FILTERS . '/' . $configlet_file;

            $this->filters[$basename] = $configlet;
        }

        $this->filters_loaded = TRUE;
    }

    /**
     * Loads the list of configured jails.
     *
     * @access private
     * @return array list of configured jails
     * @throws Engine_Exception
     */

    protected function _load_jails()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->filters_loaded)
            $this->_load_filters();

        $folder = new Folder(self::PATH_JAILS);

        if (! $folder->exists())
            return;

        $installed = $folder->get_listing();

        foreach ($installed as $configlet_file) {
            if (! preg_match('/^clearos-.*.conf$/', $configlet_file))
                continue;

            $file = new File(self::PATH_JAILS . '/' . $configlet_file);

            try {
                $enabled_value = $file->lookup_value('/^enabled\s*=\s*/');
                $enabled = (preg_match('/true/i', $enabled_value)) ? TRUE : FALSE;
            } catch (File_No_Match_Exception $e) {
                $enabled = FALSE;
            }

            try {
                $ban_time = $file->lookup_value('/^bantime\s*=\s*/');
            } catch (File_No_Match_Exception $e) {
                $ban_time = self::DEFAULT_BAN_TIME;
            }

            $basename = preg_replace('/\.conf$/', '', $configlet_file);
            $basename = preg_replace('/^clearos-/', '', $basename);

            $this->jails[$basename] = array(
                'enabled' => $enabled,
                'ban_time' => $ban_time,
            );

            if ($this->filters[$basename])
                $this->jails[$basename]['description'] = $this->filters[$basename]['description'];
        }

        $this->jails_loaded = TRUE;
    }
}
