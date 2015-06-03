<?php
require('WpParser.php');

/*
 * Decorates a BlogHolder page type, specified in _config.php
 */

class WpImporter {

	protected function getOrCreateComment($wordpressID) {
		if ($wordpressID && $comment = Comment::get()->filter(array('WordpressID' => $wordpressID))->first())
			return $comment;

		return Comment::create();
	}

	protected function importComments($post, $entry) {
		if (!class_exists('Comment'))
			return;

		$comments = $post['Comments'];
		foreach ($comments as $comment)
		{
			$page_comment = $this->getOrCreateComment($comment['WordpressID']);
			$page_comment->update($comment);
			$page_comment->ParentID = $entry->ID;
			$page_comment->write();
		}
	}

	protected function importTagsAndCategories($post, $entry) {
		if (!class_exists('Comment'))
			return;

		$tags = $post['Tags'];
		foreach ($tags as $tag) {
			$tagOb = BlogTag::get()->filter(array(
				'Title' => $tag,
				'BlogID' => $entry->ParentID
			));
			if ($tagOb->exists()) {
				$tagOb = $tagOb->First();
			} else {
				$tagOb = new BlogTag();
				$tagOb->BlogID = $entry->ParentID;
				$tagOb->Title = $tag;
				$tagOb->write();
			}
			$entry->Tags()->add($tagOb);
		}
		$categories = $post['Categories'];
		foreach ($categories as $category) {
			$catOb = BlogCategory::get()->filter(array(
				'Title' => $category,
				'BlogID' => $entry->ParentID
			));
			if ($catOb->exists()) {
				$catOb = $catOb->First();
			} else {
				$catOb = new BlogCategory();
				$catOb->BlogID = $entry->ParentID;
				$catOb->Title = $category;
				$catOb->write();
			}
			$entry->Categories()->add($catOb);
		}
	}

	protected function importAuthor($post, $entry) {
		$member = Member::get()->filter(array(
			'WordpressSlug' => $post['Author'],
		));
		if ($member->exists()) {
			$member = $member->First();
			$entry->Authors()->add($member);
		}
	}

	protected function getOrCreatePost($wordpressID) {
		if ($wordpressID) {
			$post = BlogPost::get()->filter(array('WordpressID' => $wordpressID));
			if ($post->exists()) {
				return $post->First();
			}
		}

		return BlogPost::create();
	}

	protected function importPost($post, $file) {
		Versioned::reading_stage('Stage');
		// create a blog entry
		$entry = $this->getOrCreatePost($post['WordpressID']);
		$entry->ParentID = $file->BlogID;

		// $posts array and $entry have the same key/field names
		// so we can use update here.

		$entry->update($post);
		$this->importAuthor($post, $entry);
		$this->importTagsAndCategories($post, $entry);

		$entry->write();
		//If the post was published on WP, now ensure it is also live in SS.
		if ($post['IsPublished']){
			$entry->publish("Stage", "Live");
		}

		$this->importComments($post, $entry);

		return $entry;
	}

	function newline() {
		return (Director::is_cli()) ? "\n" : "<br />";
	}

	public function process(WordpressXML $ob) {
		Versioned::reading_stage('Stage');
		// Checks if a file is uploaded
		$file = $ob->File();

		// Parse posts
		$wp = Injector::inst()->get('WpParser');
		$wp->setFileName(Director::baseFolder().'/'.$file->getFilename());
		$posts = $wp->parse();
		foreach ($posts as $post) {
			if ($ob->PostsAfter) {
				if(strtotime($ob->PostsAfter) < strtotime($post['Date'])) {
					$entry = $this->importPost($post, $ob);
					echo 'Imported '. $entry->Title . $this->newline();
				} else {
					echo 'Skipped '. $post['Title'] . $this->newline();
				}
			} else {
				$entry = $this->importPost($post, $ob);
				echo 'Imported '. $entry->Title . $this->newline();
			}
		}

		// print sucess message
		return true;
	}

}