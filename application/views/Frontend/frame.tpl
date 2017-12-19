<!DOCTYPE html>
<html class="no-js" ng-app="agApp">
     <head>
        <title><{$website.title}></title>
        <!-- Bootstrap -->
        <{include file='Frontend/css.tpl'}>
		<{include file='Frontend/js.tpl'}>
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
		<ng-view></ng-view>
    </body>
</html>