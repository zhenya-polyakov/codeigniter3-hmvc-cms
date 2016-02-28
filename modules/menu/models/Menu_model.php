<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

   function get_menu_query($menu_name){
       $result = $this->db->select('menus.*, menus_data.*')
           ->where('menus.menu_name', $menu_name)
           ->where('menus_data.visible', '1')
           ->from('menus')
           ->join('menus_data', 'menus.id = menus_data.menu_id', 'right')
           ->order_by('menus_data.order asc, menus_data.id asc')
           ->get();
       return $result;
   }

}