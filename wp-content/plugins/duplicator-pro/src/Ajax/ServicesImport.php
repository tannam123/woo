<?php

/**
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Ajax;

use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Ajax\FileTransfer\ImportUpload;
use Duplicator\Controllers\ImportPageController;
use Exception;

class ServicesImport extends AbstractAjaxService
{

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_pro_import_upload', 'importUpload');
        $this->addAjaxCall('wp_ajax_duplicator_pro_import_package_delete', 'deletePackage');
        $this->addAjaxCall('wp_ajax_duplicator_pro_import_set_view_mode', 'setViewMode');
        $this->addAjaxCall('wp_ajax_duplicator_pro_import_remote_download', 'remoteDownload');
    }

    /**
     * Import upload callback logic
     *
     * @return array
     */
    public static function importUploadCallback()
    {
        $uploader = new ImportUpload(ImportUpload::MODE_UPLOAD_LOCAL);
        return $uploader->exec();
    }

    /**
     * Import upload action
     *
     * @return void
     */
    public function importUpload()
    {
        AjaxWrapper::json(array(__CLASS__, 'importUploadCallback'), 'duplicator_pro_import_upload', $_POST['nonce'], 'import');
    }

    /**
     * Import download remote callback logic
     *
     * @return array
     */
    public static function remoteDownloadCallback()
    {
        $uploader = new ImportUpload(ImportUpload::MODE_DOWNLOAD_REMOTE);
        return $uploader->exec();
    }

    /**
     * Import download remote action
     *
     * @return void
     */
    public function remoteDownload()
    {
        AjaxWrapper::json(array(__CLASS__, 'remoteDownloadCallback'), 'duplicator_pro_remote_download', $_POST['nonce'], 'import');
    }

    /**
     * Import delete package callback
     *
     * @return bool
     */
    public static function deletePackageCallback()
    {
        $inputData = filter_input_array(INPUT_POST, array(
            'path' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => ''
                )
            ),
        ));

        if (empty($inputData['path'])) {
            throw new Exception(DUP_PRO_U::__("Invalid Request!"));
        }

        if (in_array($inputData['path'], DUP_PRO_Package_Importer::getArchiveList())) {
            if (unlink($inputData['path']) == false) {
                throw new Exception(DUP_PRO_U::__("Can\'t remove archvie!"));
            }
            DUP_PRO_Package_Importer::cleanFolder();
        }

        return true;
    }

    /**
     * Import delete backage action
     *
     * @return void
     */
    public function deletePackage()
    {
        AjaxWrapper::json(array(__CLASS__, 'deletePackageCallback'), 'duplicator_pro_import_package_delete', $_POST['nonce'], 'import');
    }

    /**
     * Set import view mode callback
     *
     * @return string
     */
    public static function setViewModeCallback()
    {
        $viewMode = filter_input(INPUT_POST, 'view_mode', FILTER_SANITIZE_SPECIAL_CHARS);

        switch ($viewMode) {
            case ImportPageController::VIEW_MODE_ADVANCED:
            case ImportPageController::VIEW_MODE_BASIC:
                break;
            default:
                throw new Exception(DUP_PRO_U::__('Invalid view mode'));
        }

        if (!($userId = get_current_user_id())) {
            throw new Exception(DUP_PRO_U::__('Invalid current urser id'));
        }

        $archives = DUP_PRO_Package_Importer::getArchiveList();
        if ($viewMode == ImportPageController::VIEW_MODE_BASIC && count($archives) > 1) {
            update_user_meta($userId, ImportPageController::USER_META_VIEW_MODE, ImportPageController::VIEW_MODE_ADVANCED);
            throw new Exception(
                __(
                    'It is not possible to set the view mode to basic if the number of packages is more than one. ' .
                    'Remove packages before performing this action.',
                    'duplicator-pro'
                )
            );
        }

        if ($viewMode != ImportPageController::getViewMode()) {
            if (update_user_meta($userId, ImportPageController::USER_META_VIEW_MODE, $viewMode) == false) {
                throw new Exception(DUP_PRO_U::__('Can\'t update user meta value'));
            }
        }

        return ImportPageController::getViewMode();
    }

    /**
     * Set import view mode action
     *
     * @return void
     */
    public function setViewMode()
    {
        AjaxWrapper::json(array(__CLASS__, 'setViewModeCallback'), 'duplicator_pro_import_set_view_mode', $_POST['nonce'], 'import');
    }
}
