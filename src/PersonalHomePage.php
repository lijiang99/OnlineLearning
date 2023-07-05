<?php
/**
 * file: PersonalHomePage.php 个人主页面，查看个人信息和管理课程
 */

session_start(); /* 开启Session会话控制 */
include_once("conn.inc.php"); /* 包含所需要连接的数据库文件 */
if($_GET['action'] == 'myCenter'){ /* 如果用户的行为是点击了"个人中心"，则显示个人信息 */
		echo "<p><b>头像:</b><img src='{$_SESSION['user']['avatar']}'/><br>";
		echo "<p><b>头衔: ".$_SESSION['users_profile']['role'].'</b><br>';
		echo "<p><b>姓名: ".$_SESSION['users_profile']['name'].'</b><br>';
		echo "<p><b>性别: ".$_SESSION['users_profile']['gender'].'</b><br>';
		echo "<p><b>签名: ".$_SESSION['user']['signature'].'</b><br>';
		echo "<p><b>高校: ".$_SESSION['users_profile']['school'].'</b><br>';
		echo "<p><b>专业: ".$_SESSION['users_profile']['major'].'</b><br>';
		echo "<p><b>账号: ".$_SESSION['user']['account'].'</b><br>';
		echo "<p><b>邮箱: ".$_SESSION['user']['email'].'</b><br>';
		echo "<p><b>手机号: ".$_SESSION['user']['phone'].'</b><br>';

		/* 提供修改个人信息的链接 */
		echo '<a href="PersonalHomePage.php?action=alter" target="_blank"><b>修改信息</b></a>';
}
/* 如果用户的行为是点击了"我的课程"且是教师用户，则进入教师的课程管理页面ManageCoursePage */
if($_GET['action'] == "myCourse" && $_SESSION['users_profile']['role'] == '教师'){
		include_once("ManageCoursePage.php"); /* 包含课程管理页面的脚本文件 */
}
/* 如果用户的行为是点击了"我的课程"且是学生用户，则进入学生的课程页面StudentCoursePage */
if($_GET['action'] == "myCourse" && $_SESSION['users_profile']['role'] == '学生'){
		include_once("StudentCoursePage.php"); /* 包含学生课程页面的脚本文件 */
}
/* 如果在个人中心页面的行为是点击"修改信息"，则执行修改信息流程 */
if($_GET['action'] == "alter"){
		include_once("alterPersonalInfo.inc.php"); /* 包含修改个人信息的脚本文件 */
		if(isset($_POST['sub'])){
				/* 若用户提交表单，则进入修改个人信息流程 */
				$allowtype = array("image/jpg", "image/png", "image/gif"); /* 允许上传的图片类型 */
				if(isset($_FILES['avatar']) && in_array($_FILES['avatar']['type'], $allowtype)){
						include_once("func.inc.php"); /* 包含自定义的函数库文件 */
						$avatar = uploadImage('avatar'); /* 调用uploadImage方法，以表单的标签名作参数上传文件到本地服务器 */
						/* 若上传成功，则设置图片的路径 */
						if($avatar[0]){ $avatarURL = "./uploads/images/".$avatar[1]; $type = $_FILES['avatar']['type']; }
						else { $avatarURL = NULL; } /* 上传失败，则设置为NULL */
				}

				if($_SESSION['users_profile']['role'] == "教师") { $table = 'teachers'; } else { $table = 'students'; } /* 根据用户身份决定使用哪张数据表 */
				include_once("conn.inc.php"); /* 包含数据库连接文件 */

				if($avatarURL == NULL){ /* 若之前上传图片失败或未上传图片, 则设置avatarURL为原头像的路径地址 */
						try{
								$sql = "SELECT avatar, type FROM {$table} WHERE id=?";
								$query = $pdo->prepare($sql); $query->bindParam(1, $_SESSION['user']['id']);
								$query->execute(); $row = $query->fetch(PDO::FETCH_ASSOC);
								$avatarURL = $row['avatar']; $type = $row['type'];
						}catch(PDOException $e){ echo "<br>Database selection failed in PersonalHomePage.php?action=alter".$e->getMessage(); exit; }
				}

				/* 根据用户是否输入信息来设置属性值 */
				$gender = ((isset($_POST['gender']) && $_POST['gender']) ? $_POST['gender'] : $_SESSION['users_profile']['gender']);
				$signature = ((isset($_POST['signature']) && $_POST['signature']) ? $_POST['signature'] : $_SESSION['user']['signature']);

				/* 单独处理用户重置密码，若用户不想重置，则密码仍为原密码 */
				try{
						$sql = "SELECT password FROM {$table} WHERE id=?";
						$stmt = $pdo->prepare($sql); $stmt->bindParam(1, $_SESSION['user']['id']);
						$stmt->execute(); $result = $stmt->fetch(PDO::FETCH_ASSOC);
				}catch(PDOException $e){ echo "<br>Database selection failed in PersonalHomePage.php?action=alter".$e->getMessage(); exit; }

				/* 若用户重置密码，则对用户提交的信息进行md5加密，否则仍使用原来被md5加密过的密码 */
				$password = ((isset($_POST['password']) && $_POST['password'] ? md5($_POST['password']) : $result['password']));

				$email = ((isset($_POST['email']) && $_POST['email'] ? $_POST['email'] : $_SESSION['user']['email']));
				$phone = ((isset($_POST['phone']) && $_POST['phone'] ? $_POST['phone'] : $_SESSION['user']['phone']));

				/* 将数据插入数据库，进行数据更新，实现修改个人信息 */
				try{
						/* 更新个人信息表中的性别 */
						$sql = "UPDATE users_profile SET gender=? WHERE id=?";
						$stmt1 = $pdo->prepare($sql); $stmt1->bindParam(1, $gender); $stmt1->bindParam(2, $_SESSION['user']['users_profile_id']);
						$stmt1->execute();

						/* 更新学生或教师表中的相关信息 */
						$sql = "UPDATE {$table} SET password=?, email=?, avatar=?, type=?, phone=?, signature=? WHERE id=?";
						$stmt2 = $pdo->prepare($sql);
						$stmt2->bindParam(1, $password); $stmt2->bindParam(2, $email); $stmt2->bindParam(3, $avatarURL);
						$stmt2->bindParam(4, $type); $stmt2->bindParam(5, $phone); $stmt2->bindParam(6, $signature); $stmt2->bindParam(7, $_SESSION['user']['id']);
						$stmt2->execute();


						/* 如果更新成功，则要重新注册$_SESSION数组当中的用户信息 */
						if(($stmt1->rowCount() > 0) || ($stmt2->rowCount() > 0)){
								$sql = "SELECT id, role, name, gender, school, major FROM users_profile WHERE id = {$_SESSION['user']['users_profile_id']}";
								$stmt_user = $pdo->prepare($sql); $stmt_user->execute();
								$_SESSION['users_profile'] = $stmt_user->fetch(PDO::FETCH_ASSOC); /* 重新注册$_SESSION['users_profile']二维数组 */

								$sql="SELECT id, users_profile_id, account, password, email, avatar, type, phone, signature FROM {$table} WHERE users_profile_id = {$_SESSION['users_profile']['id']}";
								$query2 = $pdo->prepare($sql); $query2->execute();
								/* 重新注册$_SESSION['user']二维数组 */
								$_SESSION['user'] = $query2->fetch(PDO::FETCH_ASSOC);
							   	echo "<script>alert('个人信息修改成功！')</script>";
						}else{ echo "<script>alert('个人信息修改失败！')</script>"; }
				}catch(PDOException $e){ echo "<br>Database updation failed in PersonalHomePage.php?action=alter".$e->getMessage(); exit; }
		}
}
?>
