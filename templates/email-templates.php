<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserEmailTemplates' ) ) {

    class ValidateUserEmailTemplates {

        public static function adminEmailTemplate(): string {

            ob_start();

            echo esc_html__( 'Hello', 'validate-user' ) . " {to_name},<br><br>\n\n";

            echo esc_html__( 'There is a new user application on your site', 'validate-user' ) . ' (<a href="{site_url}">{site_name}</a>)' . "<br>\n";
            echo "<ul>\n";
                echo "\t<li>" . esc_html__( 'Username', 'validate-user' ) . ": {username}</li>\n";
                echo "\t<li>" . esc_html__( 'Email', 'validate-user' ) . ": {email}</li>\n";
                echo "\t<li>\n";
					echo "\t\t" . esc_html__( 'Message', 'validate-user' ) . ":<br>\n";
                    echo "\t\t{message}\n";
				echo "\t</li>\n";
                echo "\t<li>\n";
	                echo "\t\t" . esc_html__( 'Other information', 'validate-user' ) . ":<br>\n";
	                echo "\t\t{other-info}\n";
	            echo "\t</li>\n";
            echo "</ul><br>\n";
            echo esc_html__( 'View the application at', 'validate-user' ) . ' <a href="{application_link}">{application_link}</a>.';

            return ob_get_clean();

        }

        public static function confirmationEmailTemplate(): string {

            ob_start();

            echo esc_html__( 'Hello', 'validate-user' ) . " {username},<br><br>\n\n";

	        echo esc_html__( 'Your user application for', 'validate-user' ) . ' <a href="{site_url}">{site_name}</a> ' . esc_html__( 'has been validated', 'validate-user' ) . ".<br>\n";
            echo esc_html__( 'Your username is', 'validate-user' ) . ": {username}<br>\n";
            echo esc_html__( 'Set your password', 'validate-user' ) . ' <a href="{set_password_link}">' . esc_html__( 'here', 'validate-user' ) . "</a>.<br><br>\n\n";

            echo esc_html__( 'Thanks for joining our website!', 'validate-user' ) . "<br>\n";
            echo "-{from_name}";

            return ob_get_clean();

        }

        public static function rejectionEmailTemplate(): string {

            ob_start();

	        echo esc_html__( 'Hello', 'validate-user' ) . " {username},<br><br>\n\n";

	        echo esc_html__( 'Your user application for', 'validate-user' ) . ' <a href="{site_url}">{site_name}</a> ' . esc_html__( 'has been denied', 'validate-user' ) . ".<br><br>\n\n";

	        echo esc_html__( 'Thanks for reaching out to us.', 'validate-user' ) . "<br>\n";
	        echo '-{from_name}';

            return ob_get_clean();

        }

    }

}