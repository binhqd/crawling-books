<?php
namespace Mining;
error_reporting(E_ALL);
// call library
include_once ("simple_html_dom.php");

/**
 * 
 * @author Binhqd
 * @email binhqd@gmail.com
 * 
 */
class MiningComponent {
	const CALLS = 1;
	const PUTS = 2;
	
	/**
	 * Define data schema
	 * @var static $fields
	 */
	public static $fields = array(
		
	);
	
	/**
	 * This method is used to return directory for cache
	 * @return string
	 */
	public static function cacheDir() {
		$dir = realpath(dirname(__FILE__) . "/../cache");
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		return $dir;
	}
	
	/**
	 * This method is used to get all books
	 * @param string $url
	 */
	public function getBooks($url) {
		$html = file_get_html ( $url );
		
		// first table
		$table = $html->find ( 'table', 0 );
		
		$data = array(
			'oldTesaments'	=> array(),
			'newTesaments'	=> array(),
		);
		// get old testaments
		$oldTesaments = $table->find( 'table', 0);
		
		foreach ( $oldTesaments->find ( 'a' ) as $link ) {
			$text = $link->plaintext;
			$href = $link->getAttribute('href');
			
			$data['oldTesaments'][] = array(
				'text'	=> $text,
				'href'	=> $href
			);
			
			// create folder
			$bookFolder = dirname(__FILE__) . "/../books/{$text}";
			if (!is_dir($bookFolder)) {
				mkdir($bookFolder, 755, true);
			}
		}
		
		$newTesaments = $table->find( 'table', 1);
		foreach ( $newTesaments->find ( 'a' ) as $link ) {
			$text = $link->plaintext;
			$href = $link->getAttribute('href');
			
			if (empty($text) || empty($href)) continue;
			
			$data['newTesaments'][] = array(
				'text'	=> $text,
				'href'	=> $href
			);
		}
		
		// cache book index
		file_put_contents( dirname(__FILE__) . "/../books/index.txt", serialize($data));
		
		return $data;
	}
	
	/**
	 * This method is used to get all chapter of a book
	 * @param string $url
	 * @return array $chapters
	 */
	public function getChapters($url) {
		$html = file_get_html ( $url );
		
		// Read book info
		$bookData = $html->find('.crumbtrail b', 0);
		$bookName = $bookData->plaintext;
		
		// first table
		$container = $html->find ( 'table', 0 );
		
		$chapters = array();
		
		// get old testaments
		$chaptersContainer = $container->find( 'table', 0);
		foreach ( $chaptersContainer->find ( 'a' ) as $link ) {
			$text = $link->plaintext;
			$href = $link->getAttribute('href');
			
			$chapters[] = array(
				'text'	=> $text,
				'href'	=> $href
			);
			
			// create chapter folders
			$dir = dirname(__FILE__) . "/../books/{$bookName}/{$text}";
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
		}
		
		$output = array(
			"book"	=> $bookName,
			"chapters"	=> $chapters
		);
		
		// cache chapter index
		file_put_contents(dirname(__FILE__) . "/../books/{$bookName}/index.txt", serialize($output));
		
		return $output;
	}
	
	/**
	 * This method is used to read the content of a book
	 * @param string $url
	 */
	public function getBookContent($url) {
		$html = file_get_html ( $url );
		
		// Read book info
		$chapterData = $html->find('.crumbtrail b', 0);
		$chapterName = $chapterData->plaintext;
		
		$bookData = $html->find('.crumbtrail a', 3);
		$bookName = $bookData->plaintext;
		
		// first table
		$verseContainer = $html->find ( '.content_main .general .large');
		
		$verses = array();
		
		foreach ( $verseContainer as $verseItem ) {
			$text = trim($verseItem->plaintext);
			//$href = $link->getAttribute('href');
			
			$paragraphs = array();
			foreach ( $verseItem->parent()->find('p') as $paragraph ) {
				$paragraphs[] = $paragraph->plaintext;
			}
			$verses[] = array(
				'text'	=> $text,
				'paragraphs'	=> $paragraphs
			);
			
			
		}
		$output = array(
			"book"	=> $bookName,
			"chapter"	=> $chapterName,
			"verses"	=> $verses
		);
		
		$verseFile = dirname(__FILE__) . "/../books/{$bookName}/{$chapterName}/verses.txt";
		file_put_contents($verseFile, serialize($output));
		
		return $output;
	}
	
	public function combineBooks() {
		
	}
	
	public function combineBook($bookName) {
		$contents = array();
		$contents[] = "<h1 pb_toc=\"index\">{$bookName}</h1>";
		
		$dir = dirname(__FILE__) . "/../books/{$bookName}";
		
		if (!file_exists("{$dir}/index.txt")) {
			return array();
		}
		$index = file_get_contents("{$dir}/index.txt");
		
		$BookChapters = unserialize($index);
//		if (!isset($BookChapters['chapters'])) dump(array($bookName, $index));
		$chapters = $BookChapters['chapters'];
		
		foreach ($chapters as $chapter) {
			$chapterNumber = str_replace("Chapter ", "", $chapter['text']);
			
			if (!file_exists("{$dir}/{$chapter['text']}/verses.txt"))
				continue;
			$verseIndex = file_get_contents("{$dir}/{$chapter['text']}/verses.txt");
			
			$unserialize = unserialize($verseIndex);
			$verses = $unserialize['verses'];
			
			foreach ($verses as $verse) {
				$verseNumber = str_replace("Verse ", "", $verse['text']);
				$verseNumber = str_replace("Verses ", "", $verseNumber);
				//<h2 pb_toc="index">1:1</h2>
// <p><pb_sync type=verse value="Genesis 1:1" display="now" /><b>Gen 1:1</b></p>
				
				$contents[] = "<h2 pb_toc=\"index\">{$chapterNumber}:{$verseNumber}</h2>";
				$contents[] = "<p><pb_sync type='verse' value=\"{$bookName} {$chapterNumber}:{$verseNumber}\" display=\"now\" /><b>".substr($bookName, 0, 3)." {$chapterNumber}:{$verseNumber}</b></p>";
				
				// Two blank lines
				$contents[] = '';
				$contents[] = '';
				
				foreach ($verse['paragraphs'] as $paragraph) {
					$paragraph = strip_tags($paragraph);
					$contents[] = "<p>{$paragraph}</p>";
				}
			}
		}
		
		return $contents;
	}
}