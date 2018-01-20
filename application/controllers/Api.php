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
		$this->load->helper('captcha');
		$this->load->model('Log_Model', 'myLog');
		$this->load->model('Announcemet_Model', 'announcemet');
		$this->load->model('Bank_Model', 'bank');
		$this->load->model('UserAccount_Model', 'account');
		$this->request = json_decode(trim(file_get_contents('php://input'), 'r'), true);
		
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='';
		$gitignore =array(
			'login',
			'logout',
			'registered'
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
	
	public function withdrawal()
	{
		$output['status'] = 100;
		$output['body'] =array(
			'affected_rows' =>0
		);
		$output['title'] ='取款';
		$output['message'] = '成功';
		try 
		{
			if(
				$this->request['quota']	==""  ||
				$this->request['ub_id']	==''
		
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
			
			if(
				$this->request['quota']	<=0  
			){
				$array = array(
					'message' 	=>'提款额必须大于0' ,
					'type' 		=>'api' ,
					'status'	=>'002'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$ary = array(
				'u_id' =>$this->_user['u_id'],
				'quota'=>$this->request['quota'],
				'ub_id'	=>$this->request['ub_id']
			);
			$output['body']['affected_rows']=$this->account->withdrawal($ary);
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
	
	public function getUserMessageForm()
	{
		$output['status'] = 100;
		$output['body'] =array(
		);
		$output['title'] ='站内信表单';
		$output['message'] = '成功';
		try 
		{
			$output['body']['subordinate'] = $this->user->getUserBySuperiorID($this->_user['u_id']);
			$output['body']['superior'] = $this->user->getUsetByID($this->_user['u_superior_id']);
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
	
	public function getUserMessage()
	{
		$output['status'] = 100;
		$output['body'] =array(
		);
		$output['title'] ='读取站内信';
		$output['message'] = '读取成功';
		try 
		{
			if(
				$this->request['um_id']	==""  
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
			
			$output['body']['row']=$this->user->getUserMessageByID($this->request['um_id'], $this->_user['u_id']);
			
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
	
	public function sendSuperiorUserMessage()
	{
		$output['status'] = 100;
		$output['body'] =array(
			'affected_rows'	=>0
		);
		$output['title'] ='传送站内信给上级';
		$output['message'] = '传送成功';
		try 
		{
			if(
				$this->request['title']	==""  ||
				$this->request['content']	==""  
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
			
			if($this->_user['u_superior_id'] == 0)
			{
				$array = array(
					'message' 	=>'总代用互无上级',
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$ary = array(
				'u_id' =>$this->_user['u_id'],
				'title' =>$this->request['title'],
				'content' =>$this->request['content'],
			);
			$output['body']['affected_rows']=$this->user->addSuperiorUserMmessage($ary);
			
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
	
	public function sendSubordinateUserMessage()
	{
		$output['status'] = 100;
		$output['body'] =array(
			'affected_rows'	=>0
		);
		$output['title'] ='传送站内信给下级';
		$output['message'] = '传送成功';
		try 
		{
			if(
				$this->request['account']	=="" ||
				$this->request['send_all_subordinate']	==="" ||  
				$this->request['title']	==""  ||
				$this->request['content']	==""  
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
			
			if($this->request['send_all_subordinate'] ==0)
			{
				$account_ary = array( $this->request['account']);
			
			}else
			{
				$rows= $this->user->getAllSubordinateUser($this->_user['u_id']);
				if(empty($rows))
				{
					$array = array(
					'message' 	=>'无下级用户' ,
					'type' 		=>'api' ,
					'status'	=>'999'
					);
					$MyException = new MyException();
					$MyException->setParams($array);
					throw $MyException;
				}
				foreach ($rows as $row)
				{
					$account_ary[] = $row['u_account'];
				}
			}
			
			$affected_rows = $this->user->addSubordinateUserMmessage($this->_user['u_id'], $account_ary ,htmlentities($this->request['title']) , htmlentities($this->request['content']));
			$output['body']['affected_rows']=$affected_rows;
			
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
	
	public function getRegisteredLink()
	{
		$output['status'] = 100;
		$output['title'] ='取得注册连结';
		$output['message'] = '成功取得';
		try 
		{
			$output['body']['list']= $this->user->getRegisteredLink($this->_user['u_id']);
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
	
	public function addRegisteredLink()
	{
		$output['status'] = 100;
		$output['body'] =array(
			'affected_rows' =>0
		);
		$output['title'] ='新增注册连结';
		$output['message'] = '成功新增';
		try 
		{
			$output['body']= $this->user->addRegisteredLink($this->_user['u_id']);
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
	
	public function setUserBankInfoForm()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='成功取得绑定页面';
		$output['message'] = '成功取得';
		try 
		{
			$output['body']['bank_list'] = $this->bank->getBankList();
			$output['body']['user_bank_list'] = $this->user->getUserBankInfoByID($this->_user['u_id']);
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
	
	
	public function setUserBankInfo()
	{
		$output['status'] = 100;
		$output['body'] =array(
			'affected_rows'	=>0
		);
		$output['title'] ='设定银行卡资料';
		$output['message'] = '设定成功';
		
		try 
		{
			if(
				$this->request['account']	==""||
				$this->request['account_name']	==""||
				$this->request['bank_id']	==""||
				$this->request['province']	==""||
				$this->request['city']	==""||
				$this->request['branch_name']	==""
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
			
			if (mb_strlen($this->request['province'],"utf-8") == strlen($this->request['province']))
			{
				$array = array(
					'message' 	=>'开户银行省份必須為簡體中文' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if (mb_strlen($this->request['city'],"utf-8") == strlen($this->request['city']))
			{
				$array = array(
					'message' 	=>'开户银行城市必須為簡體中文' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if (mb_strlen($this->request['account_name'],"utf-8") == strlen($this->request['account_name']))
			{
				$array = array(
					'message' 	=>'开户人姓名必須為中文' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if (mb_strlen($this->request['branch_name'],"utf-8") == strlen($this->request['branch_name']))
			{
				$array = array(
					'message' 	=>'支行名称必須為簡體中文' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if (mb_strlen($this->request['branch_name'],"utf-8") <=0 || mb_strlen($this->request['branch_name'],"utf-8") >20)
			{
				$array = array(
					'message' 	=>'支行名称長度為1-20個字符串' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			if (!is_numeric($this->request['account']) || strlen($this->request['account']) <16 || strlen($this->request['account']) >19)
			{
				$array = array(
					'message' 	=>'银行卡号为16～19位数字组成' ,
					'type' 		=>'api' ,
					'status'	=>'999'
				);
				$MyException = new MyException();
				$MyException->setParams($array);
				throw $MyException;
			}
			
			$ary['u_id']=$this->_user['u_id'];
			$ary = array_merge($ary,$this->request);
			$output['body']['affected_rows'] = 	$this->user->setBankInfo($ary);
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
	
	public function getAnnouncemetList()
	{
		$get= $this->input->get();
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='取得公告列表';
		$output['message'] = '成功取得';
		$ary['limit'] = (isset($get['limit']))?$get['limit']:5;
		$ary['p'] = (isset($get['p']))?$get['p']:1;
		try 
		{
			$output['body'] = $this->announcemet->getList($ary);
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
	
	public function getUserMessageList()
	{
		$get= $this->input->get();
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='取得站内讯息列表';
		$output['message'] = '成功取得';
		$ary['limit'] = (isset($get['limit']))?$get['limit']:5;
		$ary['p'] = (isset($get['p']))?$get['p']:1;
		$ary['um_u_id'] = array('value'=>$this->_user['u_id'], 'operator' =>'=');
		try 
		{
			$output['body'] = $this->user->getMessageList($ary);
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
	
	public function report()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='日常记录-提款';
		$output['message'] = '成功';
		$get= $this->input->get();
		$ary['limit'] = (isset($get['limit']))?$get['limit']:5;
		$ary['p'] = (isset($get['p']))?$get['p']:1;
		$type= (isset($get['type']))?$get['type']:'';
		try 
		{
			if(
					$type	==""
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
			$ary['ua_type']=array(
				'value' =>$type,
				'operator' =>'='
			);
			$ary['ua_u_id']=array(
				'value' =>$this->_user['u_id'],
				'operator' =>'='
			);
			
			$output['body'] = $this->account->getReportList($ary);
			
		}
		catch(MyException $e)
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
	
	public function withdrawalForm()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='提款申请表单';
		$output['message'] = '成功';
		
		try 
		{
			$row = $this->account->getBalance($this->_user['u_id']);
			$output['body']= $row;
			$output['body']['user_bank_list'] = $this->user->getUserBankInfoByID($this->_user['u_id']);
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
	
	public function getuserBalance()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='取得使用者帳戶餘額';
		$output['message'] = '成功取得';
		
		try 
		{
			
			$row = $this->account->getBalance($this->_user['u_id']);
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
	
	public function registered($rl_id='')
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
				$rl_id ==""
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
			
			$registeredLink =$this->user->getRegisteredLinkByID($rl_id);
			
			if( empty($registeredLink )){
				$array = array(
					'message' 	=>'注册连结无效' ,
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
				'superior_id'	=>$registeredLink['u_id'],
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
