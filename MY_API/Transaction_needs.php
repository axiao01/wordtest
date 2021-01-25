<?php
//-----------------------------------------------------issing fields: 1. Condition: used/neMw
/* Update or Create a new needs
// Function Name: AddNewWord($username, $data, $myDatabase)
// Parameters:
// 	$username 	- the username who initiate this transaction
//	$data 		- Actual data need to update. Dataformat as following:
//		$data
	`WordList`(`ID`, `Word`, `Meaning`, `Example`, `AddBy`, `UpdateTime`, `Category`)
//	$myDatabase: current db connection. Passed from the main program.*/
//-----------Tranaction Code 1002 - AddNewWord
	function AddNewWord($username,$input,$myDatabase){

		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
//--------------Validating the input data
		
		if($data->Word==''){
			$response['status']='error';
			$response['message']="Word should not be blank!";
		}else{
//-------------------------Data Validated. 
			$sql = "SELECT * FROM `WordList` WHERE  `Word` = '" . $data->Word."'";
			$result=mysql_query($sql);
			
			if(mysql_num_rows($result)){
//------------------------Update Existing Record
				$value=mysql_fetch_object($result);
				$sql="UPDATE `WordList` SET `Meaning`='".$data->Meaning."',`Example`='".$data->Example."',`AddBy`='".$username."',`Category`='".$data->Category.
					"' WHERE `Word`='".$data->Word."'";
//				$response['sqlst']=$sql;
				$response['found']=true;
				$result=mysql_query($sql);
				if($result){
					$response['status']='ok';
					$response['ID']=$value->ID;
					$response['Word']=$data->Word;
					$response['WordID']=$value->ID;
				}
				else{
					header('Content-type:application/json');
					$response['status']='error';
					$response['message']="Update Fail!";
				}
			}else{
//------------------------Insert New Record
				$response['found']=false;
				$sql ="INSERT INTO `WordList`(`ID`, `Word`, `Meaning`, `Example`, `AddBy`, `Category`) ".
					"VALUES ('".$uniqueID."','".$data->Word."','".$data->Meaning."','".$data->Example."','".$username."','".$data->Category."')";
				$result=mysql_query($sql);
				if($result){
					$response['status']='ok';
					$response['ID']=$uniqueID;
					$response['Word']=$data->Word;
					$response['WordID']=$uniqueID;
				}
				else{
					$response['status']='error';
					$response['message']="Insert data record fail!";
				}
			}
		}
		echo json_encode($response);
		
	}
//-----------Transaction Code 1001 - showWord
	function showWord($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
		if($data->Word==''){
			$response['status']='error';
			$response['message']="Word should not be blank!";
		}else{
			$sql = "SELECT * from `WordList` WHERE  `Word` = '" . $data->Word."'";
			$result=mysql_query($sql);
			$response['sql']=$sql;
			if(mysql_num_rows($result)){
//------------------------Update Existing Record
				$value=mysql_fetch_object($result);
				$response['status']='ok';
				$response['result']=$value;
			}else{
				$response['status']='error';
				$response['message']="Not found!";
			}		
		}
		echo json_encode($response);
		
	}

//-----------Transaction Code 1005 - listOtherWord
	function listOtherWord($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
		$totalRow=$data->TotalRow;
		if($totalRow==null || $totalRow<1){
			$totalRow=30;
		}
		if($data->Category==""){
			$condition="";
		}else{
			$condition=" `WordList`.`Category`='".$data->Category."' AND"; 
		}
//		$response['condi']=$condition;
		$sql ="SELECT  `WordList`.`ID`,`WordList`.`Word`,`WordList`.`Meaning`,`WordList`.`Example`,`WordList`.`AddBy`,`WordList`.`Category`  FROM  `WordList` LEFT JOIN  `GroupWordResults` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
			"AND  `GroupWordResults`.`User` =  '".$username."' WHERE ".$condition." `GroupWordResults`.`User` IS NULL ORDER BY RAND( ) LIMIT 0 ,".$totalRow;
		$value=[];
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
		}
		if(sizeof($value)>0){
			$response['status']='ok';
			$response['number']=sizeof($value);
			$response['results']=$value;
		}else{
			$response['status']='error';
			$response['number']=sizeof($value);
			$response['message']="No results";
		}
		echo json_encode($response);
	}

