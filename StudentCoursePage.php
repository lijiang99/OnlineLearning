<?php
/**
 * file: StudentCoursePage.php 学生课程页面，用于学生选课和查看课程
 */

session_start(); /* 开启Session会话 */

echo '<h1>我的课程</h1><p>';
/* 提供显示课程和选择课程的操作链接 */
echo '<a href="StudentCoursePage.php?action=showCourse">显示课程</a> ||';
include_once("searchCourse.inc.php"); /* 包含课程搜索文件，提供搜索框进行课程查询 */
echo '</p><hr>';

include_once("conn.inc.php"); /* 包含数据库连接文件 */

/* 如果学生用户使用搜索框，并提交了查询信息，则执行查询课程的流程 */
if(isset($_GET['sub']) && $_GET['invite_code'] != NULL){
		try{
				//$status = '已开始';
				$sql = "SELECT * FROM courses WHERE id IN(SELECT courses_id FROM classes WHERE invite_code={$_GET['invite_code']})";
				$stmt = $pdo->prepare($sql); $stmt->execute(); /* 执行准备好的语句 */
				if($stmt->rowCount() > 0){ /* 如果提取到了数据，则表示邀请码匹配成功，输出所有匹配课程的相关信息 */
						$row = $stmt->fetch(PDO::FETCH_ASSOC);
						if($row['status'] == '已开始'){
								/* 制作显示课程信息的表格 */
								echo '<table border="1" align="left" width=60%>';
								echo '<h2>搜索结果</h2>';
								echo '<tr bgcolor="#cccccc">';
								echo '<th>课程名</th><th>课程封面</th><th>任课教师</th><th>班级编号</th><th>课程简介</th><th>选课状态</th></tr>';
								/* 准备数据库查询语句，根据teachers_id到teachers表中选择对应的users_profile_id */
								$sql = "SELECT users_profile_id FROM teachers WHERE id={$row['teachers_id']}";
								$query1 = $pdo->prepare($sql); $query1->execute(); /* 执行准备好的语句 */
								$result1 = $query1->fetch(PDO::FETCH_ASSOC); /* 此时的$result1['users_profile_id']即为所需要的users_profile_id */
								/* 准备数据库查询语句，根据users_profile_id到users_profile表中获取教师姓名 */
								$sql = "SELECT name FROM users_profile WHERE id={$result1['users_profile_id']}";
								$query2 = $pdo->prepare($sql); $query2->execute(); /* 执行准备好的语句 */
								$result2 = $query2->fetch(PDO::FETCH_ASSOC); /* 此时的$result2['name']即为所需要的教师姓名 */
								$sql = "SELECT * FROM classes WHERE invite_code={$_GET['invite_code']}";
								$query3 = $pdo->prepare($sql); $query3->execute();
								$result3 = $query3->fetch(PDO::FETCH_ASSOC);
								
								/* 输出结果信息 */
								echo '<tr><td>'.$row['title'].'</td>';
								echo '<td><img height="80" src="'.$row['cover'].'"></td>';
								echo '<td>'.$result2['name'].'</td><td>'.$result3['class_number'].'</td><td>'.$row['summary'].'</td>';
								/* 准备语句，在选课表courses_select中查询，判断学生是否已经选择该课程 */
								$sql = "SELECT id from courses_select WHERE courses_id=? AND students_id=?";
								$query3 = $pdo->prepare($sql); $query3->bindParam(1, $row['id']); $query3->bindParam(2, $_SESSION['user']['id']); /* 绑定参数 */
								$query3->execute(); /* 执行准备好的语句 */
								if($query3->rowCount() > 0){ /* 如果能从数据表courses_select中提取到数据，则表明学生不需要再选课 */
										echo '<td>已选</td></tr>';
								}/* 若不能提取到数据，则显示未选课并提供选课链接，且在url中附带课程的courses_id作为参数 */
								else{ echo '<td>未选/<a href="StudentCoursePage.php?action=selectCourse&courses_id='.$row['id'].'&classes_id='.$result3['id'].'">加入课程</a></td></tr>'; }
						}else{echo "<h3>课程尚未发布</h3>";}
				}
				/* 若未提取到任何数据，则表示数据库中未匹配到学生用户所要查询的信息，输出提示消息 */
				else { echo "<h3>课程不存在</h3>"; }
		}
		catch(PDOException $e){ echo '<br/>Database selection failed: '.$e->getMessage(); exit; }
}
else if($_GET['action'] == 'selectCourse' && isset($_GET['courses_id'])){ /* 学生进行选课的操作流程，并且传递了所选课程的courses_id */
		include_once("student.class.php"); /* 包含学生Student类声明文件 */

		/* 实例化一个学生类对象，$_SESSION['user']二维数组中所注册的id，users_profile_id与学生表students中的数据项对应 */
		$student = new Student($_SESSION['user']['id'], $_SESSION['user']['users_profile_id']);

		/* 调用学生Student类中的SelectCourses方法，实现学生选课，并将选课数据添加到数据表courses_select */
		if($student->SelectCourses($pdo, $_GET['courses_id'], $_GET['classes_id'])){ /* 选课成功，则弹出窗口提示用户并返回学生课程页面StudentCoursePage.php */
				echo '<script language="JavaScript">;alert("选课成功");location.href="StudentCoursePage.php";</script>';
		}else{ /* 选课失败，则弹出窗口提示用户，并停留在当前页面StudentCoursePage.php?action=selectCourse */
				echo '<script language="JavaScript">;alert("选课失败");</script>';
		}
}else if($_GET['action'] == 'dropCourse'){ /* 处理学生退课流程 */
		echo $_GET['id'];
}
else{ /* 默认显示学生加入的所有课程的相关信息 */
		include_once("listCourse.inc.php");
}
?>
