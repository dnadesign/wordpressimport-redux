<?php

class WordpressXMLTest extends BuildTask {

	public function newline() {
		return (Director::is_cli()) ? "\n" : "<br />";
	}

	// controller action to be run by default
	public function run($request) {
		$files = WordpressXML::get();
		foreach($files as $file) {
			if ($file->File()->exists()) {
				echo 'Testing ' . $file->File()->getFilename() . $this->newline();
				$fileContents = file_get_contents(Director::baseFolder().'/'.$file->File()->getFilename());
				$dom = new DOMDocument;
				libxml_use_internal_errors(TRUE);
				$dom->loadXML($fileContents);
				libxml_use_internal_errors(FALSE);
				$dom->formatOutput = TRUE;
				$this->simple_xml = simplexml_load_string($dom->saveXML(), 'SimpleXMLElement');
			} else {
				echo 'No file attached for ' . $file->ID;
			}
		}
	}
}