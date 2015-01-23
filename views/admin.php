<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
    <h2>LinkGist</h2>

    <form method="post" action="options.php">
        <?php settings_fields($optionKey); ?>
        <?php do_settings_sections($pageSlug); ?>
        <?php submit_button(); ?>
    </form>
