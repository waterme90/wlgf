<?php
return array(
	//'配置项'=>'配置值'
	'URL_MODEL'  =>  '2',
	'DB_TYPE'    =>   'mysql',
	'DB_HOST'    =>   'localhost',
	'DB_NAME'    =>   'test',
	'DB_USER'    =>   'lxm',
	'DB_PWD'     =>   '',
	'DB_PORT'	  =>   '3306',
	'DB_CHARSET'=>   'utf8',
	'DB_DEBUG'  =>   'TRUE',

	 'TMPL_CACHE_ON' => false,
	'URL_CASE_INSENSITIVE'	=>	true,	// url不区分大小写
	'URL_HTML_SUFFIX'=>'',				// 隐藏伪静态后缀
	'MODULE_ALLOW_LIST'	=>	array('Home', 'Admin', 'Teacher'),

	/*路由规则*/
	'URL_ROUTER_ON'	=> true,	// 开启路由
	'URL_ROUTER_RULES'	=>	array(	


	),		// 配置路由规则


	/*'SESSION_EXPIRE'        =>  1440,
	'SESSION_TYPE'          => 	'DB',*/
);