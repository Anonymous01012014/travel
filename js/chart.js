/**
 * @author Mohanad Kaleia
 * 
 * File name: chart.js
 * Description: 
 * This file contain functions to deal with chart (fetch information using ajax) 
 * 
 * created date : 28-2-2014  
 */





/**
	 * Function name : getCountChart
	 * Description: 
	 * this function is to get count records from the database and print out the chart using ajax
	 * 
	 * created date: 28-2-2014
	 * ccreated by: Eng. Mohanad Shab Kaleia
	 * contact: ms.kaleia@gmail.com 
	 */
function getCountChart()
{	
	//read chart options
	site_id = $("#site_name").val();
	start_date = $("#startDate").val();
	end_date = $("#endDate").val();
	
	/** set chart options **/
	var options = {
                chart: {
                    renderTo: 'chart_container',
                    type: 'line',
                    marginRight: 130,
                    marginBottom: 25
                },
                title: {
                    text: 'Counter mode chart',
                    x: -20 //center
                },
                subtitle: {
                    text: '',
                    x: -20
                },
                xAxis: {
                    categories: []
                },
                yAxis: {
                    title: {
                        text: 'count'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
               
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0
                },
                
                series: []
            };

	$.ajax({
			  type: "POST",
			  url: "http://localhost/tcms/chart/countChart",			   
			  cache: false,			  	  				  	
			  success: function(json)
						{
			  		    try
			  		    {				  		    				  		    	
			  		    			  		    				  		   
			  		 		var obj = jQuery.parseJSON(json);
			  		 		
			  		 		
			  		 		alert(obj);
			  		 		
			  		 		
			  		 		//Read chart parameters			  		 		
			  		 		//options.xAxis.categories = json[0]['data'];
			                //options.series[0] = json[1];
			                //options.series[1] = json[2];
			                //options.series[2] = json[3];
			                //chart = new Highcharts.Chart(options);		  			
			  				
			  			}
			  			catch(e) {
			  				//alert('Exception while request..');
			  			}
			  	},
			  	error: function(xhr,err){
				    alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
				    alert("responseText: "+xhr.responseText);
				}
			  });
}
