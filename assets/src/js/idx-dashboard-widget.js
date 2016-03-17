var timeframe = document.querySelector('#idx_dashboard_widget #timeframe-switch');

function getTimeframe(){
    var value = document.querySelector('#idx_dashboard_widget #timeframe-switch').checked;
    if(value === true){
        return 'month';
    } else {
        return 'day';
    }
}

function leadsChart(){
    jQuery.post(
        ajaxurl, {
            'action': 'idx_dashboard_leads',
            'timeframe' : getTimeframe()
    }).done(function(jsonData){

        var data = google.visualization.arrayToDataTable(JSON.parse(jsonData));

        var options = {
            title: 'Lead Statistics',
            hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0},
            animation: {
                startup: true,
                duration: 500
            }
        };

        var chart = new google.visualization.AreaChart(document.querySelector('.leads-overview'));
        chart.draw(data, options);

    }); 
}

function getLeads(){
        google.charts.setOnLoadCallback(leadsChart);
}

function initialLoad(){
    //load Leads Chart
    google.charts.load('current', {'packages':['corechart']});
    //load chart on page load
    getLeads();
}

function listenToButtons(){
    //reload chart when timeframe is changed
    timeframe.addEventListener('change', getLeads);
    //change view when listing
}

initialLoad();
