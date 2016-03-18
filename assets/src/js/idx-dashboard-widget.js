window.addEventListener('load', function(){
    var timeframe = document.querySelector('#idx_dashboard_widget #timeframeswitch');
    var leadsOverviewButton = document.querySelector('#idx_dashboard_widget .leads');
    var listingsOverviewButton = document.querySelector('#idx_dashboard_widget .listings');
    var loader = document.querySelector('.idx-loader');
    var leadsOverviewContainer = document.querySelector('#idx_dashboard_widget .leads-overview');
    var listingsOverviewContainer = document.querySelector('#idx_dashboard_widget .listings-overview');

    function getTimeframe(){
        var value = timeframe.checked;
        if(value === true){
            return 'month';
        } else {
            return 'day';
        }
    }

    function leadsChart(){
        //show loader
        loader.style.display = 'block';
        listingsOverviewContainer.style.display = 'none';
        leadsOverviewContainer.style.opacity = 0;
        //load json data
        jQuery.post(
            ajaxurl, {
                'action': 'idx_dashboard_leads',
                'timeframe' : getTimeframe()
        }).done(function(jsonData){
            //handle error
            if(jsonData === 'No Leads Returned'){
                document.querySelector('.leads-overview').innerHTML = jsonData;
            }
            var data = google.visualization.arrayToDataTable(JSON.parse(jsonData));

            var options = {
                title: 'Lead Statistics',
                hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: 0},
                //show animation
                animation: {
                    startup: true,
                    duration: 500
                }
            };

            var chart = new google.visualization.AreaChart(document.querySelector('.leads-overview'));
            chart.draw(data, options);

            leadsOverviewContainer.style.display = 'block';
            leadsOverviewContainer.style.opacity = 1;
            return loader.style.display = 'none';
        }); 
    }

    function listingsChart(){
        //show loader
        loader.style.display = 'block';
        leadsOverviewContainer.style.display = 'none';
        //load json data
        jQuery.post(
            ajaxurl, {
                'action': 'idx_dashboard_leads',
                'timeframe' : getTimeframe()
        }).done(function(jsonData){
            //handle error
            if(jsonData === 'No Leads Returned'){
                document.querySelector('.listings-overview').innerHTML = jsonData;
            }
            var data = google.visualization.arrayToDataTable(JSON.parse(jsonData));

            var options = {
                title: 'Listing Statistics',
                hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
                vAxis: {minValue: 0},
                //show animation
                animation: {
                    startup: true,
                    duration: 500
                }
            };

            var chart = new google.visualization.AreaChart(document.querySelector('.listings-overview'));
            chart.draw(data, options);

            listingsOverviewContainer.style.display = 'block';
            return loader.style.display = 'none';
        }); 
 
    }

    function getLeads(){
        google.charts.setOnLoadCallback(leadsChart);
    }

    function initialLoad(){
        //load Leads Chart
        google.charts.load('current', {'packages':['corechart']});
        listenToButtons();
        //load chart on page load
        getLeads();
    }

    function listingOverview(event){
        event.preventDefault();
        listingsOverviewButton.classList.remove('button-primary');
        listingsOverviewButton.disabled = 'disabled';
        leadsOverviewButton.disabled = false;
        leadsOverviewButton.classList.add('button-primary');
        getListings();
    }

    function leadOverview(event){
        event.preventDefault();
        leadsOverviewButton.classList.remove('button-primary');
        leadsOverviewButton.disabled = 'disabled';
        leadsOverviewButton.disabled = true;
        listingsOverviewButton.classList.add('button-primary');
        getLeads();
    }

    function listenToButtons(){
        //reload chart when timeframe is changed
        timeframe.addEventListener('change', getLeads);
        //change view when listing
        listingsOverviewButton.addEventListener('click', listingOverview);
        leadsOverviewButton.addEventListener('click', leadOverview);
    }

    initialLoad();
});
