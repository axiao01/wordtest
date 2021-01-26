<?php
     
require_once("Rest.inc.php");
include "Transaction_needs.php";
//include "Links_needs.php";
//include_once('./lib/QrReader.php');

//require_once("Transaction_needs.php");  

class API extends REST {
     
    public $data = "";
    //Enter details of your database
    
    private $db = NULL;
 
    public function __construct(){
        parent::__construct();              // Init parent contructor
        $this->dbConnect();                 // Initiate Database connection
    }
     
    private function dbConnect(){
        $myfile = fopen("../../../wordTest.config", "r") or die("Unable to open file!");
        $configStr=fgets($myfile);
        fclose($myfile);
        $dataConfig=json_decode($configStr);
        $this->db = mysql_connect($dataConfig->DB_SERVER,$dataConfig->DB_USER,$dataConfig->DB_PASSWORD);
        $this->urlRoot = $dataConfig->ROOT;
        if($this->db)
            mysql_select_db($dataConfig->DB,$this->db);
        
        

//      $this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
//      if($this->db)
//          mysql_select_db(self::DB,$this->db);
    }
     
    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
    public function processApi(){
        if (isset($_REQUEST['rquest'])) {
            $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
        } else {
            $func = '';
        }
        if((int)method_exists($this,$func) > 0)
            $this->$func();
            else
            $this->response('Error code 404, Page not found',404);   // If the method not exist with in this class, response would be "Page not found".
    }
    
    //----------------------------LOGIN RELATED--------------------------------------------
    function login(){
        header('Content-type:application/json');

        if($this->get_request_method() != "GET"){
            $this->response('',406);
        }
        $username = $_GET['username'];
        $password = $_GET['password'];
        $uniqueID = uniqid();
        $pwd=encryptPwd($password);
        $myDatabase= $this->db;// variable to access your database
        $sql = "SELECT * from `UserMaster` WHERE `user`='" .$username."' and `word`='".$pwd."'";
        $result = mysql_query($sql);
        if(mysql_num_rows($result)){
            $sql = "UPDATE `UserMaster` SET `token`='".$uniqueID."' WHERE `user`='" .$username."' and `word`='".$pwd."'";
            $result = mysql_query($sql);
            if($result){
                // Username already exist in the table
                $response['status']='ok';
                $response['sessionID']=$uniqueID;
                $response['user']=$username;
            }else{
                $response['status']='error';
                $response['message']="Username and Password not match";
            }
        }else{
            $response['status']='error';
            $response['message']="Username and Password not match";
        }
        echo json_encode($response);
    
    }

    /**
     * Verify Session
     */
    function verifySession(){
        if($this->get_request_method() != "GET"){
            $this->response('',406);
        }
        $cookie = $_GET['sessionID'];
        if(strlen($cookie)<5){
            $this->response("{'status':'error','sessionID':'Invalid session'}", 404);  
        
        }else{
            $myDatabase= $this->db;// variable to access your database
            $sql = "SELECT `user`,`type`,`settings`,`group` FROM `UserMaster` WHERE `token`='" . $cookie ."'";
            $result = mysql_query($sql);
            if(mysql_num_rows($result)){
            // Record found
                $value1 = mysql_fetch_object($result);
                $value1->status="ok";
                $this->response(json_encode($value1),200);
            }else{
            // No record found
                $this->response("{'status':'error','sessionID':'Invalid session'}", 200);
            };
        
        }
    }
    
    /*----------------------------TRANSACTIONS--------------------------*/
    function transaction(){
        if($this->get_request_method() != "GET"){
            $this->response('',406);
        }
        $cookie = $_GET['sessionID'];
        $data = $_GET['data'];
        $data=str_replace('(@(', '{', $data);
        $data=str_replace(')@)', '}', $data);
        $transaction = $_GET['trans'];
        
        $myDatabase= $this->db;// variable to access your database
        $sql = "select * from `UserMaster` where token='" . $cookie ."'";
        $result = mysql_query($sql);
        if(mysql_num_rows($result)){
        // Record found
            $value1 = mysql_fetch_object($result);
        //----------------------------------- Security validated ------------------------
            $userRole=$value1->type;
            $username=$value1->user;
            $this->givingTree_TR($userRole,$data,$transaction,$username);
        }else{
        // No record found - user not registerd
            $this->response("{'status':'error','message':'Invalid cookie'}", 200);
        };
    
    }

