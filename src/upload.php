<?php
/**
 * file: upload.php 用于进行文件分片上传的后台文件，实例化Upload对象，进行文件合并。
 */

session_start(); /* 开启Session会话 */
include_once("upload.class.php"); /* 导入后端处理分片文件的接口 */

/* 实例化并获取系统变量传参，其中$_SESSION数组中的值是由ResourcePage.php页面传递过来的 */
/* 所以必须先运行ResourcePage.php脚本才能获取$_SESSION数组中的值，否则后台合并文件会失败 */
$upload = new Upload($_FILES['file']['tmp_name'], $_POST['blob_num'], $_POST['total_blob_num'], $_POST['file_name'],
		$_FILES['file']['size'], "teachers", $_SESSION['courses_id'], $_SESSION['chapters_id'], $_SESSION['tasks_id'], $_SESSION['user']['id']);
?>
