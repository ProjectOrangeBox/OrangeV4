<?php

$config['script_attributes'] = ['src' => '', 'type' => 'text/javascript', 'charset' => 'utf-8'];

$config['link_attributes'] = ['href' => '', 'type' => 'text/css', 'rel' => 'stylesheet'];

$config['domready_javascript'] = 'document.addEventListener("DOMContentLoaded",function(e){%%});';

$config['page_prefix'] = 'page_';

/*
The asset plugin uses this to match the current url to assets
like the CodeIgniter router the first match is used
*/
/*
$config['asset_merge'] = [
	'admin/(.*)' => [
		'plugins'=>['flash_msg','form_helpers','rest_form','confirm_dialog','input_mask','keymaster','select3','table_sticky_header','tab_save','table_sort','table_remember_position','notify','bootbox'],
	],
	'(.*)' => [
		'plugins'=>['flash_msg','form_helpers','rest_form'],
	],
];
*/

$config['asset_merge'] = [
	'(.*)' => function() {
		//ci('page')->css($css);
		//ci('page')->js($js);
		//ci('page')->plugin($plugin);
	},
];

$config['usingBundle'] = true; /* testable by plugins to determine if asset bundles are being used */
$config['usingCDNs'] = true; /* testable by plugins to determine if a CDN urls should be used */
$config['pageMin'] = true; /* generate a constant PAGE_MIN which contains .min this can be used when building asset urls */

/* on every page merge's with page_??? ie. page_title or page_css */
$config['page_'] = [
	'title'=>'SkyNet',

	/*
	'meta'=>['attr'=>'','name'=>'','content'=>''],
	'script'=>'alert("Welcome!");',
	'domready'=>'alert("Page Loaded");',
	'title'=>'',
	'style'=>'* {font-family: roboto}',
	'js'=>'/assets/javascript.js',
	'css'=>'/assets/application.css',
	'body_class'=>'app',
	*/
];
