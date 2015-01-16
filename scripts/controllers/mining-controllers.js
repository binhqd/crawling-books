var MiningCtrls = angular.module('MiningCtrls', []);
var bookUri = 'http://www.studylight.org/commentaries/bbc/';

MiningCtrls.controller('MiningCtrl', function($scope, $rootScope, $http, $stateParams) {
	$scope.part = '';
	if (!!$stateParams.part) {
		$scope.part = $stateParams.part;
	}
	$scope.global = {
		books : [],
		oldTesaments : [],
		newTesaments : [],
		errors : []
	};

	var req = {
		method : 'GET',
		url : './books.php?url=' + bookUri + ($scope.part != '' ? '&part=' + $scope.part : '')
	}

	$scope.state = {
		gettingChapter : false
	};

	$scope.done = false;
	$scope.isGetBooks = false;

	$http(req).success(
			function(res) {
				$scope.global.oldTesaments = res.oldTesaments;
				$scope.global.newTesaments = res.newTesaments;

				$scope.global.totalBooks = $scope.global.oldTesaments.length + $scope.global.newTesaments.length;

				$scope.isGetBooks = true;
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
		// getBook();
		// getBook();
	}

	$scope.getBook = function(callback) {
		var book = {};
		if ($scope.global.books.length > 0) {
			book = $scope.global.books[0];
			$scope.global.currentBook = book;

			$scope.getChapters(book, function() {
				// get another books

				if (!$scope.state.gettingChapter)
					$scope.getBook(callback);
			});
			$scope.global.books.shift();
		} else {
			callback();
		}
	}

	$scope.getVerses = function(callback) {
		// TODO: Check if chapters is empty
		if ($scope.global.chapters.length == 0) {
			callback();
		} else {
			var chapter = $scope.global.chapters[0];
			;
			$scope.global.currentChapter = chapter;
			$scope.global.chapters.shift();

			var url = './book-content.php?url=' + bookUri + encodeURIComponent(chapter.href);
			var req = {
				method : 'GET',
				url : url
			}

			$http(req).success(function(res) {
				$scope.getVerses(callback);
			}).error(function(xhr) {
				$scope.global.errors.push({
					book : $scope.global.currentBook,
					chapter : chapter,
					scenario : 'getVerses',
					url : url,
					originalUrl : bookUri + chapter.href
				});

				console.log("Can't get chapters of '" + chapter.text + "'");
				console.log(xhr);

				$scope.getVerses(callback);
			});
		}
	}

	$scope.getChapters = function(book, callback) {
		var url = './chapters.php?url=' + bookUri
				+ encodeURIComponent(book.href);
		var req = {
			method : 'GET',
			url : url
		}
		var chapter;

		$scope.state.gettingChapter = true;
		$http(req).success(function(res) {
			$scope.global.chapters = res.chapters;

			$scope.state.gettingChapter = false;
			$scope.global.totalChapter = $scope.global.chapters.length;

			$scope.getVerses(callback);

			$scope.getVerses(callback);

			$scope.getVerses(callback);

			$scope.getVerses(callback);

			$scope.getVerses(callback);

		}).error(function() {
			$scope.global.errors.push({
				book : book,
				// chapter => chapter,
				scenario : 'getChapters',
				url : url,
				originalUrl : bookUri + book.href
			});
			console.log("Can't get chapters of '" + chapter.text + "'");
			console.log(xhr);

			// get another books
			$scope.getBook();
		});
	}
});