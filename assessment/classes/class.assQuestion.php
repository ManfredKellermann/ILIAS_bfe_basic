<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

require_once "./assessment/classes/class.assClozeTestGUI.php";
require_once "./assessment/classes/class.assImagemapQuestionGUI.php";
require_once "./assessment/classes/class.assJavaAppletGUI.php";
require_once "./assessment/classes/class.assMatchingQuestionGUI.php";
require_once "./assessment/classes/class.assMultipleChoiceGUI.php";
require_once "./assessment/classes/class.assOrderingQuestionGUI.php";
require_once "./assessment/classes/class.assTextQuestionGUI.php";
require_once "./content/classes/Pages/class.ilPageObject.php";

define("LIMIT_NO_LIMIT", 0);
define("LIMIT_TIME_ONLY", 1);

define("OUTPUT_HTML", 0);
define("OUTPUT_JAVASCRIPT", 1);

/**
* Basic class for all assessment question types
*
* The ASS_Question class defines and encapsulates basic methods and attributes
* for assessment question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assQuestion.php
* @modulegroup   Assessment
*/
class ASS_Question
{
	/**
	* Question id
	*
	* A unique question id
	*
	* @var integer
	*/
	var $id;

	/**
	* Question title
	*
	* A title string to describe the question
	*
	* @var string
	*/
	var $title;

	/**
	* Question comment
	*
	* A comment string to describe the question more detailed as the title
	*
	* @var string
	*/
	var $comment;

	/**
	* Question owner/creator
	*
	* A unique positive numerical ID which identifies the owner/creator of the question.
	* This can be a primary key from a database table for example.
	*
	* @var integer
	*/
	var $owner;

	/**
	* Contains the name of the author
	*
	* A text representation of the authors name. The name of the author must
	* not necessary be the name of the owner.
	*
	* @var string
	*/
	var $author;

	/**
	* Contains estimates working time on a question (HH MM SS)
	*
	* Contains estimates working time on a question (HH MM SS)
	*
	* @var array
	*/
	var $est_working_time;

	/**
	* Indicates whether the answers will be shuffled or not
	*
	* Indicates whether the answers will be shuffled or not
	*
	* @var array
	*/
	var $shuffle;

	/**
	* The database id of a test in which the question is contained
	*
	* The database id of a test in which the question is contained
	*
	* @var integer
	*/
	var $test_id;

	/**
	* Object id of the container object
	*
	* Object id of the container object
	*
	* @var double
	*/
	var $obj_id;

	/**
	* The reference to the ILIAS class
	*
	* The reference to the ILIAS class
	*
	* @var object
	*/
	var $ilias;

	/**
	* The reference to the Template class
	*
	* The reference to the Template class
	*
	* @var object
	*/
	var $tpl;

	/**
	* The reference to the Language class
	*
	* The reference to the Language class
	*
	* @var object
	*/
	var $lng;

	/**
	* The domxml representation of the question in qti
	*
	* The domxml representation of the question in qti
	*
	* @var object
	*/
	var $domxml;

	/**
	* Contains the output type of a question
	*
	* Contains the output type of a question
	*
	* @var integer
	*/
	var $outputType;

	var $suggested_solutions;
	/**
	* ASS_Question constructor
	*
	* The constructor takes possible arguments an creates an instance of the ASS_Question object.
	*
	* @param string $title A title string to describe the question
	* @param string $comment A comment string to describe the question
	* @param string $author A string containing the name of the questions author
	* @param integer $owner A numerical ID to identify the owner/creator
	* @access public
	*/
	function ASS_Question(
		$title = "",
		$comment = "",
		$author = "",
		$owner = -1
	)
	{
		global $ilias;
		global $lng;
		global $tpl;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		$this->title = $title;
		$this->comment = $comment;
		$this->author = $author;
		if (!$this->author)
		{
			$this->author = $this->ilias->account->fullname;
		}
		$this->owner = $owner;
		if ($this->owner == -1)
		{
			$this->owner = $this->ilias->account->id;
		}
		$this->id = -1;
		$this->test_id = -1;
		$this->suggested_solutions = array();
		$this->shuffle = 1;
		$this->setEstimatedWorkingTime(0,1,0);
		$this->outputType = OUTPUT_HTML;
		register_shutdown_function(array(&$this, '_ASS_Question'));
	}

