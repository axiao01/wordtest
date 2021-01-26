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
                    var myURL = "MY_API/transaction?trans=8006&sessionID="+sessionID;
                }else{
                    var myURL = "MY_API/transaction?trans=8005&data={\"user\":\""+userName+"\",\"Transaction\":\"lastWeekSimple\"}&sessionID="+sessionID;
                }
                myURL=myURL.replace(/{/g,"(@(").replace(/}/g,")@)");
                $.get(myURL, function(data, status){
                    if(status=='success'){
                        var barData = [
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0},
                            {y:"",add:0,test:0,right:0,spell:0}
                        ];
                        var d = new Date();
                        for(var i =0;i<barData.length;i++){
                            barData[i].y=formatDate(d);
                            if(data.results!=null){
                                for(var j=0;j<data.results.length;j++){
                                    if(barData[i].y==data.results[j].scoreDate){
                                        barData[i].add=data.results[j].NoAdded;
                                        barData[i].test=data.results[j].NoTested;
                                        barData[i].right=data.results[j].NoRight;
                                        barData[i].spell=data.results[j].RightSpell;
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
                          ykeys: ['add', 'test','right','spell'],
                          labels: ['Added', 'Tested', 'Right Answer','Correct Spelling']
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

