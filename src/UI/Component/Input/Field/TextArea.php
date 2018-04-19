<?php

/* Copyright (c) 2017 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes textarea inputs.
 */
interface TextArea extends Input {

	/**
	 * set maximum number of characters
	 * @param $max_limit integer
	 */
	public function withMaxLimit($max_limit);

	/**
	 * get maximum limit of characters
	 * @return mixed
	 */
	public function getMaxLimit();

	/**
	 * set minimum number of characters
	 * @param $min_limit integer
	 */
	public function withMinLimit($min_limit);

	/**
	 * get minimum limit of characters
	 * @return mixed
	 */
	public function getMinLimit();
}
