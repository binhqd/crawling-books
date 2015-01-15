var MiningCtrls = angular.module('MiningCtrls', []);
var bookUri = 'http://www.studylight.org/commentaries/jtc/';

MiningCtrls.controller('MiningCtrl', function($scope, $rootScope, $http) {
	$scope.global = {
		books : [],
		oldTesaments : [],
		newTesaments : [],
		errors : []
	};
	
	var req = {
		method : 'GET',
		url : './books.php?url=' + bookUri
	}
	
	$scope.done = false;
	
	$http(req).success(function(res) {
		$scope.global.oldTesaments = res.oldTesaments;
		$scope.global.newTesaments = res.newTesaments;
		
		$scope.global.totalBooks = $scope.global.oldTesaments.length + $scope.global.newTesaments.length;
		
		$scope.crawlBooks($scope.global.newTesaments, function() {
			// crawl old tesaments
			$scope.crawlBooks($scope.global.oldTesaments, function() {
				if (!$scope.done) {
					$scope.done = true;
					alert("Books has been crawled successful");
				}
			});
		});
		
	}).error(function() {

	});
	
	$scope.crawlBooks = function(bookList, callback) {
		$scope.global.books = bookList;
		// serialize run to get books faster
		$scope.getBook(callback);
		//getBook();
		//getBook();
	}
	
	$scope.getBook = function(callback) {
		var book = {};
		if ($scope.global.books.length > 0) {
			book = $scope.global.books[0];
			$scope.global.currentBook = book;
			
			$scope.getChapters(book, function() {
				// get another books
				$scope.getBook(callback);
			});
			$scope.global.books.shift();
		} else {
			callback();
		}
	}
	
	$scope.getVerses = function(chapter, callback) {
		var url = './book-content.php?url=' + bookUri + encodeURIComponent(chapter.href);
		var req = {
			method : 'GET',
			url : url
		}
		var reschapter;
		
		$http(req).success(function(res) {
			if ($scope.global.chapters.length > 0) {
				reschapter = $scope.global.chapters[0];
				$scope.global.currentChapter = reschapter;
				
				$scope.getVerses(reschapter, callback);
				$scope.global.chapters.shift();
			} else {
				callback();
			}
			
		}).error(function() {
			$scope.global.errors.push({
				book	: $scope.global.currentBook,
				chapter	: chapter,
				scenario	: 'getVerses',
				url		: url
			});
			
			console.log("Can't get chapters of '"+chapter.text+"'");
			console.log(xhr);
			
			// get another chapter
			reschapter = $scope.global.chapters[0];
			$scope.global.currentChapter = reschapter;
			
			$scope.getVerses(reschapter, callback);
			$scope.global.chapters.shift();
			
		});
	}
	
	$scope.getChapters = function(book, callback) {
		var url = './chapters.php?url=' + bookUri + encodeURIComponent(book.href);
		var req = {
			method : 'GET',
			url : url
		}
		var chapter;
		
		$http(req).success(function(res) {
			$scope.global.chapters = res.chapters;
			
			chapter = $scope.global.chapters[0];
			$scope.global.currentChapter = chapter;
			$scope.global.totalChapter = $scope.global.chapters.length;
			
			$scope.global.chapters.shift();
			$scope.getVerses(chapter, function() {
				// all verses get

				callback();
			});
			
//			chapter = $scope.global.chapters[0];
//			$scope.global.chapters.shift();
//			$scope.getVerses(chapter, function() {
//				// all verses get
//
//				callback();
//			});
//			
//			chapter = $scope.global.chapters[0];
//			$scope.global.chapters.shift();
//			$scope.getVerses(chapter, function() {
//				// all verses get
//
//				callback();
//			});
			
		}).error(function() {
			$scope.global.errors.push({
				book	: book,
				//chapter	=> chapter,
				scenario	: 'getChapters',
				url		: url
			});
			console.log("Can't get chapters of '"+chapter.text+"'");
			console.log(xhr);
			
			// get another books
			$scope.getBook();
		});
	}
});