<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forms_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    //функция добавляет в бд все данные заявки
    function insert_form($form_data){
        $this->db->insert('forms', $form_data);
        $id = $this->db->insert_id();
        return $id;
    }

    function get_form($id){
        $result = $this->db->select('forms.*')
            ->where("forms.id", $id)
            ->from('forms')
            ->limit(1)
            ->get()
            ->row_array();
        return $result;
    }

}