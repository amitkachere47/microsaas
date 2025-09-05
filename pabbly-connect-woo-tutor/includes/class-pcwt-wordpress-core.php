<?php

class PCWT_WordPress_Core extends PCWT_Webhook_Handler {

    public function __construct() {
        add_action( 'user_register', array( $this, 'user_register' ), 10, 1 );
        add_action( 'publish_post', array( $this, 'publish_post' ), 10, 2 );
        add_action( 'comment_post', array( $this, 'comment_post' ), 10, 2 );
    }

    public function user_register( $user_id ) {
        $user = get_userdata( $user_id );
        // This is a generic user registration hook, so we don't check for role
        $this->trigger_webhook( 'user_register', array(
            'user_id'    => $user_id,
            'user_email' => $user->user_email,
            'user_name'  => $user->display_name,
        ) );
    }

    public function publish_post( $post_id, $post ) {
        $this->trigger_webhook( 'publish_post', array(
            'post_id'    => $post_id,
            'post_title' => $post->post_title,
            'post_type'  => $post->post_type,
            'author_id'  => $post->post_author,
        ) );
    }

    public function comment_post( $comment_id, $comment_approved ) {
        if ( 1 !== $comment_approved ) {
            return;
        }
        $comment = get_comment( $comment_id );
        $this->trigger_webhook( 'comment_post', array(
            'comment_id'      => $comment_id,
            'comment_author'  => $comment->comment_author,
            'comment_content' => $comment->comment_content,
            'post_id'         => $comment->comment_post_ID,
        ) );
    }
}

new PCWT_WordPress_Core();