	function _ASS_Question()
	{
		if (!empty($this->domxml))
		{
			$this->domxml->free();
		}
	}

	/**
	* Returns a QTI xml representation of the question
	*
	* Returns a QTI xml representation of the question and sets the internal
	* domxml variable with the DOM XML representation of the QTI xml representation
	*
	* @return string The QTI xml representation of the question
	* @access public
	*/
	function to_xml()
	{
		// to be implemented in the successor classes of ASS_Question
	}

	/**
	* Returns true, if a question is complete for use
	*
	* Returns true, if a question is complete for use
	*
	* @return boolean True, if the question is complete for use, otherwise false
	* @access public
	*/
	function isComplete()
	{
		return false;
	}

	/**
	* Returns TRUE if the question title exists in the database
	*
	* Returns TRUE if the question title exists in the database
	*
	* @param string $title The title of the question
	* @return boolean The result of the title check
	* @access public
	*/
	function questionTitleExists($title)
	{
		$query = sprintf("SELECT * FROM qpl_questions WHERE title = %s",
			$this->ilias->db->quote($title)
			);
		$result = $this->ilias->db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	* Sets the title string
	*
	* Sets the title string of the ASS_Question object
	*
	* @param string $title A title string to describe the question
	* @access public
	* @see $title
	*/
	function setTitle($title = "")
	{
		$this->title = $title;
	}

	/**
	* Sets the id
	*
	* Sets the id of the ASS_Question object
	*
	* @param integer $id A unique integer value
	* @access public
	* @see $id
	*/
	function setId($id = -1)
	{
		$this->id = $id;
	}

	/**
	* Sets the test id
	*
	* Sets the test id of the ASS_Question object
	*
	* @param integer $id A unique integer value
	* @access public
	* @see $test_id
	*/
	function setTestId($id = -1)
	{
		$this->test_id = $id;
	}

	/**
	* Sets the comment
	*
	* Sets the comment string of the ASS_Question object
	*
	* @param string $comment A comment string to describe the question
	* @access public
	* @see $comment
	*/
	function setComment($comment = "")
	{
		$this->comment = $comment;
	}

	/**
	* Sets the output type
	*
	* Sets the output type
	*
	* @param integer $outputType The output type of the question
	* @access public
	* @see $outputType
	*/
	function setOutputType($outputType = OUTPUT_HTML)
	{
		$this->outputType = $outputType;
	}


	/**
	* Sets the shuffle flag
	*
	* Sets the shuffle flag
	*
	* @param boolean $shuffle A flag indicating whether the answers are shuffled or not
	* @access public
	* @see $shuffle
	*/
	function setShuffle($shuffle = true)
	{
		if ($shuffle)
		{
			$this->shuffle = 1;
		}
			else
		{
			$this->shuffle = 0;
		}
	}

	/**
	* Sets the estimated working time of a question
	*
	* Sets the estimated working time of a question
	*
	* @param integer $hour Hour
	* @param integer $min Minutes
	* @param integer $sec Seconds
	* @access public
	* @see $comment
	*/
	function setEstimatedWorkingTime($hour=0, $min=0, $sec=0)
	{
		$this->est_working_time = array("h" => (int)$hour, "m" => (int)$min, "s" => (int)$sec);
	}

	/**
	* returns TRUE if the key occurs in an array
	*
	* returns TRUE if the key occurs in an array
	*
	* @param string $arraykey A key to an element in array
	* @param array $array An array to be searched
	* @access public
	*/
	function keyInArray($searchkey, $array)
	{
		if ($searchKey)
		{
			foreach ($array as $key => $value)
			{
				if (strcmp($key, $searchkey)==0)
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Sets the authors name
	*
	* Sets the authors name of the ASS_Question object
	*
	* @param string $author A string containing the name of the questions author
	* @access public
	* @see $author
	*/
	function setAuthor($author = "")
	{
		if (!$author)
		{
			$author = $this->ilias->account->fullname;
		}
		$this->author = $author;
	}

	/**
	* Sets the creator/owner
	*
	* Sets the creator/owner ID of the ASS_Question object
	*
	* @param integer $owner A numerical ID to identify the owner/creator
	* @access public
	* @see $owner
	*/
	function setOwner($owner = "")
	{
		$this->owner = $owner;
	}

	/**
	* Gets the title string
	*
	* Gets the title string of the ASS_Question object
	*
	* @return string The title string to describe the question
	* @access public
	* @see $title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Gets the id
	*
	* Gets the id of the ASS_Question object
	*
	* @return integer The id of the ASS_Question object
	* @access public
	* @see $id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Gets the shuffle flag
	*
	* Gets the shuffle flag
	*
	* @return boolean The shuffle flag
	* @access public
	* @see $shuffle
	*/
	function getShuffle()
	{
		return $this->shuffle;
	}

	/**
	* Gets the test id
	*
	* Gets the test id of the ASS_Question object
	*
	* @return integer The test id of the ASS_Question object
	* @access public
	* @see $test_id
	*/
	function getTestId()
	{
		return $this->test_id;
	}

	/**
	* Gets the comment
	*
	* Gets the comment string of the ASS_Question object
	*
	* @return string The comment string to describe the question
	* @access public
	* @see $comment
	*/
	function getComment()
	{
		return $this->comment;
	}

	/**
	* Gets the output type
	*
	* Gets the output type
	*
	* @return integer The output type of the question
	* @access public
	* @see $outputType
	*/
	function getOutputType()
	{
		return $this->outputType;
	}

	/**
	* Gets the estimated working time of a question
	*
	* Gets the estimated working time of a question
	*
	* @return array Estimated Working Time of a question
	* @access public
	* @see $est_working_time
	*/
	function getEstimatedWorkingTime()
	{
		if (!$this->est_working_time)
		{
			$this->est_working_time = array("h" => 0, "m" => 0, "s" => 0);
		}
		return $this->est_working_time;
	}

	/**
	* Gets the authors name
	*
	* Gets the authors name of the ASS_Question object
	*
	* @return string The string containing the name of the questions author
	* @access public
	* @see $author
	*/
	function getAuthor()
	{
		return $this->author;
	}

	/**
	* Gets the creator/owner
	*
	* Gets the creator/owner ID of the ASS_Question object
	*
	* @return integer The numerical ID to identify the owner/creator
	* @access public
	* @see $owner
	*/
	function getOwner()
	{
		return $this->owner;
	}

	/**
	* Get the object id of the container object
	*
	* Get the object id of the container object
	*
	* @return integer The object id of the container object
	* @access public
	* @see $obj_id
	*/
	function getObjId()
	{
		return $this->obj_id;
	}

	/**
	* Set the object id of the container object
	*
	* Set the object id of the container object
	*
	* @param integer $obj_id The object id of the container object
	* @access public
	* @see $obj_id
	*/
	function setObjId($obj_id = 0)
	{
		$this->obj_id = $obj_id;
	}

	/**
	* create page object of question
	*/
	function createPageObject()
	{
//		$qpl_id = ilObject::_lookupObjectId($this->getRefId());
		$qpl_id = $this->getObjId();

		$this->page = new ilPageObject("qpl", 0);
		$this->page->setId($this->getId());
		$this->page->setParentId($qpl_id);
		$this->page->setXMLContent("<PageObject><PageContent>".
			"<Question QRef=\"il__qst_".$this->getId()."\"/>".
			"</PageContent></PageObject>");
		$this->page->create();
	}

	/**
	* Insert the question into a test
	*
	* Insert the question into a test
	*
	* @param integer $test_id The database id of the test
	* @access private
	*/
	function insertIntoTest($test_id)
	{
		// get maximum sequence index in test
		$query = sprintf("SELECT MAX(sequence) AS seq FROM dum_test_question WHERE test_fi=%s",
			$this->ilias->db->quote($test_id)
			);
		$result = $this->ilias->db->query($query);
		$sequence = 1;
		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$sequence = $data->seq + 1;
		}
		$query = sprintf("INSERT INTO dum_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$this->ilias->db->quote($test_id),
			$this->ilias->db->quote($this->getId()),
			$this->ilias->db->quote($sequence)
			);
		$result = $this->ilias->db->query($query);
		if ($result != DB_OK)
		{
		// Fehlermeldung
		}
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public static
	*/
	function _getReachedPoints($user_id, $test_id)
	{
		return 0;
	}

	/**
	* Returns the points, a learner has reached answering the question
	*
	* Returns the points, a learner has reached answering the question
	*
	* @param integer $user_id The database ID of the learner
	* @param integer $test_id The database Id of the test containing the question
	* @access public
	*/
	function getReachedPoints($user_id, $test_id)
	{
		return 0;
	}

	/**
	* Returns the maximum points, a learner can reach answering the question
	*
	* Returns the maximum points, a learner can reach answering the question
	*
	* @access public
	* @see $points
	*/
	function getMaximumPoints()
	{
		return 0;
	}

	/**
	* Saves the learners input of the question to the database
	*
	* Saves the learners input of the question to the database
	*
	* @access public
	* @see $answers
	*/
	function saveWorkingData($limit_to = LIMIT_NO_LIMIT)
	{
	/*    global $ilias;
		$db =& $ilias->db;

		// Increase the number of tries for that question
		$query = sprintf("SELECT * FROM dum_assessment_solution_order WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
		$db->quote($this->ilias->account->id),
		$db->quote($_GET["test"]),
		$db->quote($this->getId())
		);
		$result = $db->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$query = sprintf("UPDATE dum_assessment_solution_order SET tries = %s WHERE solution_order_id = %s",
		$db->quote($data->tries + 1),
		$db->quote($data->solution_order_id)
		);
		$result = $db->query($query);
	*/
	}

	/**
	* Returns the image path for web accessable images of a question
	*
	* Returns the image path for web accessable images of a question.
	* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getJavaPath() {
		return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/java/";
	}

	/**
	* Returns the image path for web accessable images of a question
	*
	* Returns the image path for web accessable images of a question.
	* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getImagePath()
	{
		return CLIENT_WEB_DIR . "/assessment/$this->obj_id/$this->id/images/";
	}

	/**
	* Returns the web image path for web accessable java applets of a question
	*
	* Returns the web image path for web accessable java applets of a question.
	* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/java
	*
	* @access public
	*/
	function getJavaPathWeb()
	{
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/java/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	/**
	* Returns the web image path for web accessable images of a question
	*
	* Returns the web image path for web accessable images of a question.
	* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_QUESTION_POOL/ID_OF_QUESTION/images
	*
	* @access public
	*/
	function getImagePathWeb()
	{
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/$this->obj_id/$this->id/images/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

	/**
	* Loads solutions of the active user from the database an returns it
	*
	* Loads solutions of the active user from the database an returns it
	*
	* @param integer $test_id The database id of the test containing this question
	* @access public
	* @see $answers
	*/
	function &getSolutionValues($test_id, $ilUser)
	{
		global $ilDB;

		$db =& $ilDB->db;

		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
			$db->quote($ilUser->id),
			$db->quote($test_id),
			$db->quote($this->getId())
			);
		$result = $db->query($query);
		$values = array();
		while	($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($values, $row);
		}

		return $values;
	}

	/**
	* Checks whether the question is in use or not
	*
	* Checks whether the question is in use or not
	*
	* @return boolean The number of datasets which are affected by the use of the query.
	* @access public
	*/
	function isInUse()
	{
		$query = sprintf("SELECT COUNT(question_id) AS question_count FROM qpl_questions WHERE original_id = %s",
			$this->ilias->db->quote("$this->id")
			);
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->question_count;
	}

	/**
	* Removes all references to the question in executed tests in case the question has been changed
	*
	* Removes all references to the question in executed tests in case the question has been changed.
	* If a question was changed it cannot be guaranteed that the content and the meaning of the question
	* is the same as before. So we have to delete all already started or completed tests using that question.
	* Therefore we have to delete all references to that question in tst_solutions and the tst_active
	* entries which were created for the user and test in the tst_solutions entry.
	*
	* @access public
	*/
	function removeAllQuestionReferences($question_id = "")
	{
	/*
		if (!$question_id)
		{
			$question_id = $this->getId();
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi = %s", $this->ilias->db->quote("$question_id"));
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// Mark all tests containing this question as "not started"
			$querychange = sprintf("DELETE FROM tst_active WHERE user_fi = %s AND test_fi = %s",
				$this->ilias->db->quote("$result->user_fi"),
				$this->ilias->db->quote("$result->test_fi")
				);
			$changeresult = $this->ilias->db->query($querychange);
		}
		// delete all resultsets for this question
		$querydelete = sprintf("DELETE FROM tst_solutions WHERE question_fi = %s", $this->ilias->db->quote("$question_id"));
		$deleteresult = $this->ilias->db->query($querydelete);
		*/
	}

	/**
	* Shuffles the values of a given array
	*
	* Shuffles the values of a given array
	*
	* @param array $array An array which should be shuffled
	* @access public
	*/
	function pcArrayShuffle($array)
	{
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		if ($i > 0)
		{
			while(--$i)
			{
				$j = mt_rand(0, $i);
				if ($i != $j)
				{
					// swap elements
					$tmp = $array[$j];
					$array[$j] = $array[$i];
					$array[$i] = $tmp;
				}
			}
		}
		return $array;
	}

	/**
	* get question type for question id
	*
	* note: please don't use $this in this class to allow static calls
	*/
	function getQuestionTypeFromDb($question_id)
	{
		global $ilDB;

		$query = sprintf("SELECT qpl_question_type.type_tag FROM qpl_question_type, qpl_questions WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$ilDB->quote($question_id));

		$result = $ilDB->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);

		return $data->type_tag;
	}

	/**
	* Deletes a question from the database
	*
	* Deletes a question and all materials from the database
	*
	* @param integer $question_id The database id of the question
	* @access private
	*/
	function delete($question_id)
	{
		if ($question_id < 1)
		return;

		$query = sprintf("SELECT obj_fi FROM qpl_questions WHERE question_id = %s",
			$this->ilias->db->quote($question_id)
			);
    	$result = $this->ilias->db->query($query);
		if ($result->numRows() == 1)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$obj_id = $row["obj_fi"];
		}
		else
		{
			return;
		}

		$query = sprintf("DELETE FROM qpl_questions WHERE question_id = %s",
			$this->ilias->db->quote($question_id)
			);
		$result = $this->ilias->db->query($query);
		$query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
			$this->ilias->db->quote($question_id)
			);
		$result = $this->ilias->db->query($query);

		$this->removeAllQuestionReferences($question_id);

		// delete page object
		$page = new ilPageObject("qpl", $question_id);
		$page->delete();

		// delete the question in the tst_test_question table (list of test questions)
		$querydelete = sprintf("DELETE FROM tst_test_question WHERE question_fi = %s", $this->ilias->db->quote($question_id));
		$deleteresult = $this->ilias->db->query($querydelete);

		// delete suggested solutions contained in the question
		$querydelete = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s", $this->ilias->db->quote($question_id));
		$deleteresult = $this->ilias->db->query($querydelete);
				
		$directory = CLIENT_WEB_DIR . "/assessment/" . $obj_id . "/$question_id";
		if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
		}
	}

	/**
	* get total number of answers
	*/
	function getTotalAnswers()
	{
		return $this->_getTotalAnswers($this->id);
	}

	/**
	* get number of answers for question id (static)
	* note: do not use $this inside this method
	*
	* @param	int		$a_q_id		question id
	*/
	function _getTotalAnswers($a_q_id)
	{
		global $ilDB;

		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s",
			$ilDB->quote($a_q_id));

		$result = $ilDB->query($query);

		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}

		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi IN (%s) GROUP BY CONCAT(user_fi,test_fi)",
			join($found_id, ","));

