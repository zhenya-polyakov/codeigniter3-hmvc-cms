<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends MX_Controller {

    var $module_name = "page";

    function __construct()
    {
        parent::__construct();
        $this->load->library('common/main_lib');
        $this->load->model('page_model');
    }

    function index(){
        $this->view('homepage');
    }

    //вывод всех статей в разделе статьи или вывод одной статьи
    function view($url = null){
        $this->main_lib->check_isset_page($url, 'pages');
        $data = $this->main_lib->get_meta_data($url, 'pages');

        $data["content"] = $this->get_page($url);
        $this->main_lib->render_main_page($data);
    }

    //вывод сгенерированного содержания страницы для общего шаблона по url
    function get_page($url){
        $result = $this->page_model->get_page($url);

        //выводим 404 если страница отключена
        if($result['post_status'] == '0'){
            show_404();
        }
        if($result['cat_id'] != 0){
            $result['category'] = $this->page_model->get_category_data($result['cat_id']);
        }

        //увеличиваем колличество просмотров данной страницы
        $this->page_model->update_page_viewed($url);

        //проверяем не задан ли шаблон для этой страницы
        $template = $result['template'];
        $template != '' ? $template = $template : $template = "page_full.tpl";

        $data = $this->load->view("site/".$template,  $result,  true);
        return $data;
    }

}
