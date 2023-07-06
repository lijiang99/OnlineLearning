<?php
/**
 * file: StudentAssignmentPage.php 用于处理学生查看作业和回答作业的脚本
 */
session_start();
include_once("conn.inc.php");

/* 用于学生查看教师已发布的作业 */
if($_GET['action'] == "myAssignment"){
		/* 先从作业表assignments表提取数据，则表示教师已发布到作业到该班级 */
		$sql = "SELECT id, title FROM assignments WHERE id IN(SELECT assignments_id FROM assignments_to_class WHERE courses_id={$_GET['courses_id']} AND classes_id IN(SELECT classes_id FROM courses_select WHERE courses_id={$_GET['courses_id']} AND students_id={$_SESSION['user']['id']}))";
		$stmt1 = $pdo->prepare($sql); $stmt1->execute();
		if($stmt1->rowCount() > 0){
				$allRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
				/* 输出显示教师已发布到指定班级的作业 */
				foreach($allRows as $row){
						echo $row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
						$sql = "SELECT id FROM reply WHERE courses_id={$_GET['courses_id']} AND students_id={$_SESSION['user']['id']} AND assignments_id={$row['id']}";
						$stmt2 = $pdo->prepare($sql); $stmt2->execute();
						if($stmt2->rowCount() > 0){
								/* 若已经完成，则提供学生查看作业回答结果的链接 */
								echo '<a href="StudentAssignmentPage.php?action=viewReply&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['id'].'&title='.$row['title'].'">已完成</a><br>';
						}
						else{
								/* 若未完成，则提供学生回答作业的链接 */
								echo '<a href="StudentAssignmentPage.php?action=replyAssignment&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['id'].'&title='.$row['title'].'">未完成</a><br>';
						}
				}
		}else{ echo '<h2>作业尚未发布</h2>'; }
}

/* 若学生选择回答作业，则执行该流程 */
else if($_GET['action'] == "replyAssignment"){
		try{
				/* 根据课程id和从题库question_bank中提取教师已发布的单元检测的全部题目 */
				$sql = "SELECT * FROM question_bank WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} ORDER BY id";
				$stmt = $pdo->prepare($sql); $stmt->execute();
				$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
 				$rank = 0; /* 通过循环递增$rank来显示是第几道选择题 */
				/* 制作学生回答选择题的表单 */
				echo '<form action="StudentAssignmentPage.php?action=replyAssignment&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'&title='.$_GET['title'].'" method="post" enctype="multipart/form-data">';
				echo '<h2>'.$_GET['title'].'</h2>'; /* 显示作业名 */
				foreach($allRows as $row){ /* 通过循环来输出单元检测中的每一道选择题 */
						echo '第'.++$rank.'题: '.$row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp('.$row['scores'].' 分)'.'<br>';
						echo '<input type="radio" name="number_'.$rank.'" value="A">';
						echo '<label>&nbsp&nbspA、'.$row['option_A'].'<br>';
						echo '<input type="radio" name="number_'.$rank.'" value="B">';
						echo '<label>&nbsp&nbspB、'.$row['option_B'].'<br>';
						echo '<input type="radio" name="number_'.$rank.'" value="C">';
						echo '<label>&nbsp&nbspC、'.$row['option_C'].'<br>';
						echo '<input type="radio" name="number_'.$rank.'" value="D">';
						echo '<label>&nbsp&nbspD、'.$row['option_D'].'<br><br>';
				}
				echo '<input type="submit" name="sub" value="提交"></form>';
		}catch(PDOException $e){
				echo '<br>Database selection failed in ChapterPage.php?action=replyAssignment: '.$e->getMessage().'<br>';
				exit;
		}
		if(isset($_POST['sub'])){ /* 判断用户是否提交表单 */
				for($i = 1; $i <= $rank; ++$i){ /* 通过循环判断学生是否回答了所有的选择题 */
						$flag = 'number_'.$i;
						if(isset($_POST[$flag]) && ($_POST[$flag])){ continue; }
						else{ break; }
				}
				if($i != $rank+1){ /* 若学生回答了全部选择题，$i == $rank+1，若未全部回答，则弹出提示框，并停留在回答页面 */
						echo '<script language="JavaScript">;alert("第'.$i.'题尚未回答！");location.href="StudentAssignmentPage.php?action=replyAssignment&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'&title='.$_GET['title'].'"</script>';
				}else{
						$item = 0; /* 通过循环递增来判断提交表单中的选择题顺序 */
						foreach($allRows as $row){
								++$item; $flag = 'number_'.$item;
								$question_bank_id = $row['id']; $students_id = $_SESSION['user']['id']; $title = $row['title']; $answers = $_POST[$flag];
								$scores = (($answers == $row['answers']) ? $row['scores'] : 0); /* 判断学生提交的答案和教师发布的答案是否匹配，匹配得分，不匹配0分 */
								$students_scores += $scores; /* 计算学生单元检测的成绩 */
								$total_scores += $row['scores']; /* 计算单元检测的总分值 */
								try{
										/* 将学生的回答信息插入学生回答表reply */
										$sql = "INSERT INTO reply(courses_id,assignments_id,question_bank_id,students_id,answers,scores,created_at) VALUES(?,?,?,?,?,?,?)";
										$stmt1 = $pdo->prepare($sql);
										$stmt1->bindParam(1, $_GET['courses_id']); $stmt1->bindParam(2, $_GET['assignments_id']);
										$stmt1->bindParam(3, $question_bank_id); $stmt1->bindParam(4, $students_id); $stmt1->bindParam(5, $answers); $stmt1->bindParam(6, $scores);
										$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $stmt1->bindParam(7, $mysqltime); /* 获取系统时间戳，并绑定参数 */
										$stmt1->execute();
										
										if(!$scores){ /* 如果学生回答错误，则将错误的选择题信息插入错题集表，用于学生以后查看专属错题集 */
												/* 根据学生id和作业id从回答表reply中提取id */
												$query = "SELECT id FROM reply WHERE question_bank_id={$question_bank_id} AND students_id={$students_id} ORDER BY id";
												$stmt2 = $pdo->prepare($query); $stmt2->execute(); $result = $stmt2->fetch(PDO::FETCH_ASSOC);

												$reply_id = $result['id']; $title = $row['title']; $error_answers = $answers; $correct_answers = $row['answers']; $analysis = $row['analysis'];
 												/* 将错误的选择题信息插入错题集表 */
												$sql = "INSERT INTO error_sets(reply_id,students_id,title,error_answers,correct_answers,analysis,created_at) VALUES(?,?,?,?,?,?,?)";
												$stmt3 = $pdo->prepare($sql);
												$stmt3->bindParam(1, $reply_id); $stmt3->bindParam(2, $students_id); $stmt3->bindParam(3, $title); $stmt3->bindParam(4, $error_answers);
												$stmt3->bindParam(5, $correct_answers); $stmt3->bindParam(6, $analysis); $stmt3->bindParam(7, $mysqltime);
												$stmt3->execute();
												if(!($stmt3->rowCount() > 0)){ echo '错题集设置失败！'; }
										}
								}catch(PDOException $e){
										echo '<br> Database insertion or selection failed in number: '.$item.' in ChapterPage.php?action=replyAssignment: '.$e->getMessage();
										exit;
								}
						}
						try{
								$sql = "SELECT classes_id FROM courses_select WHERE courses_id={$_GET['courses_id']} AND students_id={$_SESSION['user']['id']}";
								$stmt4 = $pdo->prepare($sql); $stmt4->execute(); $result4 = $stmt4->fetch(PDO::FETCH_ASSOC);

								/* 将学生的单元检测成绩相关数据插入成绩表test_scores */
								$sql = "INSERT INTO test_scores(courses_id,classes_id,assignments_id,students_id,scores,total_scores,created_at) VALUES(?,?,?,?,?,?,?)";
								$stmt5 = $pdo->prepare($sql);
								$stmt5->bindParam(1, $_GET['courses_id']); $stmt5->bindParam(2, $result4['classes_id']);
								$stmt5->bindParam(3, $_GET['assignments_id']); $stmt5->bindParam(4, $_SESSION['user']['id']);
								$stmt5->bindParam(5, $students_scores); $stmt5->bindParam(6, $total_scores); $stmt5->bindParam(7, $mysqltime);
								$stmt5->execute();
						}catch(PDOException $e){ echo '<br>Database insertion failed in ChapterPage.php?action=replyAssignment: '.$e->getMessage(); }
						/* 作业提交成功，则弹出提示框，并跳转到action=viewReply页面，可查看回答结果和成绩 */
						echo '<script language="JavaScript">;alert("作业提交成功！");location.href="StudentAssignmentPage.php?action=myAssignment&courses_id='.$_GET['courses_id'].'"</script>';
			}
		}
}

