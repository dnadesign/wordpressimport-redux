<?php

class WordpressXMLProcess extends BuildTask {

	public function newline() {
		return (Director::is_cli()) ? "\n" : "<br />";
	}

	// controller action to be run by default
	public function run($request) {
		$files = WordpressXML::get()->filter(array('ProcessNow' => true));
		foreach($files as $file) {
			echo 'Processing ' . $file->File()->getFilename() . $this->newline();
			$importer = new WpImporter();
            $success = $importer->process($file);
            $file->ProcessingDate = date('Y-m-d H:i:s');
            $file->write();
		}
		echo 'Processed ' . $files->Count() . $this->newline();
	}
}