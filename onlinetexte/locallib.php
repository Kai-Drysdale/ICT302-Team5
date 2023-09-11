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
 * This file contains the definition for the library class for onlinetexte submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_onlinetexte
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// File area for online text submission assignment.
define('ASSIGNSUBMISSION_ONLINETEXT_FILEAREA', 'submissions_onlinetexte');

/**
 * library class for onlinetexte submission plugin extending submission plugin base class
 *
 * @package assignsubmission_onlinetexte
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_onlinetexte extends assign_submission_plugin {

    /**
     * Get the name of the online text submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('onlinetexte', 'assignsubmission_onlinetexte');
    }


    /**
     * Get onlinetexte submission information from the database
     *
     * @param  int $submissionid
     * @return mixed
     */
    private function get_onlinetexte_submission($submissionid) {
        global $DB;

        return $DB->get_record('assignsubmission_onlinetexte', array('submission'=>$submissionid));
    }

    /**
     * Remove a submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;
        if ($submissionid) {
            $DB->delete_records('assignsubmission_onlinetexte', array('submission' => $submissionid));
        }
        return true;
    }

    /**
     * Get the settings for onlinetexte submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultwordlimit = $this->get_config('wordlimit') == 0 ? '' : $this->get_config('wordlimit');
        $defaultwordlimitenabled = $this->get_config('wordlimitenabled');

        $options = array('size' => '6', 'maxlength' => '6');
        $name = get_string('wordlimit', 'assignsubmission_onlinetexte');

        // Create a text box that can be enabled/disabled for onlinetexte word limit.
        $wordlimitgrp = array();
        $wordlimitgrp[] = $mform->createElement('text', 'assignsubmission_onlinetexte_wordlimit', '', $options);
        $wordlimitgrp[] = $mform->createElement('checkbox', 'assignsubmission_onlinetexte_wordlimit_enabled',
                '', get_string('enable'));
        $mform->addGroup($wordlimitgrp, 'assignsubmission_onlinetexte_wordlimit_group', $name, ' ', false);
        $mform->addHelpButton('assignsubmission_onlinetexte_wordlimit_group',
                              'wordlimit',
                              'assignsubmission_onlinetexte');
        $mform->disabledIf('assignsubmission_onlinetexte_wordlimit',
                           'assignsubmission_onlinetexte_wordlimit_enabled',
                           'notchecked');
        $mform->hideIf('assignsubmission_onlinetexte_wordlimit',
                       'assignsubmission_onlinetexte_enabled',
                       'notchecked');

        // Add numeric rule to text field.
        $wordlimitgrprules = array();
        $wordlimitgrprules['assignsubmission_onlinetexte_wordlimit'][] = array(null, 'numeric', null, 'client');
        $mform->addGroupRule('assignsubmission_onlinetexte_wordlimit_group', $wordlimitgrprules);

        // Rest of group setup.
        $mform->setDefault('assignsubmission_onlinetexte_wordlimit', $defaultwordlimit);
        $mform->setDefault('assignsubmission_onlinetexte_wordlimit_enabled', $defaultwordlimitenabled);
        $mform->setType('assignsubmission_onlinetexte_wordlimit', PARAM_INT);
        $mform->hideIf('assignsubmission_onlinetexte_wordlimit_group',
                       'assignsubmission_onlinetexte_enabled',
                       'notchecked');
    }

    /**
     * Save the settings for onlinetexte submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        if (empty($data->assignsubmission_onlinetexte_wordlimit) || empty($data->assignsubmission_onlinetexte_wordlimit_enabled)) {
            $wordlimit = 0;
            $wordlimitenabled = 0;
        } else {
            $wordlimit = $data->assignsubmission_onlinetexte_wordlimit;
            $wordlimitenabled = 1;
        }

        $this->set_config('wordlimit', $wordlimit);
        $this->set_config('wordlimitenabled', $wordlimitenabled);

        return true;
    }

    /**
     * Add form elements for settings
     *
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */

    

    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
    $elements = array();

    $customText = "This is some custom text that will appear above the submission form.";

    $editoroptions = $this->get_edit_options();
    $submissionid = $submission ? $submission->id : 0;

    if (!isset($data->onlinetexte)) {
        $data->onlinetexte = '';
    }
    if (!isset($data->onlinetexteformat)) {
        $data->onlinetexteformat = editors_get_preferred_format();
    }

    if ($submission) {
        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);
        if ($onlinetextesubmission) {
            $data->onlinetexte = $onlinetextesubmission->onlinetexte;
            $data->onlinetexteformat = $onlinetextesubmission->onlineformat;
        }
    }

    $data = file_prepare_standard_editor($data,
        'onlinetexte',
        $editoroptions,
        $this->assignment->get_context(),
        'assignsubmission_onlinetexte',
        ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
        $submissionid);

    $mform->addElement('editor', 'onlinetexte_editor', $this->get_name(), null, $editoroptions);

    $mform->addElement('html', '<div id="word_count_display">Word Count: 0</div>');

    $mform->addElement('html', '<div id="timer_display">Timer: </div>');

    $duedate = $this->assignment->get_instance()->duedate;

    $this->add_real_time_word_count_script($mform, $duedate);

    return true;
}