//-----------Transaction Code 2001 - ShowFavorite
	function showMyFavoriate($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
		if($data->Word==''){
			$response['status']='error';
			$response['message']="Word should not be blank!";
		}else{
			$sql = "SELECT * from `GroupWordResults` WHERE  `Word` = '" . $data->Word."' and `User`='".$username."'";
			$result=mysql_query($sql);
			$response['sql']=$sql;
			if(mysql_num_rows($result)){
//------------------------Update Existing Record
				$value=mysql_fetch_object($result);
				$response['status']='ok';
				$response['result']=$value;
			}else{
				$response['status']='error';
				$response['message']="Not found!";
			}		
		}
		echo json_encode($response);
	
	}
//-----------Transaction Code 2002 - addMyFavorite
	function addMyFavoriate($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
//--------------Validating the input data
		
		if($data->Word==''){
			$response['status']='error';
			$response['message']="Word should not be blank!";
		}else
		{
			$sql="SELECT * FROM  `ScoreHistory` WHERE  `User` =  '".$username."' AND DATE(  `TimeStamp` ) = DATE( NOW( ) ) ";
			$result=mysql_query($sql);
			if(mysql_num_rows($result)){	
				$todayScore=true;
				$value=mysql_fetch_object($result);
				$noAdded=$value->NoAdded+1;
			}else{
				$todayScore=false;
				$noAdded=1;
			}
//-------------------------Data Validated. 
			$sql = "SELECT * FROM `GroupWordResults` WHERE  `Word` = '" . $data->Word."' and `User`='".$username."'";
			$result=mysql_query($sql);
			
			mysql_query("SET autocommit=0");
			mysql_query("START TRANSACTION");
			if(mysql_num_rows($result)){
//------------------------Update Existing Record
				$value=mysql_fetch_object($result);
				$sql="UPDATE `GroupWordResults` SET `ID`='".$uniqueID."',`WordID`='".$data->WordID."',`Word`='".$data->Word."',`User`='".$username."' WHERE ".
					"`Word`='".$data->Word."' and `User`='".$username."'";
				$response['found']=true;
			}else{
//------------------------Insert New Record
				$response['found']=false;
				$sql = "INSERT INTO `GroupWordResults`(`ID`, `WordID`, `Word`, `User`) VALUES ".
					"('".$uniqueID."','".$data->WordID."','".$data->Word."','".$username."')";
			}
			mysql_query($sql);
			if($todayScore){
				$sql="UPDATE `ScoreHistory` SET `NoAdded`=".$noAdded." WHERE `User` =  '".$username."' AND DATE(  `TimeStamp` ) = DATE( NOW( ) ) ";
			}else{
				$sql="INSERT INTO `ScoreHistory`(`ID`, `User`, `NoAdded`) VALUES ('SCR".$uniqueID."','".$username."',".$noAdded.")";
			}
			mysql_query($sql);
			$result=mysql_query("COMMIT");
			
			if($result){
				$response['status']='ok';
				$response['ID']=$uniqueID;
				$response['Word']=$data->Word;
			}
			else{
				$response['status']='error';
				$response['message']="Fail!";
			}
		}
		echo json_encode($response);
	
	}
//-----------Transaction Code 2003 - removeMyFavorite
	function removeMyFavoriate($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
//--------------Validating the input data
		
		if($data->Word==''){
			$response['status']='error';
			$response['message']="Blank WordID!";
		}else
		{
//-------------------------Data Validated. 
			$sql = "DELETE FROM `GroupWordResults` WHERE `WordID`='".$data->WordID."' and `User`='".$username."'";
			$result=mysql_query($sql);
			
			if($result){
				$response['status']='ok';
				$response['message']='Favorite removed.';
			}else{
				$response['status']='error';
				$response['message']='Not removed.';
			}
		}
		echo json_encode($response);
	}

