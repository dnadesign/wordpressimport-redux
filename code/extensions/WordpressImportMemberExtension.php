<?php

/**
 * This class is responsible for adding Wordpress Slug to members
 * so that we can assign the correct member as an author to imported
 * posts.
 *
 * @package silverstripe
 * @subpackage WordpressImport
 */
class WordpressImportMemberExtension extends DataExtension {
	/**
	 * @var array
	 */
	private static $db = array(
		'WordpressSlug' => 'Varchar',
	);

	/**
	 * {@inheritdoc}
	 */
	public function updateCMSFields(FieldList $fields) {

	}
}
