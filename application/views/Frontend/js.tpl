<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.6.4/angular-route.js"></script>

<script src="/js/main.js?<{$randseed}>"></script>

<{if $jsArray}>
	<{foreach from=$jsArray item=file}>
		<link rel="stylesheet" href="/js/<{$file}>">
	<{/foreach}>
<{/if}>




        