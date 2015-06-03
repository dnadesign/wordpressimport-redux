<?php

class WordpressTagCleanup extends BuildTask {

	protected $title = "Wordpress Import: Tag clean up";

	protected $description = "Delete all tags that have no blog posts associated with them";

	public function init() {
		parent::init();

		if (!Permission::check('ADMIN'))
		{
			return Security::permissionFailure($this);
		}
	}

	// controller action to be run by default
	public function run($request) {
		$tags = BlogTag::get();
		$count = 0;
		foreach($tags as $tag) {
			if ($tag->BlogPosts()->Count() == 0) {
				$count++;
				$tag->delete();
			}
		}
		echo 'Deleted ' . $count . ' tags';
	}
}