function add_real_time_word_count_script($mform, $duedate)
{
    // Enqueue the JavaScript file with a relative path
    $js = <<<EOD
    <script type="text/javascript">
        var duedate = new Date($duedate * 1000); // Convert Unix timestamp to milliseconds
    </script>
    <script type="text/javascript" src="submission/onlinetexte/js/real_time.js"></script>
    EOD;

    $mform->addElement('html', $js);
}



    /**
     * Editor format options
     *
     * @return array
     */
    private function get_edit_options() {
        $editoroptions = array(
            'noclean' => false,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $this->assignment->get_course()->maxbytes,
            'context' => $this->assignment->get_context(),
            'return_types' => (FILE_INTERNAL | FILE_EXTERNAL | FILE_CONTROLLED_LINK),
            'removeorphaneddrafts' => true // Whether or not to remove any draft files which aren't referenced in the text.
        );
        return $editoroptions;
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $editoroptions = $this->get_edit_options();

        $data = file_postupdate_standard_editor($data,
                                                'onlinetexte',
                                                $editoroptions,
                                                $this->assignment->get_context(),
                                                'assignsubmission_onlinetexte',
                                                ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                $submission->id);

        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);

        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_onlinetexte',
                                     ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                     $submission->id,
                                     'id',
                                     false);

        // Check word count before submitting anything.
        $exceeded = $this->check_word_count(trim($data->onlinetexte));
        if ($exceeded) {
            $this->set_error($exceeded);
            return false;
        }

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array_keys($files),
                'content' => trim($data->onlinetexte),
                'format' => $data->onlinetexte_editor['format']
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        if ($this->assignment->is_blind_marking()) {
            $params['anonymous'] = 1;
        }
        $event = \assignsubmission_onlinetexte\event\assessable_uploaded::create($params);
        $event->trigger();

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        $count = count_words($data->onlinetexte);

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'onlinetextewordcount' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($onlinetextesubmission) {

            $onlinetextesubmission->onlinetexte = $data->onlinetexte;
            $onlinetextesubmission->onlineformat = $data->onlinetexte_editor['format'];
            $params['objectid'] = $onlinetextesubmission->id;
            $updatestatus = $DB->update_record('assignsubmission_onlinetexte', $onlinetextesubmission);
            $event = \assignsubmission_onlinetexte\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {

            $onlinetextesubmission = new stdClass();
            $onlinetextesubmission->onlinetexte = $data->onlinetexte;
            $onlinetextesubmission->onlineformat = $data->onlinetexte_editor['format'];

            $onlinetextesubmission->submission = $submission->id;
            $onlinetextesubmission->assignment = $this->assignment->get_instance()->id;
            $onlinetextesubmission->id = $DB->insert_record('assignsubmission_onlinetexte', $onlinetextesubmission);
            $params['objectid'] = $onlinetextesubmission->id;
            $event = \assignsubmission_onlinetexte\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $onlinetextesubmission->id > 0;
        }
    }

    /**
     * Return a list of the text fields that can be imported/exported by this plugin
     *
     * @return array An array of field names and descriptions. (name=>description, ...)
     */
    public function get_editor_fields() {
        return array('onlinetexte' => get_string('pluginname', 'assignsubmission_onlinetexte'));
    }

    /**
     * Get the saved text content from the editor
     *
     * @param string $name
     * @param int $submissionid
     * @return string
     */
    public function get_editor_text($name, $submissionid) {
        if ($name == 'onlinetexte') {
            $onlinetextesubmission = $this->get_onlinetexte_submission($submissionid);
            if ($onlinetextesubmission) {
                return $onlinetextesubmission->onlinetexte;
            }
        }

        return '';
    }

    /**
     * Get the content format for the editor
     *
     * @param string $name
     * @param int $submissionid
     * @return int
     */
    public function get_editor_format($name, $submissionid) {
        if ($name == 'onlinetexte') {
            $onlinetextesubmission = $this->get_onlinetexte_submission($submissionid);
            if ($onlinetextesubmission) {
                return $onlinetextesubmission->onlineformat;
            }
        }

        return 0;
    }


     /**
      * Display onlinetexte word count in the submission status table
      *
      * @param stdClass $submission
      * @param bool $showviewlink - If the summary has been truncated set this to true
      * @return string
      */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $CFG;

        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);
        // Always show the view link.
        $showviewlink = true;

        if ($onlinetextesubmission) {
            // This contains the shortened version of the text plus an optional 'Export to portfolio' button.
            $text = $this->assignment->render_editor_content(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                             $onlinetextesubmission->submission,
                                                             $this->get_type(),
                                                             'onlinetexte',
                                                             'assignsubmission_onlinetexte', true);

            // The actual submission text.
            $onlinetexte = trim($onlinetextesubmission->onlinetexte);
            // The shortened version of the submission text.
            $shorttext = shorten_text($onlinetexte, 140);

            $plagiarismlinks = '';

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir . '/plagiarismlib.php');

                $plagiarismlinks .= plagiarism_get_links(array('userid' => $submission->userid,
                    'content' => $onlinetexte,
                    'cmid' => $this->assignment->get_course_module()->id,
                    'course' => $this->assignment->get_course()->id,
                    'assignment' => $submission->assignment));
            }
            // We compare the actual text submission and the shortened version. If they are not equal, we show the word count.
            if ($onlinetexte != $shorttext) {
                $wordcount = get_string('numwords', 'assignsubmission_onlinetexte', count_words($onlinetexte));

                return $plagiarismlinks . $wordcount . $text;
            } else {
                return $plagiarismlinks . $text;
            }
        }
        return '';
    }

    /**
     * Produce a list of files suitable for export that represent this submission.
     *
     * @param stdClass $submission - For this is the submission data
     * @param stdClass $user - This is the user record for this submission
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        global $DB;

        $files = array();
        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);

        // Note that this check is the same logic as the result from the is_empty function but we do
        // not call it directly because we already have the submission record.
        if ($onlinetextesubmission) {
            // Do not pass the text through format_text. The result may not be displayed in Moodle and
            // may be passed to external services such as document conversion or portfolios.
            $formattedtext = $this->assignment->download_rewrite_pluginfile_urls($onlinetextesubmission->onlinetexte, $user, $this);
            $head = '<head><meta charset="UTF-8"></head>';
            $submissioncontent = '<!DOCTYPE html><html>' . $head . '<body>'. $formattedtext . '</body></html>';

            $filename = get_string('onlinetextefilename', 'assignsubmission_onlinetexte');
            $files[$filename] = array($submissioncontent);

            $fs = get_file_storage();

            $fsfiles = $fs->get_area_files($this->assignment->get_context()->id,
                                           'assignsubmission_onlinetexte',
                                           ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                           $submission->id,
                                           'timemodified',
                                           false);

            foreach ($fsfiles as $file) {
                $files[$file->get_filename()] = $file;
            }
        }

        return $files;
    }

    /**
     * Display the saved text content from the editor in the view table
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        global $CFG;
        $result = '';
        $plagiarismlinks = '';

        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);

        if ($onlinetextesubmission) {

            // Render for portfolio API.
            $result .= $this->assignment->render_editor_content(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                                $onlinetextesubmission->submission,
                                                                $this->get_type(),
                                                                'onlinetexte',
                                                                'assignsubmission_onlinetexte');

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir . '/plagiarismlib.php');

                $plagiarismlinks .= plagiarism_get_links(array('userid' => $submission->userid,
                    'content' => trim($onlinetextesubmission->onlinetexte),
                    'cmid' => $this->assignment->get_course_module()->id,
                    'course' => $this->assignment->get_course()->id,
                    'assignment' => $submission->assignment));
            }
        }

        return $plagiarismlinks . $result;
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type and version.
     *
     * @param string $type old assignment subtype
     * @param int $version old assignment version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        if ($type == 'online' && $version >= 2011112900) {
            return true;
        }
        return false;
    }


    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment - the database for the old assignment instance
     * @param string $log record log events here
     * @return bool Was it a success?
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        // No settings to upgrade.
        return true;
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $submission,
                            & $log) {
        global $DB;

        $onlinetextesubmission = new stdClass();
        $onlinetextesubmission->onlinetexte = $oldsubmission->data1;
        $onlinetextesubmission->onlineformat = $oldsubmission->data2;

        $onlinetextesubmission->submission = $submission->id;
        $onlinetextesubmission->assignment = $this->assignment->get_instance()->id;

        if ($onlinetextesubmission->onlinetexte === null) {
            $onlinetextesubmission->onlinetexte = '';
        }

        if ($onlinetextesubmission->onlineformat === null) {
            $onlinetextesubmission->onlineformat = editors_get_preferred_format();
        }

        if (!$DB->insert_record('assignsubmission_onlinetexte', $onlinetextesubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }

        // Now copy the area files.
        $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'submission',
                                                        $oldsubmission->id,
                                                        $this->assignment->get_context()->id,
                                                        'assignsubmission_onlinetexte',
                                                        ASSIGNSUBMISSION_ONLINETEXT_FILEAREA,
                                                        $submission->id);
        return true;
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The new submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin (will be logged).
        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);
        $onlinetexteloginfo = '';
        $onlinetexteloginfo .= get_string('numwordsforlog',
                                         'assignsubmission_onlinetexte',
                                         count_words($onlinetextesubmission->onlinetexte));

        return $onlinetexteloginfo;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignsubmission_onlinetexte',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * No text is set for this plugin
     *
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $onlinetextesubmission = $this->get_onlinetexte_submission($submission->id);
        $wordcount = 0;
        $hasinsertedresources = false;

        if (isset($onlinetextesubmission->onlinetexte)) {
            $wordcount = count_words(trim($onlinetextesubmission->onlinetexte));
            // Check if the online text submission contains video, audio or image elements
            // that can be ignored and stripped by count_words().
            $hasinsertedresources = preg_match('/<\s*((video|audio)[^>]*>(.*?)<\s*\/\s*(video|audio)>)|(img[^>]*>(.*?))/',
                    trim($onlinetextesubmission->onlinetexte));
        }

        return $wordcount == 0 && !$hasinsertedresources;
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        if (!isset($data->onlinetexte_editor)) {
            return true;
        }
        $wordcount = 0;
        $hasinsertedresources = false;

        if (isset($data->onlinetexte_editor['text'])) {
            $wordcount = count_words(trim((string)$data->onlinetexte_editor['text']));
            // Check if the online text submission contains video, audio or image elements
            // that can be ignored and stripped by count_words().
            $hasinsertedresources = preg_match('/<\s*((video|audio)[^>]*>(.*?)<\s*\/\s*(video|audio)>)|(img[^>]*>(.*?))/',
                    trim((string)$data->onlinetexte_editor['text']));
        }

        return $wordcount == 0 && !$hasinsertedresources;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_ONLINETEXT_FILEAREA=>$this->get_name());
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the files across (attached via the text editor).
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'assignsubmission_onlinetexte',
                                     ASSIGNSUBMISSION_ONLINETEXT_FILEAREA, $sourcesubmission->id, 'id', false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_onlinetexte record.
        $onlinetextesubmission = $this->get_onlinetexte_submission($sourcesubmission->id);
        if ($onlinetextesubmission) {
            unset($onlinetextesubmission->id);
            $onlinetextesubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_onlinetexte', $onlinetextesubmission);
        }
        return true;
    }

    /**
     * Return a description of external params suitable for uploading an onlinetexte submission from a webservice.
     *
     * @return external_description|null
     */
    public function get_external_parameters() {
        $editorparams = array('text' => new external_value(PARAM_RAW, 'The text for this submission.'),
                              'format' => new external_value(PARAM_INT, 'The format for this submission'),
                              'itemid' => new external_value(PARAM_INT, 'The draft area id for files attached to the submission'));
        $editorstructure = new external_single_structure($editorparams, 'Editor structure', VALUE_OPTIONAL);
        return array('onlinetexte_editor' => $editorstructure);
    }

    /**
     * Compare word count of onlinetexte submission to word limit, and return result.
     *
     * @param string $submissiontext Onlinetext submission text from editor
     * @return string Error message if limit is enabled and exceeded, otherwise null
     */
    public function check_word_count($submissiontext) {
        global $OUTPUT;

        $wordlimitenabled = $this->get_config('wordlimitenabled');
        $wordlimit = $this->get_config('wordlimit');

        if ($wordlimitenabled == 0) {
            return null;
        }

        // Count words and compare to limit.
        $wordcount = count_words($submissiontext);
	echo '<script type="text/javascript">';
        echo 'var message = "' . $wordcount. '";'; // Embed PHP variable in JavaScript
        echo 'alert(message);';
        echo '</script>';

        if ($wordcount <= $wordlimit) {
            return null;
        } else {
            $errormsg = get_string('wordlimitexceeded', 'assignsubmission_onlinetexte',
                    array('limit' => $wordlimit, 'count' => $wordcount));
            return $OUTPUT->error_text($errormsg);
        }
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

    // Add this method to your class

}


