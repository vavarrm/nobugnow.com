<{if $cssArray}>
	<{foreach from=$cssArray item=file}>
		<link rel="stylesheet" href="/css/<{$file}>">
	<{/foreach}>
<{/if}>

