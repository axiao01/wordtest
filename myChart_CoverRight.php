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
                    var myURL = "MY_API/transaction?trans=8003&sessionID="+sessionID;
                }else{
                    var myURL = "MY_API/transaction?trans=8005&data={\"user\":\""+userName+"\",\"Transaction\":\"coverRight\"}&sessionID="+sessionID;
                }
                myURL=myURL.replace(/{/g,"(@(").replace(/}/g,")@)");
                $.get(myURL, function(data, status){
                    if(status=='success'){
                        if(data.CoverRight!=null){
                            for(var i =0;i<data.CoverRight.length;i++){
                                if(data.CoverRight[i].ContinueRight==0){
                                    data.CoverRight[i].label="Never Right";
                                }else{
                                    data.CoverRight[i].label="Right "+data.CoverRight[i].ContinueRight+" times";
                                }
                            }
                            var chart02= Morris.Donut({
                                element: 'chart_correctCover',
                                data: [{label:"",value:0}],
                            });

                            chart02.setData(data.CoverRight);
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
            <h3>Correct Coverage</h3>
            <div id="chart_correctCover" style="height: 250px;"></div>
        </div>
    </body>
</html>

