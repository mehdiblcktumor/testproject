<?php
require __DIR__ . '/app/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
verify_csrf();unset($_SESSION['user_id']);session_regenerate_id(true);flash('success','از حساب کاربری خارج شدید.');header('Location: '.site_url());exit;