//-----------Transaction Code 3003 - List Questions
	function listWordQuestions($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
//		$response['input']=$input;
//--------------Validating the input data
		
		if($data->numberOfWords=='' || $data->numberOfWords==null){
			$data->numberOfWords=10;
		}
		if($data->numberOfRecent=="" || $data->numberOfRecent==null){
			$data->numberOfRecent=0;
		}
		$value=array();
		if($data->numberOfRecent>0){
			$sql="SELECT *,`GroupWordResults`.`ID` as GRPID  FROM  `GroupWordResults` INNER JOIN  `WordList` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
				"WHERE  `GroupWordResults`.`User` =  '".$username."' ".
				"AND  `GroupWordResults`.`UpdateTime` BETWEEN TIMESTAMPADD(DAY , -0.5,  `GroupWordResults`.`UpdateTime` ) AND ".
				"TIMESTAMPADD( DAY , 0.5,  `GroupWordResults`.`UpdateTime` ) ORDER BY `GroupWordResults`.`ContinueWrong` DESC, `GroupWordResults`.`TestNo` ASC , RAND() LIMIT 0 , ".$data->numberOfRecent;
			$result=mysql_query($sql);
			$recentRow=mysql_num_rows($result);
			if(mysql_num_rows($result)){
				while($row=mysql_fetch_object($result)){
					$object = new stdClass();
					$object->ID = $row->GRPID;
					$object->WordID = $row->WordID;
					$object->Word = $row->Word;
					$object->Meaning = $row->Meaning;
					$object->Example = $row->Example;
					$object->RightNo = $row->RightNo;
					$object->SpellRight = $row->SpellRight;
					$object->TestNo = $row->TestNo;
					$object->AverageTime = $row->AverageTime;
					$object->LastTime = $row->LastTime;
					$sql="SELECT `WordList`.`Meaning` FROM  `GroupWordResults` INNER JOIN  `WordList` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
						"WHERE  `GroupWordResults`.`User` =  '".$username."' AND `GroupWordResults`.`WordID`!='".$row->WordID."' ORDER BY RAND() LIMIT 0 , 3";
					$result1=mysql_query($sql);
					$choice=array();
					if(mysql_num_rows($result)){
						while($row1=mysql_fetch_object($result1)){
							array_push($choice,$row1);
						}
					}
					$object->Choices = $choice;
					array_push($value,$object);
				}
			}
		}else{
			$recentRow=0;
		}
		$otherRow=$data->numberOfWords-$recentRow;
		if($otherRow<=0){
		}else{
			$sql="SELECT *,`GroupWordResults`.`ID` as GRPID  FROM  `GroupWordResults` INNER JOIN  `WordList` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
				"WHERE  `GroupWordResults`.`User` =  '".$username."' ORDER BY RAND( ) LIMIT 0 , ".$otherRow;
			$result=mysql_query($sql);
			if(mysql_num_rows($result)){
				while($row=mysql_fetch_object($result)){
					$object = new stdClass();
					$object->ID = $row->GRPID;
					$object->WordID = $row->WordID;
					$object->Word = $row->Word;
					$object->Meaning = $row->Meaning;
					$object->Example = $row->Example;
					$object->RightNo = $row->RightNo;
					$object->SpellRight = $row->SpellRight;
					$object->TestNo = $row->TestNo;
					$object->AverageTime = $row->AverageTime;
					$object->LastTime = $row->LastTime;
					$sql="SELECT `WordList`.`Meaning` FROM  `GroupWordResults` INNER JOIN  `WordList` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
						"WHERE  `GroupWordResults`.`User` =  '".$username."' AND `GroupWordResults`.`WordID`!='".$row->WordID."' ORDER BY RAND( ) LIMIT 0 , 3";
					$result1=mysql_query($sql);
					$choice=array();
					if(mysql_num_rows($result)){
						while($row1=mysql_fetch_object($result1)){
							array_push($choice,$row1);
						}
					}
					$object->Choices = $choice;
					array_push($value,$object);
				}
			}

		}

		if(sizeof($value)>0){
			$response['status']='ok';
			$response['number']=sizeof($value);
			$response['results']=$value;
		}else{
			$response['status']='error';
			$response['number']=sizeof($value);
			$response['message']="No results";
		}
		echo json_encode($response);
	}
