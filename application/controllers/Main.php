<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	
	
	public function __construct() 
	{
		parent::__construct();	
		$this->load->helper('captcha');
    }
	
	public function index()
	{
	
		echo '<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1937639262&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:1937639262:41" alt="点击这里给我发消息" title="点击这里给我发消息"/></a>';
		$vals = array(
			'word'          => 'Random word',
			'img_path'      => './captcha/',
			'img_url'       => '/captcha/',
			'font_path'     => './path/to/fonts/texb.ttf',
			'img_width'     => '150',
			'img_height'    => 30,
			'expiration'    => 7200,
			'word_length'   => 8,
			'font_size'     => 16,
			'img_id'        => 'Imageid',
			'pool'          => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',

			// White background and border, black text and red grid
			'colors'        => array(
					'background' => array(255, 255, 255),
					'border' => array(255, 255, 255),
					'text' => array(0, 0, 0),
					'grid' => array(255, 40, 40)
			)
		);

		$cap = create_captcha($vals);
		echo $cap['image'];
		$data['cap'] = create_captcha($vals);
		// $this->load->view('captcha_view', $data);
	}
	
}
