<!doctype html>
<html class="no-js">
<head>
<meta charset="utf-8">
<title>Mining Yahoo Finance</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width">
<link rel="shortcut icon" href="/favicon.ico">
<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
<!-- build:css(.) styles/vendor.css -->
<!-- bower:css -->
<link rel="stylesheet" href="./styles/bootstrap/bootstrap.css" />
<!-- endbower -->
<!-- endbuild -->
<!-- build:css(.tmp) styles/main.css -->
<link rel="stylesheet" href="styles/main.css">
<!-- endbuild -->
<style>
.headrow td {
	font-weight: bold;
	font-size: 16px;
}

tr.head td {
	background: #ddd
}
</style>
</head>
<body>
	<!--[if lt IE 10]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->


	<div class="container">
		<div class="header">
			<ul class="nav nav-pills pull-right">
				<li class="active"><a href="#">Home</a></li>
				<li><a href="#">About</a></li>
				<li><a href="#">Contact</a></li>
			</ul>
			<h3 class="text-muted">Mining script</h3>
		</div>

		<!-- $info here -->
		<table class="table table-striped">
			<caption>Books</caption>
			
		</table>
		
		<div class="footer">
			<p>
				<a href='https://www.linkedin.com/profile/view?id=183138464&trk=nav_responsive_tab_profile'>Binh Quan</a>
			</p>
		</div>

	</div>


	<!-- build:js(.) scripts/vendor.js -->
	<!-- bower:js -->
	<script src="./scripts/jquery/jquery.js"></script>
	<!-- endbower -->
	<!-- endbuild -->

	<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->

	<!-- build:js(.) scripts/plugins.js -->
	<script src="./scripts/bootstrap/affix.js"></script>
	<script src="./scripts/bootstrap/alert.js"></script>
	<script src="./scripts/bootstrap/dropdown.js"></script>
	<script src="./scripts/bootstrap/tooltip.js"></script>
	<script src="./scripts/bootstrap/modal.js"></script>
	<script src="./scripts/bootstrap/transition.js"></script>
	<script src="./scripts/bootstrap/button.js"></script>
	<script src="./scripts/bootstrap/popover.js"></script>
	<script src="./scripts/bootstrap/carousel.js"></script>
	<script src="./scripts/bootstrap/scrollspy.js"></script>
	<script src="./scripts/bootstrap/collapse.js"></script>
	<script src="./scripts/bootstrap/tab.js"></script>
	<!-- endbuild -->

	<!-- build:js({app,.tmp}) scripts/main.js -->
	<script src="./scripts/main.js"></script>
	<!-- endbuild -->
	
	<script>
	var books = [];
	
	var urlPrefix = "";

	var chapters = [];

	function getVerses(chapter, callback) {
		$.ajax({
			url: './book-content.php?url=http://www.studylight.org/commentaries/jtc/' + encodeURIComponent(chapter.href),
			success : function(res) {
				if (chapters.length > 0) {
					chapter = chapters[0];
					getVerses(chapter, callback);
					chapters.shift();
				} else {
					callback();
				}
			},
			error: function(xhr) {
				console.log("Can't get chapters of '"+chapter.text+"'");
				console.log(xhr);
			}
		});
	}
	
	function getChapters(book, callback) {
		$.ajax({
			url: './chapters.php?url=http://www.studylight.org/commentaries/jtc/' + encodeURIComponent(book.href),
			success : function(res) {
				chapters = res.chapters;
				
				chapter = chapters[0];
				chapters.shift();
				getVerses(chapter, function() {
					// all verses get

					callback();
				});
				
			},
			error: function(xhr) {
				console.log("Can't get chapters of '"+book.text+"'");
				console.log(xhr);
			}
		});
	}

	function getBook(callback) {
		var book = {};
		if (books.length > 0) {
			book = books[0];
			
			getChapters(book, function() {
				// get another books
				getBook(callback);
			});
			books.shift();
		} else {
			callback();
		}
	}
	
	function crawlBooks(bookList, callback) {
		books = bookList;
		
		// serialize run to get books faster
		getBook(callback);
		//getBook();
		//getBook();
	}
	
	$(document).ready(function() {
		$.ajax({
			// http://www.studylight.org/commentaries/jtc/
			url: './books.php?url=http://www.studylight.org/commentaries/jtc/',
			success : function(res) {
				crawlBooks(res.newTesaments, function() {
					// crawl old tesaments
					crawlBooks(res.oldTesaments, function() {
						alert("Books has been crawled successful");
					});
				});
			},
			error: function(xhr) {
				console.log("Can't get books list");
				console.log(xhr);
			}
		});
	});
	</script>
</body>
</html>
