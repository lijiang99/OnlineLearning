<?php
/**
 * file: QuestionPage.php 教师用于编辑作业选择题的脚本
 */
session_start();
include_once("conn.inc.php");

/* 如果教师选择编辑作业，则执行作业编辑并发布的流程，教师可进行新建选择题，删除选择题，查看已发布的作业，发布作业且作业一旦发布，则不能再进行编辑 */
if($_GET['action'] == "editQuestion" || $_GET['action'] == "createQuestion" || $_GET['action'] == "deleteQuestion" || $_GET['action'] == "alterQuestion"
		|| $_GET['action'] == "publishQuestion" || $_GET['action'] == "viewQuestion"){
		/* 若教师选择新建选择题，则执行选择题新建流程，一次创建一道选择题 */
		if($_GET['action'] == "createQuestion"){
				/* 制作表单，用于教师进行设置选择题信息 */
				echo '<form action="QuestionPage.php?action=createQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'" method="post" enctype="multipart/form-data">';
				echo '<label>题目设置: </label><input type="text" name="title"><br>';
				echo '<label>选项A: </label><input type="text" name="option_A"><br>';
				echo '<label>选项B: </label><input type="text" name="option_B"><br>';
				echo '<label>选项C: </label><input type="text" name="option_C"><br>';
				echo '<label>选项D: </label><input type="text" name="option_D"><br>';
				echo '<label>答案设置: <select name="answers"><option>A</option><option>B</option><option>C</option><option>D</option></select><br>';
				echo '<label>解析设置: </label><input type="text" name="analysis"><br>';
				echo '<label>分值设置: </label><input onkeyup="this.value=this.value.replace(/\D/g, \'\')" onafterpaste="this.value=this.value.replace(/\D/g, \'\')" name="scores"><br>';
				echo '<input type="submit" name="sub" value="确认新建">';

				if(isset($_POST['sub'])){ /* 判断用户是否提交，且提交的表单信息是否完善 */
						if(isset($_POST['title']) && ($_POST['title']) && isset($_POST['option_A']) && ($_POST['option_A'])
								&& isset($_POST['option_B']) && ($_POST['option_B']) && isset($_POST['option_C']) && ($_POST['option_C'])
								&& isset($_POST['option_D']) && ($_POST['option_D']) && isset($_POST['answers']) && ($_POST['answers'])
								&& isset($_POST['analysis']) && ($_POST['analysis']) && isset($_POST['scores']) && ($_POST['scores'])){
								try{
										$sql = "INSERT INTO temp_question_bank(courses_id,assignments_id,title,option_A,option_B,option_C,option_D,answers,analysis,scores,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
										$stmt2 = $pdo->prepare($sql); /* 准备数据库插入语句，将教师提交的选择题信息插入临时的作业表temp_question_bank暂存 */
										/* 进行参数绑定 */
										$stmt2->bindParam(1, $_GET['courses_id']); $stmt2->bindParam(2, $_GET['assignments_id']); $stmt2->bindParam(3, $_POST['title']);
										$stmt2->bindParam(4, $_POST['option_A']); $stmt2->bindParam(5, $_POST['option_B']);
										$stmt2->bindParam(6, $_POST['option_C']); $stmt2->bindParam(7, $_POST['option_D']);
										$stmt2->bindParam(8, $_POST['answers']); $stmt2->bindParam(9, $_POST['analysis']);
										$scores = (int)$_POST['scores']; $stmt2->bindParam(10, $scores); /* 强制类型转换，将表单提交的分数值由string转换为int，并绑定到对应参数 */
										$mysqltime = date('Y-m-d H:i:s', strtotime("now")); $stmt2->bindParam(11, $mysqltime); /* 获取系统时间戳，并绑定参数 */
										$stmt2->execute(); /* 执行准备好的语句 */

										if($stmt2->rowCount() > 0){ /* 如果插入成功，则输出提示框，并跳转到编辑作业的页面: QuestionPage.php?action=editQuestion */
												echo '<script language="JavaScript">;alert("题目创建成功！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
										}
										/* 若创建失败，则需要寻找错误原因 */
										else{ echo '<script language="JavaScript">;alert("题目创建失败！")</script>'; }
								}catch(PDOException $e){ /* 异常处理 */
										echo '<br>Database selection or insertion failed in QuestionPage.php?action=createQuestion: '.$e->getMessage().'<br>';
										exit;
								}
						}
						/* 若教师提交的信息不完整，则弹出提示框，并停留在当前创建选择题的页面: QuestionPage.php?action=createQuestion */
						else{ echo '<script language="JavaScript">;alert("题目信息设置不完整！")</script>'; }
				}
		}
		/* 若教师选择删除已创建的选择题，则执行选择题删除流程 */
		else if($_GET['action'] == "deleteQuestion"){
				try{
						$sql = "DELETE FROM temp_question_bank WHERE id={$_GET['temp_question_bank_id']}";
						$stmt = $pdo->prepare($sql); $stmt->execute(); /* 准备语句并执行，根据id从临时作业表temp_question_bank中删除对应的选择题 */
						if($stmt->rowCount() > 0){ /* 若删除成功，则弹出提示框，并跳转到编辑作业的页面: QuestionPage.php?action=editQuestion */
								echo '<script language="JavaScript">;alert("删除成功！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
						}else{ /* 若删除失败，则弹出提示框，并跳转到编辑作业的页面: QuestionPage.php?action=editQuestion */
								echo '<script language="JavaScript">;alert("删除失败！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
						}
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br>Database deletion failed in QuestionPage.php?action=deleteQuestion: '.$e->getMessage().'<br>';
						exit;
				}
		}

		/* 若教师选择修改已创建的选择题，则执行选择题修改流程 */
		else if($_GET['action'] == "alterQuestion"){
				echo '<html><body>'; /* 制作用于进行修改选择题设置信息的表单 */
				echo '<form action="QuestionPage.php?action=alterQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'&temp_question_bank_id='.$_GET['temp_question_bank_id'].'" method="post" enctype="multipart/form-data">';
				try{
						$sql = "SELECT * FROM temp_question_bank WHERE id={$_GET['temp_question_bank_id']}";
						$stmt = $pdo->prepare($sql); $stmt->execute(); $row = $stmt->fetch(PDO::FETCH_ASSOC);
						/* 使用数据库中原先的设置信息作为表单的默认值 */
						echo '<label>题目设置: </label><input type="text" name="title" placeholder='.$row['title'].'><br>';
						echo '<label>选项A: </label><input type="text" name="option_A" placeholder='.$row['option_A'].'><br>';
						echo '<label>选项B: </label><input type="text" name="option_B" placeholder='.$row['option_B'].'><br>';
						echo '<label>选项C: </label><input type="text" name="option_C" placeholder='.$row['option_C'].'><br>';
						echo '<label>选项D: </label><input type="text" name="option_D" placeholder='.$row['option_D'].'><br>';
						echo '<label>答案设置: <select name="answers"><option>'.$row['answers'].'</option><option>A</option><option>B</option><option>C</option><option>D</option></select><br>';
						echo '<label>解析设置: </label><input type="text" name="analysis" placeholder='.$row['analysis'].'><br>';
						echo '<label>分值设置: </label><input onkeyup="this.value=this.value.replace(/\D/g, \'\')" onafterpaste="this.value=this.value.replace(/\D/g, \'\')" name="scores" placeholder='.$row['scores'].'><br>';
						echo '<input type="submit" name="sub_alter" value="确认">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
						echo '</body></html>';

						if($_POST['sub_alter']){ /* 判断用户是否提交表单 */
								/* 判断用户提交的表单信息是否完整 */
								if((isset($_POST['title']) && $_POST['title']) || (isset($_POST['option_A']) && $_POST['option_A'])
										|| (isset($_POST['option_B']) && $_POST['option_B']) || (isset($_POST['option_C']) && $_POST['option_C'])
										|| (isset($_POST['option_D']) && $_POST['option_D']) || (isset($_POST['answers']) && $_POST['answers'])
										|| (isset($_POST['answers']) && $_POST['answers']) || (isset($_POST['scores']) && $_POST['scores'])){
										/* 若用户提交了修改信息，则使用新的设置，否则使用默认的设置信息 */
										$title = ($_POST['title']) ? $_POST['title'] : $row['title'];
										$option_A = ($_POST['option_A']) ? $_POST['option_A'] : $row['option_A'];
										$option_B = ($_POST['option_B']) ? $_POST['option_B'] : $row['option_B'];
										$option_C = ($_POST['option_C']) ? $_POST['option_C'] : $row['option_C'];
										$option_D = ($_POST['option_D']) ? $_POST['option_D'] : $row['option_D'];
										$answers = ($_POST['answers']) ? $_POST['answers'] : $row['answers'];
										$analysis = ($_POST['analysis']) ? $_POST['analysis'] : $row['analysis'];
										$scores = ($_POST['scores']) ? $_POST['scores'] : $row['scores'];
								}

								/* 更新数据库中的选择题信息 */
								$sql = "UPDATE temp_question_bank SET title=?,option_A=?,option_B=?,option_C=?,option_D=?,answers=?,analysis=?,scores=? WHERE id={$_GET['temp_question_bank_id']}";
								$stmt1 = $pdo->prepare($sql);
								$stmt1->bindParam(1, $title); $stmt1->bindParam(2, $option_A); $stmt1->bindParam(3, $option_B); $stmt1->bindParam(4, $option_C); $stmt1->bindParam(5, $option_D);
								$stmt1->bindParam(6, $answers); $stmt1->bindParam(7, $analysis); $stmt1->bindParam(8, $scores);
								$stmt1->execute();
								if($stmt1->rowCount()){ /* 若对数据表temp_question_bank有行数影响，则表示修改成功 */
										echo '<script language="JavaScript">;alert("修改成功！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
								}else{ /* 若无影响，则表示用户未编辑表单信息就进行提交，弹出提示框并停留在当前页面 */
										echo '<script language="JavaScript">;alert("请填写修改信息！")</script>';
								}
						}
						/* 提供一个"取消"链接，用于取消修改选择题并返回选择题编辑页面action=editQuestion */
						echo '<a href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'">取消</a>';
				}catch(PDOException $e){ echo '<br>Database selection or updation failed in QuestionPage.php?action=alterQuestion: '.$e->getMessage().'<br>'; exit; }
		}
		/* 若教师选择发布作业，则执行作业发布流程，且作业一旦发布就无法再进行编辑 */
		else if($_GET['action'] == "publishQuestion"){
				try{
						$query = "SELECT * FROM temp_question_bank WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} ORDER BY id";
						$stmt = $pdo->prepare($query); $stmt->execute(); /* 准备数据库查询语句并执行，从临时作业表temp_question_bank中提取出所有教师已编辑的选择题 */
						if($stmt->rowCount() > 0){
								$allRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
								foreach($allRows as $row){ /* 通过循环将临时作业表temp_question_bank中的数据复制到最终发布的作业表assignments中，实现作业发布 */
										$sql = "INSERT INTO question_bank(courses_id,assignments_id,title,option_A,option_B,option_C,option_D,answers,analysis,scores,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)";
										$stmt = $pdo->prepare($sql); /* 准备数据库插入语句 */
										$stmt->bindParam(1, $row['courses_id']); $stmt->bindParam(2, $_GET['assignments_id']); $stmt->bindParam(3, $row['title']);
										$stmt->bindParam(4, $row['option_A']); $stmt->bindParam(5, $row['option_B']);
										$stmt->bindParam(6, $row['option_C']); $stmt->bindParam(7, $row['option_D']);
										$stmt->bindParam(8, $row['answers']); $stmt->bindParam(9, $row['analysis']); $stmt->bindParam(10, $row['scores']); $stmt->bindParam(11, $row['created_at']);
										$stmt->execute(); /* 绑定参数后，执行语句 */
										
										if($stmt->rowCount() > 0){ /* 作业发布成功，则弹出提示框，并跳转到作业查看页面: QuestionPage.php?action=viewQuestion */
												$sql = "UPDATE assignments SET status=? WHERE id={$_GET['assignments_id']}";
												$stmt1 = $pdo->prepare($sql); $status = '已发布'; $stmt1->bindParam(1, $status); $stmt1->execute();
												if($stmt1->rowCount() > 0){
														echo '<script language="JavaScript">;alert("作业编辑成功！");location.href="AssignmentPage.php?action=editAssignment&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
												}else{
														echo '<script language="JavaScript">;alert("作业编辑失败！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
												}
										}else{ /* 作业发布失败，则弹出提示框，并跳转到作业编辑页面: TaskPage.php?action=editQuestion */
												echo '<script language="JavaScript">;alert("作业编辑失败！");location.href="QuestionPage.php?action=editQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
										}
								}
						}
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br>Database selection or insertion failed in QuestionPage.php?action=publishQuestion: '.$e->getMessage().'<br>';
						exit;
				}
		}

		/* 若教师选择查看单元检测，则执行单元检测查看流程，输出单元检测中的所有选择题 */
		else if($_GET['action'] == "viewQuestion"){
				try{
						/* 从assignments表中查询数据 */
						$query = "SELECT * FROM question_bank WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} ORDER BY id";
						$stmt1 = $pdo->prepare($query); $stmt1->execute();
						if($stmt1->rowCount() > 0){ /* 若能查询到数据，则表示教师已发布作业，不可再进行作业编辑，显示所有已发布的选择题 */
								echo '<h2>作业已编辑完成</h2>';
								$allRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
								$rank = 0; /* 通过循环递增rank来显示是第几道选择题 */
								foreach($allRows as $row){ /* 通过循环来显示所有已编辑完成的选择题 */
										echo '第'.++$rank.'题: '.$row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.'('.$row['scores'].' 分)'.'<br>';
										echo 'A、'.$row['option_A'].'&nbsp&nbsp&nbsp&nbsp'.'B、'.$row['option_B'].'&nbsp&nbsp&nbsp&nbsp';
										echo 'C、'.$row['option_C'].'&nbsp&nbsp&nbsp&nbsp'.'D、'.$row['option_D'].'<br>';
										echo '正确答案: '.$row['answers'].'<br>';
										echo '解析: '.$row['analysis'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<br><br>';
								}
						}else{ echo 'hello'; }
				}catch(PDOException $e){ echo '<br>Database selection failed in TaskPage.php?action=createQuestion: '.$e->getMessage().'<br>'; exit; }
				/* 提供教师将作业发布到指定班级的链接 */
				echo '<a href="QuestionPage.php?action=publishToClass&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"><h2>发布作业</h2></a>';
				echo '<a href="QuestionPage.php?action=checkStudentsReply&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"><h2>作业回答</h2></a>';
		}

		/* 默认页面为编辑单元检测editQuestion页面，执行单元检测编辑的流程 */
		else{
				/* 为教师提供新建选择题的链接 */
				echo '<a href="QuestionPage.php?action=createQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"><h2>新建题目</h2></a>';
				$query = "SELECT * FROM temp_question_bank WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} ORDER BY id";
				$stmt2 = $pdo->prepare($query); $stmt2->execute();
				if($stmt2->rowCount() > 0){ /* 若能提取到数据，则表示temp_question_bank表保存了教师已编辑但尚未发布的选择题，通过循环将信息输出 */
						$allRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
						$rank = 0; /* 通过循环递增rank来显示是第几道选择题 */
						foreach($allRows as $row){ /* 通过循环来显示已编辑的选择题 */
								echo '第'.++$rank.'题: '.$row['title'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp'.'('.$row['scores'].' 分)'.'<br>';
								echo 'A、'.$row['option_A'].'&nbsp&nbsp&nbsp&nbsp'.'B、'.$row['option_B'].'&nbsp&nbsp&nbsp&nbsp';
								echo 'C、'.$row['option_C'].'&nbsp&nbsp&nbsp&nbsp'.'D、'.$row['option_D'].'<br>';
								echo '正确答案: '.$row['answers'].'<br>';
								echo '解析: '.$row['analysis'].'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';

								/* 为教师提供修改选择题的链接 */
								echo '<a href="QuestionPage.php?action=alterQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['assignments_id'].'&temp_question_bank_id='.$row['id'].'">修改本题</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp';
								/* 为教师提供删除选择题的链接 */
								echo '<a href="QuestionPage.php?action=deleteQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$row['assignments_id'].'&temp_question_bank_id='.$row['id'].'">删除本题</a><br><br>';
						}
						/* 为教师通过发布作业的链接，且一旦发布就不能再进入作业编辑页面 */
						echo '<a onclick="return confirm(\'确定已编辑完成？\')" href="QuestionPage.php?action=publishQuestion&id=&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'">确认编辑完成</a>';
				}						
		}
}

/* 如果教师选择将作业发布到对应班级，则执行该流程 */
else if($_GET['action'] == "publishToClass"){
		$sql = "SELECT * FROM assignments WHERE id={$_GET['assignments_id']}";
		$stmt1 = $pdo->prepare($sql); $stmt1->execute(); $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
		$sql = "SELECT class_number FROM classes WHERE courses_id={$_GET['courses_id']} ORDER BY class_number";
		$stmt2 = $pdo->prepare($sql); $stmt2->execute(); $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		/* 提供教师将作业发布到对应班级的表单 */
		echo '<form action="QuestionPage.php?action=publishToClass&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'" method="post" enctype="multipart/form-data">';
		echo '<label>发布到班级: </label><select name="class_number">';
		/* 将提取到的所有班级编号作为下拉选择列表中的数据项 */
		foreach($result2 as $row){ echo '<option>'.$row['class_number'].'</option>'; }
		echo '</select>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="submit" name="sub" value="确认">';

		/* 如果教师选择确认，并且提交了班级编号，则执行该流程 */
		if(isset($_POST['sub']) && $_POST['sub'] && isset($_POST['class_number']) && $_POST['class_number']){
				/* 先根据课程id，作业id，班级id从assignments_to_class表中提取数据 */
				$sql = "SELECT id FROM assignments_to_class WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} AND classes_id IN(SELECT id FROM classes WHERE courses_id={$_GET['courses_id']} AND class_number={$_POST['class_number']})";
				$stmt3 = $pdo->prepare($sql); $stmt3->execute();
				if($stmt3->rowCount() > 0){ /* 若提取的到数据，则表示教师已发布过作业到该班级 */
						echo '<script language="JavaScript">;alert("当前班级已有该作业，不能进行重复发布！");location.href="QuestionPage.php?action=publishToClass&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
				}else{ /* 否则，将数据写入assignments_to_class实现作业发布到指定班级 */
						$sql = "SELECT id FROM classes WHERE courses_id={$_GET['courses_id']} AND class_number={$_POST['class_number']}";
						$stmt4 = $pdo->prepare($sql); $stmt4->execute(); $result4 = $stmt4->fetch(PDO::FETCH_ASSOC);
						$sql = "INSERT INTO assignments_to_class(courses_id,assignments_id,classes_id) VALUES(?,?,?)";
						$stmt5 = $pdo->prepare($sql);
						$stmt5->bindParam(1, $_GET['courses_id']); $stmt5->bindParam(2, $_GET['assignments_id']); $stmt5->bindParam(3, $result4['id']);
						$stmt5->execute();
						if($stmt5->rowCount() > 0){
								echo '<script language="JavaScript">;alert("作业发布成功！");location.href="QuestionPage.php?action=viewQuestion&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'"</script>';
						}else{ echo '<script language="JavaScript">;alert("作业发布失败！")</script>'; }
				}
		}
}

/* 若教师选择查看学生成绩，则执行该流程 */
else if($_GET['action'] == "checkStudentsReply"){
		$sql = "SELECT * FROM assignments WHERE id={$_GET['assignments_id']}";
		$stmt1 = $pdo->prepare($sql); $stmt1->execute(); $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
		$sql = "SELECT class_number FROM classes WHERE courses_id={$_GET['courses_id']} ORDER BY class_number";
		$stmt2 = $pdo->prepare($sql); $stmt2->execute(); $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

		/* 提供教师查看对应班级成绩的表单 */
		echo '<form action="QuestionPage.php?action=checkStudentsReply&courses_id='.$_GET['courses_id'].'&assignments_id='.$_GET['assignments_id'].'" method="post" enctype="multipart/form-data">';
		echo '<label>选择班级: </label><select name="class_number">';
		/* 将提取到的所有班级编号作为下拉选择列表中的数据项 */
		foreach($result2 as $row){ echo '<option>'.$row['class_number'].'</option>'; }
		echo '</select>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<input type="submit" name="sub" value="确认"><br>';

		/* 判断教师是否选择了班级编号 */
		if(isset($_POST['sub']) && $_POST['sub'] && isset($_POST['class_number']) && $_POST['class_number']){
				/* 先从assignments_to_class表中提取数据，判断教师是否已发布作业到该班级 */
				$sql = "SELECT id FROM assignments_to_class WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} AND classes_id IN(SELECT id FROM classes WHERE courses_id={$_GET['courses_id']} AND class_number={$_POST['class_number']})";
				$stmt3 = $pdo->prepare($sql); $stmt3->execute();

				if($stmt3->rowCount() > 0){ /* 提取的到数据，则表示教师已发布作业到该班级 */
						$sql = "SELECT students_id,scores,total_scores FROM test_scores WHERE courses_id={$_GET['courses_id']} AND assignments_id={$_GET['assignments_id']} AND classes_id IN(SELECT id FROM classes WHERE courses_id={$_GET['courses_id']} AND class_number={$_POST['class_number']})";
						$stmt4 = $pdo->prepare($sql); $stmt4->execute();

						if($stmt4->rowCount() > 0){ /* 若能提取到数据，则表示有学生回答了作业 */
								$allRows = $stmt4->fetchAll(PDO::FETCH_ASSOC);
								/* 制作显示学生作业成绩的表单 */
								echo '<table border="1" align="left" width="60%">';
								echo '<tr bgcolor="#cccccc">';
								echo '<th>学生姓名</th><th>性别</th><th>得分</th><th>总分</th></tr>';
								foreach($allRows as $row){
										$sql = "SELECT name,gender FROM users_profile WHERE id IN(SELECT users_profile_id FROM students WHERE id={$row['students_id']})";
										$stmt5 = $pdo->prepare($sql); $stmt5->execute(); $result5 = $stmt5->fetch(PDO::FETCH_ASSOC);
										echo '<td>'.$result5['name'].'</td><td>'.$result5['gender'].'</td><td>'.$row['scores'].'</td><td>'.$row['total_scores'].'</td></tr>';
								}
						}else{ echo '<h2>该班级尚未有学生完成作业</h2>'; }
				}else{ echo '<h2>作业尚未发布到该班级</h2>'; }
		}

}

?>
