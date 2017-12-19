<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
class Api extends CI_Controller {
	
	private $request = array();
	
	public function __construct() 
	{
		parent::__construct();	
		$this->request = json_decode(trim(file_get_contents('php://input'), 'r'), true);
    }
	
	public function test()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='測試用';
		
		try 
		{
		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
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
