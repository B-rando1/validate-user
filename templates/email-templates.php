<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserEmailTemplates' ) ) {

    class ValidateUserEmailTemplates {

        public static function adminEmailTemplate(): string {

            ob_start();

            echo esc_html__( 'Hello', '/languages' ) . " {to_name},<br><br>\n\n";

            echo esc_html__( 'There is a new user application on your site', '/languages' ) . ' (<a href="{site_url}">{site_name}</a>)' . "<br>\n";
            echo "<ul>\n";
                echo "\t<li>" . esc_html__( 'Username', '/languages' ) . ": {username}</li>\n";
                echo "\t<li>" . esc_html__( 'Email', '/languages' ) . ": {email}</li>\n";
                echo "\t<li>\n";
					echo "\t\t" . esc_html__( 'Message', '/languages' ) . ":<br>\n";
                    echo "\t\t{message}\n";
				echo "\t</li>\n";
                echo "\t<li>\n";
	                echo "\t\t" . esc_html__( 'Other information', '/languages' ) . ":<br>\n";
	                echo "\t\t{other-info}\n";
	            echo "\t</li>\n";
            echo "</ul><br>\n";
            echo esc_html__( 'View the application at', '/languages' ) . ' <a href="{application_link}">{application_link}</a>.';

            return ob_get_clean();

        }

        public static function confirmationEmailTemplate(): string {

            ob_start();

            echo esc_html__( 'Hello', '/languages' ) . " {username},<br><br>\n\n";

	        echo esc_html__( 'Your user application for', '/languages' ) . ' <a href="{site_url}">{site_name}</a> ' . esc_html__( 'has been validated', '/languages' ) . ".<br>\n";
            echo esc_html__( 'Your username is', '/languages' ) . ": {username}<br>\n";
            echo esc_html__( 'Set your password', '/languages' ) . ' <a href="{set_password_link}">' . esc_html__( 'here', '/languages' ) . "</a>.<br><br>\n\n";

            echo esc_html__( 'Thanks for joining our website!', '/languages' ) . "<br>\n";
            echo "-{from_name}";

            return ob_get_clean();

        }

        public static function rejectionEmailTemplate(): string {

            ob_start();

	        echo esc_html__( 'Hello', '/languages' ) . " {username},<br><br>\n\n";

	        echo esc_html__( 'Your user application for', '/languages' ) . ' <a href="{site_url}">{site_name}</a> ' . esc_html__( 'has been denied', '/languages' ) . ".<br><br>\n\n";

	        echo esc_html__( 'Thanks for reaching out to us.', '/languages' ) . "<br>\n";
	        echo '-{from_name}';

            return ob_get_clean();

        }

    }

}