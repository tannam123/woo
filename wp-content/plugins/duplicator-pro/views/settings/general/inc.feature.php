<?php
defined("ABSPATH") or die("");

DUP_PRO_U::hasCapability('manage_options');

$global = DUP_PRO_Global_Entity::get_instance();

$nonce_action    = 'duppro-settings-general-edit';
$action_updated  = null;
$action_response = DUP_PRO_U::__("Profile Settings Updated");
$dup_version     = DUPLICATOR_PRO_VERSION;

//SAVE RESULTS
if (isset($_REQUEST['action'])) {
    DUP_PRO_U::verifyNonce($_POST['_wpnonce'], $nonce_action);
    if ($_REQUEST['action'] == 'save') {
        $global->profile_idea = isset($_POST['_profile_idea']) ? 1 : 0;
        $global->profile_beta = isset($_POST['_profile_beta']) ? 1 : 0;
    }

    $action_updated = $global->save();
    $global->adjust_settings_for_system();
}

$import_tab_url   = 'admin.php?page=duplicator-pro-import';
$recovery_tab_url = 'admin.php?page=duplicator-pro-tools&tab=recovery';
$beta_features_url = 'admin.php?page=duplicator-pro-settings&tab=general&subtab=bfeathures';
?>

<style>
    sup.new-badge {background-color:maroon; border: maroon 1px solid; border-radius:8px; color:#fff; padding:1px 3px 2px 3px; margin:0; font-size:11px; line-height:11px; display:inline-block; font-style: normal}
    div.profile-type {font-size:18px !important; font-weight: bold; padding: 1px 0 5px 0}
    p.item {padding:0 0 15px 30px}
    div.lic-up {font-style: italic; padding: 3px 0 0 20px}
    b.sub-title {font-size:18px}
</style>

<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="dup-settings-action" name="action" value="save">
    <input type="hidden" name="page" value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
    <input type="hidden" name="tab" value="general">
    <input type="hidden" name="subtab" value="profile">

    <?php if ($action_updated) : ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
    <?php endif; ?>

    <!-- ===============================
     NEW FEATURES
    <!-- Uncomment and edit when we add more major features-->
    <table class="dup-pro-new-feathures" >       
        <thead>            
            <tr>
                <td colspan="2 ">
                    <div class="profile-type">
                        <?php _e("Recent Highlights", 'duplicator-pro'); ?>
                    </div>
                    <?php _e("The following are the new feature highlights from recent releases", 'duplicator-pro'); ?>
                </td>
            </tr>
        </thead>
        <tbody>
           <tr>
                <td class="icon" >
                    <i class="fas fa-arrow-alt-circle-down"></i>
                </td>
                <td>
                    <b class="sub-title"><?php _e('Import Features', 'duplicator-pro'); ?></b>
                    <ul>
                        <li>
                            <b><?php _e('Import from a link', 'duplicator-pro'); ?>:</b>
                            <?php
                                echo __('Import an archive directly from a URL link!', 'duplicator-pro');
                            ?>
                        </li>
                    </ul>

                </td>
            </tr>
            <tr>
                <td class="icon" >
                    <i class="fas fa-sitemap"></i>
                </td>
                <td>
                    <b class="sub-title"><?php _e('Multisite Features', 'duplicator-pro'); ?></b>
                    <ul>
                        <li>
                            <b><?php _e('Subsite to Multisite Import', 'duplicator-pro'); ?>:</b>
                            <?php
                                echo __('Import subsites from a multisite package into a multisite!  ', 'duplicator-pro');
                            ?>
                        </li>
                        <li>
                           <b><?php _e('Standalone to Multisite Import', 'duplicator-pro'); ?>:</b>
                            <?php
                                echo __('Import standalone site package into a multisite to add as a subsite!  ', 'duplicator-pro') .
                                '<div class="lic-up">' . __('Multisite Features require a Business or Gold license.', 'duplicator-pro') . '</div>';
                            ?>
                        </li>
                    </ul>
                </td>
            </tr>
        </tbody> 
    </table>
    <hr class="separator"/>
    <p>        
        <b><?php DUP_PRO_U::esc_html_e("Recent Changes"); ?></b>
        <br/>
        See <a class="dup-changelog-link" href="https://snapcreek.com/duplicator/docs/changelog/" target="_blank" >
            changelog
        </a> for complete list of new features and fixes in this release.  
    </p>



    <!-- ===============================
    RECENT FEATURES -->
    <!-- uncomment when have some <br/><hr class="separator"/>
    <label class="profile-type"><?php DUP_PRO_U::esc_html_e("Recent Features "); ?></label>
    <p class="item">
    <?php
    $storageLink = admin_url("admin.php?page=duplicator-pro-storage&tab=storage&inner_page=edit");
    $storageLink = wp_nonce_url($storageLink, 'storage-edit');
    echo wp_kses(DUP_PRO_U::__("<sup class='new-badge'>new</sup> <b>OneDrive for Business Support</b> - Use Microsoft OneDrive for Business to store and manage packages. Note:One Drive Personal support was already present."), array(
        'sup' => array(),
        'b'   => array(),
    ));
    echo '<br/> ';
    echo wp_kses(sprintf(DUP_PRO_U::__("<small>Go to <a href='%s'>Storage > Add New</a> to setup.</small>"), $storageLink), array(
        'small' => array(),
        'a'     => array('href'),
    ));
    ?>
    </p>-->
    <hr class="separator"/>
    <!-- ===============================
    FEATURE SURVEY -->
    <b><?php DUP_PRO_U::esc_html_e("Want a New Feature?") ?></b><br/>
    <?php
    echo '<a class="dup-prosurvey-link" target="blank" href="https://snapcreek.com/prosurvey" >' .
    DUP_PRO_U::__('Just answer this single question') . '</a>' . DUP_PRO_U::__(' to tell us what feature you want added!');

    // Remove this to enable the beta feathures section
    echo '</form>';
    return;
    ?>

    <!-- ===============================
    EXPERIMENTAL FEATURES -->
    <br/><hr class="separator"/>
    <!--<label class="profile-type"><?php DUP_PRO_U::esc_html_e("Experimental Features"); ?></label><br/>-->
    <label class="profile-type"><?php DUP_PRO_U::esc_html_e("Beta Features"); ?></label><br/>

    <?php
    //DUP_PRO_U::esc_html_e("Beta and Design Concepts sections let you preview upcoming features the Duplicator team is working on. Check the feature sections you would like to enable.");
    DUP_PRO_U::esc_html_e("Beta features are considered experimental and should not be enabled on production sites.");
    ?>
    <br/><br/>


    <div style="padding:0 0 0 30px">
        <!-- ================
        BETA -->
        <input type="checkbox" name="_profile_beta" id="_profile_beta" <?php echo DUP_PRO_UI::echoChecked($global->profile_beta); ?> />
        <label for="_profile_beta" class="profile-type"><?php DUP_PRO_U::esc_html_e("Enable"); ?></label>
        <i class="fas fa-question-circle fa-sm"
           data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Debug views"); ?>"
           data-tooltip="<?php
            DUP_PRO_U::esc_attr_e('Checking this checkbox will enable various beta features.  These features should NOT be used in production environments.  Please '
               . 'let us know your thoughts and report any issue encountered.  This will help to more quickly get the feature out of Beta.');
            ?>"></i>

        <p class="item">
            <?php
            $importURL = self_admin_url() . "admin.php?page=" . DUP_PRO_Constants::$TOOLS_SUBMENU_SLUG . '&tab=import';

            echo wp_kses(DUP_PRO_U::__("<b>Drag & Drop Install:</b> Overwrite a site by dragging a package into the plugin. No need to FTP a package!"), array(
                'b' => array(),
                ));
            echo '<br/>';
            echo wp_kses(sprintf(DUP_PRO_U::__("<small>Go to <a href='%s'>Tools > Import</a> to overwrite the current site. </small>"), $importURL), array(
                'a'     => array(
                    'href'  => array(),
                    'title' => array()
                ),
                'small' => array(),
                ));
            ?>
        </p>


        <br/>


    </div>
    <p class="submit" style="margin:5px 0px 0xp 5px;">
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::esc_attr_e('Save Feature Settings') ?>" style="display: inline-block;" />
    </p>

</form>
