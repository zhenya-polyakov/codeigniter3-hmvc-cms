<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['page'] = 'page';
$route['page/(:any)'] = 'page/view/$1';