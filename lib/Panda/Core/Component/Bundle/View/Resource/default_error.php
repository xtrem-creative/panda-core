<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo $v_errorCode; ?></title>
    </head>
    <body>
        <h1><?php echo $v_errorCode; ?></h1>
        <?php if (isset($v_message)) : ?>
            <p><?php echo $v_message; ?></p>
        <?php else : ?>
            <p>An error occured</p>
        <?php endif; ?>
        <hr />
        <address>Powered by Panda Framework</address>
    </body>
</html>