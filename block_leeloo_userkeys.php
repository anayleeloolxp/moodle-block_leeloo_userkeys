<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Keys block
 *
 * @package   block_leeloo_userkeys
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Keys block
 *
 * @package   block_leeloo_userkeys
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_leeloo_userkeys extends block_base {
    /**
     * Block initialization.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_leeloo_userkeys');
    }

    /**
     * Allow instace configration.
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Dont allow multiple blocks
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Return contents of leeloo_userkeys block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        global $USER, $CFG;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $leeloolxplicense = get_config('block_leeloo_userkeys')->license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = [
            'license_key' => $leeloolxplicense,
        ];

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_userkeys');
            return $this->content;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $infoleeloolxp->data->install_url;
        } else {
            $this->content->text = get_string('nolicense', 'block_leeloo_userkeys');
            return $this->content;
        }

        $leelooapibaseurl = 'https://leeloolxp.com/api/moodle_sell_course_plugin/';

        $vendorkey = get_config('block_leeloo_userkeys', 'vendorkey');

        $siteprefix = str_ireplace('https://', '', $CFG->wwwroot);
        $siteprefix = str_ireplace('http://', '', $siteprefix);
        $siteprefix = str_ireplace('www.', '', $siteprefix);
        $siteprefix = str_ireplace('.', '_', $siteprefix);
        $siteprefix = str_ireplace('/', '_', $siteprefix);
        $siteprefix = $siteprefix . '_pre_';
        $siteprefix = '';

        $username = $USER->username;
        $leeloousername = $siteprefix . $username;

        $post = [
            'license_key' => $vendorkey,
            'username' => base64_encode($leeloousername),
        ];

        $url = $leelooapibaseurl . 'getuserkeys.php';
        $postdata = [
            'license_key' => $vendorkey,
            'username' => base64_encode($leeloousername),
        ];
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_leeloo_userkeys');
            return $this->content;
        }

        $keysdata = json_decode($output);

        $keyshtml = '';
        foreach ($keysdata->data->keys as $keydata) {
            $keyshtml .= $keydata->name . ' = ' . $keydata->count . '</br>';
        }

        if ($keyshtml == '') {
            $keyshtml = get_string('nokeys', 'block_leeloo_userkeys');
        }

        $this->content->text = $keyshtml;

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}
