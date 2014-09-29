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
 * Define the helpdesk block's class
 *
 * @package    	block_helpdesk
 * @author 		Ivana Skelic, Hrvoje Golcic
 * @copyright	2014 IPA "Let's Study Together!"" project
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/phpmailer/class.phpmailer.php'); //required

/**
 * helpdesk block class
 */
class block_helpdesk extends block_base {
	function init() {
		$this->title = get_string('pluginname', 'block_helpdesk');
	}

    function has_config() {
        return false;
    }

    /**
     * Disable multiple instances of this block
     *
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
	 * Set where the block should be allowed to be added
	 *
	 * @return array
	 */
	public function applicable_formats() {
		return array('all' => true);
	}

	/**
	 * Set the content of the block
	 *
	 * @return string
	 */
	function get_content(){
		global $COURSE, $PAGE, $USER, $OUTPUT;

		if ($this->content !== NULL) {
			return $this->content;
		}

		if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real users
        }

        $this->page->requires->js('/blocks/helpdesk/sendemail.js');

		$this->content = new stdClass;
		$this->content->text = '';
		$this->content->footer = '';
		
		if (empty($this->instance)) {
			return $this->content;
		}

		$context = context_module::instance($COURSE->id);
		
		require_capability('block/helpdesk:cansend', $context);

		//can not send moodle_url object as required param, send path instead
		$pageurl = '/mod/book/view.php';

		$divattrs = array('id' => 'helpdesk', 'class' => 'content1');

		$this->content->text .= html_writer::start_tag('div', $divattrs);

		$this->content->text .= html_writer::start_tag('div', array('id' => 'helpdesk_txt', 'class' => ''));
		$this->content->text .= get_string('badstructure', 'block_helpdesk');
		$this->content->text .= html_writer::end_tag('div');

		if (has_capability('block/helpdesk:cansend', $context) && (strpos($pageurl, 'book'))) {

			$params = array('sesskey'=>sesskey(), 'context' => (int)$PAGE->context->id, 
				'courseid' => (int)$COURSE->id, 'page' => $PAGE->url);

			$link_url = new moodle_url('/blocks/helpdesk/sendmail.php', $params);

			$divattr = array('id' => 'helpdesk_link');
			$this->content->text .= html_writer::start_tag('div', $divattr);

			$paramsjs = array('sesskey'=>sesskey(), 'context' => (int)$PAGE->context->id, 
				'courseid' => (int)$COURSE->id);
			
			$this->content->text .= $OUTPUT->action_link($link_url, get_string('composenew', 'block_helpdesk'), 
				new component_action('click', 'block_helpdesk_sendemail', $paramsjs));

			$this->content->text .= html_writer::end_tag('div');

		} else {

			$divattr = array('id' => 'helpdesk_text');
			$this->content->text .= html_writer::start_tag('div', $divattr);
			$this->content->text .= get_string('link', 'block_helpdesk');
			$this->content->text .= html_writer::end_tag('div');
		}

		$this->content->text .= html_writer::end_tag('div');
		
		$this->content->text .= html_writer::start_tag('div', array('class' => 'content2'));

		//success scenario
		$this->content->text .= html_writer::start_tag('div', array('id' => 'helpdesk_success', 
			'style' => 'display: none'));
		$this->content->text .= get_string('success', 'block_helpdesk');
		$this->content->text .= html_writer::end_tag('div');

		//failure scenario
		$this->content->text .= html_writer::start_tag('div', array('id' => 'helpdesk_failure', 
			'style' => 'display: none'));
		$this->content->text .= get_string('failure', 'block_helpdesk');
		$this->content->text .= html_writer::end_tag('div');

		$this->content->text .= html_writer::end_tag('div');

		return $this->content;
	}

}