<?php

class PCWT_Tutor_LMS extends PCWT_Webhook_Handler {

    public function __construct() {
        add_action( 'tutor_after_enroll', array( $this, 'after_enroll' ), 10, 2 );
        add_action( 'tutor_lesson_completed_after', array( $this, 'lesson_completed' ) );
        add_action( 'tutor_quiz/attempt_ended', array( $this, 'quiz_ended' ), 10, 1 );
        add_action( 'tutor_assignment/after_submit', array( $this, 'assignment_submitted' ), 10, 1 );
        add_action( 'tutor_qna/new_question_posted', array( $this, 'question_posted' ), 10, 1 );
    }

    private function is_course_webhook_enabled( $course_id ) {
        $selected_courses = get_option( 'pcwt_tutor_lms_courses', array() );
        if ( empty( $selected_courses ) || in_array( 'all', $selected_courses ) ) {
            return true;
        }
        return in_array( $course_id, $selected_courses );
    }

    public function after_enroll( $course_id, $enrollment_id ) {
        if ( ! $this->is_course_webhook_enabled( $course_id ) ) return;

        $enrollment = get_post( $enrollment_id );
        $user_id = $enrollment->post_author;
        $user_info = get_userdata( $user_id );
        $course = get_post( $course_id );

        $this->trigger_webhook( 'tutor_after_enroll', array(
            'user_id' => $user_id, 'user_email' => $user_info->user_email,
            'course_id' => $course_id, 'course_title' => $course->post_title,
        ) );
    }

    public function lesson_completed( $lesson_id ) {
        $course_id = get_post_meta( $lesson_id, '_tutor_course_id_for_lesson', true );
        if ( ! $this->is_course_webhook_enabled( $course_id ) ) return;
        $user_id = get_current_user_id();
        $user_info = get_userdata( $user_id );
        $this->trigger_webhook( 'tutor_lesson_completed', array(
            'user_id' => $user_id, 'user_email' => $user_info->user_email,
            'lesson_id' => $lesson_id, 'course_id' => $course_id,
        ) );
    }

    public function quiz_ended( $attempt ) {
        if ( ! $attempt || ! $attempt->is_passed || ! $this->is_course_webhook_enabled( $attempt->course_id ) ) return;
        $user_info = get_userdata( $attempt->user_id );
        $this->trigger_webhook( 'tutor_quiz_passed', array(
            'user_id' => $attempt->user_id, 'user_email' => $user_info->user_email,
            'quiz_id' => $attempt->quiz_id, 'course_id' => $attempt->course_id,
        ) );
    }

    public function assignment_submitted( $assignment_submit_id ) {
        $course_id = get_post_meta( get_post( $assignment_submit_id )->post_parent, '_tutor_course_id_for_assignment', true );
        if ( ! $this->is_course_webhook_enabled( $course_id ) ) return;
        $assignment_submit = get_post( $assignment_submit_id );
        $user_info = get_userdata( $assignment_submit->post_author );
        $this->trigger_webhook( 'tutor_assignment_submitted', array(
            'user_id' => $assignment_submit->post_author, 'user_email' => $user_info->user_email,
            'assignment_id' => $assignment_submit->post_parent, 'course_id' => $course_id,
        ) );
    }

    public function question_posted( $question_id ) {
        $course_id = get_post_meta( $question_id, 'tutor_course_id', true );
        if ( ! $this->is_course_webhook_enabled( $course_id ) ) return;
        $question = get_post( $question_id );
        $user_info = get_userdata( $question->post_author );
        $this->trigger_webhook( 'tutor_question_posted', array(
            'user_id' => $question->post_author, 'user_email' => $user_info->user_email,
            'question_id' => $question_id, 'course_id' => $course_id,
        ) );
    }
}

new PCWT_Tutor_LMS();
