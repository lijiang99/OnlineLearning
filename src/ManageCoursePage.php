<?php
/**
 * file: ManageCoursePage.php 教师的课程管理页面，用于查看课程、创建课程
 */

session_start(); /* 开启Session会话 */
echo '<h1>课程管理</h1><p>';
/* 提供显示课程和创建课程的操作链接 */
echo '<a href="ManageCoursePage.php?action=showCourse">显示课程</a> || ';
echo '<a href="ManageCoursePage.php?action=createCourse">创建课程</a> || ';
echo '</p><hr>';

if($_GET['action'] == 'createCourse'){ /* 如果教师的选择创建课程，则进入课程创建流程 */
		include_once("createCourse.inc.php"); /* 包含创建课程所要用到的表单文件 */
		include_once("course.class.php"); /* 包含所要用到的课程Course类声明文件 */
		include_once("teacher.class.php"); /* 包含所要用到的教师Teacher类声明文件 */
		if(isset($_POST['sub'])){
				if(isset($_POST['course_title']) && $_POST['course_title'] != NULL){
						/* 如果教师提交的表单符合要求，则实例化一个Course类对象 */
						$course = new Course($_SESSION['user']['id'], $_POST['course_title']);
						
						/* 设置课程封面，并判断用户提交的图片文件是否符合要求 */
						$allowtype = array("image/jpg", "image/png", "image/gif"); /* 设置允许上传的图片类型 */
						if(isset($_FILES['course_cover']) && in_array($_FILES['course_cover']['type'], $allowtype)){
								include_once("func.inc.php"); /* 导入自定义的函数文件 */
								/* 以表单中上传的图片文件的标签名作为参数，调用uploadImage函数，将用户上传的课程封面文件上传到本地服务器 */
								$image = uploadImage('course_cover');
								/* 如果图片上传成功，则设置图片路径和图片类型，否则使用默认的课程图片 */
								if($image[0]){ $coverURL = "./uploads/images/".$image[1]; $type = $_FILES['course_cover']['type']; }
								else { $coverURL = "./uploads/images/defaultCover.png"; $type = "image/png"; }
						}else{ /* 若用户上传图片不符合要求，则使用当前目录的uploads目录下images目录中的默认课程封面图片 */
								$coverURL = "./uploads/images/defaultCover.png";
								$type = "image/png";
						}
						
						/* 自动调用Course类的__set魔术方法来设置成员属性值 */
						$course->cover = $coverURL;
						$course->type = $type;
						isset($_POST['course_summary']) ? ($course->summary = $_POST['course_summary']) : ($course->summary = ''); 
						isset($_POST['chapters_count']) ? ($course->chapters_count = $_POST['chapters_count']) : ($course->chapters_count = 0);
						
						/* 实例化一个教师Teacher类对象，将课程信息存入数据库实现课程创建 */
						$teacher = new Teacher($_SESSION['user']['id'], $_SESSION['user']['users_profile_id']);
						$teacher->CreateCourse($course);
						$teacher->InsertIntoCourses($pdo);
						echo '<script language="JavaScript">;alert("课程创建成功");location.href="ManageCoursePage.php";</script>;';
				}
				/* 教师提交的课程信息不完整，提醒用户后再返回创建课程的表单页面 */
				else{ echo "<script>alert('课程信息不完整！')</script>"; }
		}
}else if($_GET['action'] == 'publishCourse'){ /* 如果教师选择发布课程，则修改课程表courses中该课程的状态status */
		/* 根据GET数组中的id——courses表中的课程id，来确认所要发布的课程 */
		include_once("conn.inc.php"); /* 包含数据库连接文件 */
		try{
				$update = "UPDATE courses SET status=? WHERE id=?";
				$stmt = $pdo->prepare($update); /* 准备数据库更新语句 */
				$status = '已开始'; $stmt->bindParam(1, $status); $stmt->bindParam(2, $_GET['id']);
				$stmt->execute(); /* 执行准备好的语句 */
				/* 如果UPDATE语句执行成功，并对数据表courses有行数影响，则更新数据成功 */
				if($stmt->rowCount() > 0){ /* 提示教师是否发布成功，并返回到课程管理页面 */
						echo '<script language="JavaScript">;alert("课程发布成功");location.href="ManageCoursePage.php";</script>;';
				}else{
						echo '<script language="JavaScript">;alert("课程发布失败");location.href="ManageCoursePage.php";</script>;';
				}
		}catch(PDOException $e){ echo '<br/>Database updation failed: '.$e->getMessage(); exit; } /* 异常处理 */

}else if($_GET['action'] == 'deleteCourse'){ /* 如果教师选择删除课程，则从数据库中删除有关该课程的所有信息 */
		echo $_GET['id'];
}
/* 默认显示教师创建的所有课程的相关信息 */
else{
		include_once("listCourse.inc.php");
}
?>
