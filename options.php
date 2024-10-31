<div class="wrap">
    <h2>Ruler Analytics</h2>

    <form method="post" action="options.php">
        <?php wp_nonce_field('update-options'); ?>
        <?php settings_fields('ruleranalytics_options');
        $site_id = get_option('ruler_site_id');
        ?>

        <table class="form-table">

            <tr valign="top">
                <th scope="row">Site Id:</th>
                <td><input type="text" name="ruler_site_id" value="<?php echo get_option('ruler_site_id'); ?>" />
                    <?php if($site_id == ''){?>
                        Missing Site id! <br />
                        Visit <a href="http://www.ruleranalytics.com/signup">www.ruleranalytics.com</a> to get your tracking id
                        <?php } ?>
                </td>
            </tr>

        </table>

        <input type="hidden" name="action" value="update" />

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

    </form>
</div>
