<!doctype html>
<html lang=''>
	<head>
		<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
		<script src="assets/js/userManagement.js"></script>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
		<script>
			$(document).ready(function(){
				showDashboard();
			});
			
			function showDashboard(){
				var sessionID = '<?php
					echo $_GET['sessionID'];
				?>';
				if (sessionID==""){
					sessionID = getCookie("wordTestLogin");
				}
				var userName = '<?php
					$user = $_GET['user'];
					echo $user;
				?>';
				if(userName==""){
					var myURL = "MY_API/transaction?trans=8004&sessionID="+sessionID;
				}else{
					var myURL = "MY_API/transaction?trans=8005&data={\"user\":\""+userName+"\",\"Transaction\":\"lastWeek\"}&sessionID="+sessionID;
				}
				myURL=myURL.replace(/{/g,"(@(").replace(/}/g,")@)");
				$.get(myURL, function(data, status){
					if(status=='success'){
						var barData = [
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0},
							{y:"",add:0,test:0,wrong:0}
						];
						var d = new Date();
						for(var i =0;i<barData.length;i++){
							barData[i].y=formatDate(d);
							if(data.recentAdd!=null){
								for(var j=0;j<data.recentAdd.length;j++){
									if(barData[i].y==data.recentAdd[j].Add_Date){
										barData[i].add=data.recentAdd[j].value;
										break;
									}
								}
							}
							if(data.recentTested!=null){
								for(var j=0;j<data.recentTested.length;j++){
									if(barData[i].y==data.recentTested[j].Test_Date){
										barData[i].test=data.recentTested[j].value;
										break;
									}
								}
							}
							if(data.recentWrong!=null){
								for(var j=0;j<data.recentWrong.length;j++){
									if(barData[i].y==data.recentWrong[j].Mistake_Date){
										barData[i].wrong=data.recentWrong[j].value;
										break;
									}
								}
							}
							d.setDate(d.getDate() - 1);
						}
						Morris.Bar({
						  element: 'chart_lastweek',
						  data: barData,
						  xkey: 'y',
						  ykeys: ['add', 'test','wrong'],
						  labels: ['Added', 'Tested', 'Made Mistake']
						});
					}
				});
			
			}
			function formatDate(date) {
				var d = new Date(date),
					month = '' + (d.getMonth() + 1),
					day = '' + d.getDate(),
					year = d.getFullYear();

				if (month.length < 2) month = '0' + month;
				if (day.length < 2) day = '0' + day;

				return [year, month, day].join('-');
			}
		</script>
		<style>
			h1, h2, h3, h4, h5, h6 {
				font-family: 'Open Sans', sans-serif;
				color: #313f47;
				line-height: 1.5;
				margin: 0 0 0.75em 0;
			}
		</style>
	</head>
	<body>
		<div style="width:100%;text-align:center">
			<h3>Last Week Status</h3>
			<div id="chart_lastweek" style="height: 250px;"></div>
		</div>
	</body>
</html>

