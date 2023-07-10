# Validate User

Validate User is a WordPress plugin for facilitating new user applications when admin validation is required. I made it
to grow my WordPress plugin development skills. Note that I don't currently have plans to maintain it, so use it at your
own risk.

## How It Works

With Validate User, you can put a New User Form anywhere in the site. When someone fills it out, an application will
appear in the backend of the site. Admins will then be able to view the application and choose whether to confirm or
reject the user's application. Validate User also has several flexible options for sending emails at various stages of
this process.

## How-to Guide

### Displaying the Form

To display the form, there are two options:

1. Using the Gutenberg block editor, you will be able to directly insert a New User Form into the page.
2. Add the shortcode `validate_user_form` to the page.

### Customizing the Form

By default, the form displayed on the page will ask for a username, email, and a message. If you desire more
customizability, you can create your own form through the Contact Form 7 plugin and set the form in the settings page.
In order for the form to work correctly, it must contain a field with a name of 'username' and a field with a name of '
email'. If the Contact Form 7 form you select to use with Validate User does not contain those two fields, users will
encounter an error when they submit the form.

### Settings Page

All settings for the Validate User plugin can be set by going to the admin panel titled 'Validate User.'

### Applications

When someone fills out the form, a new application will be created. All applications are viewable from the tab under '
Validate User' titled 'Applications.' You can view an application, and from there choose to validate or reject it.

### Validated Users

All validated users are given the role 'validated-user' (these have the same permissions as the role 'subscriber'). You
can view them in the 'Users' admin screen. When you view a validated user, any other information they entered into the
fom they submitted (other than username, email, and message) will appear at the bottom of their information screen. When
you edit them, you will have the option to add information at the bottom.

## Developer Resources

### Filter Hooks

#### `validate-user-admin-email-message( string $message, int $post_id ): string`

Allows developers to change the content of the admin message for a new application.

Parameters:

- `$message: string` - the message body as created by the plugin.

- `$post_id: int` - the id of the custom post type containing the information for the new user application.

Returns:

- `$message: string` - the updated message body.

#### `validate-user-confirmation-email-message( string $message, int $post_id ): string`

Allows developers to change the content of the message for a confirmed application.

Parameters:

- `$message: string` - the message body as created by the plugin.

- `$post_id: int` - the id of the custom post type containing the information for the new user application.

Returns:

- `$message: string` - the updated message body.

#### `validate-user-rejection-email-message( string $message, int $post_id ): string`

Allows developers to change the content of the message for a rejected application.

Parameters:

- `$message: string` - the message body as created by the plugin.

- `$post_id: int` - the id of the custom post type containing the information for the new user application.

Returns:

- `$message: string` - the updated message body.

### Action Hooks
#### `validate-user-confirm-user( int $user_id, int $post_id, array $errors )`
Allows developers to add an action right after a new user application has been accepted.

Parameters:

- `$user_id: int` - the id for the new user that has been created.

- `$post_id: int` - the id for the application. 

- `$errors: string[]` - an array of strings containing any error messages that may have been generated during user creation.
  - Note that if `$errors` is not empty, the application will not be deleted.

#### `validate-user-reject-user( int $post_id, array $errors )`
Allows developers to add an action right after a new user application has been rejected.

Parameters:

- `$post_id: int` - the id for the application that is about to be deleted.

- `$errors: string[]` - an array of strings containing any error messages that may have been generated during user rejection.
    - Note that if `$errors` is not empty, the application will not be deleted.