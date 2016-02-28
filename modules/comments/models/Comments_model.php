<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    //функция возвращает все комментарии по типу и ID
    function get_all_comments($id, $comment_type){
        $result = $this->db->where('type', $comment_type)->where('article_id', $id)->where('public', 1)->order_by("created", "DESC")->get('comments')->result_array();
        return $result;
    }

    //функция возвращает данные комментария по ID
    function get_comment($id){
        $result = $this->db->select('comments.*')
            ->where("comments.id", $id)
            ->from('comments')
            ->limit(1)
            ->get()
            ->row_array();
        return $result;
    }

    //функция добавляет в бд все данные комментария
    function insert_comment($comment_data){
        $this->db->insert('comments', $comment_data);
        $id = $this->db->insert_id();
        return $id;
    }


}