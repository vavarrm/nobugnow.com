<?php
	class User_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		public function insert()
		{
			$sql="SELECT  FROM user";
			// echo "D";
			$query = $this->db->query($sql, $bind);
			$error = $this->db->error();
			if($error['message'] !="")
			{
				$MyException = new MyException();
				$array = array(
					'message' =>$error['message'] ,
					'type' =>'db' ,
				);
				
				$MyException->setParams($array);
				throw $MyException;
			}
			// var_dump(); 
		}
	}
?>