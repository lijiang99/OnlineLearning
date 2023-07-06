<?php
/**
 * file: CoursePage.php 当教师点击进入课堂时，会执行该脚本，默认显示某一个特定课程的章节列表，并且教师可以进行章节的创建和发布，班级创建及管理
 */
session_start(); /* 开启Session会话 */
include_once("conn.inc.php"); /* 导入数据库连接文件 */

/* 处理教师查看班级和创建班级的流程 */
if($_GET['action'] == "viewClass" || $_GET['action'] == "createClass"){
		if($_GET['action'] == 'viewClass'){
				/* 提供教师创建班级的链接 */
				echo '<a href="CoursePage.php?action=createClass&courses_id='.$_GET['courses_id'].'"><h2>创建班级</h2></a>'; /* 提供教师新建章节的链接 */
				try{
						/* 先查询班级表calsses，若有数据则默认显示班级列表 */
						$sql = "SELECT * FROM classes WHERE courses_id={$_GET['courses_id']}";
						$stmt = $pdo->prepare($sql); $stmt->execute();
						if($stmt->rowCount() > 0){ /* 若提取得到数据，则显示班级列表 */
								echo '<table border="1" align="left" width="60%">';
								echo '<tr bgcolor="#cccccc">';
								echo '<th>班级号</th><th>邀请码</th><th>操作</th></tr>';
								$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
								foreach($allRows as $row){
										echo "<tr><td>".$row['class_number']."</td>";
										echo "<td>".$row['invite_code']."</td>";
										echo '<td><a href="CoursePage.php?action=viewStudents&courses_id='.$_GET['courses_id'].'&classes_id='.$row['id'].'&class_number='.$row['class_number'].'">查看学生</a></td></tr>';
								}
						}else{ echo '<h3>尚未创建任何班级</h3>'; }
				}catch(PDOException $e){ echo '<br>Database selection failed in CoursePage.php?action=viewClass: '.$e->getMessage(); exit; }
		}
		else if($_GET['action'] == "createClass"){ /* 若教师选择创建班级，则执行班级创建流程 */
				/* 提供教师创建班级的表单 */
				echo '<form action="CoursePage.php?action=createClass&courses_id='.$_GET['courses_id'].'" method="post" enctype="multipart/form-data">';
				echo '<label>班级编号: </label><input onkeyup="this.value=this.value.replace(/\D/g, \'\')" onafterpaste="this.value=this.value.replace(/\D/g, \'\')" name="class_number"><br>';
				echo '<input type="submit" name="sub" value="确认创建">';
				if(isset($_POST['sub']) && isset($_POST['class_number']) && $_POST['class_number']){ /* 判断用户提交的信息是否完整 */
						$sql = "INSERT INTO classes(courses_id, class_number, invite_code) VALUES(?,?,?)";
						$stmt = $pdo->prepare($sql); $stmt->bindParam(1, $_GET['courses_id']); $stmt->bindParam(2, $_POST['class_number']);
						include("func.inc.php"); $invite_code = nonceStr(6); /* 生成六位随机数作为邀请码 */
						while(!checkInviteCode($pdo, $invite_code)) { $invite_code = nonceStr(6); } /* 调用自定义的方法检查邀请码是否已存在，若已存在，则重新生成随机数 */
						$stmt->bindParam(3, $invite_code); $stmt->execute();
						/* 数据插入成功，则表示班级创建成功，跳转到查看班级列表的页面 */
						if($stmt->rowCount() > 0){ echo '<script language="JavaScript">;alert("创建成功！");location.href="CoursePage.php?action=viewClass&courses_id='.$_GET['courses_id'].'";</script>'; }
						else{ echo "<script>alert('创建失败！')</script>"; }
				}
		}
}

/* 如果教师选择查看某一班级的学生选课情况，则执行该流程 */
else if($_GET['action'] == "viewStudents"){
		$sql = "SELECT name,gender,major FROM users_profile WHERE id IN(SELECT users_profile_id FROM students WHERE id IN(SELECT students_id FROM courses_select WHERE courses_id={$_GET['courses_id']} AND classes_id={$_GET['classes_id']}))";
		$stmt = $pdo->prepare($sql); $stmt->execute();
		/* 如果提取到数据，则表示该班级已有学生选课，输出学生信息 */
		if($stmt->rowCount() > 0){
				echo '<h1>班级: '.$_GET['class_number'].'</h1>';
				echo '<table border="1" align="left" width="60%">';
				echo '<tr bgcolor="#cccccc">';
				echo '<th>姓名</th><th>性别</th><th>专业</th></tr>';
				$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($allRows as $row){ echo '<tr><td>'.$row['name'].'</td><td>'.$row['gender'].'</td><td>'.$row['major'].'</td></tr>'; }
		}else{ echo '<h3>尚未有该班级的学生选择课程</h3>'; }
}


?>
