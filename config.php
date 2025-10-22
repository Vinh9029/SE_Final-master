<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
             || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'];
$port = $_SERVER['SERVER_PORT'];
$addLink = "/xampp/htdocs";
$addLink="";
$project_folder = basename(dirname(__FILE__));

// Add port to base_url if not default (80 for http, 443 for https)
$port_part = ($port != 80 && $port != 443) ? ":$port" : "";

$base_url = $protocol . $host . $port_part . $addLink . "/" . $project_folder;
?>