		$result = $ilDB->query($query);

		return $result->numRows();
	}


	/**
	* get number of answers for question id (static)
	* note: do not use $this inside this method
	*
	* @param	int		$a_q_id		question id
	*/
	function _getTotalRightAnswers($a_q_id)
	{
		global $ilDB;
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE original_id = %s",
			$ilDB->quote($a_q_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 0)
		{
			return 0;
		}
		$found_id = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($found_id, $row->question_id);
		}
		$query = sprintf("SELECT * FROM tst_solutions WHERE question_fi IN (%s) GROUP BY CONCAT(user_fi,test_fi)",
			join($found_id, ",")
		);
		$result = $ilDB->query($query);
		$answers = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$question =& ASS_Question::_instanciateQuestion($row->question_fi);
			$reached = $question->getReachedPoints($row->user_fi, $row->test_fi);
			$max = $question->getMaximumPoints();
			array_push($answers, array("reached" => $reached, "max" => $max));
		}
		$max = 0.0;
		$reached = 0.0;
		foreach ($answers as $key => $value)
		{
			$max += $value["max"];
			$reached += $value["reached"];
		}
		if ($max > 0)
		{
			return $reached / $max;
		}
		else
		{
			return 0;
		}
	}

	function copyPageOfQuestion($a_q_id)
	{
		if ($a_q_id > 0)
		{
			$page = new ilPageObject("qpl", $a_q_id);

			$xml = str_replace("il__qst_".$a_q_id, "il__qst_".$this->id,
				$page->getXMLContent());

			$this->page->setXMLContent($xml);
			$this->page->updateFromXML();
		}
	}

	function getPageOfQuestion()
	{
		$page = new ilPageObject("qpl", $this->id);
		return $page->getXMLContent();
	}

