<?php
// This file is part of the Aferium plugin for Moodle.
//
// It is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
// License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
// any later version. It is distributed WITHOUT ANY WARRANTY. See <http://www.gnu.org/licenses/>.

namespace local_aferium\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Builds a quiz from an Aferium assessment: imports the questions (Moodle XML) and adds them to a new quiz.
 *
 * Runs as the teacher who owns the token, so Moodle's own capabilities decide whether it is allowed.
 *
 * @package   local_aferium
 * @copyright 2026 Aferium
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_quiz extends external_api {

    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course that receives the quiz'),
            'name' => new external_value(PARAM_TEXT, 'Quiz name'),
            'xml' => new external_value(PARAM_RAW, 'The questions, in Moodle XML'),
            'visible' => new external_value(PARAM_BOOL, 'Show the quiz to students right away', VALUE_DEFAULT, false),
            'timeopen' => new external_value(PARAM_INT, 'Opening time, 0 for none', VALUE_DEFAULT, 0),
            'timeclose' => new external_value(PARAM_INT, 'Closing time, 0 for none', VALUE_DEFAULT, 0),
            'timelimit' => new external_value(PARAM_INT, 'Time limit in seconds, 0 for none', VALUE_DEFAULT, 0),
            'attempts' => new external_value(PARAM_INT, 'Attempts allowed, 0 for unlimited', VALUE_DEFAULT, 0),
            'grademethod' => new external_value(PARAM_INT, '1 highest, 2 average, 3 first, 4 last', VALUE_DEFAULT, 1),
            'shuffleanswers' => new external_value(PARAM_BOOL, 'Shuffle within questions', VALUE_DEFAULT, true),
            'shufflequestions' => new external_value(PARAM_BOOL, 'Shuffle the question order', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Creates the quiz. Everything happens in one transaction: a failed import leaves no empty quiz behind.
     *
     * @param int $courseid
     * @param string $name
     * @param string $xml
     * @param bool $visible
     * @param int $timeopen
     * @param int $timeclose
     * @param int $timelimit
     * @param int $attempts
     * @param int $grademethod
     * @param bool $shuffleanswers
     * @param bool $shufflequestions
     * @return array
     */
    public static function execute(int $courseid, string $name, string $xml, bool $visible = false, int $timeopen = 0,
            int $timeclose = 0, int $timelimit = 0, int $attempts = 0, int $grademethod = 1,
            bool $shuffleanswers = true, bool $shufflequestions = false): array {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/format/xml/format.php');

        $params = self::validate_parameters(self::execute_parameters(), compact('courseid', 'name', 'xml', 'visible',
            'timeopen', 'timeclose', 'timelimit', 'attempts', 'grademethod', 'shuffleanswers', 'shufflequestions'));
        $name = trim($params['name']);
        if ($name === '' || $params['timeopen'] < 0 || $params['timeclose'] < 0 || $params['timelimit'] < 0
                || ($params['timeopen'] && $params['timeclose'] && $params['timeclose'] <= $params['timeopen'])
                || $params['attempts'] < 0 || $params['attempts'] > QUIZ_MAX_ATTEMPT_OPTION
                || !in_array($params['grademethod'], [QUIZ_GRADEHIGHEST, QUIZ_GRADEAVERAGE, QUIZ_ATTEMPTFIRST,
                    QUIZ_ATTEMPTLAST])) {
            throw new \invalid_parameter_exception('Invalid quiz settings.');
        }

        $course = get_course($params['courseid']);
        $context = \context_course::instance($course->id);
        self::validate_context($context);
        require_capability('mod/quiz:addinstance', $context);
        require_capability('moodle/course:manageactivities', $context);
        require_capability('moodle/question:add', $context);

        // The site's own quiz defaults, so the new quiz behaves like one the teacher would create by hand.
        $config = get_config('quiz');
        $info = (object) [
            'modulename' => 'quiz',
            'module' => $DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST),
            'section' => 0,
            'visible' => $params['visible'] ? 1 : 0,
            'visibleoncoursepage' => 1,
            'cmidnumber' => '',
            'groupmode' => 0,
            'groupingid' => 0,
            'availability' => null,
            'completion' => 0,
            'completionview' => 0,
            'completionexpected' => 0,
            'completionpassgrade' => 0,
            'completiongradeitemnumber' => null,
            'name' => $name,
            'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0],
            'timeopen' => $params['timeopen'],
            'timeclose' => $params['timeclose'],
            'timelimit' => $params['timelimit'],
            'attempts' => $params['attempts'],
            'grademethod' => $params['grademethod'],
            'shuffleanswers' => $params['shuffleanswers'] ? 1 : 0,
            'attemptonlast' => 0,
            'canredoquestions' => 0,
            'preferredbehaviour' => $config->preferredbehaviour,
            'overduehandling' => $config->overduehandling,
            'graceperiod' => $config->graceperiod,
            'decimalpoints' => $config->decimalpoints,
            'questiondecimalpoints' => $config->questiondecimalpoints,
            'questionsperpage' => $config->questionsperpage,
            'navmethod' => $config->navmethod,
            'showuserpicture' => $config->showuserpicture,
            'showblocks' => $config->showblocks,
            'browsersecurity' => $config->browsersecurity,
            'quizpassword' => '',
            'subnet' => '',
            'delay1' => 0,
            'delay2' => 0,
            'grade' => $config->maximumgrade,
            'sumgrades' => 0,
        ];
        $times = ['during' => 0x10000, 'immediately' => 0x01000, 'open' => 0x00100, 'closed' => 0x00010];
        foreach (['attempt', 'correctness', 'maxmarks', 'marks', 'specificfeedback', 'generalfeedback', 'rightanswer',
                'overallfeedback'] as $field) {
            foreach ($times as $when => $bit) {
                $info->{$field . $when} = ((int) ($config->{'review' . $field} ?? 0) & $bit) ? 1 : 0;
            }
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $info = add_moduleinfo($info, $course);
            $quiz = $DB->get_record('quiz', ['id' => $info->instance], '*', MUST_EXIST);
            $quiz->cmid = $info->coursemodule;
            // The questions live in the quiz's own bank, which also exists in Moodle 5 (no course bank there).
            $modcontext = \context_module::instance($info->coursemodule);

            $file = make_request_directory() . '/aferium.xml';
            file_put_contents($file, $params['xml']);
            $format = new \qformat_xml();
            $format->setCategory(question_make_default_categories([$modcontext]));
            $format->setContexts([$modcontext]);
            $format->setCourse($course);
            $format->setFilename($file);
            $format->setRealfilename('aferium.xml');
            $format->setMatchgrades('nearest');
            $format->setCatfromfile(false);
            $format->setContextfromfile(false);
            $format->setStoponerror(true);
            $format->set_display_progress(false);
            // The importer prints its notices; a web service must answer only JSON.
            ob_start();
            try {
                $imported = $format->importpreprocess() && $format->importprocess() && $format->importpostprocess();
            } finally {
                ob_end_clean();
            }
            if (!$imported || !$format->questionids) {
                throw new \moodle_exception('importfailed', 'local_aferium');
            }

            foreach ($format->questionids as $questionid) {
                quiz_add_quiz_question($questionid, $quiz);
            }
            if ($params['shufflequestions']) {
                $DB->set_field('quiz_sections', 'shufflequestions', 1, ['quizid' => $quiz->id]);
            }
            \mod_quiz\quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();
            $transaction->allow_commit();
        } catch (\Throwable $error) {
            try {
                $transaction->rollback($error);
            } finally {
                // The course cache is versioned and was rebuilt inside the transaction, so it still lists the
                // module that no longer exists, with a newer version than the database now has. Drop it.
                $cache = \cache::make('core', 'coursemodinfo');
                $cache->acquire_lock($course->id);
                try {
                    $cache->delete($course->id);
                } finally {
                    $cache->release_lock($course->id);
                }
                rebuild_course_cache($course->id, true);
            }
        }

        return [
            'quizid' => (int) $quiz->id,
            'cmid' => (int) $info->coursemodule,
            'questions' => count($format->questionids),
        ];
    }

    /**
     * Result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'quizid' => new external_value(PARAM_INT, 'Quiz id'),
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
            'questions' => new external_value(PARAM_INT, 'Questions added to the quiz'),
        ]);
    }
}
