# Validate User
Validate User is a WordPress plugin for facilitating new user applications when admin validation is required. I made it to grow my WordPress plugin development skills. Note that while I have done my best to make it secure, it has not been used on a live site or assessed by professionals, so I would not recommend using it on a live site.

## Displaying the Form
To display the form, there are two options:
1. Using the Gutenberg block editor, you will be able to directly insert a New User Form into the page.
2. Add the shortcode `validate_user_form` to the page.

## Customizing the Form
By default, the form displayed on the page will ask for a username, email, and a message.  If you desire more customizability, you can create your own form through the Contact Form 7 plugin and set the form in the settings page.  In order for the form to work correctly, it must contain a field with a name of 'username' and a field with a name of 'email'.  If the Contact Form 7 form you select to use with Validate User does not contain those two fields, users will encounter an error when they submit the form.

## Settings Page
All settings for the Validate User plugin can be set by going to the admin panel titled 'Validate User.'

## Applications
When someone fills out the form, a new application will be created.  All applications are viewable from the tab under 'Validate User' titled 'Applications.'  You can view an application, and from there choose to validate or reject it.

## Validated Users
All validated users are given the role 'validated-user' (these have the same permissions as the role 'subscriber').  You can view them in the 'Users' admin screen.  When you view a validated user, any other information they entered into the fom they submitted (other than username, email, and message) will appear at the bottom of their information screen.  When you edit them, you will have the option to add information at the bottom.

## Developer Resources
### Filter Hooks
#### validate-user-admin-email-message( string $message ): string
Allows developers to change the content of the admin message for a new application.

Parameters:
$message: string - the message body as created by the plugin.

Returns:
$message: string - the updated message body.

#### validate-user-confirmation-email-message( string $message ): string
Allows developers to change the content of the message for a confirmed application.

Parameters:
$message: string - the message body as created by the plugin.

Returns:
$message: string - the updated message body.

#### validate-user-rejection-email-message( string $message ): string
Allows developers to change the content of the message for a rejected application.

Parameters:
$message: string - the message body as created by the plugin.

Returns:
$message: string - the updated message body.
