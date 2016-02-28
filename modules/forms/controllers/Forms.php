<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Forms extends MX_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->library('common/main_lib');
        $this->load->helper('security');
        $this->load->model('forms_model');
	}

	function get($id)
	{
		$data["captcha"] = $this->config->item('captcha', 'site_settings');
        $this->load->view("site/forms/forms_get_" . $id . ".tpl", $data);
	}

    function get_ajax($id)
    {
        $is_ajax = $this->input->is_ajax_request();
        if($is_ajax) {
            $data["captcha"] = $this->config->item('captcha', 'site_settings');
            $this->load->view("site/forms/forms_get_" . $id . ".tpl", $data);
        }
        else{
            show_404();
        }
    }

	function send_form1()
    {
        $is_ajax = $this->input->is_ajax_request();
        if ($is_ajax) {
            $post = $this->input->post();
            $post = $this->security->xss_clean($post);

            if ($this->_captcha_is_on()) {
               if($this->_check_captcha($post) == FALSE){
                   $error = 'Вы не правильно ввели проверочный код!<br/>Попробуйте отправить заявку еще раз.';
                   $this->show_error($error);
                   return FALSE;
               }
            }
            $fields = array();
            if(!isset($post['name']) || ($post['name'] == '')){
                $error = 'Вы не указали имя!';
                $this->show_error($error);
                return FALSE;
            }

            (isset($post['name'])) ? $fields['name'] = html_escape($post['name']) : $fields['name'] = '';
            (isset($post['tel'])) ? $fields['tel'] = html_escape($post['tel']) : $fields['tel'] = '';
            (isset($post['email'])) ? $fields['email'] = html_escape($post['email']) : $fields['email'] = '';

            $fields['message'] = '';
            (isset($post['message'])) ? $fields['message'] .= 'Сообщение: '.html_escape($post['message']).'<br/>
            ' : $fields['message'] .= '';

            $fields['ip'] = $this->input->ip_address();

            $to_base = array(
                'form_id' => 1,
                'status' => 1,
                'name' => $fields['name'],
                'tel' => $fields['tel'],
                'email' => $fields['email'],
                'message' => $fields['message'],
                'date' => date('Y-m-d H:i:s'),
                'ip' => $fields['ip']
            );
            $id = $this->forms_model->insert_form($to_base);

            if ($this->_send_mail()) {
                $this->_form_mail($id);
            }
            $this->_final($id);
        }
        else{
            show_404();
        }
	}

	function _form_mail($id)
	{
        $form = $this->forms_model->get_form($id);

		$theme = "Order: ".$form['id'];
        $message = "";
        $message .= "Имя: ".$form['name']."<br>";
        if($form['tel'] != ''){
            $message .= "Телефон: ".$form['tel']."<br>";
        }
        if($form['email'] != ''){
            $message .= "Email: ".$form['email']."<br>";
        }
        if($form['message'] != ''){
            $message .= "Комментарии: ".$form['message']."<br>";
        }

        //все данные для отправки берем из бд-настройки
        $data = $this->config->item('site_settings');

        $sitename = '=?UTF-8?B?'.base64_encode($data['sitename']).'?=';
        $email_sender = $data['email_sender'];
        $email_orders = $data['email_order'];

        $email_orders = explode(',',$email_orders);
        foreach($email_orders as $email_order)
        {
            $email_order = trim($email_order);
            mail($email_order, $theme, $message, "From: ".$sitename." <".$email_sender.">\nContent-Type: text/html;\n charset=utf-8\nX-Priority: 0");
        }
	}

    function _final($id)
    {
        $data['id'] = $id;
        $data += $this->config->item('site_settings');
        if(isset($data['forms_parser']))
        {
            $data['content'] = $data['forms_final_template'];
            $this->parser->parse("site/block_parse.tpl", $data);
        }
        else{
            $this->load->view("site/forms/forms_final.tpl", $data);
        }
    }

    //функция проверки включена ли капча в настройках сайта
    function _captcha_is_on(){
        //проверяем включена ли капча в настройках сайта
        $data = $this->config->item('captcha', 'site_settings');
        return $data;
    }

    //функция проверки отправки писем на почту
    function _send_mail(){
        //проверяем включена ли каптча в настройках сайта
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

    function show_error($message = FALSE){
        if($message){
            $data['message'] = $message;
            $this->load->view("site/forms/forms_show_error.tpl", $data);
        }
        else{
            return TRUE;
        }
    }

    //функция проверки правильной введенной капчи
    function _check_captcha($post){
        $code = $this->session->userdata('code');
        if ($post['code'] != $code){
            return FALSE;
        }
        else{
            return TRUE;
        }
    }

}