/**
* Returns the question type of a question with a given id
* 
* Returns the question type of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question type string
* @access private
*/
  function _getQuestionType($question_id) {
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      return $data->type_tag;
    } else {
      return "";
    }
  }

/**
* Returns the question title of a question with a given id
* 
* Returns the question title of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question title
* @access private
*/
  function _getQuestionTitle($question_id) {
		global $ilDB;

    if ($question_id < 1)
      return "";

    $query = sprintf("SELECT title FROM qpl_questions WHERE qpl_questions.question_id = %s",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_ASSOC);
      return $data["title"];
    } else {
      return "";
    }
  }

/**
* Loads the question from the database
*
* Loads the question from the database
*
* @param integer $question_id A unique key which defines the question in the database
* @access public
*/
	function loadFromDb($question_id)
	{
		$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		$this->suggested_solutions = array();
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->suggested_solutions[$row["subquestion_index"]] = array(
					"internal_link" => $row["internal_link"],
					"import_id" => $row["import_id"]
				);
			}
		}
	}

	/**
	* Saves the question to the database
	*
	* Saves the question to the database
	*
	* @param integer $original_id
	* @access public
	*/
	function saveToDb($original_id = "")
	{
		require_once "./content/classes/Pages/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
		foreach ($this->suggested_solutions as $index => $solution)
		{
			$query = sprintf("INSERT INTO qpl_suggested_solutions (suggested_solution_id, question_fi, internal_link, import_id, subquestion_index, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getId() . ""),
				$this->ilias->db->quote($solution["internal_link"] . ""),
				$this->ilias->db->quote($solution["import_id"] . ""),
				$this->ilias->db->quote($index . "")
			);
			$this->ilias->db->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("qst", $this->getId(), $matches[2], $matches[3], $matches[1]);
			}
		}
	}
	
