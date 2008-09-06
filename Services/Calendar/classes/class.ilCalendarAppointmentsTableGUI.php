<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarAppointmentsTableGUI extends ilTable2GUI
{
	private $cat_id = 0;
	private $categories = null;
	private $is_editable = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj,$a_category_id)
	{
	 	global $lng,$ilCtrl;
	 	
	 	
	 	$this->categories = ilCalendarCategories::_getInstance();
	 	$this->cat_id = $a_category_id;
	 	$this->is_editable = $this->categories->isEditable($this->cat_id); 
	 	
	 	$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
	 	$this->ctrl = $ilCtrl;
	 	
		parent::__construct($a_parent_obj,'edit');
		$this->setFormName('appointments');
	 	$this->addColumn('','f',"1");
	 	$this->addColumn($this->lng->txt('title'),'title',"50%");
	 	$this->addColumn($this->lng->txt('cal_start'),'begin',"20%");
	 	$this->addColumn($this->lng->txt('cal_duration'),'duration',"20%");
	 	$this->addColumn($this->lng->txt('cal_recurrences'),'frequence',"10%");
	 	
	 	if($this->is_editable)
	 	{
	 		$this->addMultiCommand('askDeleteAppointments',$this->lng->txt('delete'));
			$this->enable('select_all');
	 	}
	 	else
	 	{
	 		$this->disable('select_all');
	 	}
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_appointment_row.html","Services/Calendar");
		$this->enable('sort');
		$this->enable('header');
		$this->enable('numinfo');
		
		$this->setDefaultOrderField('begin');
		
		$this->setSelectAllCheckbox('appointments');
		$this->setPrefix('apps');
	}
	
	
	/**
	 * fill row
	 *
	 * @access protected
	 * @param array set of data
	 * @return
	 */
	protected function fillRow($a_set)
	{
		global $ilUser;
		
		
		if($a_set['deletable'])
		{
			$this->tpl->setVariable('VAL_ID',$a_set['id']);
		}
		
		$this->tpl->setVariable('VAL_DESCRIPTION',$a_set['description']);
		
		$this->tpl->setVariable('VAL_TITLE_LINK',$a_set['title']);
		$this->ctrl->setParameterByClass('ilcalendarappointmentgui','app_id',$a_set['id']);
		$this->tpl->setVariable('VAL_LINK',$this->ctrl->getLinkTargetByClass('ilcalendarappointmentgui','edit'));

		switch($a_set['frequence'])
		{
			case IL_CAL_FREQ_DAILY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_daily'));
				break;
				
			case IL_CAL_FREQ_WEEKLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_weekly'));
				break;
			
			case IL_CAL_FREQ_MONTHLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_monthly'));
				break;
			
			case IL_CAL_FREQ_YEARLY:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_yearly'));
				break;
			
			default:
				$this->tpl->setVariable('VAL_FREQUENCE',$this->lng->txt('cal_no_recurrence'));
				break;	$this->addColumn($this->lng->txt('cal_duration'),'duration',"20%");
		}
		// TOD: Localization
		if($a_set['fullday'])
		{
			$start = new ilDate($a_set['begin'],IL_CAL_UNIX);
			$this->tpl->setVariable('VAL_BEGIN',ilDatePresentation::formatDate($start));
		}
		else
		{
			$start = new ilDateTime($a_set['begin'],IL_CAL_UNIX,'UTC');
			$this->tpl->setVariable('VAL_BEGIN',ilDatePresentation::formatDate($start));
		}
		if($a_set['duration'])
		{
			$this->tpl->setVariable('VAL_DURATION',ilFormat::_secondsToString($a_set['duration']));	
		}
		else
		{
			$this->tpl->setVariable('VAL_DURATION','');
		}
		
		
	}

	/**
	 * set appointments
	 *
	 * @access public
	 * @return
	 */
	public function setAppointments($a_apps)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarRecurrences.php');
		
			
		foreach($a_apps as $cal_entry_id)
		{
			$entry = new ilCalendarEntry($cal_entry_id);
 			$rec = ilCalendarRecurrences::_getFirstRecurrence($entry->getEntryId());
			
			$tmp_arr['id'] = $entry->getEntryId();
			$tmp_arr['title'] = $entry->getPresentationTitle();
			$tmp_arr['description'] = $entry->getDescription();
			$tmp_arr['fullday'] = $entry->isFullday();
 			$tmp_arr['begin'] = $entry->getStart()->get(IL_CAL_UNIX);
 			$tmp_arr['end'] = $entry->getEnd()->get(IL_CAL_UNIX);
 			$tmp_arr['duration'] = ($dur = $tmp_arr['end'] - $tmp_arr['begin']) ? $dur : 60 * 60 * 24;
 			
 			if($tmp_arr['fullday'])
 			{
 				$tmp_arr['duration'] += (60 * 60 * 24);
 			}

 			if(!$tmp_arr['fullday'] and $tmp_arr['end'] == $tmp_arr['begin'])
 			{
 				$tmp_arr['duration'] = '';
 			}
 			$tmp_arr['frequence'] = $rec->getFrequenceType();
 			$tmp_arr['deletable'] = (!$entry->isAutoGenerated() and $this->is_editable);
			
			$appointments[] = $tmp_arr;		
		}
		$this->setData($appointments ? $appointments : array());
	}
	
}
?>