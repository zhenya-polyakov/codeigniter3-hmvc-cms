<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    // массив нужных для всех остальных страниц настроек
    function get_settings()
    {
        $settings = $this->db->get('settings')->result_array();
        $data = array();
        foreach ($settings as $key => $value)
        {
            $data[$value['name']] = $value['value'];
        }
        return $data;
    }

    function get_all_settings(){
        $settings = $this->db->get('settings')->result_array();
        return $settings;
    }

    // выбор одной настройки сайта
    function get_one_setting($name)
    {
        $data = $this->db->where("name", $name)->get('settings')->row_array();
        return $data['value'];
    }
}