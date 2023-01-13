<?php

/**
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 */

?>
<div class="dup-pro-import-upload-message" >
    <p class="import-upload-reset-message-error">
        <i class="fa fa-exclamation-triangle"></i> <b><?php DUP_PRO_U::esc_html_e('UPLOAD FILE PROBLEM'); ?></b>
    </p>
    <p>
        <?php DUP_PRO_U::_e('Error message:'); ?>&nbsp;
        <b><span class="import-upload-error-message"><!-- here is set the message received from the server --></span></b>
    </p>
    <div><?php DUP_PRO_U::_e('Possible solutions:'); ?></div>
    <ul class="dup-pro-simple-style-list" >
        <li>
            <?php _e('If you are using P2P transfer function make sure the URL is a valid URL', 'duplicator-pro'); ?>
        </li>
        <li>
            <?php
                printf(
                    __('If you are using the upload function try to change the chunk size in <a href="%s">settings</a> and try again', 'duplicator-pro'),
                    'admin.php?page=duplicator-pro-settings&tab=import'
                );
                ?>
        </li>
        <li>
            <?php
                printf(
                    __('Upload the file via FTP/file manager to the "%s" folder and reload the page.', 'duplicator-pro'),
                    esc_html(DUPLICATOR_PRO_PATH_IMPORTS)
                );
                ?>
        </li>
    </ul>
</div>