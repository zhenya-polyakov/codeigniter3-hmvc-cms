<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends MX_Controller {

	var $module_name = "menu";

	function __construct()
	{
		parent::__construct();
		$this->load->library('common/main_lib');
        $this->load->model('menu_model');
	}

    // генерация меню
    function get_menu($menu_name){
        $query = $this->menu_model->get_menu_query($menu_name);

        foreach($query->result_array() as $cat)
        {
            $cats_ID[$cat['id']][] = $cat;
            $cats[$cat['parent_id']][$cat['id']] =  $cat;
        }

        $menu = $this->_build_tree($cats, '0');
        return $menu;
    }

    function _build_tree($cats,$parent_id,$only_parent = false){
        if(is_array($cats) and isset($cats[$parent_id])){
            $tree = '<ul>';
            if($only_parent==false){
                foreach($cats[$parent_id] as $cat){
                    $tree .= '<li><a href="'.$cat['url'].'">'.$cat['name'].'</a>';
                    $tree .=  $this->_build_tree($cats,$cat['id']);
                    $tree .= '</li>';
                }
            }elseif(is_numeric($only_parent)){
                $cat = $cats[$parent_id][$only_parent];
                $tree .= '<li>'.$cat['name'].' #'.$cat['id'];
                $tree .=  $this->_build_tree($cats,$cat['id']);
                $tree .= '</li>';
            }
            $tree .= '</ul>';
        }
        else return null;
        return $tree;
    }

    function get_simple_menu($menu_name){
        $query = $this->menu_model->get_menu_query($menu_name);

        $html ='<nav class="blog-nav">';
        foreach ($query->result() as $row)
        {
            $href = $row->url;
            $name = $row->name;
            $html .= "<a class='blog-nav-item' href='".$href."'>";
            $html .= $name;
            $html .= "</a>";
        }
        $html .= "</nav>";
        return $html;

    }
    
 
}