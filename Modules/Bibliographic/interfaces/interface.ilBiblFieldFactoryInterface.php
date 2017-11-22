<?php

/**
 * Interface ilBiblFieldFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldFactoryInterface {

	/**
	 * @param int        $type MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
	 *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
	 * @param     string $identifier
	 *
	 * @throws \ilException if a wrong $type is passed or field is not found
	 *
	 * @return \ilBiblFieldInterface
	 */
	public function getFieldByTypeAndIdentifier($type, $identifier);


	/**
	 * @param int        $type MUST be ilBiblTypeFactoryInterface::DATA_TYPE_RIS or
	 *                         ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX
	 * @param     string $identifier
	 *
	 * @throws \ilException if a wrong $type is passed
	 *
	 * @return \ilBiblFieldInterface
	 */
	public function findOrCreateFieldByTypeAndIdentifier($type, $identifier);


	/**
	 * @param int $obj_id
	 *
	 * @return ilBiblFieldInterface[] instances of all known standard-fields for the given type
	 */
	public function getAvailableFieldsForObjId($obj_id);


	/**
	 * @param int $obj_id
	 *
	 * @return string
	 */
	public function getBiblAttributeById($obj_id);


	/**
	 * @param int $id
	 *
	 * @return int affected rows
	 */
	public function deleteBiblAttributeById($id);

	/**
	 * @param int $data_type
	 *
	 * @return array of il_bibl_attribute record data
	 */
	public function getAllAttributeNamesByDataType($data_type);

	/**
	 * @param string $identifier
	 *
	 * @return array of il_bibl_attribute record data
	 */
	public function getAllAttributeNamesByIdentifier($identifier);


	/**
	 * @param int $obj_id
	 *
	 * @return array of il_bibl_attribute record data
	 */
	public function getAttributeNameAndFileName($obj_id);


	/**
	 * @return \ilBiblTypeInterface
	 */
	public function getType();


	/**
	 * @param int $id
	 *
	 * @return \ilBiblFieldInterface
	 */
	public function findById($id);

	/**
	 * checks if a ilBiblField Entry for the il_bibl_attribute exists
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function hasIlBiblFieldEntry($name);

	/**
	 * creates ilBiblField Entry for the il_bibl_attribute
	 *
	 * @param array $il_bibl_attribute
	 *
	 * @return boolean
	 */
	public function createIlBiblFieldForIlBiblAttribute($il_bibl_attribute);

	/**
	 * @param integer $id
	 *
	 * @return array ilBiblData Record
	 */
	public function getilBiblDataById($id);
}