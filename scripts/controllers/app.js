var app = angular.module('miningApp', [
	'ngRoute',
	'MiningCtrls',
	'ui.router',
	'ui.bootstrap'
]);

app.config(function($routeProvider, $stateProvider) {

	$stateProvider

	// setup an abstract state for the tabs directive
	.state('mining', {
		url: '/mining',
		views: {
			'tabContent': {
				templateUrl: '/templates/mining.html',
				controller: 'MiningCtrl'
			}
		}
	})
	.state('miningsingle', {
		url: '/mining/:part',
		views: {
			'tabContent': {
				templateUrl: '/templates/mining.html',
				controller: 'MiningCtrl'
			}
		}
	})
});
