<?php
	function registerUserSub($myDatabase,$dataStr){

		$data=json_decode($dataStr);
		$uniqueID = uniqid();
		$mailHeaders = "MIME-Version: 1.0" . "\r\n";
		$mailHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$mailHeaders .= 'From: <info@gogivingtree.com>' . "\r\n";
		$mailHeaders .= 'Bcc: dxiao69@gmail.com' . "\r\n";
		$mailText = "Hi ".$firstName." ".$lastName.",<br>Thank you for choosing gogivingtree.com. Please click the <a href='http://www.gogivingtree.com/MY_API/resP?tkn=".$uniqueID."'>link</a> to change your password.<br><br>Regads,<br>gogivingtree.com";
		$mailSubj = "[Do not reply this message] Reset your password within 12 hours";
		//		echo var_dump($data);
		$user			=	$data->Email;
		$firstName		=	$data->firstName;
		$lastName		=	$data->lastName;
		$email			=	$data->Email;
		$Unit			=	$data->Unit;
		$Address		=	$data->Address;
		$type			=	$data->type;
		$LAT			=	$data->LAT;
		$LNG			=	$data->LNG;
		$charity		=	$data->Chairty;
		$RegNo			=	$data->RegNo;
		$Tel			=	$data->TelNo;
		if($user==""){
			echo '{"status":"error","message":"email cannot be blank"}';
		}else{
			$sql = "select * from `givingTree_User_Master` where `email`='" . $email ."'";
			$result = mysql_query($sql);
			if(mysql_num_rows($result)){
				// user with this email address already registerd
//				echo "{'status':'error','message':'user exist'}";
				echo '{"status":"error","message":"email already registered"}';
			}else{
				// No user is using this email address
				if($type=="donor"){
					$sql="INSERT INTO `givingTree_User_Master`
							(`user`, `email`, `register`, `type`, `firstName`, `lastName`, `status`, `Unit`, `Address`, `LAT`, `LNG`,`token` )
							VALUES ('".$email."','".$email."','$date','".$type."','".$firstName."','".$lastName."','pending email Verify','".$Unit."','".$Address."','".$LAT."','".$LNG."','".$uniqueID."')";

					//				$sql+="(`user`, `email`, `word`, `register`, `type`, `token`, `update`, `firstName`, `lastName`, `comments`, `status`, `Unit`, `Address`, `LAT`, `LNG`, `Charity`, `RegNo`, `Tel`, `LicIMG`, `InsIMG`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12],[value-13],[value-14],[value-15],[value-16],[value-17],[value-18],[value-19],[value-20])";
				}elseif($type=="charity"){
					$sql="INSERT INTO `givingTree_User_Master`
							(`user`, `email`, `register`, `type`, `firstName`, `lastName`, `status`, `Unit`, `Address`, `LAT`, `LNG`, `Charity`, `RegNo`, `Tel`,`token` )
							VALUES ('".$email."','".$email."','$date','".$type."','".$firstName."','".$lastName."','pending Verify','".$Unit."','".$Address."','".$LAT."','".$LNG."','".$charity."','".$RegNo."','".$Tel."','".$uniqueID."')";

				}elseif($type=="courier"){
					$image01 = addslashes(file_get_contents($_FILES['image01']['tmp_name'])); //SQL Injection defence!
					$image01_name = addslashes($_FILES['image01']['name']);
					$image02 = addslashes(file_get_contents($_FILES['image02']['tmp_name'])); //SQL Injection defence!
					$image02_name = addslashes($_FILES['image02']['name']);

					$sql="INSERT INTO `givingTree_User_Master`
							(`user`, `email`, `register`, `type`, `firstName`, `lastName`, `status`, `Unit`, `Address`, `LAT`, `LNG`, `LicIMG`, `InsIMG`,`token` )
							VALUES ('".$email."','".$email."','$date','".$type."','".$firstName."','".$lastName."','pending email Verify','".$Unit."','".$Address."','".$LAT."','".$LNG."','{$image01}','{$image02}','".$uniqueID."')";
				}
//				echo $sql;
				if (!mysql_query($sql)) { // Error handling
					echo '{"status":"error","message":"Something went wrong! :("}';
				}else{
					mail($email,$mailSubj,$mailText,$mailHeaders);
//					echo "{'status':'ok','message':'Thank you! Request has been submitted!'}";
					echo '{"status":"ok","message":"Thank you! Request as '.$type.' has been submitted!"}';
				}
			}
			
		}
	}
	function resetPwd($myDatabase,$token,$passwd,$action,$urlRoot){
		$sql = "SELECT `user`,`email`,`type`,`firstName`,`lastName`,`update` FROM `givingTree_User_Master` WHERE `token`='" . $token ."'";
		$result = mysql_query($sql);
			?>
		<html>
			<link rel="icon" type="image/png" href="<?php echo $urlRoot; ?>images/logo16x16.png" />
			<link rel="apple-touch-icon" href="<?php echo $urlRoot; ?>images/logo16x16.png">
			<meta name="mobile-web-app-capable" content="yes">
			<meta name="apple-mobile-web-app-title" content="givingTree">
			<meta name="apple-mobile-web-app-capable" content="yes">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
			<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
			<meta charset="utf-8" />
			<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
			<script src="/give/assets/js/userManagement.js"></script>
			<script>
				<?php
				$changeShow=true;
				if($action=="reset" && $passwd!="" ){
					$uniqueID = uniqid();
					$pwd=encryptPwd($passwd);
					$sql="UPDATE `givingTree_User_Master` SET `word`='".$pwd."', `status`='active',`token`='".$uniqueID."' WHERE `token`='".$token."'";
					$result=mysql_query($sql);
					if($result){
						$changeShow=false;
//						setcookie("logincookie", $token, time() + (86400 * 1), "/");
						?>
							$(document).ready(function(){
								window.open("<?php echo $urlRoot; ?>","_self");
							});
						<?php
					}
				}
				?>
				function resetPwd(){
					var pwd1=$("#pswd").val();
					var pwd2=$("#pswd1").val();
					if(pwd1!=pwd2){
						$("#info").html("Password does not match.");
					}else{
						$("#info").html("Password OK.");
						window.open("/MY_API/resP?tkn=<?php echo $token; ?>&action=reset&word="+pwd1,"_self");
					}
				}
			</script>
			<style>
				#headerLine{
					top:50px;
					left:0;
					height:2px;
					width:100%;
					background: #7fb800;
					display:block;
					position:fixed;
					z-index:150;
				}
			</style>
				<div style="text-align:center;width:100%;">
					<img style="width:50px;" src="/images/logo.png"><br>
					<div id="headerLine"></div>
			
		<?php
		if(mysql_num_rows($result) && $changeShow ){
			//find record, need to change your password
			$value = mysql_fetch_object($result);
			$currentTime=time();
			$updateTime=strtotime($value->update);
			if((abs($currentTime - $updateTime) / 3600)>72){
				//Your token has expired.
				?><div id="info">Your token expired.</div><?php
			}else{
				//Token valid
				if($action=="reset" && $passwd!="" && $value->status!="active"){
					$pwd=$this->encryptP($passwd);
					$sql="UPDATE `givingTree_User_Master` SET `word`='".$pwd."', `status`='active' WHERE `token`='".$token."'";
					$result=mysql_query($sql);
					if($result){
						?><div>Your password has been successfully reset, </div><?php
					}else{
						?><div>Something is wrong, please contact info@gogivingtree.com </div><?php
					}
				}else{
					?>
					<form>
						<div>
							<h3>Hello, <?php echo $value->firstName." ".$value->lastName; ?>, please input your new password.</h3>
							<label for="pswd" class="ui-hidden-accessible">Password:</label>
							<input type="password" name="passw" id="pswd" placeholder="Password">
							<label for="pswd1" class="ui-hidden-accessible">Password:</label>
							<input type="password" name="passw1" id="pswd1" placeholder="Password">
							<input type="button" onclick="resetPwd();" id="ResetPassword" data-inline="true" value="Submit">
						</div>
					</form>
					<div id="info"></div>
					<?php
				}
			}
		}else{
			//This is a invalid session.
			?><div id="info">Invalid token or Password Changed</div><?php
		}
		?>
		</div></html>
		<?php
	}	//After registration, an email link will be sent to you. This is to activate your user and set the password


