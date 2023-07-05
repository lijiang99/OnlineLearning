<?php
/**
 * file: conn.inc.php 数据库连接文件
 */

try {
		/* 选择mydb作为默认数据库，连接本地主机localhost，用户名为root，密码为990503 */
		$pdo = new PDO('mysql:dbname=mydb;host=localhost', 'root', '990503');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
		/* 异常处理数据库连接失败 */
		echo 'Database connection failed: '.$e->getMessage();
		exit;
}
?>
