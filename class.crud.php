<?php

class crud
{
	private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}
	
	public function create($name,$url,$basetag,$updatetag, $cdatatag, $defaultcountry, $joblocationtype, $isChild, $utmValue)
	{
		$now = new DateTime();
		$createdate = $now->format('Y-m-d H:i:s');
		$status = "Pending";
		try
		{
			$stmt = $this->db->prepare(
				"INSERT INTO feedinfo(cronid, name,url,basetag,updatetag,cdatatag,createdate,updatedate,defaultcountry,joblocationtype,status,utm) 
						VALUES(:cronid, :name, :url, :basetag, :updatetag, :cdatatag, :createdate, :updatedate, :defaultcountry, :joblocationtype, :status, :utm)");
			$stmt->bindparam(":cronid",$isChild);
			$stmt->bindparam(":name",$name);
			$stmt->bindparam(":url",$url);
			$stmt->bindparam(":basetag",$basetag);
			$stmt->bindparam(":updatetag",$updatetag);
			$stmt->bindparam(":cdatatag",$cdatatag);
			$stmt->bindparam(":createdate",$createdate);
			$stmt->bindparam(":updatedate",$createdate);
			$stmt->bindparam(":defaultcountry",$defaultcountry);
			$stmt->bindparam(":joblocationtype",$joblocationtype);
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":utm",$utmValue);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}
	
	public function getID($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id=:id");
		$stmt->execute(array(":id"=>$id));
		$editRow=$stmt->fetch(PDO::FETCH_ASSOC);
		return $editRow;
	}

	// 681 is 32 GB
	// 381 is 10 GB, 55 is 10GB
	// 750 is 20 GB
	// 377 is 15 GB
	// 873 is also
	// 955 is large Joveo US Premium
	// 948 is large Recruit.net US Premium

	public function getAll($order) {
		$order = $order - 1;
		$feedAll = [];
		$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id MOD 20 = :remain AND id != '681' AND id !='381' AND id !='55' AND id != '377' AND id != '750' AND id != '873' AND id !='948' AND id !='955'");
		$stmt->bindparam(":remain",$order);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$feedAll[] = $row;
		}
		return $feedAll;
	}

	public function getLarge($order) {
		if($order == '681') {
			$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id = 681 OR id = 55 OR id = 873");
		}
		if($order == '750') {
			$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id = 750 OR id = 377 OR id = 381");	
		}
		$feedAll = [];
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$feedAll[] = $row;
		}
		return $feedAll;
	}

	public function getSpecial() {
		$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id = 948 OR id = 955");
		$feedAll = [];
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$feedAll[] = $row;
		}
		return $feedAll;
	}

	public function cronStatus($order, $status) {
		$status = $status;
		$now = new DateTime();
		$updateDate = $now->format('Y-m-d H:i:s');
		try{
			$stmt = $this->db->prepare("UPDATE cron SET status=:status, updated_at=:updated_at WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$order);
			$stmt->bindparam(":updated_at",$updateDate);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	//get cronstatus
	public function getCronStatus($order) {
		$count = false;
		$stmt = $this->db->prepare("SELECT status FROM cron WHERE id=:id");
		$stmt->bindparam(":id",$order);
		$stmt->execute();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
			$count = $row['status'];
		}
		return $count;
	}

	public function checkCronSatus() {
		$ret = [];
		$stmt = $this->db->prepare("SELECT id, updated_at FROM cron");
		$stmt->execute();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
			$ret[] = $row; 
		}
		return $ret;
	}

	public function update($id,$name,$updatetag,$xmlurl,$defaultcountry,$industry,$joblocationtype, $utmValue)
	{
		$now = new DateTime();
		$updateDate = $now->format('Y-m-d H:i:s');
		try
		{
			$newtag = "";
			$isnew = 0;
			$stmt=$this->db->prepare("UPDATE feedinfo SET name=:fname, updatetag=:updatetag, updatedate=:updatedate, url=:xmlurl, defaultcountry=:defaultcountry, industry=:industry, joblocationtype=:joblocationtype, utm=:utm, newtag=:newtag, isnew=:isnew WHERE id=:id ");
			$stmt->bindparam(":fname",$name);
			$stmt->bindparam(":updatedate",$updateDate);
			$stmt->bindparam(":updatetag",$updatetag);
			$stmt->bindparam(":id",$id);
			$stmt->bindparam(":xmlurl",$xmlurl);
			$stmt->bindparam(":isnew",$isnew);
			$stmt->bindparam(":newtag",$newtag);
			$stmt->bindparam(":defaultcountry",$defaultcountry);
			$stmt->bindparam(":industry",$industry);
			$stmt->bindparam(":joblocationtype",$joblocationtype);
			$stmt->bindparam(":utm",$utmValue);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}
	
	public function delete($id)
	{
		$stmt = $this->db->prepare("DELETE FROM feedinfo WHERE id=:id");
		$stmt->bindparam(":id",$id);
		$stmt->execute();
		return true;
	}

	//initial xml making
	public function createRunning($id) {
		$is_stmt = $this->db->prepare("SELECT * FROM running WHERE feedid=:feedid");
		$is_stmt->bindparam(":feedid",$id);
		$is_stmt->execute();
		if($is_stmt->rowCount() > 0) {
			return "warning";
		}
		else {
			try
			{
				$status = "Checking";
				$stmt = $this->db->prepare(
					"INSERT INTO running(feedid, status)
							VALUES(:feedid, :status)");
				$stmt->bindparam(":feedid",$id);
				$stmt->bindparam(":status",$status);
				$stmt->execute();

				$stmt = $this->db->prepare("UPDATE feedinfo SET status=:status
																	WHERE id=:id");
				$stmt->bindparam(":status",$status);
				$stmt->bindparam(":id",$id);
				$stmt->execute();
				return true;
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();	
				return false;
			}
		}
	}

	//Generate random string
	public function generateRandomString($length = 8) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
	}

	// save downloadfile information
	public function createDownloading($url) {
		$is_stmt = $this->db->prepare("SELECT * FROM filexml WHERE inputurl=:url");
		$is_stmt->bindparam(":url",$url);
		$is_stmt->execute();
		if($is_stmt->rowCount() > 0) {
			return "warning";
		}
		else {
			try
			{
				$name = $this->generateRandomString(8);
				$status = "Downloading";
				$stmt = $this->db->prepare(
					"INSERT INTO filexml(inputurl, name, status)
							VALUES(:inputurl, :name, :status)");
				$stmt->bindparam(":inputurl",$url);
				$stmt->bindparam(":name",$name);
				$stmt->bindparam(":status",$status);
				$stmt->execute();
				return $name;
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();	
				return false;
			}
		}
	}

	// Get download file information
	public function getDownloading($order) {
		$runningList = [];
		$downloadingId = [];
		$status = "Downloading";
		if($order > 1) {
			$stmt = $this->db->prepare("SELECT * FROM filexml WHERE status=:status AND (id = '54' OR id = '59' OR id = '126' OR id = '258')");
		}
		else {
			$stmt = $this->db->prepare("SELECT * FROM filexml WHERE status=:status AND id != '54' AND id != '59' AND id != '126' AND id != '258'");
		}
		$stmt->bindparam(":status", $status);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$runningList[] = $row;
			$downloadingId[] = $row['id'];
		}
		$result = ['runningList' => $runningList, 'downloadingId' => $downloadingId];
		return $result;
	}

	// Get download file information
	// 377 is 54 in feedinfo
	// 381 is 59 in feedinfo
	// 681 is 126 in feedinfo
	// public function getDownloadingLarge() {
	// 	$runningList = [];
	// 	$downloadingId = [];
	// 	$status = "Downloading";
	// 	$stmt = $this->db->prepare("SELECT * FROM filexml WHERE status=:status AND (id = '54' OR id = '59' OR id = '126')");
	// 	$stmt->bindparam(":status", $status);
	// 	$stmt->execute();
	// 	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	// 		$runningList[] = $row;
	// 		$downloadingId[] = $row['id'];
	// 	}
	// 	$result = ['runningList' => $runningList, 'downloadingId' => $downloadingId];
	// 	return $result;
	// }

	//remove downloadfile information
	public function deleteFile($id)
	{
		$stmt = $this->db->prepare("DELETE FROM filexml WHERE id=:id");
		$stmt->bindparam(":id",$id);
		$stmt->execute();
		return true;
	}

	// download to ready status change
	public function setDownToReady($id) {
		try{
			$status = "Ready";
			$stmt = $this->db->prepare("UPDATE filexml SET status=:status
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// download to ready status change
	public function setDownToError($id) {
		try{
			$status = "Error";
			$stmt = $this->db->prepare("UPDATE filexml SET status=:status
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// download to special case status change
	public function setDownToCheck($id) {
		try{
			$status = "Checkagain";
			$stmt = $this->db->prepare("UPDATE filexml SET status=:status
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// download to ready status change
	public function setDownToDownloading($id) {
		try{
			$status = "Downloading";
			$stmt = $this->db->prepare("UPDATE filexml SET status=:status
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// get row with inputurl
	public function getIsReady($url) {
		$status = "Ready";
		$stmt = $this->db->prepare("SELECT * FROM filexml WHERE status=:status AND inputurl=:url");
		$stmt->bindparam(":status", $status);
		$stmt->bindparam(":url", $url);
		$stmt->execute();
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return $row;
		}
		else {
			return false;
		}
	}

	// get row with inputurl in feedinfo
	public function isxml($name, $url) {
		$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE name=:name OR url=:url");
		$stmt->bindparam(":url", $url);
		$stmt->bindparam(":name", $name);
		$stmt->execute();
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return $row;
		}
		else {
			return false;
		}
	}

	// Change status in feedinfo table
	public function changeStatusFinal($id, $status, $count, $repeats) {
		$now = new DateTime();
		$updatedate = $now->format('Y-m-d H:i:s');
		try{
			$status = $status;
			$stmt = $this->db->prepare("UPDATE feedinfo SET status=:status, totalcount=:totalcount, repeats=:repeats, updatedate=:updatedate
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":repeats",$repeats);
			$stmt->bindparam(":updatedate",$updatedate);
			$stmt->bindparam(":id",$id);
			$stmt->bindparam(":totalcount",$count);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// Change status in feedinfo table with key changed
	public function changeStatusFinalChangeTag($id, $status, $count, $repeats, $basetag, $updatetag, $cdatatag, $baseArrayNew) {
		$newtag = "";
		$isnew = 1;
		$now = new DateTime();
		$updatedate = $now->format('Y-m-d H:i:s');
		try{
			if(empty($baseArrayNew)) {
				$isnew = 0;
			}
			foreach ($baseArrayNew as $key => $value) {
				$newtag .= $value.",";
			}
			$status = $status;
			$stmt = $this->db->prepare("UPDATE feedinfo SET status=:status, totalcount=:totalcount, repeats=:repeats, updatedate=:updatedate, basetag=:basetag, updatetag=:updatetag, cdatatag=:cdatatag, newtag=:newtag, isnew=:isnew WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":repeats",$repeats);
			$stmt->bindparam(":updatedate",$updatedate);
			$stmt->bindparam(":basetag",$basetag);
			$stmt->bindparam(":updatetag",$updatetag);
			$stmt->bindparam(":cdatatag",$cdatatag);
			$stmt->bindparam(":newtag",$newtag);
			$stmt->bindparam(":isnew",$isnew);
			$stmt->bindparam(":id",$id);
			$stmt->bindparam(":totalcount",$count);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// Change status in feedinfo table
	public function changeStatus($id, $status) {
		try{
			$status = $status;
			$stmt = $this->db->prepare("UPDATE feedinfo SET status=:status
																	WHERE id=:id");
			$stmt->bindparam(":status",$status);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// Set download to Progress
	public function setDownToProgress($idList) {
		try{
			$in  = str_repeat('?,', count($idList) - 1) . '?';
			$stmt = $this->db->prepare("UPDATE filexml SET status='Progressing'
																	WHERE id IN ($in)");
			$stmt->execute($idList);
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	public function getRunning() {
		$runningList = [];
		$runningStatus = [];
		$query = "SELECT * FROM running";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		if($stmt->rowCount() > 0) {
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$runningList[] = $row['feedid'];
				$runningStatus[] = $row['status'];
			}
		}
		$result = ['runningList' => $runningList, 'runningStatus' => $runningStatus];
		return $result;
	}

	public function getRunningChecking() {
		$runningList = [];
		$status = "Checking";
		$stmt = $this->db->prepare("SELECT * FROM running WHERE status=:status");
		$stmt->bindparam(":status", $status);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$runningList[] = $row['feedid'];
		}
		return $runningList;
	}

	public function getRunningItem($list) {
		$runningList = [];
		$in  = str_repeat('?,', count($list) - 1) . '?';
		$stmt = $this->db->prepare("SELECT * FROM feedinfo WHERE id IN ($in)");
		$stmt->execute($list);
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$runningList[] = $row;
		}
		return $runningList;
	}

	public function deleteChecking($id) {
		$stmt = $this->db->prepare("DELETE FROM running WHERE feedid=:feedid");
		$stmt->bindparam(":feedid",$id);
		$stmt->execute();
		return true;
	}

	public function setCheckToProgress($list) {
		try{
			$in  = str_repeat('?,', count($list) - 1) . '?';
			$stmt = $this->db->prepare("UPDATE running SET status='Progressing'
																	WHERE feedid IN ($in)");
			$stmt->execute($list);
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	public function modifyTag($willUpdateTag, $id) {
		try{
			$stmt = $this->db->prepare("UPDATE feedinfo SET updatetag=:updatetag
																	WHERE id=:id");
			$stmt->bindparam(":updatetag",$willUpdateTag);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	public function changeToDownloading() {
		try{
			$stmt = $this->db->prepare("UPDATE filexml SET status='Downloading'");
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	public function dataFileView($query) {
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		if($stmt->rowCount() > 0)
		{
			$returnString = "";

			while($row=$stmt->fetch(PDO::FETCH_ASSOC))
			{
				if($row['status'] == "Downloading") {
					$class = "text-green";
				}
				else if($row['status'] == "Ready" || $row['status'] == "Progressing") {
					$class = "text-blue";
				}
				else {
					$class = "text-red";
				}
				$returnString .= "
					<tr>
						<td>".$row['name']."</td>
						<td>".$row['inputurl']."</td>
						<td class='".$class."'>".$row['status']."</td>
						<td><a href='#' date-id='".$row['id']."' class='btn btn-default btn-sm mr-1 removeFile'  data-toggle='modal' data-target='#removeModal'><i class='far fa-trash-alt'></i></a></td>
					</tr>
				";
			}
			return $returnString;
		}
	}
		
	public function dataview($query, $s3)
	{
		// $runningInfo = $this->getRunning();
		$main_url = "https://s3.us-east-2.amazonaws.com/converted.bebee.com/";
		$stmt = $this->db->prepare($query);
		$stmt->execute();
		if($stmt->rowCount() > 0)
		{
			while($row=$stmt->fetch(PDO::FETCH_ASSOC))
			{
				$outputUrl = $main_url.str_replace(" ", "_", strtolower($row['name'])).".xml";
				$filePath = str_replace(" ", "_", strtolower($row['name'])).".xml";
				?>
					<tr>
						<td><?php echo($row['name']); ?> <?php if(!empty($row['isnew'])) echo '<i class="fas fa-bell zoom-in-zoom-out"></i>'; ?></td>
						<td style="width: 300px; word-break: break-word;"><?php echo($row['url']); ?></td>
						<td style="width: 300px; word-break: break-word;"><?php echo $outputUrl; ?></td>
						<td><?php echo($row['createdate']); ?></td>
						<td><?php echo($row['updatedate']); ?></td>
						<?php
							if($row['status'] == "Progressing" || $row['status'] == "Pending") {
								$class = "text-green";
							}
							else if($row['status'] == "Ready") {
								$class = "text-blue";
							}
							else {
								$class = "text-red";
							}
						?>
						<td class=<?php echo $class; ?>><?php echo($row['status']); ?></td>
						<!-- <td> -->
							<?php
								// if($s3->doesObjectExist('converted.bebee.com', $filePath)) {
								// 	echo date("Y-m-d H:i:s", filemtime(S3XML.$filePath));
								// }
								// else {
								// 	echo "Not Converted Yet";
								// }
							?>
						<!-- </td> -->
						<td>
							<a href="edit-data.php?edit_id=<?php echo($row['id']); ?>" class="btn btn-default btn-sm mr-1"><i class="fas fa-pen"></i></a>
							<a href="#" data-id="<?php echo($row['id'])?>" class="btn btn-default btn-sm mr-1 removeXml"  data-toggle="modal" data-target="#removeModal"><i class="far fa-trash-alt"></i></a>
							<a href="#" data-id="<?php echo($row['id'])?>" class="btn btn-default btn-sm mr-1 runningXml" data-toggle="modal" data-target="#runningModal"><i class="fas fa-download"></i></a>
						</td>
					</tr>
					<?php
			}
		}
	}
	
	// Get all users
	public function getAllUser($query) {		

		$stmt = $this->db->prepare($query);
		$stmt->execute();
		if($stmt->rowCount() > 0)
		{
			$input_url_list = "";
			$i = 0;
			while($row=$stmt->fetch(PDO::FETCH_ASSOC))
			{
				$i ++ ;
				$input_url_list .=  "<tr>".
										"<td><input type='checkbox' class='userIdSelect' name='userIdSelect' value='".$row['id']."' onclick='resetSelectAll();'></td>".
										"<td>".$i."</td>".
										"<td>".$row['username']."</td>".
										"<td>".$row['email']."</td>".
										// "<td><i class='fas fa-clipboard text-grey'></i> ".$jobCount." inserted jobs"."</td>".
										"<td><i class='fas fa-calendar-alt text-grey'></i> ".$row['created_at']."</td>".
										"<td><a href='#' data-id='".$row['id']."' id='removeUserHref'><i class='fas fa-trash'></i></a>
											<a href='#' data-user='".json_encode($row)."' id='editUser' data-toggle='modal' data-target='#createUser'><i class='fas fa-edit'></i></a>
										</td>".
									"</tr>";
				
			}
			echo $input_url_list;
		}
	}

	// check is user
	public function isUser($email) {
		$stmt = $this->db->prepare("SELECT * FROM users WHERE email=:email");
		$stmt->bindparam(":email", $email);
		$stmt->execute();
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return $row;
		}
		else {
			return false;
		}
	}

	// Remove User
	public function removeUser($id) {
		$idReal = $id;
		if(strpos($id, ",") !== false){
			$idReal = explode(",",$id);
			array_pop($idReal);
			try
			{
				$ids = implode("','", $idReal);
				$stmt = $this->db->prepare("DELETE FROM users WHERE id IN ('".$ids."')");
				$stmt->bindparam(":id",$id);
				$stmt->execute();
				return true;
			}
			catch(PDOException $e)
			{
				echo $e->getMessage();
				return false;
			}
		}
		
		try
		{
			$stmt = $this->db->prepare("DELETE FROM users WHERE id=:id");
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
			return false;
		}	
	}

	// Create user
	public function createUser($name, $email, $pwd, $role) {
		$now = new DateTime();
		$createdate = $now->format('Y-m-d H:i:s');
		try
		{
			$stmt = $this->db->prepare(
				"INSERT INTO users(username, email, password, role, created_at, updated_at) 
						VALUES(:username, :email, :password, :role, :created_at, :updated_at)");
			$stmt->bindparam(":username",$name);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":password",$pwd);
			$stmt->bindparam(":role", $role);
			$stmt->bindparam(":created_at",$createdate);
			$stmt->bindparam(":updated_at",$createdate);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}

	// Update user
	public function updateUser($name, $email, $pwd, $role, $id) {
		$now = new DateTime();
		$createdate = $now->format('Y-m-d H:i:s');
		try
		{
			$stmt = $this->db->prepare("UPDATE users SET username=:username, email=:email, password=:password, role=:role, updated_at=:updated_at WHERE id=:id");
			$stmt->bindparam(":username",$name);
			$stmt->bindparam(":email",$email);
			$stmt->bindparam(":password",$pwd);
			$stmt->bindparam(":role", $role);
			$stmt->bindparam(":updated_at",$createdate);
			$stmt->bindparam(":id",$id);
			$stmt->execute();
			return true;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();	
			return false;
		}
	}
}
?>