    function givingTree_TR($user,$data, $transaction,$username){
    //---------------Check the Authorization from the database and execute the functions defined.
        $myDatabase= $this->db;// variable to access your database
        $sql = "SELECT * FROM  `transactionList` WHERE  `ID` = " . $transaction;
        $result=mysql_query($sql);
        if(mysql_num_rows($result)){
            $value=mysql_fetch_object($result);
            $myFunc = $value->functionName;
            $userAuth=$value->authorization;

            if($user=="administrator"){
                $runable=true;
            }elseif(strpos($userAuth, $user)!==false){
                $runable=true;
            }
            if($runable){
                $myFunc($username,$data,$this->db);
            }else{
                echo "{'status':'error','message':'The user is not authorized for this transaction - ".$transaction."'}";
            }
        }else{
            echo "{'status':'error','message':'Transaction code (".$transaction.") does not exist'}";
        }
    }
    function registerUser(){
        if($this->get_request_method() != "POST"){
            $this->response('',406);
        }
        $myDatabase= $this->db;
        $dataStr=$_POST['data'];
        registerUserSub($myDatabase,$dataStr);
    }
    function resP(){
        if($this->get_request_method() != "GET"){
            $this->response('',406);
        }
        $token = $_GET['tkn'];
        $action = $_GET['action'];
        $passwd = $_GET['word'];
        $myDatabase= $this->db;// variable to access your database
        resetPwd($myDatabase,$token,$passwd,$action,$this->urlRoot);
    }	//After registration, an email link will be sent to you. This is to activate your user and set the password
    function uploadImage(){
        if($this->get_request_method() != "POST"){
            $this->response('',406);
        }
        $myDatabase= $this->db;
        $needsID=$_POST['needsID'];
        $cookie = $_POST['sessionID'];
        $sql = "select * from `givingTree_User_Master` where token='" . $cookie ."'";
        $result = mysql_query($sql);
        
        if(mysql_num_rows($result)){
        // Record found
            $value = mysql_fetch_object($result);		
            $username = $value->user;
            $image = addslashes(file_get_contents($_FILES['image']['tmp_name'])); //SQL Injection defence!
            $image_name = addslashes($_FILES['image']['name']);
            $sql = "UPDATE `givingTree_Needs_Master` set `needsImage`='{$image}' where ID='".$needsID."' and `postUserName`='".$username."'";
            if (!mysql_query($sql)) { // Error handling
                $response['status']='error';
                $response['message']="Something went wrong! :(";
                echo 'error: update fail!';
            }else{
                $response['status']='ok';
                $response['message']= $needsID." has been updated.";
                echo 'upload success: '.$needsID;
            }
        }else{
            $response['status']='error';
            $response['message']="Invalide Session ID";
            echo 'error: invalid sessionID';
        }
        
    }
    function displayIMG(){
        //-----Sample html call: <img style="width:150px" src='/MY_API/displayIMG?needsID=NEEDS-584b8dbd5d2d1'>
        
        if($this->get_request_method() != "GET"){
            $this->response('',406);
        }
        $myDatabase= $this->db;
        $id=$_GET['needsID'];
          // do some validation here to ensure id is safe
        $link = mysql_connect("localhost", "root", "");
        $sql = "SELECT `needsImage` FROM `givingTree_Needs_Master` WHERE ID='".$id."'";
        $result = mysql_query("$sql");
        $row = mysql_fetch_assoc($result);

        header("Content-type: image/jpeg");
        echo $row['needsImage'];
    }
 
     
    /*
     *  Encode array into JSON
    */
    private function json($data){
        if(is_array($data)){
            return json_encode($data);
        }
    }
}
 
// Initiiate Library
    
$api = new API;
$api->processApi();
?>