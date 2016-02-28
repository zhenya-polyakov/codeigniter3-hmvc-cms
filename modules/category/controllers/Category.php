<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends MX_Controller {

	var $module_name = "category";

	function __construct()
	{
		parent::__construct();
        $this->load->library('common/main_lib');
		$this->load->model('category_model');
	}

    function index(){
        show_404();
    }

    //вывод всех статей в разделе статьи или вывод одной статьи
    function view($url = null){
        //проверяем существует ли раздел
        $this->main_lib->check_isset_page($url, 'category');
        //выбираем все данные о категории
        $category = $this->category_model->get_category_by_url($url);
        //выводим 404 если страница отключена
        if($category['post_status'] == '0'){
            show_404();
        }
        //Определяем массив с мета данными текущей категории
        $data = $this->main_lib->get_meta_data($url, 'category');
        //Формируем содержание категории
        $data["content"] = $this->get_category($category);
        //выводим раздел
        $this->main_lib->render_main_page($data);
    }

    //вывод сгенерированного содержания страницы для общего шаблона по url
    function get_category($category){
        //выбираем все страницы раздела и делаем пагинацию
        $data_pages_links = $this->get_category_pages_links($category);
        //помещаем в массив данные всех страниц раздела
        $category["pages"] = $data_pages_links['pages'];
        //помещаем в массив данные о навигации раздела
        $category['links'] = $data_pages_links['links'];
        //выбираем все дочерние категории
        $category["categories"] = $this->category_model->get_child_categories($category['id']);
        //проверяем не задан ли шаблон для этой страницы
        $category['template'] != '' ? $template = $category['template'] : $template = "category_full.tpl";
        $data = $this->load->view("site/".$template,  $category,  true);
        return $data;
    }

    function get_category_pages_links($category)
    {
        //загружаем библиотеку пагинация
        $this->load->library('pagination');
        //подключаем конфигурацию пагинации bootstrap
        $config = $this->main_lib->pagination();
        //задаем путь раздела для навигации
        $config["base_url"] = base_url().'category/'.$category['meta_url'].'/';
        //задаем какой сегмент url определяет текущий уровень
        $config["uri_segment"] = 3;
        //подсчитываем общее число страниц в разделе для генерации уровней пагинации
        $config['total_rows'] = $this->category_model->count_category_pages($category['id']);
        //выбираем текущее положение пагинации по url или оставим пустой
        $uri_segment = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        //делаем выборку всех страниц для текущего раздела с учетом текущего уровня
        $pages = $this->category_model->get_all_category_page($config["per_page"], $uri_segment, $category['id']);
        //если страницы существуют, подготавливаем краткое содержание страницы используем функцию общей библиотеки
        if (!empty($pages)){
            for($i=0; $i<count($pages); $i++){
                $pages[$i]['content'] = $this->main_lib->short_content($pages[$i]['content']);
            }
        }
        //инициализируем постраничную навигации
        $this->pagination->initialize($config);
        //создаем список ссылок для перехода
        $data['links'] = $this->pagination->create_links();
        //возвращаем массив страниц для вывода в категории
        $data['pages'] = $pages;
        return $data;
    }

}