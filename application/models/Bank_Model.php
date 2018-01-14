<?php
	class Bank_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		public function getBankList()
		{
			$sql="SELECT * FROM bank_info  ORDER BY bi_id ASC";
			$query = $this->db->query($sql);

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
		
	}
?>