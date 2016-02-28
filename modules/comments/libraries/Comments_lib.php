<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Comments_lib {

    public $categories = array();
    public $level = 0;

    function __construct()
    {
        $this->CI = get_instance();
    }

    //создание древовидного каталога для списка комментариев
    function _build_comments($comments) {
        $this->menu = $comments;
        $new_cats = array();
        if ($this->menu)
            foreach ($this->menu as $cats) {
                if ($cats['parent_id'] == 0) {
                    # Category Level
                    $cats['level'] = $this->level;
                    # Category SubTree
                    $sub = $this->_get_sub_comment($cats['id']);
                    if (count($sub))
                        $cats['subtree'] = $sub;
                        array_push($new_cats, $cats);
                }
            }
        unset($this->menu);
        return $new_cats;
    }

    //выборка дочерних комментариев
    function _get_sub_comment($parent_id) {
        $new_sub_cats = array();
        $this->level++;
        foreach ($this->menu as $sub_cats) {
            if ($sub_cats['parent_id'] == $parent_id) {
                $sub_cats['level'] = $this->level;
                $sub = $this->_get_sub_comment($sub_cats['id']);
                if (count($sub))
                    $sub_cats['subtree'] = $sub;
                    array_push($new_sub_cats, $sub_cats);
            }
        }
        $this->level--;
        return $new_sub_cats;
    }



}

