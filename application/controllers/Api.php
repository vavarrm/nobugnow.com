<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
class Api extends CI_Controller {
	
	private $request = array();
	private $_user = array();
	
	public function __construct() 
	{
		parent::__construct();	
		

		$get = $this->input->get();
		$this->load->model('User_Model', 'user');
		$this->load->model('Log_Model', 'myLog');
		$this->request = json_decode(trim(file_get_contents('php://input'), 'r'), true);
		
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='';
		$gitignore =array(
			'login',
			'logout'
		);		
		
		try 
		{
			if(!in_array($this->uri->segment(2),$gitignore))
			{
				
				if(
					$get['sess']	==""
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
				
				$encrypt_user_data = $this->session->userdata('encrypt_user_data');
			
				if(empty($encrypt_user_data)){
					$array = array(
						'message' 	=>'尚未登入' ,
						'type' 		=>'api' ,
						'status'	=>'999'
					);
					$MyException = new MyException();
					$MyException->setParams($array);
					throw $MyException;
				}	
			
				$decrypt_user_data= $this->decryptUser($get['sess'], $encrypt_user_data);
				if(empty($decrypt_user_data))
				{
					$array = array(
						'message' 	=>'無法取得使用者資料' ,
						'type' 		=>'api' ,
						'status'	=>'999'
					);
					$MyException = new MyException();
					$MyException->setParams($array);
					throw $MyException;	
				}
				$this->_user = $decrypt_user_data;
			}
		}catch(MyException $e)
		{
			$parames = $e->getParams();
			$parames['class'] = __CLASS__;
			$parames['function'] = __function__;
			$this->myLog->error_log($parames);
			$output['message'] = $parames['message']; 
			$output['status'] = $parames['status']; 
			$this->response($output);
			exit;
		}
    }
	
	public function setUserPassword()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='設定使用者密碼';
		$output['message'] = '設定成功';
		try 
		{
			if(
				$this->request['passwd']	=="" 
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
			
			if($this->_user['u_money_passwd'] ==md5($this->request['passwd']))
			{
				$array = array(
					'message' 	=>'使用者密碼不能與資金密碼相同' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$affected_rows = $this->user->setUserPasswd($this->request['passwd'], $this->_user['u_id']);
			if($affected_rows==0)
			{
				$array = array(
					'message' 	=>'密碼未更新' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
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
	
	public function setUserMoneyPassword()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='設定資金密碼';
		$output['message'] = '設定成功';
		try 
		{
			if(
				$this->request['passwd']	=="" 
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
			
			if($this->_user['u_passwd'] ==md5($this->request['passwd']))
			{
				$array = array(
					'message' 	=>'使用者密碼不能與資金密碼相同' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$affected_rows = $this->user->setUserMoneyPasswd($this->request['passwd'], $this->_user['u_id']);
			if($affected_rows==0)
			{
				$array = array(
					'message' 	=>'密碼未更新' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
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
	
	public function getuserBalance()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='取得使用者帳戶餘額';
		$output['message'] = '成功取得';
		
		try 
		{
			
			$row = $this->user->getBalance($this->_user['u_id']);
			$output['body']= $row;
		}catch(MyException $e)
		{
			$parames = $e->getParams();
			$parames['class'] = __CLASS__;
			$parames['function'] = __function__;
			$output['message'] = $parames['message']; 
			$output['status'] = $parames['status']; 
			$this->myLog->error_log($parames);
		}
		
		$this->response($output);
	}
	
	public function test()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='測試用';
		$output['message'] = '成功取得';
		
		try 
		{
			$this->user->insert();
		}catch(MyException $e)
		{
			$parames = $e->getParams();
			$parames['class'] = __CLASS__;
			$parames['function'] = __function__;
			$output['message'] = $parames['message']; 
			$output['status'] = $parames['status']; 
			$this->myLog->error_log($parames);
		}
		
		$this->response($output);
	}
	
	private function decryptUser($rsa_randomKey, $encrypt_user_data)
	{
		$randomKey =  $this->token->privateDecrypt($rsa_randomKey);
		$encrypt_user_data = $this->session->userdata('encrypt_user_data');
		$decrypt_user_data = $this->token->AesDecrypt($encrypt_user_data , $randomKey );
		$user_data = unserialize($decrypt_user_data);
		return $user_data;
	}
	
	public function getUser()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='取得使用者登入資料';
		$output['message'] = '成功';
		try 
		{
			
			
			$encrypt_user_data = $this->session->userdata('encrypt_user_data');
			
			if(empty($encrypt_user_data)){
				$array = array(
					'message' 	=>'尚未登入' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}	
			
			$decrypt_user_data= $this->decryptUser($get['sess'], $encrypt_user_data);
			unset($this->_user['u_passwd']);
			$output['body']['user'] = $this->_user ;
			
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
	
	private function doLogin($row)
	{
		$randomKey = $this->token->getRandomKey();
		$rsaRandomKey = $this->token->publicEncrypt($randomKey);
		$encrypt_user_data = $this->token->AesEncrypt(serialize($row), $randomKey);
		$this->session->set_userdata('encrypt_user_data', $encrypt_user_data);
		$encrypt_user_data = $this->session->userdata('encrypt_user_data');
		$urlRsaRandomKey = urlencode($rsaRandomKey) ;
		return $urlRsaRandomKey ;
	}
	
	public function login()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='登入';
		$output['message'] = '登入成功';
		
		try 
		{
			if(
				$this->request['account']	==""|| 
				$this->request['passwd']	=="" 
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
			$row = $this->user->getUesrByAccount($this->request['account']);
			if(empty($row))
			{
				$array = array(
					'message' 	=>'查無此帳號' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if($row['u_passwd'] !=md5($this->request['passwd']))
			{
				$array = array(
					'message' 	=>'密碼錯誤' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$sess = $this->doLogin($row);
			$output['body'] = array(
				'sess' =>$sess 
			);
			
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
	
	public function logout()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='登出';
		$output['message'] = '成功';
		try 
		{
			$this->session->unset_userdata('encrypt_user_data');
			
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
	
	public function registered()
	{
		$output['status'] = '100';
		$output['message'] = '註冊成功'; 
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
			
			$accountIsExist = $this->user->accountIsExist($this->request['account']);
			if($accountIsExist ==1)
			{
				$array = array(
					'message' 	=>'使用者帳號已存在' ,
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
