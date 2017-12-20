<?php
	class Log_Model extends CI_Model 
	{
		function __construct()
		{
			
			parent::__construct();
			$this->load->database();
		}
		
		public function  error_log($ary)
		{
			$sql="INSERT INTO  api_error_log  
					(ael_type, aei_error_message,aei_class,aei_function, aei_add_datetime) 
					VALUES(?,?,?,?,NOW())";
			$bind = array(
				$ary['type'],
				$ary['message'],
				$ary['class'],
				$ary['function'],
			);
			$query = $this->db->query($sql, $bind);
			
		}
	}
?>