/*-----------Confirm Delivery ---------*/
	function receiveDeliverySub($item,$status,$cookieVal,$myDatabase,$urlRoot){
		$item = $_GET['item'];
		$status = $_GET['status'];
		$cookie_name="logincookie";
		$cookieVal = $_COOKIE[$cookie_name];
		if($status=="Received"){$status="Done";}
		$action = $_GET['action'];
		$link="/MY_API/receiveDelivery?item=".$item."&action=confirm&status=".$status;
		$link0="/MY_API/receiveDelivery?item=".$item."&status=".$status;
		$isLogin=true;
			?>
	<html>
		<link rel="icon" type="image/png" href="<?php echo $urlRoot; ?>images/logo16x16.png" />
		<link rel="apple-touch-icon" href="<?php echo $urlRoot; ?>images/logo16x16.png">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-title" content="givingTree">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
		<meta charset="utf-8" />
		<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
		<script src="<?php echo $urlRoot; ?>assets/js/userManagement.js"></script>
		<script>
		function loginReceive(){
			var userName = $("#usrnm").val();
			var passWord = $("#pswd").val();
			$.ajax({
				url: '/MY_API/loginRe?username='+userName+'&password='+passWord,
				data: {
					format: 'json'
				},
				error: function() {
					$('#info').html('<p>An error has occurred</p>');
				},
		//		dataType: 'jsonp',
				success: function(data) {
					var $title = $('<p>').text(data.status);
					if(data.status=="ok"){
						setCookie("logincookie",data.sessionID,1);
						window.open("<?php echo $link0; ?>","_self");
					}else{
						$('#info').html(data.message);
					}
				},
				type: 'GET'
			});
		}
		</script>
		
		<style>
			#headerLine{
				top:50px;
				left:0;
				height:2px;
				width:100%;
				background: #7fb800;
				display:block;
				position:fixed;
				z-index:150;
			}
		</style>
			<div style="text-align:center;width:100%;">
				<img style="width:50px;" src="<?php echo $urlRoot; ?>images/logo.png"><br>
				<div id="headerLine"></div>

		<?php
		if(!isset($cookieVal)) {
			$isLogin=false;
		} else {
			$sessionID= $cookieVal;
//			$myDatabase= $this->db;// variable to access your database
			$sql = "SELECT `user`,`email`,`type`,`firstName`,`lastName` FROM `givingTree_User_Master` WHERE `token`='" . $sessionID ."'";
			$result = mysql_query($sql);
			if(mysql_num_rows($result)){
			// Record found
				$value1 = mysql_fetch_object($result);
				$username = $value1->user;
				
				$sql="SELECT * from `givingTree_Donate_Master` where `ID`='".$item."'";
				$sql = "SELECT `givingTree_Donate_Master`.* ,  `givingTree_Needs_Master`.`needsTitle` , `givingTree_Needs_Master`.`Charity` , ".
					"`givingTree_Needs_Master`.`postUserName`, LENGTH(`givingTree_Needs_Master`.`needsImage`) as imgLen,  ".
					"`givingTree_Needs_Master`.`needsUnit`, `givingTree_Needs_Master`.`needsQuantity` ".
					", `givingTree_Needs_Master`.`needsMailingLabel`,`givingTree_Needs_Master`.`needsDeliveryLNG` ".
					", `givingTree_Needs_Master`.`needsDeliveryLAT`,`givingTree_Needs_Master`.`needsDeliveryAddress` ".
					", `givingTree_Needs_Master`.`needsDeliverAvailable`,`givingTree_Needs_Master`.`createTime` ".
					"FROM  `givingTree_Donate_Master` ".
					"INNER JOIN  `givingTree_Needs_Master` ON  `givingTree_Donate_Master`.`needsID` =  `givingTree_Needs_Master`.`ID` ".
					"WHERE `givingTree_Donate_Master`.`ID`='".$item."'";
				$result=mysql_query($sql);
				if($result){
					$value=mysql_fetch_object($result);
					$myLAT=$value->needsDeliveryLAT;
					$myLNG=$value->needsDeliveryLNG;
					$myLAT1=$value->LAT;
					$myLNG1=$value->LNG;
					$needsTitle=$value->needsTitle;
					$needsUnit=$value->needsUnit;
					$needsQty=$value->Qty;
					$needsFrom=$value->address;
					$needsTo=$value->needsDeliveryAddress;
					if($value->postUserName==$username){
						if($value->donateStatus==$status){
							if($action=='confirm'){	// Confirm Charity received this.
								// Update the database
								mysql_query("SET autocommit=0");
								mysql_query("START TRANSACTION");
								$sql="UPDATE `givingTree_Donate_Master` SET `donateStatus`='Received' WHERE ID='".$item."'";
								mysql_query($sql);
								$sql = "UPDATE  `givingTree_Delivery_Master` SET  `myLAT`='".$myLAT."',`myLNG`='".$myLNG."',`status` =  'received'  WHERE `donationID`='".$item."'";
								mysql_query($sql);
								$result=mysql_query("COMMIT");
								if($result){
									if($value->imgLen>0){
										?>
											<img style="width:12em;" src="/MY_API/displayIMG?needsID=<?php echo $value->NeedsID; ?>">
										<?php								
									}
									?>
									<p>You have successfully confirmed that you received the following from <?php echo $value->username; ?></p>
									<?php
								}else{
									?><p>Your transaction to confirm your have receive the following from <?php echo $value->username; ?> failed.</p><?php
								}
								?>
								<h2><?php echo $value->needsTitle." - "; ?></h2>
								<p>Quantity: <?php echo $value->Qty." ".$value->needsUnit; ?></p>
								<p>Delivery Address: <?php echo $value->needsMailingLabel." - ".$value->needsDeliveryAddress; ?></p>
								<?php
							}else
							{	
								// Display the donation information
								?>
								<div data-role="navbar">
									<ul>
										<li><a href="#" onclick="window.close();" data-icon="carat-l" data-transition="flip">Back</a></li>
										<li><a href="<?php echo $link;?>" onclick="window.open('<?php echo $link;?>','_self');" data-icon="check">Confirm</a></li>
									</ul>
								</div>
								<?php
								if($value->imgLen>0){
									?>
											<img style="width:12em;" src="/MY_API/displayIMG?needsID=<?php echo $value->NeedsID; ?>">
									<?php								
								}
								?>
								<p>You are about to confirm that you received the following from <?php echo $value->username; ?></p>
								<h2><?php echo $value->needsTitle." - "; ?></h2>
								<p>Quantity: <?php echo $value->Qty." ".$value->needsUnit; ?></p>
								<p>Delivery Address: <?php echo $value->needsMailingLabel." - ".$value->needsDeliveryAddress; ?></p>
								<?php
							}
						}else 
						{
							if($value->imgLen>0){
								?>
								<img style="width:12em;" src="/MY_API/displayIMG?needsID=<?php echo $value->NeedsID; ?>">
								<?php								
							}
								?>
							<p>This donation has already been received!</p>
							<h2><?php echo $value->needsTitle." - "; ?></h2>
							<p>Quantity: <?php echo $value->Qty." ".$value->needsUnit; ?></p>
							<p>Delivery Address: <?php echo $value->needsMailingLabel." - ".$value->needsDeliveryAddress; ?></p>
							<?php
						}
					
					}else
					{
						$myLAT=$value->LAT;
						$myLNG=$value->LNG;
						$sql="SELECT * FROM `givingTree_Delivery_Master` WHERE `status`='booked' and `donationID`='".$item."' and `deliverUser`='".$username."'";
						$result=mysql_query($sql);
						if(mysql_num_rows($result)){
							if($action=='confirm'){ // Confirm the driver recevied the goods.
								mysql_query("SET autocommit=0");
								mysql_query("START TRANSACTION");
								$sql = "UPDATE  `givingTree_Delivery_Master` SET  `status` =  'picked'  WHERE `status`='booked' and `donationID`='".$item."' and `deliverUser`='".$username."'";
								mysql_query($sql);
								$sql = "UPDATE  `givingTree_Delivery_Master` SET  `myLAT` =  '".$myLAT1."', `myLNG`= '".$myLNG1."'  WHERE `status`!='received' and `deliverUser`='".$username."'";
								mysql_query($sql);
								$sql = "UPDATE `givingTree_Donate_Master` SET `donateStatus`='DLV_picked' WHERE `ID`='".$item."'";
								mysql_query($sql);
								$result=mysql_query("COMMIT");
								if($result){
									?><p>You have successfully confirmed that you picked up the following from <?php echo $value->username; ?></p>
									<?php
								}else
								{
									?><p>Your transaction to confirm your have picked up the following from <?php echo $value->username; ?> failed.</p><?php
								}
								?>
								<h2><?php echo $value->needsTitle." - "; ?></h2>
								<p>Quantity: <?php echo $value->Qty." ".$value->needsUnit; ?></p>
								<p>Delivery Address: <?php echo $value->needsMailingLabel." - ".$value->needsDeliveryAddress; ?></p>
								<?php
							}else
							{
							// Display the donation information
								?>
								<div data-role="navbar">
									<ul>
										<li><a href="#" onclick="window.close();" data-icon="carat-l" data-transition="flip">Back</a></li>
										<li><a href="<?php echo $link;?>" onclick="window.open('<?php echo $link;?>','_self');" data-icon="check">Confirm</a></li>
									</ul>
								</div>
								<?php
								if($value->imgLen>0){
									?>
										<img style="width:12em;" src="/MY_API/displayIMG?needsID=<?php echo $value->NeedsID; ?>">
									<?php								
								}
								?>
								<p>You are about to confirm that you picked up the following from <?php echo $value->username; ?></p>
								<h2><?php echo $value->needsTitle." - "; ?></h2>
								<p>Quantity: <?php echo $value->Qty." ".$value->needsUnit; ?></p>
								<p>Delivery Address: <?php echo $value->needsMailingLabel." - ".$value->needsDeliveryAddress; ?></p>
								<?php
							}
						}
						else{
							$sql="SELECT * FROM `givingTree_Delivery_Master` WHERE `status`='picked' and `donationID`='".$item."' and `deliverUser`='".$username."'";
							$result=mysql_query($sql);
							if(mysql_num_rows($result)){
								?>
								<p>The following donation has already been picked up</p>
								<h2><?php echo $needsTitle." - "; ?></h2>
								<p>Quantity: <?php echo $needsQty." ".$needsUnit; ?></p>
								<p>From: <?php echo $needsFrom." <br>To: ".$needsTo; ?></p>
								<?php
								
							}else
							{
								?><a><?php echo "You ,".$username.", are not the user to accept this donation!";
								?>
								</a><br>
								<input type="button" value="Login as different User" onclick="$('#loginForm').show();">
								<?php
							}
						}
					}
				}
				
			}else{
			// No record found
				$isLogin=false;
			}
			
			
		};
			?>
				<form id="loginForm" style="display:none;">
					<div>
						<h3>Login information</h3>
						<label for="usrnm" class="ui-hidden-accessible">Username:</label>
						<input type="text" name="user" id="usrnm" placeholder="Username">
						<label for="pswd" class="ui-hidden-accessible">Password:</label>
						<input type="password" name="passw" id="pswd" placeholder="Password">
						<input type="button" onclick="loginReceive();" id="loginRece" data-inline="true" value="Log in">
					</div>
				</form>
				<div id="info"></div>
			<?php

		?>
			</div>
		<?php
		if($isLogin==false){
		?>
		<script>
			$(document).ready(function(){		
				$('#loginForm').show();
			});
		</script>
		<?php
		} 
		?>
		</html>


		<?php
			
		
//		echo $item;
	
	}
