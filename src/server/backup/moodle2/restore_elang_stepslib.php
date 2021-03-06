<?php
// This file is part of mod_elang for moodle.
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
 * Steps to restore an elang activity
 *
 * @package     mod_elang
 *
 * @copyright   2013-2018 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 *
 * @since       1.1.0
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Steps to restore an elang activity
 *
 * @copyright   2013-2018 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 *
 * @since  1.1.0
 */
class restore_elang_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Define the structure of an elang activity, and return it.
     *
     * @return prepared structure of elang for activity task
     */
    protected function define_structure() {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('elang', '/activity/elang');
        $paths[] = new restore_path_element('elang_cue', '/activity/elang/cues/cue');

        if ($userinfo) {
            $paths[] = new restore_path_element('elang_user', '/activity/elang/cues/cue/users/user');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Create a copy of former activity, add it to DB
     *
     * @param   array  $data  The data making the activity.
     *
     * @return void
     */
    protected function process_elang($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        // Insert the elang record.
        $newitemid = $DB->insert_record('elang', $data);

        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Create a copy of former cues and add them to DB
     *
     * @param   array  $data  The data making the cues.
     *
     * @return void
     */
    protected function process_elang_cue($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->id_elang = $this->get_new_parentid('elang');

        $newitemid = $DB->insert_record('elang_cues', $data);
        $this->set_mapping('elang_cue', $oldid, $newitemid);
    }

    /**
     * Create a copy of former users and add them to DB
     *
     * @param   array  $data  The data making the users.
     *
     * @return void
     */
    protected function process_elang_user($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->id_elang = $this->get_new_parentid('elang');
        $data->id_cue = $this->get_new_parentid('elang_cue');
        $data->id_user = $this->get_mappingid('user', $data->id_user);

        $newitemid = $DB->insert_record('elang_users', $data);
        $this->set_mapping('elang_user', $oldid, $newitemid);
    }

    /**
     * Add related files
     *
     * @return void
     */
    protected function after_execute() {
        // Add elang related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_elang', 'videos', null);
        $this->add_related_files('mod_elang', 'poster', null);
        $this->add_related_files('mod_elang', 'subtitle', null);
    }
}
