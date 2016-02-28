<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Comments extends MX_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->library('common/main_lib');
        $this->load->library('comments/comments_lib');
        $this->load->model('comments_model');
        $this->load->helper('security');
	}

    function get_all_comments($id, $comment_type){
        $comments = $this->comments_model->get_all_comments($id, $comment_type);

        $data['total_comments'] = count($comments);
        $tree = $this->comments_lib->_build_comments($comments);
        $data["comments"] = $this->renderCommentList($tree);

        $this->load->view("site/comments/comment_index.tpl", $data);
    }

    //рендер всех комментариев по шаблону
    private function renderCommentList($tree) {
        $html = '';
        foreach ($tree as $item) {
            $html .= '<div class="single-comment">';
            $html .= $this->load->view('site/comments/comment_render.tpl', array('item' => $item), true);
            if (isset($item['subtree'])) {
                $html .= '<div class="multilevel-commtents">';
                $html .= $this->renderCommentList($item['subtree']);
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    function get_comment_form($id = null, $comment_type = 'pages')
    {
        $data["captcha"] = $this->_captcha_is_on();
        $data['article_id'] = $id;
        $data['comment_type'] = $comment_type;

        $this->load->view("site/comments/comment_form.tpl", $data);
    }

    function get_comment_form_ajax($id = null, $comment_type = 'pages')
    {
        $is_ajax = $this->input->is_ajax_request();
        if($is_ajax) {
            $data["captcha"] = $this->_captcha_is_on();
            $data['article_id'] = $id;
            $data['comment_type'] = $comment_type;
            $this->load->view("site/comments/comment_form.tpl", $data);
        }
        else{
            show_404();
        }
    }

    function get_reply_comment_form()
    {
        $data["captcha"] = $this->_captcha_is_on();
        $is_ajax = $this->input->is_ajax_request();
        if($is_ajax) {
            $post = $this->input->post();
            $post = $this->security->xss_clean($post);
            $data['article_id'] = $post['article_id'];
            $data['comment_id'] = $post['comment_id'];
            $data['comment_type'] = $post['comment_type'];

            $this->load->view("site/comments/comment_reply_form.tpl", $data);
        }
        else{
            show_404();
        }
    }

    function send_reply_comment($comment_type = NULL)
    {
        $is_ajax = $this->input->is_ajax_request();
        if ($is_ajax) {
            $post = $this->input->post();
            $post = $this->security->xss_clean($post);
            if ($this->_captcha_is_on() == 1) {
                if ($this->_check_captcha($post) == false) {
                    exit();
                }
            }
            (isset($post['site'])) ? $post['site'] = $post['site'] : $post['site'] = '';
            (isset($post['parent_id'])) ? $parent_id = $post['parent_id'] : $parent_id = '0';
            (!isset($comment_type)) ? $comment_type = 'pages' : '';
            (isset($post['created'])) ? $created = $post['created'] : $created = date('Y-m-d H:i:s');

            $user_ip = $this->input->ip_address();
            $sended_comments_by_ip = $this->db->where("`created` > (NOW() - INTERVAL 5 MINUTE)")->where('ip', $user_ip)->get('comments')->result_array();
            //$sended_comments_by_ip = '';
            if(!empty($sended_comments_by_ip)){
                $data = array();
                $this->load->view("site/comments/comment_disable.tpl", $data);
            }
            else{
                $to_base = array(
                    'article_id' => $post['article_id'],
                    'parent_id' => $parent_id,
                    'type' => $comment_type,
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'site' => $post['site'],
                    'message' => $post['message'],
                    'created' => $created,
                    'public' => 1,
                    'ip' => $user_ip
                );
                $this->db->insert('comments', $to_base);
                $id = $this->db->insert_id();
                if ($this->_send_mail() == '1') {
                    $this->_comment_mail($id);
                }
                $this->_final_reply($id);
            }

        }
        else{
            show_404();
        }
    }

    function send_comment($comment_type = 'pages')
    {
        $is_ajax = $this->input->is_ajax_request();
        if ($is_ajax) {
            $post = $this->input->post();
            $post = $this->security->xss_clean($post);
            if ($this->_captcha_is_on() == 1) {
                if ($this->_check_captcha($post) == false) {
                    exit();
                }
            }
            (isset($post['site'])) ? $post['site'] = $post['site'] : $post['site'] = '';
            (isset($post['parent_id'])) ? $parent_id = $post['parent_id'] : $parent_id = '0';
            $user_ip = $this->input->ip_address();

            $sended_comments_by_ip = $this->db->where("`created` > (NOW() - INTERVAL 5 MINUTE)")->where('ip', $user_ip)->get('comments')->result_array();
            //$sended_comments_by_ip = '';
            if(!empty($sended_comments_by_ip)){
                $data = array();
                $this->load->view("site/comments/comment_disable.tpl", $data);
            }
            else{
                $to_base = array(
                    'article_id' => $post['article_id'],
                    'parent_id' => $parent_id,
                    'type' => $comment_type,
                    'name' => $post['name'],
                    'email' => $post['email'],
                    'site' => $post['site'],
                    'message' => $post['message'],
                    'created' => date('Y-m-d H:i:s'),
                    'public' => 1,
                    'ip' => $user_ip
                );
                $id = $this->comments_model->insert_comment($to_base);

                if ($this->_send_mail() == '1') {
                    $this->_comment_mail($id);
                }
                $this->_final($id);
            }

        }
        else{
            show_404();
        }
    }

    function _final($id)
    {
        $data['id'] = $id;
        $data += $this->config->item('site_settings');
        $this->load->view("site/comments/comment_final.tpl", $data);
    }
    function _final_reply($id)
    {
        $data['id'] = $id;
        $data += $this->config->item('site_settings');
        $this->load->view("site/comments/comment_final_reply.tpl", $data);
    }

    function _comment_mail($id)
    {
        $comment = $this->comments_model->get_comment($id);

        if($comment['type'] == 'pages') {
            $page_data = $this->db->where('id', $comment['article_id'])->get('pages')->row_array();
        }
        elseif($comment['type'] == 'category') {
            $page_data = $this->db->where('id', $comment['article_id'])->get('category')->row_array();
        }

        $theme = "Новый комментарий к статье: ".$page_data['name'];
        $message = "";
        $message .= "Имя: ".$comment['name']."<br>";

        if($comment['email'] != ''){
            $message .= "Email: ".$comment['email']."<br>";
        }
        if($comment['message'] != ''){
            $message .= "Комментарии: ".$comment['message']."<br>";
        }
        $message .= "Дата : ".$comment['created']."<br>";

        if($comment['type'] == 'pages') {
            $message .= 'Статья: <a href="' . base_url() . 'page/' . $page_data['meta_url'] . '">' . $page_data['name'] . '</a><br>';
        }
        elseif($comment['type'] == 'category') {
            $message .= 'Категория: <a href="' . base_url() . 'category/' . $page_data['meta_url'] . '">' . $page_data['name'] . '</a><br>';
        }

        //все данные для отправки берем из бд-настройки
        $sitename = $data = $this->config->item('sitename', 'site_settings');
        $email_sender = $data = $this->config->item('email_sender', 'site_settings');
        $email_orders = $data = $this->config->item('email_order', 'site_settings');

        $sitename = '=?UTF-8?B?'.base64_encode($sitename).'?=';

        $email_orders = explode(',',$email_orders);
        foreach($email_orders as $email_order)
        {
            $email_order = trim($email_order);
            mail($email_order, $theme, $message, "From: ".$sitename." <".$email_sender.">\nContent-Type: text/html;\n charset=utf-8\nX-Priority: 0");
        }
    }

    //функция проверки включена ли каптча в настройках сайта
    function _captcha_is_on(){
        //проверяем включена ли каптча в настройках сайта
        $data = $this->config->item('captcha', 'site_settings');
        return $data;
    }

    //функция проверки отправки писем на почту
    function _send_mail(){
        //проверяем отправку писем администратору
        $data = $this->config->item('send_mail', 'site_settings');
        return $data;
    }

    //функция подключения капчи
    function captcha(){
        $char = rand(1000, 9999);
        $im = @imagecreate (50, 20) or die ("Cannot initialize new GD image stream!");
        $bg = imagecolorallocate ($im, 255, 255, 255);

        $white = imagecolorallocate ($im, 0, 0, 0);
        imagettftext ($im, 15, 0, 1, 17, $white, "./plugins/captcha/font.ttf", $char );
        $this->session->set_userdata('code', $char);

        //антикеширование
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        //создание рисунка в зависимости от доступного формата
        if (function_exists("imagepng")) {
            header("Content-type: image/png");
            imagepng($im);
        } elseif (function_exists("imagegif")) {
            header("Content-type: image/gif");
            imagegif($im);
        } elseif (function_exists("imagejpeg")) {
            header("Content-type: image/jpeg");
            imagejpeg($im);
        } else {
            die("No image support in this PHP server!");
        }
        //return $im;
        imagedestroy ($im);

    }

    //функция проверки правильной введенной капчи
    function _check_captcha($post){
        $code = $this->session->userdata('code');
        if ($post['code'] != $code){
            echo '<h3 style="color: red;">Вы не правильно ввели проверочный код!</h3> <p align="center">Попробуйте отправить заявку еще раз.</p>';
            return false;
        }
        else{
            return true;
        }
    }


}
