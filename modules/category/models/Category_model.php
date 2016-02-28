<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category_model extends CI_Model {
    
    function __construct()
    {
        parent::__construct();
    }

    //Функция выбирает каталог по meta_url
    function get_category_by_url($url)
    {
        $result = $this->db->where('meta_url', $url)->get('category')->row_array();
        return $result;
    }

    //выбираем все дочерние категории
    function get_child_categories($category_id){
        $result = $this->db->where('parent_id', $category_id)->where('post_status', 1)->get('category')->result_array();
        return $result;
    }

    //выборка всех страниц данного каталога для пагинации
    function get_all_category_page($num, $offset, $catalog_id = null){
        $this->db->select('*');
        if ($catalog_id != null){
            $this->db->where('cat_id', $catalog_id);
        }
        $this->db->where('post_status', 1);
        $this->db->order_by("pages.edited", "DESC");
        $result = $this->db->get("pages", $num, $offset)->result_array();
        return $result;
    }

    //Подсчет количества страницы категории по id для пагинации
    public function count_category_pages($id){
        $this->db->where('cat_id', $id);
        $this->db->from('pages');
        $result = $this->db->count_all_results();
        return $result;
    }


}