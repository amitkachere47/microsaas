<?php

class PCWT_Trigger_Manager {

    public static function get_triggers() {
        $triggers = array(
            'WordPress Core' => array(
                'user_register' => 'User Registers',
                'publish_post' => 'Post Published',
                'comment_post' => 'Comment Posted',
            ),
        );

        if ( pcwt_connector()->is_woocommerce_active() ) {
            $triggers['WooCommerce'] = array(
                'woocommerce_order_status_changed' => 'Order Status Changed',
                'woocommerce_new_order' => 'Order Created',
                'woocommerce_new_product' => 'Product Created',
                'woocommerce_new_customer' => 'Customer Created',
                'woocommerce_add_to_cart' => 'Product Added to Cart',
            );
        }

        if ( pcwt_connector()->is_tutor_lms_active() ) {
            $triggers['Tutor LMS'] = array(
                'tutor_after_enroll' => 'Student Enrolls in Course',
                'tutor_lesson_completed' => 'Lesson Completed',
                'tutor_quiz_passed' => 'Quiz Passed',
                'tutor_assignment_submitted' => 'Assignment Submitted',
                'tutor_question_posted' => 'Question Posted',
            );
        }

        return $triggers;
    }

    public static function get_trigger_fields( $trigger ) {
        $fields = array(
            'user_register' => array( 'user_id' => 'User ID', 'user_email' => 'User Email' ),
            'publish_post' => array( 'post_id' => 'Post ID', 'post_title' => 'Post Title' ),
            'comment_post' => array( 'comment_id' => 'Comment ID', 'post_id' => 'Post ID' ),
            'woocommerce_order_status_changed' => array( 'order_id' => 'Order ID', 'total' => 'Total' ),
            'woocommerce_new_order' => array( 'order_id' => 'Order ID', 'total' => 'Total' ),
            'woocommerce_new_product' => array( 'product_id' => 'Product ID', 'price' => 'Price' ),
            'woocommerce_new_customer' => array( 'user_id' => 'User ID', 'user_email' => 'User Email' ),
            'woocommerce_add_to_cart' => array( 'product_id' => 'Product ID', 'quantity' => 'Quantity' ),
            'tutor_after_enroll' => array( 'course_id' => 'Course ID', 'user_id' => 'User ID' ),
            'tutor_lesson_completed' => array( 'lesson_id' => 'Lesson ID', 'course_id' => 'Course ID' ),
            'tutor_quiz_passed' => array( 'quiz_id' => 'Quiz ID', 'course_id' => 'Course ID' ),
            'tutor_assignment_submitted' => array( 'assignment_id' => 'Assignment ID', 'course_id' => 'Course ID' ),
            'tutor_question_posted' => array( 'question_id' => 'Question ID', 'course_id' => 'Course ID' ),
        );
        return isset( $fields[$trigger] ) ? $fields[$trigger] : array();
    }
}