/**
* Deletes all suggestes solutions in the database
*
* Deletes all suggestes solutions in the database
*
* @access public
*/
	function deleteSuggestedSolutions()
	{
		// delete the links in the qpl_suggested_solutions table
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$this->ilias->db->quote($this->getId() . "")
		);
		$result = $this->ilias->db->query($query);
		// delete the links in the int_link table
		require_once "./content/classes/Pages/class.ilInternalLink.php";
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->getId());
	}
	
/**
* Returns a suggested solution for a given subquestion index
*
* Returns a suggested solution for a given subquestion index
*
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @return array A suggested solution array containing the internal link
* @access public
*/
	function getSuggestedSolution($subquestion_index = 0)
	{
		if (array_key_exists($subquestion_index, $this->suggested_solutions))
		{
			return $this->suggested_solutions[$subquestion_index];
		}
		else
		{
			return array();
		}
	}

/**
* Returns the title of a suggested solution at a given subquestion_index
*
* Returns the title of a suggested solution at a given subquestion_index.
* This can be usable for displaying suggested solutions
*
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @return string A string containing the type and title of the internal link
* @access public
*/
	function getSuggestedSolutionTitle($subquestion_index = 0)
	{
		if (array_key_exists($subquestion_index, $this->suggested_solutions))
		{
			$title = $this->suggested_solutions[$subquestion_index]["internal_link"];
			// TO DO: resolve internal link an get link type and title
		}
		else
		{
			$title = "";
		}
		return $title;
	}

