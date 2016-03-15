google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        ['Month', 'Searches', 'Registrations'],
        ['January',  100, 40],
        ['February',  110, 46],
        ['March',  60, 9],
        ['April',  100, 15]
    ]);

    var options = {
        title: 'Lead Statistics',
        hAxis: {title: 'Month',  titleTextStyle: {color: '#333'}},
        vAxis: {minValue: 0}
    };

    var chart = new google.visualization.AreaChart(document.querySelector('.leads-overview'));
    chart.draw(data, options);
}
