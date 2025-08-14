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
 * Steps definitions related to assignfeedback_offline.
 *
 * @package   assignfeedback_offline
 * @category  test
 * @copyright 2025 Steve Bader
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to assignfeedback_offline.
 *
 * @copyright 2016 Steve Bader
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_assignfeedback_offline extends behat_base {

    /**
     * Exports submissions csv and reads content to create grade/feedback csv.
     *
     * @Then /^following "(?P<link_string>(?:[^"]|\\")*)" create a grade csv$/
     * @param string $link
     */
    public function following_to_create_grade_csv($link) {
        global $CFG;
        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

        // It will stop spinning once file is downloaded or time out.
        $behatgeneralcontext = behat_context_helper::get('behat_general');
        $result = $this->spin(
            function($context, $args) use ($behatgeneralcontext) {
                $link = $args['link'];
                return $behatgeneralcontext->download_file_from_link($link);
            },
            array('link' => $link),
            behat_base::get_extended_timeout(),
            $exception
        );

        // Now read the downloaded file and create a grade csv.
        $csvrows = preg_split("/\r\n|\n|\r/", $result);
        $csvresult = [];
        foreach ($csvrows as $csvline) {
            $csvresult[] = str_getcsv($csvline);
        }
        $csvresult[1][4] = '95.00';
        $csvresult[2][4] = '72.00';

        $filename = $CFG->dirroot . '/mod/assign/feedback/offline/tests/fixtures/assignfeedback_offline_grading.csv';
        $fp = fopen($filename,"w");

        foreach ($csvresult as $csvrow) {
            fputcsv($fp, $csvrow);
        }
        fclose($fp);

    }

    /**
     * Exports submissions csv and reads content to create grade/feedback csv.
     *
     * @Then /^following "(?P<link_string>(?:[^"]|\\")*)" create a grade and feedback csv$/
     * @param string $link
     */
    public function following_to_create_grade_and_feedback_csv($link) {
        global $CFG;
        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

        // It will stop spinning once file is downloaded or time out.
        $behatgeneralcontext = behat_context_helper::get('behat_general');
        $result = $this->spin(
            function($context, $args) use ($behatgeneralcontext) {
                $link = $args['link'];
                return $behatgeneralcontext->download_file_from_link($link);
            },
            array('link' => $link),
            behat_base::get_extended_timeout(),
            $exception
        );

        // Now read the downloaded file and create a grade csv.
        $csvrows = preg_split("/\r\n|\n|\r/", $result);
        $csvresult = [];
        foreach ($csvrows as $csvline) {
            $csvresult[] = str_getcsv($csvline);
        }
        $csvresult[1][4] = '95.00';
        $csvresult[1][10] = 'This is the first student feedback.';
        $csvresult[2][4] = '72.00';
        $csvresult[2][10] = 'This is the second student feedback.';

        $filename = $CFG->dirroot . '/mod/assign/feedback/offline/tests/fixtures/assignfeedback_offline_grading.csv';
        $fp = fopen($filename,"w");

        foreach ($csvresult as $csvrow) {
            fputcsv($fp, $csvrow);
        }
        fclose($fp);

    }

}