//-----------Transaction Code 5001 - submit result one Question
	function submitAnswer($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
		$sql="SELECT * FROM  `ScoreHistory` WHERE  `User` =  '".$username."' AND DATE(  `TimeStamp` ) = DATE( NOW( ) ) ";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){	
			$todayScore=true;
			$value=mysql_fetch_object($result);
			$noRight=$value->NoRight+$data->RightNo;
			$rightSpell=$value->RightSpell+$data->SpellRight;
			$noTested=$value->NoTested+$data->TestNo;
		}else{
			$todayScore=false;
		}
		$sql="SELECT * FROM `GroupWordResults` WHERE `ID`='".$data->ID."'";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=mysql_fetch_object($result);
			$rightNo=$value->RightNo+$data->RightNo;
			$spellRight=$value->SpellRight+$data->SpellRight;
			$testNo=$value->TestNo+$data->TestNo;
			$averageTime=$value->AverageTime;
			$averageTime=($averageTime*$value->TestNo+$data->Time)/$testNo;
			$continueRight=$value->ContinueRight;
			$continueWrong=$value->ContinueWrong;
			if(($data->RightNo+$data->SpellRight)<2){
				//----Something is Wrong
				$continueRight=0;
				$continueWrong++;
			}else{
				//----Both Spelling and Answer are right.
				$continueWrong=0;
				$continueRight++;
			}
			mysql_query("SET autocommit=0");
			mysql_query("START TRANSACTION");
			$sql="UPDATE `GroupWordResults` SET `RightNo`=".$rightNo.",`SpellRight`=".$spellRight.",`TestNo`=".$testNo.", `AverageTime`=".$averageTime.", `LastTime`=".$data->Time.
				",`ContinueRight`=".$continueRight.",`ContinueWrong`=".$continueWrong." WHERE `ID`='".$data->ID."'";
			mysql_query($sql);
			if($todayScore){
				$sql="UPDATE `ScoreHistory` SET `NoRight`=".$noRight.",`RightSpell`=".$rightSpell.",`NoTested`=".$noTested." WHERE `User` =  '".$username."' AND DATE(  `TimeStamp` ) = DATE( NOW( ) ) ";
			}else{
				$sql="INSERT INTO `ScoreHistory`(`ID`, `User`, `NoRight`, `RightSpell`, `NoTested`) VALUES ('SCR".$uniqueID."','".$username."',".$data->RightNo.",".$data->SpellRight.",".$data->TestNo.")";
			}
			mysql_query($sql);
			$result=mysql_query("COMMIT");
			if($result){
				$response['status']='ok';
				$response['message']='Updated';
			}else{
				$response['status']='error';
				$response['message']='Fail';
			}
				
		}else{
			$response['status']='error';
			$response['message']='No Such ID';
		}
		echo json_encode($response);
	}

//-----------Transaction Code 3001 - List Words
/*	Input 
		{"TotalRow":30,"TimeFrame":1-7 (days),"ContinueWrong":2,"PercentMistake":100}
*/
	function listWordsCard($username,$input,$myDatabase){
		$data = json_decode($input);
		$uniqueID = uniqid();
		$dateCreate = date('Y-m-d');
		header('Content-type:application/json');
		$totalRow=$data->TotalRow;
		if($totalRow==null || $totalRow<1){
			$totalRow=30;
		}
		$timeFrame = $data->TimeFrame;		// Number of days 
		if($timeFrame!=null || $timeFrame >1){
			$condition="`GroupWordResults`.`UpdateTime` BETWEEN TIMESTAMPADD(DAY , -".$timeFrame.",  `GroupWordResults`.`UpdateTime` ) AND ".
				"TIMESTAMPADD( DAY , 0,  `GroupWordResults`.`UpdateTime` ) AND";
		}elseif($timeFrame==1){
			$condition="DATE(`GroupWordResults`.`UpdateTime`)=DATE(NOW()) ";
		}else{
			$condition="";
		}
		$averageTime=$data->AverageTime;
		if($averageTime!=null and $averageTime>1){
			$condition = $condition." `GroupWordResults`.`AverageTime`>".$averageTime." AND";
		}
		$continueWrong = $data->ContinueWrong;
		if($continueWrong!=null && $continueWrong >0){
			$condition = $condition." `GroupWordResults`.`ContinueWrong`>=".$continueWrong." AND ";
		}
		$continueRight = $data->ContinueRight;
		if($continueRight!=null && $continueRight >0){
			$condition = $condition." `GroupWordResults`.`ContinueRight`>=".$continueRight." AND ";
		}
		if($data->PercentMistake !=null and $data->PercentMistake>=0){
			$conditionHaving = "Having (rightRate <= ".(1-$data->PercentMistake/100)." OR rightRate is null) ";
		}else{
			$conditionHaving = "";
		}
		
		$sql="SELECT *,`GroupWordResults`.`ID` as GRPID, (`GroupWordResults`.`RightNo`+`GroupWordResults`.`SpellRight`)/2/`GroupWordResults`.`TestNo` as rightRate FROM  `GroupWordResults` INNER JOIN  `WordList` ON  `GroupWordResults`.`WordID` =  `WordList`.`ID` ".
			"WHERE ".$condition." `GroupWordResults`.`User` =  '".$username."' ".$conditionHaving." ORDER BY RAND( ) LIMIT 0 , ".$totalRow;
		$value=[];
//		$response['input']=$input;
		$response['sql']=$sql;
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			while($row=mysql_fetch_object($result)){
				$object = new stdClass();
				$object->ID = $row->GRPID;
				$object->WordID = $row->WordID;
				$object->Word = $row->Word;
				$object->Meaning = $row->Meaning;
				$object->Example = $row->Example;
				$object->RightNo = $row->RightNo;
				$object->SpellRight = $row->SpellRight;
				$object->TestNo = $row->TestNo;
				$object->AverageTime = $row->AverageTime;
				$object->LastTime = $row->LastTime;
				array_push($value,$object);
			}
		}
		if(sizeof($value)>0){
			$response['status']='ok';
			$response['number']=sizeof($value);
			$response['results']=$value;
		}else{
			$response['status']='error';
			$response['number']=sizeof($value);
			$response['message']="No results";
		}
		echo json_encode($response);
		
	}

