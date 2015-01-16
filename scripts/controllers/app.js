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
				templateUrl: '/finance-mining/templates/mining.html',
				controller: 'MiningCtrl'
			}
		}
	})
});