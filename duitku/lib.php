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
 * @package   enrol_duitku
 * @copyright Michael David <mikedh2612@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use paygw_duitku\duitku_status_codes;

defined('MOODLE_INTERNAL') || die();

//Creates a notification for every non-expired pending payment
//Function must be outside of class to be detected by Moodle
function paygw_duitku_before_footer() {
    global $USER, $DB;
    
    $params = [
        'userid' => (int)$USER->id,
        'payment_status' => duitku_status_codes::CHECK_STATUS_PENDING
    ];
    $pending_transactions = $DB->get_records_sql('SELECT * FROM {paygw_duitku} WHERE userid = :userid AND payment_status = :payment_status', $params);

    foreach ($pending_transactions as $transaction) {
        $select_statement = '
        SELECT *
        FROM {course}
        INNER JOIN {enrol}
        ON {enrol}.`courseid` = {course}.`id`
        WHERE {enrol}.`id` = :itemid
        ';
        $params = ['itemid' => $transaction->itemid];
        $course = $DB->get_records_sql($select_statement, $params);
        \core\notification::add("You have a pending payment for the '{$course[$transaction->itemid]->fullname}' course <a href='{$transaction->referenceurl}'>here</a>", \core\output\notification::NOTIFY_WARNING);
    }
}