//-----------Transaction Code 8001 - getSummary()
	function getSummary($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
//----------------Word Coverage
		$sql="SELECT  `TestNo` , COUNT(  `ID` ) AS value FROM  `GroupWordResults` WHERE `User`='".$username."'".
			"GROUP BY  `TestNo` ORDER BY  `TestNo` DESC ";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['CoverAll']=$value;
		}
//---------------Correctly tested recently Word
		$sql="SELECT  `ContinueRight` , COUNT(  `ID` ) AS value FROM  `GroupWordResults` WHERE `User`='".$username."'".
			"GROUP BY  `ContinueRight` ORDER BY  `ContinueRight` DESC ";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['CoverRight']=$value;
		}
//-----How Many Words has been tested in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Test_Date , COUNT(  `ID` ) as value FROM  `GroupWordResults` WHERE `TestNo`>0 AND `User`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentTested']=$value;
		}
//-----How Many Words has been mistake(still not corrected) in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Mistake_Date , COUNT(  `ID` ) as value FROM  `GroupWordResults` WHERE `ContinueWrong`>0 AND `User`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentWrong']=$value;
		}
//-----How Many Words has been added/updated in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Add_Date, COUNT(  `ID` ) as value FROM  `WordList` WHERE `AddBy`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentAdd']=$value;
		}
		$response['status']='ok';
		
		echo json_encode($response);
	
	}

//-----------Transaction Code 8002 - getCoverage()
	function getCoverage($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
//----------------Word Coverage
		$sql="SELECT  `TestNo` , COUNT(  `ID` ) AS value FROM  `GroupWordResults` WHERE `User`='".$username."'".
			"GROUP BY  `TestNo` ORDER BY  `TestNo` DESC ";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['CoverAll']=$value;
		}
		echo json_encode($response);
	}
//-----------Transaction Code 8003 - getRightCover
	function getRightCover($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
//---------------Correctly tested recently Word
		$sql="SELECT  `ContinueRight` , COUNT(  `ID` ) AS value FROM  `GroupWordResults` WHERE `User`='".$username."'".
			"GROUP BY  `ContinueRight` ORDER BY  `ContinueRight` DESC ";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['CoverRight']=$value;
		}
		echo json_encode($response);
	}

//-----------Transaction Code 8004 - getLastWeek
	function getLastWeek($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
//-----How Many Words has been tested in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Test_Date , COUNT(  `ID` ) as value FROM  `GroupWordResults` WHERE `TestNo`>0 AND `User`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentTested']=$value;
		}