/**
* Sets a suggested solution for the question
*
* Sets a suggested solution for the question.
* If there is more than one subquestion (i.e. close questions) may enter a subquestion index.
*
* @param string $solution_id An internal link pointing to the suggested solution
* @param integer $subquestion_index The index of a subquestion (i.e. a close test gap). Usually 0
* @param boolean $is_import A boolean indication that the internal link was imported from another ILIAS installation
* @access public
*/
	function setSuggestedSolution($solution_id = "", $subquestion_index = 0, $is_import = false)
	{
		if (strcmp($solution_id, "") != 0)
		{
			$import_id = "";
			if ($is_import)
			{
				$import_id = $solution_id;
				$solution_id = $this->_resolveInternalLink($import_id);
			}
			$this->suggested_solutions[$subquestion_index] = array(
				"internal_link" => $solution_id,
				"import_id" => $import_id
			);
		}
	}
	
	function _resolveInternalLink($internal_link)
	{
		if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches))
		{
			require_once "./content/classes/Pages/class.ilInternalLink.php";
			require_once "./content/classes/class.ilLMObject.php";
			require_once "./content/classes/class.ilGlossaryTerm.php";
			switch ($matches[2])
			{
				case "lm":
					$resolved_link = ilLMObject::_getIdForImportId($internal_link);
					break;
				case "pg":
					$resolved_link = ilInternalLink::_getIdForImportId("PageObject", $internal_link);
					break;
				case "st":
					$resolved_link = ilInternalLink::_getIdForImportId("StructureObject", $internal_link);
					break;
				case "git":
					$resolved_link = ilInternalLink::_getIdForImportId("GlossaryItem", $internal_link);
					break;
				case "mob":
					$resolved_link = ilInternalLink::_getIdForImportId("MediaObject", $internal_link);
					break;
			}
			if (strcmp($resolved_link, "") == 0)
			{
				$resolved_link = $internal_link;
			}
		}
		else
		{
			$resolved_link = $internal_link;
		}
		return $resolved_link;
	}
	
	function _resolveIntLinks($question_id)
	{
		global $ilDB;
		$resolvedlinks = 0;
		$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$internal_link = $row["internal_link"];
				$resolved_link = ASS_Question::_resolveInternalLink($internal_link);
				if (strcmp($internal_link, $resolved_link) != 0)
				{
					// internal link was resolved successfully
					$queryupdate = sprintf("UPDATE qpl_suggested_solutions SET internal_link = %s WHERE suggested_solution_id = %s",
						$ilDB->quote($resolved_link),
						$ilDB->quote($row["suggested_solution_id"] . "")
					);
					$updateresult = $ilDB->query($queryupdate);
					$resolvedlinks++;
				}
			}
		}
		if ($resolvedlinks)
		{
			// there are resolved links -> reenter theses links to the database

			// delete all internal links from the database
			require_once "./content/classes/Pages/class.ilInternalLink.php";
			ilInternalLink::_deleteAllLinksOfSource("qst", $question_id);

			$query = sprintf("SELECT * FROM qpl_suggested_solutions WHERE question_fi = %s",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches))
					{
						ilInternalLink::_saveLink("qst", $question_id, $matches[2], $matches[3], $matches[1]);
					}
				}
			}
		}
	}
	
	function _getInternalLinkHref($target = "")
	{
		global $ilDB;
		$linktypes = array(
			"lm" => "LearningModule",
			"pg" => "PageObject",
			"st" => "StructureObject",
			"git" => "GlossaryItem",
			"mob" => "MediaObject"
		);
		$href = "";
		if (preg_match("/il__(\w+)_(\d+)/", $target, $matches))
		{
			$type = $matches[1];
			$target_id = $matches[2];
			switch($linktypes[$matches[1]])
			{
				case "LearningModule":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "PageObject":
				case "StructureObject":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "GlossaryItem":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) ."/goto.php?target=" . $type . "_" . $target_id;
					break;
				case "MediaObject":
					$href = ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/content/lm_presentation.php?obj_type=" . $linktypes[$type] . "&cmd=media&ref_id=".$_GET["ref_id"]."&mob_id=".$target_id;
					break;
			}
		}
		return $href;
	}
	
