<?php

require_once('../inc/config.inc.php');

ini_set('include_path', ini_get('include_path').':..');

define('BASE', APP_PATH."docu/");
define('BUILD_HTML', '/_build/html/');
define('BUILD_PDF', '/_build/latex/');
define('BUILD_EPUB', '/_build/epub/');
define('FALLBACK_LANG', 'de'); // XXX for now de, later en

$lang = 'de';

$filename = '/';
if (isset($_SERVER['PATH_INFO'])) {
        $filename = $_SERVER['PATH_INFO'];
}
if ($filename=='/') {
        $filename = 'index.html';
}

$ppos = strrpos($filename, '.');
$suffix = $ppos!==FALSE ? substr($filename, $ppos+1, strlen($filename)-$ppos) : '';

$file = BASE."$lang";
if ($suffix=='pdf') {
        $file .= BUILD_PDF;
} else if ($suffix=='epub') {
        $file .= BUILD_EPUB;
} else {
        $file .= BUILD_HTML;
}
$file .= $filename;
$file = realpath($file);
if (strpos($file, APP_PATH)!==0) {
        header("HTTP/1.0 404 Not Found");
        print "404 not found";
        die();
}
$mimetypes = array(
        'pdf' => "application/pdf",
        'css' => "text/css",
        'js'  => "text/js",
        'html'=> "text/html",
);
header('Content-type: '.(isset($mimetypes[$suffix]) ? $mimetypes[$suffix] : "application/download"));
readfile($file);
