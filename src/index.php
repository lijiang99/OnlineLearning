<?php
/**
 * file: index.php 网站主页面
 */

session_start(); /* 开启Session会话控制 */
include_once("conn.inc.php"); /* 包含所需要连接的数据库文件 */
echo "学习网站<br/>";
/* 判断Session中的登录变量是否为真 */
if(isset($_SESSION['isLogin']) && $_SESSION['isLogin'] === 1){
		/* 若已经登录则可以根据数组SESSION['user']中注册的users_profile_id作为过滤条件从数据库中获取用户详细信息 */
		$query_user = "SELECT id, role, name, gender, school, major FROM users_profile WHERE id = {$_SESSION['user']['users_profile_id']}";
		$stmt_user = $pdo->prepare($query_user); /* 准备数据库查询语句 */
		$stmt_user->execute();

		/* 将提取到的用户信息注册到数组SESSION['users_profile']中，形成一个二维数组 */
		$_SESSION['users_profile'] = $stmt_user->fetch(PDO::FETCH_ASSOC);

		/* 如果用户点击"个人中心"或者"我的课程"则跳转到PersonalHomePage页面 */
		echo '<a href="PersonalHomePage.php?action=myCenter" target="_blank">个人中心</a>&nbsp&nbsp<a href="PersonalHomePage.php?action=myCourse" target="_blank">我的课程</a>';
}else{ /* 尚未登录则提供登录链接 */
		echo "<a href='login.php'>登录/注册</a></p>";
}
?>
