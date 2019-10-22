<!DOCTYPE html>
<?php
    $predefinedUsers = ['admin', 'root'];

    $login = isset($_POST['login']);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN);

    // Only one of the next states can be TRUE
    $formEmpty = empty($username) && empty($password);
    $usernameEmpty = empty($username) && !empty($password);
    $passwordEmpty = !empty($username) && empty($password);
    $invalidUsername = !empty($username) && !empty($password) && !in_array($username, $predefinedUsers);
    $invalidPassword = !empty($username) && !empty($password) && in_array($username, $predefinedUsers);
?>
<html>
    <head>
        <title>Log In</title>

        <!-- Icon source: https://www.favicon.cc/?action=icon&file_id=393493 -->
        <!--link href="favicon.ico" rel="icon" type="image/x-icon" /-->
        <link href="data:image/x-icon;base64,AAABAAEAEBAQAAAAAAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAgAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAsC8qAP+EAACzh1cAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAAAAACAAAAAAAAACAAAAAAAAEiAAAAADAAAiAAAAAAMzAiAAAAAAAAMzAAAAAAAAAiMzMAAAAAAAADAzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA" rel="icon" type="image/x-icon" />

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
            }
            body {
                background-color: #38424d;
                margin: 0;
                font-family: "Muli", "Helvetica", "Tahoma", "Geneva", "Arial", sans-serif;
            }
        </style>
    </head>
    <body>

        <div id="login">
            <?php if (!$login) { ?>
            <?php } else if ($formEmpty) { ?>
                <p class="message warning">Sorry, but we cannot identify you in the system. Please log in.</p>
            <?php } else if ($usernameEmpty) { ?>
                <p class="message error">The username field is empty.</p>
            <?php } else if ($passwordEmpty) { ?>
                <p class="message error">The password field is empty.</p>
            <?php } else if ($invalidUsername) { ?>
                <p class="message error">Invalid username.</p>
            <?php } else if ($invalidPassword) { ?>
                <p class="message error">The password you entered for the username <strong><?php echo $username; ?></strong> is incorrect.</p>
            <?php } ?>

            <form id="login-form" action="" method="POST">
                <input type="hidden" name="login" value="1" />
                <label>Username <input type="text" name="username" id="login-form-username" value="<?php echo $username; ?>" /></label>
                <label>Password <input type="password" name="password" id="login-form-password" value="" /></label>
                <label><input type="checkbox" name="remember" <?php if ($remember) echo 'checked="checked"'; ?> /> Remember Me</label>
                <p><input type="submit" class="button primary large" value="Log In" /></p>
            </form>

            <script>
                (function () {
                    var focusOn = 'login-form-<?php echo ($passwordEmpty || $invalidPassword) ? 'password' : 'username'; ?>';
                    var element = document.getElementById(focusOn);
                    element.focus();
                    element.select(); // Highlight the text
                })()
            </script>
        </div><!-- End of #login -->

        <style>
            body {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            #login {
                background-color: #fff;
                width: 284px;
                padding: 18px;
                font-size: 17px;
                border-radius: 6px;
            }
            #login .message + form {
                margin: 18px 0 0;
            }
            #login label {
                cursor: pointer;
                display: inline-block;
                width: 100%;
                margin: 0 0 16px;
            }
            #login input {
                font-size: 18px;
            }
            #login input[type="text"],
            #login input[type="password"] {
                width: 100%;
                box-sizing: border-box;
                padding: 2px;
                margin: 0;
                font-size: 21px;
            }
            #login form p {
                margin: 0;
                text-align: center;
            }
            .message {
                border-top: none;
                border-radius: 6px;
                padding: 15px;
                margin: 0;
                background-color: #fff;
                color: #555555;
                position: relative;
            }
            .message.notice {
                border-top: 30px solid #6ab0de;
                background-color: #e7f2fa;
            }
            .message.warning {
                border-top: 30px solid #f0b37e;
                background-color: #fff2db;
            }
            .message.error {
                border-top: 30px solid #d9534f;
                background-color: #fae2e2;
            }
            .message.notice:before,
            .message.warning:before,
            .message.error:before {
                position: absolute;
                top: -30px;
                line-height: 30px;
                color: #fff;
                font-weight: bold;
            }
            .message.notice:before {
                content: "Notice";
            }
            .message.warning:before {
                content: "Warning";
            }
            .message.error:before {
                content: "Error";
            }
            .button {
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                user-select: none;
                border: 1px solid transparent;
                padding: 0.375rem 0.75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: 0.25rem;
                cursor: pointer;
            }
            .button.primary {
                color: #fff;
                background-color: #337ab7;
                border-color: #2e6da4;
            }
            .button.primary:hover {
                color: #fff;
                background-color: #0069d9;
                border-color: #0062cc;
            }
            .button.large {
                padding: .5rem 1rem;
                font-size: 1.25rem;
                line-height: 1.5;
                border-radius: .3rem;
            }
        </style>

    </body>
</html>
