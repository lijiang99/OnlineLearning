<?php
/**
 * file: login.php 提供用户登录表单和处理用户登录
 */

session_start(); /* 开启Session会话控制 */
include_once("conn.inc.php"); /* 包含所要连接的数据库文件 */

if(isset($_POST['sub_login'])){
		/* 使用从表单中接收的账号和密码，作为在数据库学生表students中查询的条件 */
		$query_stu = "SELECT id, users_profile_id, account, password, email, avatar, type, phone, signature FROM students WHERE account = ? AND password = ?";
		$stmt_stu = $pdo->prepare($query_stu);
		$stmt_stu->execute(array($_POST['account'], md5($_POST['password']))); /* 使用md5加密算法使输入的密码与数据库中存放的密码匹配 */

		/* 如果能从students表中获取到数据则登录成功且是学生登录 */
		if($stmt_stu->rowCount() > 0){
				$_SESSION['user'] = $stmt_stu->fetch(PDO::FETCH_ASSOC); /* 将提取到的用户信息全部注册到数组SESSION['user']中，形成一个二维数组 */
				$_SESSION['isLogin'] = 1; /* 注册一个用来判断登录成功的变量 */
				header("Location:index.php"); /* 将脚本执行转向网站首页 */
		}else{
				/* 使用从表单中接收的账号和密码，作为在数据库教师表teachers中查询的条件 */
				$query_tech = "SELECT id, users_profile_id, account, email, avatar, type, phone, signature FROM teachers WHERE account = ? AND password = ?";
				$stmt_tech = $pdo->prepare($query_tech);
				$stmt_tech->execute(array($_POST['account'], md5($_POST['password']))); /* 使用md5加密算法使输入的密码与数据库中存放的密码匹配 */

				/* 如果能从teachers表中获取到数据则登录成功且是教师登录 */
				if($stmt_tech->rowCount() > 0){
						$_SESSION['user'] = $stmt_tech->fetch(PDO::FETCH_ASSOC); /* 将提取到的用户信息全部注册到SESSION['user']中，形成一个二维数组 */
						$_SESSION['isLogin'] = 1;
						header("Location:index.php");
				}
				else echo '<font color="red">用户名或密码错误！</font>'; /* 如果用户名或密码无效则登录失败 */
		}
}else if(isset($_POST['sub_register'])){
		/* 如果用户选择进行注册则转向注册页面 */
		header("Location:register.php");
}
?>

<html>
<!--用户登录表单-->
<head><title>系统登录</title></head>
<body>
<form action="login.php" method="post">
账号: <input type="text" name="account"><br>
密码: <input type="password" name="password"><br>
<input type="submit" name="sub_login" value="登录">
<input type="submit" name="sub_register" value="注册">
</form>
</body>
</html>
