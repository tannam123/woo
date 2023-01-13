<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content">

                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox">

                        <h2><span><?php esc_attr_e('Custom Product Addons Migration', WCPA_TEXT_DOMAIN); ?></span></h2>
                        <div class="inside">

                            <?php
                            if (isset($response) && $response) {
                                foreach ($response as $res) {
                                    if ($res['status']) {
                                        echo '<div clas="notice notice-success">';
                                    } else {
                                        echo '<div clas="notice notice-error">';
                                    }
                                    echo $res['message'];
                                    echo '</div>';
                                }
                            }
                            ?>
                            <p>
                                <?php _e('Found some data from old version of this plugin, You can migrate the old data to compatible with new plugin by clicking the migrate now button below ', WCPA_TEXT_DOMAIN); ?>

                            </p>
                            <a href="<?php print wp_nonce_url(admin_url('options-general.php?page=wcpa_settings&view=migration&action=migrate'), 'wcpa_migration', 'wcpa_nonce');
                                ?>" ><input class="button-primary" type="submit" name="Example" value="<?php esc_attr_e('Migrate Now'); ?>" /></a>

                        </div>
                        <!-- .inside -->

                    </div>
                    <!-- .postbox -->

                </div>
                <!-- .meta-box-sortables .ui-sortable -->

            </div>
            <!-- post-body-content -->


        </div>
        <!-- #post-body .metabox-holder .columns-2 -->

        <br class="clear">
    </div>
    <!-- #poststuff -->

</div> <!-- .wrap -->