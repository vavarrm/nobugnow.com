<?php
	class User_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		
		public function setBankInfo($ary=array())
		{
			if(!is_array($ary) || count($ary) == 0)
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>'參數輸入有誤' ,
					'type' 		=>'system' ,
					'status'	=>'003'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			
			try 
			{
				$user = $this->getUsetByID($ary['u_id']);
				if(empty($user))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'使用者不存在' ,
						'type' 		=>'db' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				$account = $this->getUserBankInfoByAccount($ary['account']);
				if(!empty($account))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'此銀行帳號已被綁定' ,
						'type' 		=>'db' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				$bank = $this->getBankInfoByID($ary['bi_id']);
				
				if(!empty($bank))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'此銀行帳號不存在' ,
						'type' 		=>'db' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
			}catch(MyException $e)
			{
				throw $e;
				return false;
			}
			
		 
			$sql="INSERT INTO user_bank_info(ub_u_id, ub_account, ub_account_name, ub_bank_id,ub_province,ub_city,ub_branch_name,ub_add_datetime)
				 VALUES(?,?,?,?,?,?,?,NOW())";
			$bind = array(
				$ary['u_id'],
				$ary['account'],
				$ary['account_name'],
				$ary['bank_id'],
				$ary['province'],
				$ary['city'],
				$ary['branch_name'],
			);
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			return $this->db->affected_rows();
		}
		
		public function getBankInfoByID($bi_id)
		{
			$sql="SELECT * FROM bank_info WHERE bi_id = ?";
			$bind = array(
				$bi_id
			);
			$query = $this->db->query($sql, $bind);

			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row;
		}
		
		public function getUserBankInfoByAccount($account)
		{
			$sql="SELECT * FROM user_bank_info WHERE ub_account = ?";
			$bind = array(
				$account
			);
			$query = $this->db->query($sql, $bind);

			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row;
		}
		
		public function getBalance($uid)
		{
			$sql ="SELECT IFNULL(SUM(ua_value),0) as balance FROM user_account WHERE ua_u_id =?";
			$bind = array(
				$uid
			);
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row;
		}
		
		public function setUserPasswd($passwd, $uid)
		{
			$sql = "UPDATE user SET u_passwd = ? WHERE u_id=?";
			$bind = array(
				md5($passwd),
				$uid
			);
			$query = $this->db->query($sql, $bind);

			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			return $this->db->affected_rows();
		}
		
		public function setUserMoneyPasswd($passwd, $uid)
		{
			$sql = "UPDATE user SET u_money_passwd= ? WHERE u_id=?";
			$bind = array(
				md5($passwd),
				$uid
			);
			$query = $this->db->query($sql, $bind);

			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			return $this->db->affected_rows();
		}
		
		public function getUsetByID($u_id)
		{
			$sql = "SELECT *   FROM user WHERE u_id = ?";
			$bind = array(
				$u_id
			);
			$query = $this->db->query($sql, $bind);

			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row;
		}
		
		public function getUesrByAccount($account)
		{
			$sql = "SELECT *  FROM user_bank_info WHERE u_account = ?";
			$bind = array(
				$account
			);
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row;
		}
		
		public function accountIsExist($account)
		{
			
			$sql = "SELECT COUNT(*) as isExist FROM user WHERE u_account = ?";
			$bind = array(
				$account
			);
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			$row =  $query->row_array();
			$query->free_result();
			return $row['isExist'];
		}
		
		public function insert($ary)
		{
			$sql="	INSERT INTO user(u_superior_id	,u_name,u_account,u_passwd,u_add_datetime)
					VALUES(?,?,?,?,NOW())";
			$bind = array(
				$ary['superior_id'],
				$ary['u_name'],
				$ary['u_account'],
				$ary['u_passwd']
			);
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' 	=>$error['message'] ,
					'type' 		=>'db' ,
					'status'	=>'001'
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
		}
	}
?>