/**
* Returns the original id of a question
*
* Returns the original id of a question
*
* @param integer $question_id The database id of the question
* @return integer The database id of the original question
* @access public
*/
	function _getOriginalId($question_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["original_id"] > 0)
			{
				return $row["original_id"];
			}
			else
			{
				return $row["question_id"];
			}
		}
		else
		{
			return "";
		}
	}

	function syncWithOriginal()
	{
		require_once "./content/classes/Pages/class.ilInternalLink.php";
		$query = sprintf("DELETE FROM qpl_suggested_solutions WHERE question_fi = %s",
			$this->ilias->db->quote($this->original_id . "")
		);
		$result = $this->ilias->db->query($query);
		ilInternalLink::_deleteAllLinksOfSource("qst", $this->original_id);
		foreach ($this->suggested_solutions as $index => $solution)
		{
			$query = sprintf("INSERT INTO qpl_suggested_solutions (suggested_solution_id, question_fi, internal_link, import_id, subquestion_index, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->original_id . ""),
				$this->ilias->db->quote($solution["internal_link"] . ""),
				$this->ilias->db->quote($solution["import_id"] . ""),
				$this->ilias->db->quote($index . "")
			);
			$this->ilias->db->query($query);
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution["internal_link"], $matches))
			{
				ilInternalLink::_saveLink("qst", $this->original_id, $matches[2], $matches[3], $matches[1]);
			}
		}
	}

	function createRandomSolution($test_id, $user_id)
	{
	}

