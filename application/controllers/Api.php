<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends CI_Controller {
	
	private $request = array();
	
	public function __construct() 
	{
		parent::__construct();	
		$this->request = json_decode(trim(file_get_contents('php://input'), 'r'), true);
		$this->load->model('Food_Model', 'food');
		$this->load->model('Order_Model', 'order');
		$this->load->library('pagination');
    }
	
	
	public function ordersCount()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='get Order Count';
		try 
		{
			$rows= $this->order->getOrdersCount();
			if(!empty($rows))
			{
				foreach($rows as $value)
				{
					$orders_count[$value['o_status']] = $value;
				}
			}
			$output['body']['orders_count'] = $orders_count;
		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
		}
		
		$this->response($output);
	}
	
	public function orderDetailList($o_id)
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='get Detail list';
		try 
		{
			$ary = array(
				'od.o_id'	=>$o_id
			);
			$output['body']['details'] = $this->order->getDetailList($ary);
		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
		}
		
		$this->response($output);
	}
	
	public function updateOrderStatus()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='Update Order Status';
		try 
		{
			if(is_array($this->request['o_id']))
			{
				if(empty($this->request['o_id']))
				{
					$status ='000';
					throw new Exception("o_id error");
				}
				
				foreach($this->request['o_id'] as $key =>$value)
				{
					$afftected_rows = 0;
					$temp =array(
						'o_id'	=>$value,
						'o_status'	=>$this->request['o_status']
					);
					$row = $this->order->updOrderStatus($temp);
					$afftected_rows+=$row['afftected_rows'];
				}
				$row['afftected_rows'] = $afftected_rows;
				
			}else
			{
				$row = $this->order->updOrderStatus($this->request);
			}
			
			if($row['afftected_rows'] <=0)
			{
				$status ='000';
				throw new Exception("update error");
			}
			$rows = $this->order->getOrdersCount();
			if(!empty($rows))
			{
				foreach($rows as $value)
				{
					$orders_count[$value['o_status']] = $value;
				}
			}
			$output['body']['orders_count'] = $orders_count;
		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
		}
		
		$this->response($output);
	}
	
	private function getPage($total, $records=1,$p=1)
	{
		$page_numbers = ceil($total/$records);
		for($i=1;$i<=$page_numbers;$i++)
		{
			$pages[] = array('p'=>$i);
		}
		$start = abs($p-1)*$records+1;
		$end = $start + $records;
		if($total <$end)
		{
			$end = $total;
		}
		$output = array(
			'pages' =>$pages,
			'p' 	=>$p,
			'limit' 	=>array(
				'start'=>$start,
				'end' =>$end
			),
			'prev' 	=>abs($p-1),
			'next' 	=>$p+1,
		);
		return $output;
	}
	
	public function orderList()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='get order list';
		try 
		{
			$ary = $this->input->get();
			$data =  $this->order->getOrderList($ary);
			$output['body']['orders'] = $data['rows'];
			$output['body']['total'] = $data['total'];
			
			$pageInfo = $this->getPage($data['total'], $ary['records'], $ary['p']);
			$output['body']['pageInfo'] = $pageInfo;
			
			if(!empty($output['body']['orders']) )
			{
				foreach($output['body']['orders'] as $value)
				{
					$o_id[] = $value['o_id'];
				}
				$rows = $this->order->getDetailListByOid($o_id);
				if(!empty($rows))
				{
					foreach($rows as $value)
					{
						$details[$value['o_id']][] = $value;
					}
				}
				
				foreach($output['body']['orders'] as &$value)
				{
					$value['details'] = $details[$value['o_id']];
				}
			}
			
			$output['body']['order_status'] = $this->order->getOrderStatus();
			
		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
		}
		
		$this->response($output);
	}
	
	public function foodForIdApi()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='get food edit';
		try 
		{
			
			if(empty($this->request))
			{
				$status ='000';
				throw new Exception("request is empty");
			}
			
			$f_id =$this->request['f_id'];
			$output['body']['row'] = $this->food->getFoodForId($f_id);
			$output['body']['category_list'] = $this->food->getFoodCategory();

		}catch(Exception $e)
		{
			$output['status'] = $status ;
			$output['message'] = $e->getMessage();
		}
		
		$this->response($output);
	}
	
	public function foodListApi()
	{
		$output['status'] = 100;
		$output['body'] =array();
		$output['title'] ='get food list';
		try 
		{
			$ary = $this->input->get();
			$data = $this->food->getFoodFroList($ary);
			$pageInfo = $this->getPage($data['total'], $ary['records'], $ary['p']);
			$output['body']['list'] = $data['rows'];
			$output['body']['total'] = $data['total'];
			$output['body']['pageInfo'] = $pageInfo ;
			$output['body']['category_list'] = $this->food->getFoodCategory();
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
