<?php
/**
 * file: AssignmentPage.php 教师用于新建作业和查看已发布的作业的脚本
 */
session_start();
include_once("conn.inc.php");

echo '<a href="AssignmentPage.php?action=createAssignment&courses_id='.$_GET['courses_id'].'"><h2>新建作业</h2></a>'; /* 提供教师新建作业的链接 */
/* 若教师选择新建作业，则执行该流程 */
if($_GET['action'] == 'createAssignment'){
		/* 提供教师新建作业的表单 */
		echo '<form action="AssignmentPage.php?action=createAssignment&courses_id='.$_GET['courses_id'].'" method="post" enctype="multipart/form-data">';
		echo '<label>作业名: </label><input type="text" name="title"><br>';
		echo '<input type="submit" name="sub" value="确认">';
		/* 判断教师提交的信息是否完整 */
		if(isset($_POST['sub']) && isset($_POST['title']) && $_POST['title']){
				/* 将数据插入作业表 */
				$sql = "INSERT INTO assignments(courses_id,teachers_id,title,created_at) VALUES(?,?,?,?)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(1, $_GET['courses_id']); $stmt->bindParam(2, $_SESSION['user']['id']); $stmt->bindParam(3, $_POST['title']);
				$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $stmt->bindParam(4, $mysqltime); $stmt->execute();
				if($stmt->rowCount() > 0){
						echo '<script language="JavaScript">;alert("创建成功！");location.href="AssignmentPage.php?action=editAssignment&courses_id='.$_GET['courses_id'].'"</script>';
				}else{ echo "<script>alert('创建失败！')</script>"; }
		}
}
/* 若教师选择已经创建的作业进行题目编辑，则执行该流程 */
else if($_GET['action'] == "editAssignment"){
		$sql = "SELECT * FROM assignments WHERE courses_id={$_GET['courses_id']}";
		$stmt = $pdo->prepare($sql); $stmt->execute();
		if($stmt->rowCount() > 0){
				$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($allRows as $row){
						echo $row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
						/* 若作业的题目尚未编辑完成，则提供编辑题目的页面 */
						if($row['status'] == "待编辑"){ echo '<a href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['id'].'"><b>待编辑</b></a><br>'; }
						/* 若作业的题目已经编辑完成，则不能再进行编辑，提供查看链接 */
						else{ echo '<a href="QuestionPage.php?action=viewQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['id'].'" target="_blank"><b>已编辑</b></a><br>'; }
				}
		}else{ echo '<h3>尚未创建任何作业</h3>'; }
}

?>
