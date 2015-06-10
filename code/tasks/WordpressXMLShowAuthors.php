<?php

class WordpressXMLShowAuthors extends BuildTask {

	protected $title = "Wordpress Import: Show Authors";

	protected $description = "Parse any WordpressXML files and show the author slugs that wordpress used";

	public function newline() {
		return (Director::is_cli()) ? "\n" : "<br />";
	}

	// controller action to be run by default
	public function run($request) {
		$files = WordpressXML::get();
		$count = $files->Count();
		if ($files->exists()) {
			foreach($files as $file) {
				if ($file->FileID) {
					if ($file->File()->getExtension() != 'xml') continue;
					echo 'Processing ' . $file->File()->getFilename() . $this->newline();
					$importer = Injector::inst()->get('WpImporter');
					$authors = $importer->showAuthors($file);
					foreach ($authors as $author => $count) {
						echo ' --  ' . $author . ' - ' . $count . $this->newline();
					}
				}
			}
		}
		echo 'Processed ' . $count . $this->newline();

	}
}