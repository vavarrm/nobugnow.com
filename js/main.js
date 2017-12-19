

var FoodForIdApi ="/Api/foodForIdApi/";
var FoodListApi ="/Api/foodListApi/";
var OrderListApi ="/Api/orderList/";
var OrderDetailListApi ="/Api/orderDetailList/";
var UpdateOrderStatusApi ="/Api/updateOrderStatus/";
var OrdersCountApi ="/Api/ordersCount/";

var templatePath = "template/pc/";

var agApp = angular.module("agApp", ['ngRoute']);

agApp.config(function($routeProvider){
	$routeProvider.when("/",{
		templateUrl: templatePath+"index.html"+"?"+ Math.random(),
		controller: "indexCtrl",
		cache: false,
    })
});

var indexCtrl = function(){
}

agApp.controller('indexCtrl',  ['$scope', '$http', indexCtrl]);