/* 若学生选择查看回答结果和成绩，则执行查看流程 */
if($_GET['action'] == "viewReply"){
		try{
				echo '<h2>'.$_GET['title'].'</h2>'; /* 根据url中传递的参数显示作业名 */
				$sql = "SELECT * FROM question_bank WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} ORDER BY id";
				$stmt = $pdo->prepare($sql); $stmt->execute(); $allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

 				$rank = 0; /* 通过循环递增$rank来显示是第几道选择题 */
				foreach($allRows as $row){
						/* 根据学生id和assignments_id来确定学生对于每道选择题具体的回答 */
						$query = "SELECT * FROM reply WHERE question_bank_id={$row['id']} AND students_id={$_SESSION['user']['id']}";
						$stmt1 = $pdo->prepare($query); $stmt1->execute(); $result = $stmt1->fetch(PDO::FETCH_ASSOC);
						
						echo '第'.++$rank.'题: '.$row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp('.$row['scores'].' 分)'.'<br>';
						/* 输出选项 */
						echo 'A、'.$row['option_A'].'&nbsp&nbsp&nbsp&nbsp'.'B、'.$row['option_B'].'&nbsp&nbsp&nbsp&nbsp';
						echo 'C、'.$row['option_C'].'&nbsp&nbsp&nbsp&nbsp'.'D、'.$row['option_D'].'<br>';
						/* 输出学生的回答 */
						echo '你的回答: '.$result['answers'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
						/* 若学生得分为0，则表示回答错误，否则回答正确 */
						if($result['scores']){ echo '回答正确<br>'; }
						else{ echo '回答错误<br>'; }
						echo '正确答案: '.$row['answers'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
						echo '解析: '.$row['analysis'].'<br><br>';
						
						/* 计算选择题总分$total_scores和学生的得分$your_scores */
						$total_scores += $row['scores']; $your_scores += $result['scores'];
				}
				echo '<h3>总分: '.$total_scores.'分</h3><h3>得分: '.$your_scores.'分</h3>'; /* 输出总分和得分 */
		}catch(PDOException $e){
				echo '<br>Database selection failed in ChapterPage.php?action=viewScores: '.$e->getMessage().'<br>';
				exit;
	}
}
?>
