<?php
	class User_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		public function getMessageList($ary)
		{
			$where .=" WHERE 1 = 1";
			$gitignore = array(
				'limit',
				'p',
				'order'
			);
			$limit = sprintf(" LIMIT %d, %d",abs($ary['p']-1)*$ary['limit'],$ary['limit']);

			if(is_array($ary))
			{
				foreach($ary as $key => $value)
				{
					if(in_array($key, $gitignore) ||  $value ==='' || $value['value'] ==="")	
					{
						continue;
					}
					$where.=sprintf( " AND %s%s?", $key,  $value['operator']);
					$bind[] = $value['value'];
				}
			}
			
			if(is_array($ary['order']))
			{
				$order =" ORDER BY ";
				foreach($ary['order'] AS $key =>$value)
				{
					$order.=sprintf( '%s %s', $key, $value);
				}
			}
			
			$sql =" SELECT 
						*
					FROM 
						user_message";
			$search_sql = $sql.$where.$order.$limit ;
			$query = $this->db->query($search_sql, $bind);
			$rows = $query->result_array();
			
			$total_sql = sprintf("SELECT COUNT(*) AS total FROM(%s) AS t",$sql.$where) ;
			$query = $this->db->query($total_sql, $bind);
			$row = $query->row_array();
			
			$query->free_result();
			$output['list'] = $rows;
			$output['pageinfo']  = array(
				'total'	=>$row['total'],
				'pages'	=>ceil($row['total']/$ary['limit'])
			);
			
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
			return 	$output  ;
		}
		
		public function getRegisteredLinkByID($rl_id)
		{
			$sql="SELECT * FROM registered_link WHERE rl_id = ?";
			$bind = array(
				$rl_id
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
		
		public function getRegisteredLink($u_id)
		{
			$sql="SELECT * FROM registered_link WHERE u_id = ?";
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
			$rows =  $query->result_array();
			$query->free_result();
			return $rows;
		}
		
		public function addRegisteredLink($u_id)
		{
			$output = array(
				'affected_rows'	=>0,
				'rl_id'	=>''
			);
			try 
			{
				$user = $this->getUsetByID($u_id);
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
			}catch(MyException $e)
			{
				throw $e;
				return false;
			}
			
			$sql="INSERT INTO registered_link (u_id) VALUES(?)";
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
			$insert_id = $this->db->insert_id();
			$output['rl_id'] = sprintf('%08d',$insert_id) ;
			$output['affected_rows'] = $this->db->affected_rows() ;
			return $output;
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
		
		
		
		public function getUserBankInfoByID($u_id)
		{
			$sql="SELECT * FROM user_bank_info WHERE ub_u_id = ?";
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
			$rows =  $query->result_array();
			$query->free_result();
			return $rows;
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
		
		public function getAllSubordinateUser($u_superior_id)
		{
			$sql = "SELECT *   FROM user WHERE u_superior_id = ?";
			$bind = array(
				$u_superior_id
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
			$rows =  $query->result_array();
			$query->free_result();
			return $rows;
		}
		
		public function  getUserBySuperiorID($id)
		{
			$sql = "SELECT *  FROM  user WHERE u_superior_id =?";
			$bind = array(
				$id,
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
			$rows =  $query->result_array();
			$query->free_result();
			return $rows;
		}
		
		public function getUserMessageByID($id, $u_id)
		{
			$sql = "SELECT *   FROM user_message WHERE um_id= ? AND um_u_id =?";
			$bind = array(
				$id,
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
			
			if(!empty($row) && $row['um_is_read'] ==0)
			{
				$sql ="UPDATE user_message set 	um_is_read ='1' WHERE um_id =? AND um_u_id=?"; 
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
			
			return $row;
		}
		
		public function  addSuperiorUserMmessage($ary=array())
		{
			try 
			{
				
				$user = $this->getUsetByID($ary['u_id']);
				if(empty($user))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'无此使用者' ,
						'type' 		=>'api' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				if($user['u_superior_id'] === 0)
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'总代用互无上级',
						'type' 		=>'api' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				
				$sql =" INSERT INTO user_message(um_u_id, um_title, um_content, um_add_datetime, um_from_u_id)
				        VALUES(?,?,NOW(),?,?)";
				
				$bind = array(
					$user['u_superior_id'],
					$ary['title'],
					$ary['content'],
					$ary['u_id'],
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

				return $this->db->affected_rows() ;
				
			}catch(MyException $e)
			{
				throw $e;
			}
		}
		
		public function  addSubordinateUserMmessage($u_superior_id, $u_account=array(), $title, $content)
		{
			try 
			{
				if(!is_array($u_account) || count($u_account) == 0)
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
				
				
				$subordinateUser= $this->getSubordinateUserByAccount($u_superior_id, $u_account);
				if(empty($subordinateUser))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'无此下级使用者' ,
						'type' 		=>'db' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				$this->db->trans_start();
				foreach( $subordinateUser as $value)
				{
					$sql ="INSERT INTO user_message (um_u_id, um_title, um_content ,um_from_u_id, um_add_datetime)
						VALUES(?,?,?,?,NOW())";
					$bind = array(
						$value['u_id'],
						$title,
						$content,
						$u_superior_id
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
						break;
					}
					$affected_rows += $this->db->affected_rows() ;
					
				}
				$this->db->trans_complete();
				
				if($affected_rows==0)
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'新增站内讯息失败' ,
						'type' 		=>'db' ,
						'status'	=>'001'
					);
					
					$MyException->setParams($array);
					throw $MyException;
					break;
				}
				return $affected_rows;
				
			}catch(MyException $e)
			{
				throw $e;
				return false;
			}
		}
		
		public function getSubordinateUserByAccount($u_superior_id, $u_account)
		{
			
			try 
			{
				if(!is_array($u_account) || count($u_account) == 0)
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
				
			}catch(MyException $e)
			{
				throw $e;
				return false;
			}
			
			
			$u_account_str = join("','", $u_account);
			
			$sql = sprintf("SELECT *  FROM user WHERE u_superior_id =? AND u_account IN ('%s')", $u_account_str);
			// echo $sql;
			$bind = array(
				$u_superior_id
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
			$rows =  $query->result_array();
			$query->free_result();
			return $rows;
		}
		
		public function getUesrByAccount($account)
		{
			$sql = "SELECT *  FROM user WHERE u_account = ?";
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