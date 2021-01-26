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
                    var myURL = "MY_API/transaction?trans=8002&sessionID="+sessionID;
                }else{
                    var myURL = "MY_API/transaction?trans=8005&data={\"user\":\""+userName+"\",\"Transaction\":\"coverAll\"}&sessionID="+sessionID;
                }
                myURL=myURL.replace(/{/g,"(@(").replace(/}/g,")@)");
                $.get(myURL, function(data, status){
                    if(status=='success'){
                        if(data.CoverAll!=null){
                            for(var i =0;i<data.CoverAll.length;i++){
                                if(data.CoverAll[i].TestNo==0){
                                    data.CoverAll[i].label="Never Tested";
                                }else{
                                    data.CoverAll[i].label="Tested "+data.CoverAll[i].TestNo+" times";
                                }
                            }
                            var chart01= Morris.Donut({
                                element: 'chart_overallCover',
                                data: [{label:"",value:0}],
                            });		
                            chart01.setData(data.CoverAll);
                        }
                    }
                });
            
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
            <h3>Test Coverage</h3>
            <div id="chart_overallCover" style="height: 250px;"></div>
        </div>
    </body>
</html>

