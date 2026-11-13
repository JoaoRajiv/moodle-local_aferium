<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

namespace local_aferium\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Which options a student marked in each question of a quiz attempt.
 *
 * Core web services only give this inside the review HTML. Here it comes as data: the position of each marked
 * option in the question's original order (the order Aferium exported), whatever the shuffling was.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_attempt_choices extends external_api {

    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt id'),
        ]);
    }

    /**
     * Reads the choices. Only for who can see the quiz reports (the teacher), as in Moodle's own reports.
     *
     * @param int $attemptid
     * @return array
     */
    public static function execute(int $attemptid): array {
        global $CFG;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $params = self::validate_parameters(self::execute_parameters(), ['attemptid' => $attemptid]);
        $attempt = \mod_quiz\quiz_attempt::create($params['attemptid']);
        $context = $attempt->get_quizobj()->get_context();
        self::validate_context($context);
        require_capability('mod/quiz:viewreports', $context);
        if (!$attempt->is_review_allowed()) {
            throw new \moodle_exception('noreviewattempt', 'quiz');
        }

        $slots = [];
        foreach ($attempt->get_slots() as $slot) {
            $qa = $attempt->get_question_attempt($slot);
            $question = $qa->get_question(false);
            $response = $qa->get_last_qt_data();
            $choices = null;
            if ($question instanceof \qtype_multichoice_base) {
                $original = array_keys($question->answers);
                $shown = $question->get_order($qa);
                $marked = [];
                if ($question instanceof \qtype_multichoice_single_question) {
                    $index = $response['answer'] ?? '';
                    if ($index !== '' && isset($shown[$index])) {
                        $marked[] = $shown[$index];
                    }
                } else {
                    foreach ($shown as $index => $answerid) {
                        if (!empty($response['choice' . $index])) {
                            $marked[] = $answerid;
                        }
                    }
                }
                $choices = [];
                foreach ($marked as $answerid) {
                    $position = array_search($answerid, $original);
                    if ($position !== false) {
                        $choices[] = $position;
                    }
                }
                sort($choices);
            } else if ($question instanceof \qtype_truefalse_question) {
                // Aferium exports "true" first and "false" second.
                $answer = $response['answer'] ?? '';
                $choices = $answer === '' ? [] : [$answer ? 0 : 1];
            }
            $slots[] = ['slot' => (int) $slot, 'known' => $choices !== null, 'choices' => $choices ?? []];
        }
        return ['slots' => $slots];
    }

    /**
     * Result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'slots' => new external_multiple_structure(new external_single_structure([
                'slot' => new external_value(PARAM_INT, 'Slot of the question in the attempt'),
                'known' => new external_value(PARAM_BOOL, 'False when the question is not a choice question'),
                'choices' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Position of a marked option in the original order, from 0'),
                    'Empty when the question was left blank'
                ),
            ])),
        ]);
    }
}
