<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for exercise verification
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjExerciseVerificationGUI: ilWorkspaceAccessGUI
 */
class ilObjExerciseVerificationGUI extends ilObject2GUI
{
    public function getType() : string
    {
        return "excv";
    }

    public function create() : void
    {
        $ilTabs = $this->tabs_gui;

        $this->lng->loadLanguageModule("excv");

        $ilTabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "cancel")
        );

        $table = new ilExerciseVerificationTableGUI($this, "create");
        $this->tpl->setContent($table->getHTML());
    }

    public function save() : void
    {
        global $DIC;

        $ilUser = $this->user;

        $objectId = $this->getRequestValue("exc_id");
        if ($objectId) {
            $certificateVerificationFileService = new ilCertificateVerificationFileService(
                $DIC->language(),
                $DIC->database(),
                $DIC->logger()->root(),
                new ilCertificateVerificationClassMap()
            );

            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificatePresentation = $userCertificateRepository->fetchActiveCertificateForPresentation(
                (int) $ilUser->getId(),
                (int) $objectId
            );

            $newObj = null;
            try {
                $newObj = $certificateVerificationFileService->createFile($userCertificatePresentation);
            } catch (\Exception $exception) {
                ilUtil::sendFailure($this->lng->txt('error_creating_certificate_pdf'));
                $this->create();
            }

            if ($newObj) {
                $parent_id = $this->node_id;
                $this->node_id = null;
                $this->putObjectInTree($newObj, $parent_id);

                $this->afterSave($newObj);
            } else {
                ilUtil::sendFailure($this->lng->txt("msg_failed"));
            }
        } else {
            ilUtil::sendFailure($this->lng->txt("select_one"));
        }
        $this->create();
    }

    public function deliver() : void
    {
        $file = $this->object->getFilePath();
        if ($file) {
            ilUtil::deliverFile($file, $this->object->getTitle() . ".pdf");
        }
    }

    public function render(bool $a_return = false, string $a_url = '') : string
    {
        $ilUser = $this->user;
        $lng = $this->lng;

        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());

            $caption = $lng->txt("wsp_type_excv") . ' "' . $this->object->getTitle() . '"';

            $valid = true;
            $message = '';
            if (!file_exists($this->object->getFilePath())) {
                $valid = false;
                $message = $lng->txt("url_not_found");
            } elseif (!$a_url) {
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if (!$access_handler->checkAccess("read", "", $wsp_id)) {
                    $valid = false;
                    $message = $lng->txt("permission_denied");
                }
            }

            if ($valid) {
                if (!$a_url) {
                    $a_url = $this->getAccessHandler()->getGotoLink($wsp_id, $this->object->getId());
                }
                return '<div><a href="' . $a_url . '">' . $caption . '</a></div>';
            }

            return '<div>' . $caption . ' (' . $message . ')</div>';
        }

        return "";
    }

    public function downloadFromPortfolioPage(ilPortfolioPage $a_page) : void
    {
        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }

        throw new ilExerciseException($this->lng->txt('permission_denied'));
    }

    public static function _goto(string $a_target) : void
    {
        $id = explode("_", $a_target);

        $_GET["baseClass"] = "ilsharedresourceGUI";
        $_GET["wsp_id"] = $id[0];
        include("ilias.php");
        exit;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    protected function getRequestValue(string $key, $default = null)
    {
        if (isset($this->request->getQueryParams()[$key])) {
            return $this->request->getQueryParams()[$key];
        }

        if (isset($this->request->getParsedBody()[$key])) {
            return $this->request->getParsedBody()[$key];
        }

        return $default ?? null;
    }
}
