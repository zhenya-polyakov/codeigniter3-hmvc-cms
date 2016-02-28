<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Main_lib {

	function __construct()
	{
        $this->CI =& get_instance();
        $this->CI->config->load('common/site_settings', TRUE);
        //отладка и оптимизация
        //$this->CI->output->enable_profiler(TRUE);
	}

    // финальная сборка страницы сайта
	function render_main_page($data)
	{
        //$data += Modules::run('settings/get_settings');
        $data += $this->CI->config->item('site_settings');
        $this->CI->parser->parse("site/index.tpl", $data);
	}

    //проверяем существует ли страница в таблице бд
    function check_isset_page($url, $table){
        $this->CI->db->where('meta_url', $url)
            ->from($table);
        if($this->CI->db->count_all_results() < 1){
            show_404();
        }
    }

    function get_meta_data($url, $table){
        $result =  $this->CI->db->select('meta_title, meta_description, meta_keywords, name')->where('meta_url', $url)->get($table)->row_array();
        $result['meta_title'] != "" ? $result['meta_title'] = $result['meta_title'] : $result['meta_title'] = $result['name'];

        //добавляем суффикс к title если не пустой
        //$title_suffix = Modules::run('settings/get_one_setting', 'title_suffix');
        $title_suffix = $this->CI->config->item('title_suffix', 'site_settings');
        if(!empty($title_suffix)) $result['meta_title'] .= $title_suffix;

        return $result;
    }

    //конфигурация пагинации
    function pagination()
    {
        //элементов на cтранице
        $config["per_page"] = '5';

        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><span>';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['first_link'] = '&laquo;';
        $config['prev_link'] = '&lsaquo;';
        $config['last_link'] = '&raquo;';
        $config['next_link'] = '&rsaquo;';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';

        // $config['display_pages'] = FALSE;
        //$config['anchor_class'] = 'follow_link';
        return $config;
    }

    //функция подготавливает краткое содержание статьи для вывода в категории
    function short_content($text, $position = NULL, $more_tag = FALSE) {
        $this->CI->load->helper('text');
        if ($position == NULL && $more_tag == FALSE) {
            $position = '200'; //задаем количество символов для вывода
            $short_text = strip_tags($text, '<p><a>');
            $short_text = character_limiter($short_text, $position, '...');

        }
        if ($position != NULL && $more_tag == FALSE) {
            $short_text = strip_tags($text, '<p><a>'); //удаляем все теги в тексте кроме <a> и <p>
            $short_text = character_limiter($short_text, $position, '...');
        }
        elseif($more_tag){
            $position = strrpos($text, '<!-- more -->'); // поиск позиции точки с конца строки
            $text = substr($text, 0, $position); // обрезаем строку используя количество
            $short_text = strip_tags($text, '<p><a>');
        }

        return $short_text;
    }



}

