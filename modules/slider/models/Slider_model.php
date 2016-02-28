<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Slider_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function get_slider($slider_name){
       $result = $this->db->select('*')->where('slider_name', $slider_name)->get('widget_slider')->row_array();
       return $result;
    }

    function get_slider_data($slider_id){
        $result = $this->db->where('status', '1')->where('slider_id', $slider_id)->order_by("id", "asc")->get('widget_slider_data')->result_array();
        return $result;
    }

}