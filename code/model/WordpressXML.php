<?php

class WordpressXML extends DataObject {

	protected $uploadField;

	private static $db = array(
		'Name' => 'Varchar(255)',
		'UploadingDate' => 'SS_DateTime',
		'ProcessNow' => 'Boolean',
		'ProcessingDate' => 'SS_DateTime',
		'PostsAfter' => 'Date'
	);

	private static $has_one = array(
		'Blog' => 'Blog',
		'File' => 'File',
	);

	private static $singular_name = 'XML';

	private static $summary_fields = array(
		'Name' => 'Name',
		'getDate' => 'Date',
		'PostsAfter' => 'Posts After',
		'getProcessed' => 'Processed'
	);

	public function getCMSValidator() {
		return RequiredFields::create('File');
	}

	public function getCMSFields() {
		$fields = $fields = FieldList::create(
     			TabSet::create('Root',
    				Tab::create('Main')));

		$uploadField = UploadField::create('File', 'File');
		$uploadField->getValidator()->setAllowedMaxFileSize((30 * 1024 * 1024));
		$uploadField->setAllowedExtensions(array('xml', 'XML'));
		$uploadField->setAllowedMaxFileNumber(1);
		$uploadField->setFolderName('Uploads/blog/');
		$fields->addFieldToTab('Root.Main', $uploadField);

		$fields->addFieldToTab('Root.Main', $postsAfter = new DateField('PostsAfter', 'Import posts after:'));
		$postsAfter->setRightTitle('(Jan 1, YYYY) Leave blank if all posts should be imported');

		$fields->addFieldToTab('Root.Main', new CheckboxField('ProcessNow'));

		$this->uploadField = $uploadField;

		return $fields;
	}

	public function onBeforeWrite() {
		if ($this->FileID) {
			$file = File::get()->byId($this->FileID);
			$this->Name = $file->Name;
			$this->UploadingDate = date('Y-m-d H:i:s');
		}

		parent::onBeforeWrite();
	}

	public function onBeforeDelete() {
		// Delete File
		if ($this->File() && $this->File()->exists()) {
			$this->File()->delete();
		}

		parent::onBeforeDelete();
	}

	public function getDate() {
		return $this->dbObject('UploadingDate')->Nice();
	}

	public function getProcessed() {
		if ($this->ProcessNow) {
			return 'Pending...';
		} else if ($this->ProcessingDate) {
			return $this->ProcessingDate;
		}
		return 'Waiting';
	}

}