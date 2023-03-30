<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class ilStudyProgrammeBlockGUI extends ilDashboardBlockGUI
{
    protected ?string $visible_on_pd_mode = null;

    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_MY_STUDYPROGRAMME
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        return '';
    }

    public function initData(): void
    {
        $user_table =  ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserTable'];
        $user_table->disablePermissionCheck(true);
        $rows = $user_table->fetchSingleUserRootAssignments($this->user->getId());
        $items = [];
        foreach ($rows as $row) {
            $prg = ilObjStudyProgramme::getInstanceByObjId($row->getNodeId());
            if (!$this->isReadable($prg) || !$prg->isActive()) {
                continue;
            }

            $min = $row->getPointsRequired();
            $max = $row->getPointsReachable();
            $cur = $row->getPointsCurrent();
            $required_string = $min;
            if ((float)$max < (float)$min) {
                $required_string .= ' ' . $this->lng->txt('prg_dash_label_unreachable') . ' (' . $max . ')';
            }

            $properties = [
                [$this->lng->txt('prg_dash_label_minimum') => $required_string],
                [$this->lng->txt('prg_dash_label_gain') => $cur],
                [$this->lng->txt('prg_dash_label_status') => $row->getStatus()],
            ];

            if (in_array(
                $row->getStatusRaw(),
                [ilPRGProgress::STATUS_COMPLETED, ilPRGProgress::STATUS_ACCREDITED],
                true
            )) {
                $validity = $row->getExpiryDate() ?: $row->getValidity();
                $properties[] = [$this->lng->txt('prg_dash_label_valid') => $validity];
            } else {
                $properties[] = [$this->lng->txt('prg_dash_label_finish_until') =>$row->getDeadline()];
            }

            $validator = new ilCertificateDownloadValidator();
            if ($validator->isCertificateDownloadable($row->getUsrId(), $row->getNodeId())) {
                $cert_url = "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $prg->getRefId() . "&cmd=deliverCertificate";
                $cert_link = $this->factory->link()->standard($this->lng->txt('download_certificate'), $cert_url);
                $properties[] = [$this->lng->txt('certificate') => $this->renderer->render($cert_link)];
            }

            $items[] = [
                'title' => $prg->getTitle(),
                'description' => $prg->getDescription(),
                'properties' => $properties,
                'ref_id' => $prg->getRefId(),
                'obj_id' => $prg->getId(),
                'type' => $prg->getType(),
                'obj' => $prg,
                'start' => null,
                'end' => null,
            ];
        }

        $this->setData(['' => $items]);
    }

    protected function isReadable(ilObjStudyProgramme $prg): bool
    {
        if ($this->getVisibleOnPDMode() === ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
            return true;
        }
        return $this->access->checkAccess('read', "", $prg->getRefId(), "prg", $prg->getId());
    }

    protected function getVisibleOnPDMode(): string
    {
        if (is_null($this->visible_on_pd_mode)) {
            $this->visible_on_pd_mode =
                $this->settings->get(
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD,
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ
                );
        }
        return $this->visible_on_pd_mode;
    }

    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $item_gui = $this->byType($data['type']);
        $item_gui->initItem(
            $data['ref_id'],
            $data['obj_id'],
            $data['type'],
            $data['title'],
            $data['description'],
        );
        $item_gui->enableCommands(true, false);
        $item_gui->insertCommands();
        $current_item_selection_list = $item_gui->getCurrentSelectionList();
        if ($current_item_selection_list instanceof ilAdvancedSelectionListGUI) {
            $commands = $current_item_selection_list->getItems();
        } else {
            $commands = [];
        }

        $commands = array_map(
            function ($command) {
                return $this->factory->button()->shy(
                    $command['title'],
                    $command['link']
                );
            },
            $commands
        );
        $prg = $data['obj'];
        $properties = $data['properties'];
        $title = $prg->getTitle();
        $link = $this->getDefaultTargetUrl($prg->getRefId());
        $title_btn = $this->factory->button()->shy($title, $link);
        $description = $prg->getLongDescription() ?? "";
        $max = (int) $this->settings->get("rep_shorten_description_length");
        if ($max !== 0 && $this->settings->get("rep_shorten_description")) {
            $description = ilStr::shortenTextExtended($description, $max, true);
        }

        $icon = $this->factory->symbol()->icon()->standard('prg', $title, 'medium');
        return $this->factory->item()->standard($title_btn)
                             ->withProperties(array_merge(...$properties))
                             ->withDescription($description)
                             ->withLeadIcon($icon)
                            ->withActions(
                                $this->factory->dropdown()->standard($commands)
                            );
    }

    protected function getDefaultTargetUrl(int $prg_ref_id): string
    {
        $this->ctrl->setParameterByClass(
            ilObjStudyProgrammeGUI::class,
            'ref_id',
            $prg_ref_id
        );
        $link = $this->ctrl->getLinkTargetByClass(
            [
                ilRepositoryGUI::class,
                ilObjStudyProgrammeGUI::class,
            ]
        );
        $this->ctrl->setParameterByClass(
            ilObjStudyProgrammeGUI::class,
            'ref_id',
            null
        );
        return $link;
    }

    public function getBlockType(): string
    {
        return 'pdprg';
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, mixed $ref_id): void
    {
        return;
    }
}
