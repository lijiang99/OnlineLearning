<?php
/**
 * file: upload.class.php 类声明文件，本类提供接口，用于后端对分片文件进行合并，不可直接访问
 */

class Upload{
		private $filepath = './uploads';    /* 先将分片后的文件上传到服务器的项目目录的uploads目录下 */
		private $tmpPath;                   /* 前端分片后的文件所要上传到的临时目录 */
		private $blobNum;                   /* 记录是第几个文件块 */
		private $totalBlobNum;              /* 文件块的总数 */
		private $fileName;                  /* 文件名 */
		private $fileSize;                  /* 文件大小 */

		private $role;                      /* 用于区分用户身份是教师还是学生 */
		private $courses_id;                /* 文件所属的课程id */
		private $chapters_id;               /* 文件所属的章节id */
		private $tasks_id;                  /* 文件所属的小节id */
		private $user_id;                   /* 文件上传者的id，即students表或teachers表中的id */

		/* 构造函数，用于实例化一个对象合并分片文件，并将完整文件移动到对应目录下 */
		public function __construct($tmpPath, $blobNum, $totalBlobNum, $fileName, $fileSize, $role, $courses_id, $chapters_id, $tasks_id, $user_id){
				/* 初始化成员属性值 */
				$this->tmpPath = $tmpPath; $this->blobNum = $blobNum; $this->totalBlobNum = $totalBlobNum; $this->fileName = $fileName; $this->fileSize = $fileSize;
				$this->role = $role; $this->courses_id = $courses_id; $this->chapters_id = $chapters_id; $this->tasks_id = $tasks_id; $this->user_id = $user_id;

				/* 调用私有方法将临时目录下分片后的文件，移动到服务器的项目目录的uploads目录下 */
				$this->moveFile();
				/* 调用私有方法合并uploads目录下的分片文件，将合并后的完整文件移动到对应目录下，删除分片文件 */
				$this->fileMerge();
		}

				/* 判断是否是最后一块，如果是则进行文件合并并且删除文件块 */
		private function fileMerge(){
				if($this->blobNum == $this->totalBlobNum){
						include_once("conn.inc.php"); /* 导入数据库连接文件 */

						/* 计算合并后文件的大小 */
						if($this->totalBlobNum > 1){ /* 若分片总数大于1，则文件大小的单位为MB */
								$tmpSize = (double)(($this->totalBlobNum-1)*1024*1024+$this->fileSize)/1000/1000;
								$totalSize = (string)number_format($tmpSize, 1, '.', '').'MB'; /* 四舍五入并保留1位小数 */
						}else{ /* 若分片总数等于1，则文件大小的单位为KB */
								$tmpSize = (double)$this->fileSize/1000;
								$totalSize = (string)number_format($tmpSize, 1, '.', '').'KB';
						}

						/* 通过'.'对文件名进行分割，获取最后一个点'.'面的字符串，即文件扩展名 */
						$extension = end(explode('.', $this->fileName));

						/* 根据文件扩展名的类型决定合并后的文件所要上传到的文件夹 */
						switch($extension){
						case 'pdf': $savePath = $this->filepath.'/pdfs'; break;
						case 'ppt':
						case 'pptx': $savePath = $this->filepath.'/ppts'; break;
						case 'doc':
						case 'docx': $savePath = $this->filepath.'/docs'; break;
						default: $savePath = $this->filepath.'/videos'; break;
						}

						if($this->role == 'teachers'){ /* 如果是教师上传资源，则将文件信息存放到courses_resources表中 */
								try{
										$sql = "INSERT INTO courses_resources(courses_id,chapters_id,tasks_id,teachers_id,title,type,media_url,size,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?)";
										$stmt = $pdo->prepare($sql); $stmt->bindParam(1, $this->courses_id); $stmt->bindParam(2, $this->chapters_id);
										$stmt->bindParam(3, $this->tasks_id); $stmt->bindParam(4, $this->user_id);
										
										/* title绑定为原始文件名，type为文件的扩展名 */
										$stmt->bindParam(5, $this->fileName); $stmt->bindParam(6, $extension);
								
										/* 合并后服务器所保存文件名是由时间戳，100~999的随机数组成的随机文件名，防止同名文件冲突 */
										$saveName = date('YmdHis').'_'.rand(100, 999).'.'.$extension;
										/* 设置上传到服务器后文件资源的url */
										$media_url = "http://www.o2ocourse.top/demo".ltrim($savePath, '.').'/'.$saveName;
										$stmt->bindParam(7, $media_url);
										
										/* 绑定第八个参数为计算所得的文件大小 */
										$stmt->bindParam(8, $totalSize);
										
										/* 获取当前系统时间并将其转换为mysql的timestamp格式 */
										$mysqltime = date('Y-m-d H:i:s', strtotime("now"));
										$stmt->bindParam(9, $mysqltime); $stmt->bindParam(10, $mysqltime);
										
										$stmt->execute(); /* 执行准备好的语句 */
								}catch(PDOException $e){
										echo "<br>Database insertion failed in upload.class.php: ".$e->getMessage().'<br>';
										exit;
								}
						}
						
						for($i=1; $i<= $this->totalBlobNum; $i++){ /* 通过循环将后缀为'__'+'数字'的分片文件合并 */
								$contents = file_get_contents($this->filepath.'/'. $this->fileName.'__'.$i);
								file_put_contents($savePath.'/'. $saveName, $contents, FILE_APPEND | LOCK_EX); /* 进行文件追加写入，并设置独占锁，防止并发访问时造成冲突，破坏文件数据 */
						}
						$this->deleteFileBlob(); /* 删除零散文件块，只保留合并后的完整文件 */
				}
		}

		/* 删除文件块 */
		private function deleteFileBlob(){
				for($i=1; $i<= $this->totalBlobNum; $i++){
						@unlink($this->filepath.'/'. $this->fileName.'__'.$i);
				}
		}

		/* 移动文件 */
		private function moveFile(){
				$this->touchDir();
				$filename = $this->filepath.'/'. $this->fileName.'__'.$this->blobNum;
				move_uploaded_file($this->tmpPath,$filename);
		}

		/* 建立上传文件夹 */
		private function touchDir(){
				/* 若服务器不存在所设置的上传目录，则新建一个同名目录 */
				if(!file_exists($this->filepath)){
						return mkdir($this->filepath);
				}
		}
}
?>
