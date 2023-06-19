import { registerBlockType } from '@wordpress/blocks';
import reCAPTCHA from "react-google-recaptcha";
const { __ } = wp.i18n;

registerBlockType( 'validate-user/gutenberg-block', {
    title: 'New User Form',
    icon: 'feedback',
    category: 'design',
    attributes: {
    },
    example: {},
    edit: () => {
        return (
            <div className="validate-user">
                <div id="form-success" className="form-success"></div>
                <div id="form-error" className="form-error"></div>

                <form id="validate-user-form">

                    <label htmlFor="username">{ __( 'Username:', '/languages' ) }</label><br />
                    <input type="text" id="username" name="username" required /><br /><br />

                    <label htmlFor="email">{ __( 'Email:', 'languages' ) }</label><br />
                    <input type="email" id="email" name="email" required /><br /><br />

                    <label htmlFor="message">{ __( 'Message:', '/languages' ) }</label><br />
                    <textarea id="message" className="auto-resize" name="message"></textarea>

                    <button type="">{ __( 'Submit Request', '/languages' ) }</button>

                </form>
            </div>
        );
    },
    save: () => {
        return null;
    },
} );