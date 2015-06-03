<?php
class WordpressImport extends DataExtension {

	public static $has_many = array(
		'XMLs' => 'WordpressXML',
	);

	public function updateCMSFields(FieldList $fields) {
		$xmlGrid = GridField::create(
			'XMLs',
			'XMLs',
			$this->owner->XMLs(),
			$gridFieldConfig = GridFieldConfig_RecordEditor::create()
		);
		$fields->addFieldsToTab('Root.Import', $xmlGrid);
	}
}