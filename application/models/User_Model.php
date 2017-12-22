<?php
	class User_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		
		public function getBalance($uid)
		{
			$sql ="SELECT IFNULL(SUM(ua_value),0) as balance FROM user_account WHERE ua_from =?";
			$bind = array(
				$uid
			);
			$query = $this->db->query($sql, $bind);
			$row =  $query->row_array();
			$query->free_result();
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
		
		public function getUesrByAccount($account)
		{
			$sql = "SELECT *  FROM user WHERE u_account = ?";
			$bind = array(
				$account
			);
			$query = $this->db->query($sql, $bind);

			$row =  $query->row_array();
			$query->free_result();
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
			return $row;
		}
		
		public function accountIsExist($account)
		{
			
			$sql = "SELECT COUNT(*) as isExist FROM user WHERE u_account = ?";
			$bind = array(
				$account
			);
			$query = $this->db->query($sql, $bind);
			$row =  $query->row_array();
			$query->free_result();
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
			return $row['isExist'];
		}
		
		public function insert($ary)
		{
			$sql="	INSERT INTO user(superior_id,u_name,u_account,u_passwd,u_add_datetime)
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
			// var_dump(); 
		}
	}
?>