/**
* Returns true if the question already exists in the database
*
* Returns true if the question already exists in the database
*
* @param integer $question_id The database id of the question
* @result boolean True, if the question exists, otherwise False
* @access public
*/
	function _questionExists($question_id)
	{
		global $ilDB;

		if ($question_id < 1)
		{
			return false;
		}
		
		$query = sprintf("SELECT question_id FROM qpl_questions WHERE question_id = %s",
			$ilDB->quote($question_id)
		);
    $result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

/**
* Creates an instance of a question with a given question id
*
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id) 
	{
		if (strcmp($question_id, "") != 0)
		{
			$question_type = ASS_Question::_getQuestionType($question_id);
			switch ($question_type) {
				case "qt_cloze":
					$question = new ASS_ClozeTest();
					break;
				case "qt_matching":
					$question = new ASS_MatchingQuestion();
					break;
				case "qt_ordering":
					$question = new ASS_OrderingQuestion();
					break;
				case "qt_imagemap":
					$question = new ASS_ImagemapQuestion();
					break;
				case "qt_multiple_choice_sr":
				case "qt_multiple_choice_mr":
					$question = new ASS_MultipleChoice();
					break;
				case "qt_javaapplet":
					$question = new ASS_JavaApplet();
					break;
				case "qt_text":
					$question = new ASS_TextQuestion();
					break;
			}
			$question->loadFromDb($question_id);
			return $question;
		}
  }

}

?>
