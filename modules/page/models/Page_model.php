<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    //функция возвращает все данные страницы page
    function get_page($url){
        $result = $this->db->where('meta_url', $url)->get('pages')->row_array();
        return $result;
    }

    //функция возвращает все данные категории страницы
    function get_category_data($cat_id){
        $result = $this->db->where('id', $cat_id)->get('category')->row_array();
        return $result;
    }

    //функция увеличивает число просмотров страницы
    function update_page_viewed($url){
        $this->db->where('meta_url', $url)->set('post_viewed', '`post_viewed`+ 1', FALSE)->update('pages');
    }
}