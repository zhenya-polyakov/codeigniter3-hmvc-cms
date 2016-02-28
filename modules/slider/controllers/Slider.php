<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Slider extends MX_Controller {

	var $module_name	= "slider";

	function __construct()
	{
		parent::__construct();
		$this->load->library('common/main_lib');
        $this->load->model('slider_model');
	}
    
    //вывод слайдера
    function get_slider($slider_name) {
        $res['slide'] = $this->slider_model->get_slider($slider_name);
        $res['sliders'] =  $this->slider_model->get_slider_data($res['slide']['id']);
        $data = $this->load->view('site/slider/slider.tpl',  $res,  true);
        return $data;
	}
 
}