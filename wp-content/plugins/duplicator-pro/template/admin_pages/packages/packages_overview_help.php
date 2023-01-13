<?php

/**
 * Duplicator package row in table packages list
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 * Variables
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 * @var \DUP_PRO_Package
 *
 */

defined("ABSPATH") or die("");
//$package = $tplData['package'];
$lang_wppathinfo  = __('This sites root path is:', 'duplicator-pro') . '<br/><i>' . duplicator_pro_get_home_path() . '</i>';
?>

<div class="dup-dlg-links-subtxt">
    <?php esc_html_e("Learn how Duplicator works in just a few minutes...", 'duplicator-pro'); ?>
</div>

<div id="dup-ovr-hlp-tabs" class="dup-tabs-flat">
   <div class="data-tabs">
        <a href="javascript:void(0)" class="tab active"><i class="fas fa-archive fa-fw"></i>  <?php _e('Package Overview', 'duplicator-pro'); ?></a>
        <a href="javascript:void(0)" class="tab"><i class="fas fa-link fa-fw"></i> <?php _e('Install Overview', 'duplicator-pro'); ?></a>
   </div>

    <!-- =================
    TAB1: OVERVIEW HELP -->
    <div class="data-panels">
        <div class="panel">

            <div id="dup-link-spinner-1" class="dup-spinner">

                <div class="area-left">
                    <i class="fas fa-chevron-circle-left area-arrow"></i>
                </div>

                <!-- DATA -->
                <div class="area-data">

                    <!-- SPIN-1: PACKAGE  -->
                    <div class="item active dup-spin-hlp">
                        <h3>
                            <i class="fas fa-archive fa-fw">
                            </i> <?php _e('What\'s a Package?', 'duplicator-pro'); ?>
                        </h3>
                        <small>
                            <?php _e('A package is a backup of your site and refers to these files:', 'duplicator-pro'); ?>
                        </small>
                        <br/><br/>

                        <div class="title">
                            <i class="far fa-file-archive fa-fw"></i> <?php _e('Archive File', 'duplicator-pro'); ?>
                            <i class="fas fa-question-circle"
                                data-tooltip-title="<?php _e("Archive File", 'duplicator-pro'); ?>"
                                data-tooltip="<?php _e('Archive files can be created in either .zip or .daf file formats. The Duplicator archive format (daf) '
                                    . 'is a custom format designed '
                                . 'for large sites on budget hosts', 'duplicator-pro'); ?>">
                            </i>
                        </div>
                        <?php _e(' The archive.zip/daf file contains your WordPress files and database', 'duplicator-pro'); ?>
                         <br/><br/>

                        <div class="title">
                            <i class="fas fa-bolt fa-fw"></i> <?php _e('Archive Installer', 'duplicator-pro'); ?>
                            <i class="fas fa-question-circle"
                                data-tooltip-title="<?php _e("Archive Installer", 'duplicator-pro'); ?>"
                                data-tooltip="<?php _e('In case you loose this file an exact copy of this file is also stored inside the archive '
                                . 'named installer-backup.php', 'duplicator-pro'); ?>"></i>
                        </div>
                        <?php _e('The installer.php file deploys the contents of the archive file.', 'duplicator-pro'); ?>
                    </div>

                    <!-- SPIN-2: Archive -->
                    <div class="item dup-spin-hlp">
                        <h3><i class="far fa-file-archive fa-fw"></i> <?php _e('What\'s in an Archive?', 'duplicator-pro'); ?></h3>
                        <small><?php _e('An archive file contains your WordPress site with the following assets:', 'duplicator-pro'); ?></small>
                        <br/><br/>

                        <div class="title">
                            <i class="fas fa-folder-open fa-fw"></i> <?php _e('Site Files', 'duplicator-pro'); ?>
                            <i class="fas fa-question-circle"
                                data-tooltip-title="<?php _e("WordPress Site Info", 'duplicator-pro'); ?>"
                                data-tooltip="<?php echo $lang_wppathinfo ?>"></i>
                        </div>
                        <?php _e('All WordPress core files, plugins, themes and files starting at the WordPress root folder.', 'duplicator-pro'); ?>
                        <br/><br/>

                        <div class="title">
                            <i class="fas fa-database fa-fw"></i> <?php _e('Database', 'duplicator-pro'); ?>
                        </div>
                        <?php _e('The database is stored in a single SQL file named database.sql.', 'duplicator-pro'); ?>
                        <br/><br/>
                        <small>
                              <?php _e('By default all files/database tables are included unless filters are set.', 'duplicator-pro'); ?>
                        </small>
                    </div>

                    <!-- SPIN-3: Installer -->
                    <div class="item dup-spin-hlp">
                        <h3><i class="fas fa-bolt fa-fw"></i> <?php _e('What\'s an Installer?', 'duplicator-pro'); ?></h3>
                        <small><?php _e('The installer is a PHP script that does the following:', 'duplicator-pro'); ?></small>
                        <br/><br/>

                        <div class="title">
                            <i class="fas fa-file-export fa-fw"></i> <?php _e('Exactss Archive Contents', 'duplicator-pro'); ?>
                        </div>
                        <?php _e('Helps to restores your WordPress files at a location of your choice.', 'duplicator-pro'); ?>
                        <br/><br/>

                        <div class="title">
                            <i class="fas fa-database fa-fw"></i> <?php _e('Installs the Database ', 'duplicator-pro'); ?>
                        </div>

                        <?php _e('Restores database and properly updates all URL/paths.', 'duplicator-pro'); ?>
                        <br/><br/>
                    </div>

                    <!-- SPIN-4: Installer Secure -->
                    <div class="item dup-spin-hlp">
                        <h3><i class="fas fa-bolt fa-fw"></i> <?php _e('What\'s a Secure Installer?', 'duplicator-pro'); ?></h3>
                        <small>
                            <?php _e('A secure installer keeps the location of your install process hidden.', 'duplicator-pro'); ?>
                        </small><br/>
                        <ol>
                            <li>
                                <b><i class="fas fa-lock-open fa-fw"></i> <?php _e('Unsecured', 'duplicator-pro'); ?></b><br/>
                                 <?php _e('An unsecured installer is named "installer.php".  This mode should only '
                                     . 'be used when outside users cannot access your server.', 'duplicator-pro'); ?>
                            </li>
                            <li>
                                 <b><i class="fas fa-lock fa-fw"></i> <?php _e('Secured', 'duplicator-pro'); ?></b><br/>
                                 <?php _e('A secure installer is named "[name]_[hash]_[date]_installer.php" and is only known by you. '
                                     . 'This keeps it safe from outside threats.', 'duplicator-pro'); ?>
                            </li>
                        </ol>
                        <br/><br/>

                         <div class="dup-ovr-continue">
                             <a href="javascript:void(0)" id="dup-ovr-next-exe">
                                 <?php _e('Next Section', 'duplicator-pro'); ?>
                                 <i class="fas fa-chevron-circle-right"></i>
                             </a>
                         </div>
                    </div>
                </div>

                <div class="area-right">
                    <i class="fas fa-chevron-circle-right"></i>
                </div>

               <!-- Progress -->
                <div class="area-nav">
                    <span class="num"></span>
                    <progress class="progress"></progress>
                </div>
            </div>
        </div>


        <!-- =================
        TAB-2: INSTALLER RESOURCES -->
        <div class="panel" data-panel="2">
            <div id="dup-link-spinner-2" class="dup-spinner">

                <div class="area-left">
                    <i class="fas fa-chevron-circle-left area-arrow"></i>
                </div>

                <!-- Data -->
                <div class="area-data">

                    <!-- SPIN-1:  INSTALL MODES -->
                    <div class="item dup-spin-hlp active">
                        <h3>
                            <i class="fas fa-bolt fa-fw"></i>
                            <?php echo sprintf('%s <b>%s</b>?', __('What are', 'duplicator-pro'), __('Install Modes', 'duplicator-pro')); ?>
                        </h3>
                        <small>
                            <?php _e('There are 3 standard install modes for site re-deployment.', 'duplicator-pro'); ?>
                        </small>
                        <br/><br/>

                        <div class="title">
                            <i class="fas fa-arrow-alt-circle-down fa-fw"></i>&nbsp;
                             <b><?php _e('Import Install', 'duplicator-pro'); ?></b><br/>
                        </div>
                        <?php _e('Drag-n-drop a file or link archive source to any destination', 'duplicator-pro'); ?>.
                        <br/><br/>

                        <div class="title">
                            <i class="far fa-window-close fa-fw"></i>&nbsp;
                            <b><?php _e('Overwrite Install', 'duplicator-pro'); ?></b><br/>
                        </div>
                        <?php _e('Quickly overwrite an existing WordPress site in a few clicks', 'duplicator-pro'); ?>.
                        <br/><br/>

                        <div class="title">
                            <i class="far fa-save fa-fw"></i>&nbsp;
                            <b><?php _e('Classic Install', 'duplicator-pro'); ?></b><br/>
                        </div>
                        <?php _e('Install to an empty server directory like a new WordPress install does', 'duplicator-pro'); ?>.<br/>
                    </div>


                    <!-- SPIN-2:  INSTALL RESOURCES -->
                    <div class="item dup-spin-hlp">
                        <h3>
                            <i class="fas fa-link fa-fw"></i>
                            <?php echo sprintf('%s <b>%s</b>?', __('What are', 'duplicator-pro'), __('Install Resources', 'duplicator-pro'));?>
                        </h3>
                        <small>
                            <?php _e('Install resources aid in deployment when using an install mode.', 'duplicator-pro'); ?>
                        </small>
                        <br/><br/>
                        
                        <div class="title">
                            <i class="far fa-file-archive fa-fw"></i> 
                                <?php _e('Archive File', 'duplicator-pro'); ?>
                                <i class="fas fa-question-circle"
                                    data-tooltip-title="<?php _e("Archive File", 'duplicator-pro'); ?>"
                                    data-tooltip="<?php _e(
                                        'An import install only requires the archive file and can be from many different remote locations',
                                        'duplicator-pro'
                                    );
                                                        ?>">
                                </i>
                        </div>
                        <ol style="margin-top:2px">
                            <li>
                                 <?php _e('Use <b><i class="far fa-copy fa-xs"></i> Copy Link</b> to run a remote import install.', 'duplicator-pro'); ?>
                            </li>
                            <li>
                                <?php _e('Use <b><i class="fas fa-download fa-xs"></i> Download</b> to run a classic/overwrite install.', 'duplicator-pro'); ?>
                            </li>
                        </ol>

                        <div class="title">
                            <i class="fas fa-bolt fa-fw"></i>
                            <?php _e('Archive Installer', 'duplicator-pro'); ?>
                            <i class="fas fa-question-circle"
                                data-tooltip-title="<?php _e("Archive Installer", 'duplicator-pro'); ?>"
                                data-tooltip="<?php _e(
                                    'Secure install names are complex, quickly copy the name to improve your workflow.',
                                    'duplicator-pro'
                                ); ?>">
                            </i>
                        </div>
                        <?php _e('The installer.php file is only used for overwrite/classic install modes.', 'duplicator-pro');?>
                    </div>


                    <!-- SPIN-3:  IMPORT INSTALL -->
                    <div class="item dup-spin-hlp">
                        <h3>
                            <i class="fas fa-download fa-fw"></i>
                            <?php echo sprintf('%s <b>%s</b>?', __('How to run ', 'duplicator-pro'), __('Import Install', 'duplicator-pro')); ?>
                        </h3>
                        <small>
                            <?php _e('This mode imports an archive into an existing WordPress site and overwrites it.', 'duplicator-pro'); ?>
                        </small> 
                        <br/><br/>

                        <div id="dup-ovr-hlp-vert-tabs-1" class="dup-tabs-vert">
                            <div class="data-tabs">
                                <div class="void"><i class="fab fa-wordpress-simple"></i>   <?php _e('Source Site', 'duplicator-pro'); ?></div>
                                <div class="tab active">1. <?php _e('Create Package', 'duplicator-pro'); ?></div>
                                <div class="tab">2. <?php _e('Choose Import', 'duplicator-pro'); ?></div>

                                <div class="void"><i class="fab fa-wordpress-simple"></i> <?php _e('Destination Site', 'duplicator-pro'); ?></div>
                                <div class="tab">3. <?php _e('Check WordPress', 'duplicator-pro'); ?></div>
                                <div class="tab">4. <?php _e('Import Archive', 'duplicator-pro'); ?></div>
                            </div>
                            <div class="data-panels dup-tabvert-hlp">
                                <div class="panel">
                                    <div class="title">
                                        <i class="fas fa-archive fa-fw"></i> <?php _e('Create a Package', 'duplicator-pro'); ?><br/>
                                        <small><?php _e('Pro ❯ Packages ❯ Create New', 'duplicator-pro'); ?></small>
                                    </div>
                                    <?php _e('On any WordPress site create a package', 'duplicator-pro'); ?>.<br/>
                                </div>

                                <div class="panel">
                                    <div class="title">
                                        <i class="fas fa-link fa-fw"></i> <?php _e('Choose Import Method', 'duplicator-pro'); ?> <br/>
                                         <small><?php _e('Pro ❯ Packages ❯ Package Overview', 'duplicator-pro'); ?></small>
                                    </div>
                                    <b>1. <?php _e('URL Import', 'duplicator-pro'); ?></b> <br/>
                                    <?php
                                        _e('Use', 'duplicator-pro');
                                        echo ' <i><i class="far fa-copy fa-xs"></i> ' . __('Copy Link', 'duplicator-pro') . '</i>&nbsp;';
                                        _e('to run import Link install', 'duplicator-pro');
                                    ?>.
                                    <br/><br/>

                                    <b>2. <?php _e('File Import', 'duplicator-pro'); ?></b> <br/>
                                    <?php
                                        _e('Use', 'duplicator-pro');
                                        echo ' <i><i class="fas fa-download fa-xs"></i> ' . __('Download', 'duplicator-pro') . '</i>&nbsp;';
                                        _e('to run import file install', 'duplicator-pro');
                                    ?>.
                                </div>
                                
                                <div class="panel">
                                    <div class="title">
                                        <?php _e('Install WordPress', 'duplicator-pro'); ?>
                                    </div>
                                    <?php _e('Install WordPress if not already installed', 'duplicator-pro'); ?>.<br/>
                                 <small>
                                     <?php _e('Most Hosting platforms have a one click WordPress install, this will be the quickest method to get '
                                         . 'WordPress on your host or have you host do it for you', 'duplicator-pro'); ?>&nbsp;.
                                 </small>
                                </div>
                                
                                <div class="panel">
                                    <div class="title">
                                        <?php _e('Import Archive ', 'duplicator-pro'); ?><br/>
                                        <small><?php _e('Pro ❯ Import ', 'duplicator-pro'); ?></small>
                                    </div>

                                     <b><?php _e('URL Import', 'duplicator-pro'); ?></b> <br/>
                                     <i><i class="far fa-copy fa-xs"></i> <?php _e('Paste Link', 'duplicator-pro'); ?></i> 
                                     <?php _e('from source site', 'duplicator-pro'); ?>.
                                     <br/><br/>

                                     <b><?php _e('File Import', 'duplicator-pro'); ?></b> <br/>
                                     <i><i class="fas fa-download fa-xs"></i> <?php _e('Drag-n-drop', 'duplicator-pro'); ?></i> 
                                     <?php _e('archive file from source site', 'duplicator-pro'); ?>.
                                </div>
                            </div>
                       </div>
                    </div>

                    <!-- SPIN-4:  OVERWRITE INSTALL -->
                    <div class="item dup-spin-hlp">
                        <h3>
                            <i class="far fa-window-close fa-fw"></i>
                            <?php echo sprintf('%s <b>%s</b>?', __('How to run ', 'duplicator-pro'), __('Overwrite/Classic Install', 'duplicator-pro')); ?>
                        </h3>

                        <ol>
                            <li>
                                <b><?php _e('Create package', 'duplicator-pro'); ?>:</b> <?php _e(' Create package on source site', 'duplicator-pro'); ?>.
                            </li>
                            <li>
                                <b><?php _e('Transfer package', 'duplicator-pro'); ?>:</b> 
                                <?php _e('Use FTP, cPanel or host utilities to copy package files to destination server', 'duplicator-pro'); ?>
                                <?php _e('where your existing WordPress site resides', 'duplicator-pro'); ?>.
                            </li>
                            <li>
                                <b><?php _e('Run Installer', 'duplicator-pro'); ?>:</b> 
                                <?php _e('In a web browser browse to the installer.php file on destination server', 'duplicator-pro'); ?>.
                            </li>
                        </ol>

                        <small>
                            <?php _e('The main difference between Overwrite and Classic modes is that during the install', 'duplicator-pro'); ?>
                            <?php _e('process the Database  inputs will be pre-filled from the existing wp-config.php settings', 'duplicator-pro'); ?>.
                        </small>
                    </div>

                    <!-- SPIN-6: MORE INFO -->
                    <div class="item dup-spin-hlp">
                        <h3>
                            <i class="fas fa-info-circle"></i>
                            <?php _e('More information', 'duplicator-pro'); ?>
                        </h3>
                        <small><?php _e('For additional detailed information checkout our online resources', 'duplicator-pro'); ?>.</small>
                        <br/>

                        <ul>
                             <li>
                                <i class='fa fa-home'></i>
                                <a href='<?php echo DUPLICATOR_PRO_DUPLICATOR_DOCS_URL; ?>' class='dup-knowledge-base' target='_sc-home'>
                                    <?php DUP_PRO_U::esc_html_e('Knowledge Base'); ?>
                                </a>
                            </li>
                            <li>
                                <i class='fa fa-book'></i>
                                <a href='<?php echo DUPLICATOR_PRO_USER_GUIDE_URL; ?>' class='dup-full-guide' target='_sc-guide'>
                                    <?php DUP_PRO_U::esc_html_e('Full User Guide'); ?>
                                </a>
                            </li>
                            <li>
                                <i class='far fa-file-code'></i>
                                <a href='<?php echo DUPLICATOR_PRO_TECH_FAQ_URL; ?>' class='dup-faqs' target='_sc-faq'>
                                    <?php DUP_PRO_U::esc_html_e('Technical FAQs'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="area-right">
                    <i class="fas fa-chevron-circle-right"></i>
                </div>

               <!-- Progress -->
                <div class="area-nav">
                    <span class="num"></span>
                    <progress class="progress"></progress>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {

    //INIT
    $("a#dup-ovr-next-exe").on("click", function() {
        $($('#dup-ovr-hlp-tabs div.data-tabs a.tab').get(1)).trigger("click");
    });

    Duplicator.UI.Ctrl.spinner('dup-link-spinner-1');
    Duplicator.UI.Ctrl.spinner('dup-link-spinner-2');
    Duplicator.UI.Ctrl.tabsFlat('dup-ovr-hlp-tabs');
    Duplicator.UI.Ctrl.tabsVert('dup-ovr-hlp-vert-tabs-1');

    //DEBUG Package Overview Area & Dialog
//    $('tr#dup-row-pack-id-905').next('tr').show();
//    setTimeout(function(){
//        DupPro.Pack.openLinkDetails();
//        $($('div#dup-ovr-hlp-tabs a.tab').get(1)).trigger("click");
//        $('div#dup-link-spinner-2 div.area-right').trigger("click");
//    }, 100);
});
</script>