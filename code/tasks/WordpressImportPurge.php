<?php

class WordpressImportPurge extends BuildTask {

	public function init() {
		parent::init();

		if (!Permission::check('ADMIN'))
		{
			return Security::permissionFailure($this);
		}
	}

	// controller action to be run by default
	public function run($request) {
		$posts = BlogPost::get()->filter(array('WordpressID:GreaterThan' => 0));
		$count = 0;
		foreach($posts as $post) {
			$count++;
			if (class_exists('Comments')) {
				foreach($post->AllComments() as $comment) {
					$comment->delete();
				}
			}

			foreach ($post->Tags() as $tag) {
				$post->Tags()->remove($tag);
			}
			foreach ($post->Categories() as $cat) {
				$post->Categories()->remove($cat);
			}
			foreach ($post->Authors() as $a) {
				$post->Authors()->remove($a);
			}
			$post->deleteFromStage('Stage');
			$post->deleteFromStage('Live');
		}
		echo 'Deleted ' . $count . ' posts';
	}
}