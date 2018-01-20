<?php
	class UserAccount_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		public function getBalance($u_id)
		{
			try 
			{
				
				$sql ="
						SELECT   IFNULL(SUM(Balance),0)  AS balance FROM  (
							SELECT ua_id , IFNULL(ua_value,0) AS Balance FROM user_account WHERE ua_u_id = ? AND ua_status = 'recorded'
							UNION
							SELECT ua_id , IFNULL(ua_value,0)*-1  AS Balance FROM user_account WHERE  ua_u_id = ? AND ua_status = 'payment'
						) AS t";
				$bind = array(
					$u_id,
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
				
			}catch(MyException $e)
			{
				throw $e;
			}
		}
		
		
		public function getBankInfoByIDAndUId($ub_id,$u_id)
		{
			try 
			{
				
				$sql ="SELECT * FROM  user_bank_info WHERE 	ub_id =? AND ub_u_id = ?";
				$bind = array(
					$ub_id,
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
				
			}catch(MyException $e)
			{
				throw $e;
			}
		}
		
		public function withdrawal($ary)
		{
			try 
			{
				
				$balance = $this->getBalance($ary['u_id']);

				if($ary['quota']>$balance['balance'])
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'馀额不足' ,
						'type' 		=>'system' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				$bankInfo = $this->getBankInfoByIDAndUId($ary['ub_id'], $ary['u_id']);
				
				if(empty($bankInfo))
				{
					$MyException = new MyException();
					$array = array(
						'message' 	=>'尚未绑定银行卡' ,
						'type' 		=>'system' ,
						'status'	=>'999'
					);
					
					$MyException->setParams($array);
					throw $MyException;
				}
				
				$sql ="INSERT INTO user_account(ua_value, ua_type, ua_add_datetime,ua_u_id, ua_ub_id)
					   VALUE(?,3,NOW(),?,?)";
				$bind = array(
					$ary['quota'],
					$ary['u_id'],
					$ary['ub_id']
				);
				$query = $this->db->query($sql, $bind);
				$affected_rows =  $this->db->affected_rows();
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
				return $affected_rows ;
				
			}catch(MyException $e)
			{
				throw $e;
			}
		}
		
		public function getReportList($ary = array())
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
					if($key =="start_time" || $key=="end_time"  )
					{
						if($value['value']!='')
						{
							$where .=sprintf(" AND DATE_FORMAT(`an_datetime`, '%s') %s ?", '%Y-%m-%d', $value['operator']);					
							$bind[] = $value['value'];
						}
					}else
					{
						$where .=sprintf(" AND %s %s ?", $key, $value['operator']);					
						$bind[] = $value['value'];
					}
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
						user_account";
			$search_sql = $sql.$where.$order.$limit ;
			// echo $search_sql;
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
	}
?>