//-----How Many Words has been mistake(still not corrected) in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Mistake_Date , COUNT(  `ID` ) as value FROM  `GroupWordResults` WHERE `ContinueWrong`>0 AND `User`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentWrong']=$value;
		}
//-----How Many Words has been added/updated in the last 10 days
		$sql="SELECT DATE(  `UpdateTime` ) as Add_Date, COUNT(  `ID` ) as value FROM  `WordList` WHERE `AddBy`='".$username."'".
			"GROUP BY DATE(  `UpdateTime` ) ORDER BY DATE(  `UpdateTime` ) DESC LIMIT 0 , 10";
		$result=mysql_query($sql);
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['recentAdd']=$value;
		}
		$response['status']='ok';
		
		echo json_encode($response);
	}

//-----------Transaction Code 8006 - getLastWeekSimple
	function getLastWeekSimple($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
//-----How Many Words has been tested in the last 10 days
		$sql = "SELECT *, DATE(`TimeStamp`) as scoreDate FROM  `ScoreHistory` WHERE  `User` =  '".$username."' ORDER BY  `TimeStamp` DESC  LIMIT 0 , 7";
		$result=mysql_query($sql);$value=array();
		if(mysql_num_rows($result)){
			$value=array();
			while($row=mysql_fetch_object($result)){
				array_push($value,$row);
			}
			$response['results']=$value;
		}
		$sql = "DELETE FROM  `ScoreHistory` WHERE  `User` =  '".$username."' AND  `TimeStamp` < DATE( NOW( ) ) -7";
		$result=mysql_query($sql);
		$response['status']='ok';
		echo json_encode($response);
		
		
	}
//-----------Transaction Code 8005 - getCoverage for my kids
	function getDadSummary($username,$input,$myDatabase){
		$data = json_decode($input);
		if($data->Transaction=="coverAll"){
			getCoverage($data->user,"",$myDatabase);
		}
		if($data->Transaction=="coverRight"){
			getRightCover($data->user,"",$myDatabase);
		}
		if($data->Transaction=="lastWeek"){
			getLastWeek($data->user,"",$myDatabase);
		}
		if($data->Transaction=="lastWeekSimple"){
			getLastWeekSimple($data->user,"",$myDatabase);
		}
		
		
	}

//---------------------Transaction Code 9006 updateSettings
/* Input:
//	settings
//	Output:	Success or Failure.
// 	Process: update related filed in `givingTree_User_Master
//------------------------------------------------------------------*/
	function updateSettings($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
		$sql="UPDATE `UserMaster` SET `settings`='".$input."' WHERE `user`='".$username."'";
		$result=mysql_query($sql);
		if($result){
			$response['status']='ok';
			$response['message']="Success.";
		}else
		{
			$response['status']='error';
			$response['message']="Something is wrong.";
		}
		echo json_encode($response);
	}

//---------------------Transaction Code 9003 changePassword
/* Input: data = {"old":,"new1":,"new2"}
	Output: Message	*/
	function changePassword($username,$input,$myDatabase){
		$data = json_decode($input);
		header('Content-type:application/json');
		$old = $data->old;
		$new1 = $data->new1;
		$new2 = $data->new2;
		$old = encryptPwd($old);
		$new1 = encryptPwd($new1);
		$new2 = encryptPwd($new2);
		if($old!="" && $old!=null && $new1==$new2 && $new1!="" && $new1!=null){
			$sql = "SELECT * FROM `UserMaster` WHERE `user`='".$username."' and `word`='".$old."'";
			$result=mysql_query($sql);
			if(mysql_num_rows($result)){
				$sql="UPDATE `UserMaster` SET `word`='".$new1."' WHERE `user`='".$username."'";
				$result=mysql_query($sql);
				if($result){
					$response['status']='ok';
					$response['message']="Password Changed!";
				}else{
					$response['status']='error';
					$response['message']="DB problem!";
				}
			}else{
				$response['status']='error';
				$response['message']="Invalid old password!";
			
			}
		}else{
			$response['status']='error';
			$response['message']="Invalid password input!";
		}
		echo json_encode($response);
	}
	function encryptPwd($pwd){
		$KEY = "caMYPP0P";
		$phrase= hash("sha256", $pwd.$KEY);
		return $phrase;
	}
