<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
class Api extends CI_Controller {
	
	private $request = array();
	
	public function __construct() 
	{
		parent::__construct();	
		$this->request = json_decode(trim(file_get_contents('php://input'), 'r'), true);
		$this->load->model('User_Model', 'user');
		$this->load->model('Log_Model', 'myLog');
    }
	
	public function test()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='測試用';
		
		try 
		{
			$this->user->insert();
		}catch(MyException $e)
		{
			$parames = $e->getParams();
			$parames['class'] = __CLASS__;
			$parames['function'] = __function__;
			$this->myLog->error_log($parames);
		}
		
		$this->response($output);
	}
	
	public function registered()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='註冊下級用戶';
		
		try 
		{
			if(
				$this->request['name']	==""|| 
				$this->request['account']	==""|| 
				$this->request['passwd']	=="" ||
				$this->request['superior']	=="" 
			){
				$array = array(
					'message' 	=>'reponse 必傳參數為空' ,
					'type' 		=>'api' ,
					'status'	=>'002'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if(strlen($this->request['name']) <8 || strlen($this->request['name'])>12){
				$array = array(
					'message' 	=>'暱稱長度為8~12位' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if(strlen($this->request['account']) <8 || strlen($this->request['account'])>12){
				$array = array(
					'message' 	=>'帳號長度為8~12位' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if(strlen($this->request['passwd']) <8 || strlen($this->request['passwd'])>12){
				$array = array(
					'message' 	=>'密碼長度為8~12位' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if($this->request['name'] == $this->request['account']){
				$array = array(
					'message' 	=>'使用者名稱不能與帳號相同' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if(intval($this->request['superior']) <=0){
				$array = array(
					'message' 	=>'無法註冊總代號' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$ary =array(
				'superior_id'	=>$this->request['superior'],
				'u_name'		=>$this->request['name'],
				'u_account'		=>$this->request['account'],
				'u_passwd'		=>md5($this->request['passwd']),
			);
			
			$this->user->insert($ary);
		}catch(MyException $e)
		{
			$parames = $e->getParams();
			$parames['class'] = __CLASS__;
			$parames['function'] = __function__;
			$this->myLog->error_log($parames);
			$output['message'] = $parames['message']; 
			$output['status'] = $parames['status']; 
		}
		
		$this->response($output);
	}
	
	private function response($output)
	{
		
		$output = array(
			'body'		=>$output['body'],
			'title'		=>$output['title'],
			'status'	=>$output['status'],
			'message'	=>$output['message']
		);
		
		header('Content-Type: application/json');
		echo json_encode($output , true);
	}
}
