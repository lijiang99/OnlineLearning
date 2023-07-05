<?php
/**
 * file: register.php 提供用户注册表单和处理用户注册
 */

if(isset($_POST['sub_confirm_register'])){ /* 用户提交了注册信息，判断是否符合要求 */
		/* 用户提交了完整的注册信息 */
		if(isset($_POST['name']) && ($_POST['name']) && isset($_POST['idcard']) && ($_POST['idcard'])
				&& isset($_POST['school']) && ($_POST['school']) && isset($_POST['major']) && ($_POST['major'])
				&& isset($_POST['email']) && ($_POST['email']) && isset($_POST['role']) && ($_POST['role'])
				&& isset($_POST['password']) && ($_POST['password']) && ($_POST['password'] == $_POST['confirm_password'])){
				include_once("register.class.php"); /* 导入注册类Register的类声明文件 */
				
				/* 调用Register类构造方法实例化一个对象 */
				$register = new Register($_POST['role'], $_POST['name'], $_POST['idcard'], $_POST['school'], $_POST['major'], $_POST['gender']);
				
				/* 调用Register类方法设置用户账号密码信息 */
				include_once("func.inc.php"); /* 导入自定义的函数库文件 */
				include_once("conn.inc.php"); /* 导入数据库连接文件 */

				$account = nonceStr(11); /* 调用自定义的nonceStr函数，生成11位由随机数组成的字符串 */
				while(!checkAccount($pdo, $account)){ $account = nonceStr(11); } /* 调用自定义的checkAccount函数检查账号是否存在，若账号已存在，则重新生成随机数字符串 */
				$register->SetAccountInfo($account, $_POST['password']);
				
				/* 调用Register类Set系列方法设置用户其他非必选信息: 邮箱，电话，个性签名 */
				$email = (isset($_POST['email']) ? $_POST['email'] : '');
				$phone = (isset($_POST['phone']) ? $_POST['phone'] : '');
				$signature = (isset($_POST['signature']) ? $_POST['signature'] : '');
				$register->SetEmailInfo($email)->SetPhoneInfo($phone)->SetSignatureInfo($signature);

				/* 设置用户头像信息，并判断用户提交的图片文件是否符合要求 */
				$allowtype = array("image/jpg", "image/png", "image/gif"); //设置允许上传的图片类型
				if(isset($_FILES['avatar']) && in_array($_FILES['avatar']['type'], $allowtype)){
						include_once("func.inc.php"); /* 导入自定义的函数文件 */
						/* 以表单中上传的图片文件的标签名作为参数，调用uploadImage函数，将用户的图片文件上传到本地服务器 */
						$image = uploadImage('avatar');

						/* 若果上传成功，则图片的存放路径为当前目录的uploads目录的images目录下，否则使用默认头像图片 */
						if($image[0]){ $imageURL = "./uploads/images/".$image[1]; $type = $_FILES['avatar']['type']; }
						else { $imageURL = "./uploads/images/defaultAvatar.jpg"; $type = 'image/jpg'; }

						$register->SetAvatarInfo($imageURL, $type); /* 调用Register类成员方法设置用户头像 */

				}else{ /* 如果用户没有上传图片或上传的图片不符合要求，则默认使用当前目录的uploads目录的images目录下的默认头像图片 */
						$path = "./uploads/images/defaultAvatar.jpg";
						$register->SetAvatarInfo($path, "image/jpg");
				}

				/* 调用Register类的成员方法将信息添加到数据库 */
				$register->InsertIntoUsersProfile($pdo);
				$register->InsertIntoStudentsOrTeachers($pdo);
				echo '<script language="JavaScript">;alert("注册成功！您的账号为：'.$account.'");location.href="index.php";</script>';
		}else if($_POST['password'] != $_POST['confirm_password']){ /* 若用户两次输入的密码不匹配，则提醒用户 */
				echo "<script>alert('两次密码不匹配！')</script>";
		}
		/* 用户提交的注册信息不完整，提醒用户后再返回注册页面 */
		else{ echo "<script>alert('注册信息不完整！')</script>"; }
}
?>

<html>
<!--用户注册表单-->
<head><title>注册账号</title></head>
<body>
<form action="register.php" method="post" enctype="multipart/form-data">
*真实姓名: <input type="text" name="name"><br>
*身份证号: <input type="text" name="idcard"><br>
*所属院校: <select name="school"><option>江苏科技大学</option></select>
*所属专业: <select name="major"><option>物联网工程</option><option>通信工程</option><option>计算机科学与技术</option><option>软件工程</option></select><br>
电话号码: <input type="text" name="phone"><br>
*电子邮箱: <input type="text" name="email"><br>
性别: <input type="radio" id="gender-male" name="gender" value="男"><label for="gender-male">男</label>
      <input type="radio" id="gender-female" name="gender" value="女"><label for="gender-female">女</label><br>
*身份: <input type="radio" id="student" name="role" value="3"><label for="student">学生</label>
      <input type="radio" id="teacher" name="role" value="2"><label for="teacher">教师</label><br>
<label for="picture">头像设置: </label>
<input type="file" id="picture" name="avatar"><br>
个性签名: <input type="text" name="signature"><br>
*密码: <input type="password" name="password"><br>
*确认密码: <input type="password" name="confirm_password"><br>
<aside><p>注: *号为必填项</p></aside>
<input type="submit" name="sub_confirm_register" value="确认注册">
</body>
</html>
