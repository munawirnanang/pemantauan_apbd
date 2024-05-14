!function(i){"use strict";var r=function(){};r.prototype.respChart=function(r,a,o,e){Chart.defaults.global.defaultFontColor="#6c7897",Chart.defaults.scale.gridLines.color="rgba(108, 120, 151, 0.1)";var t=r.get(0).getContext("2d"),d=i(r).parent();function n(){r.attr("width",i(d).width());switch(a){case"Line":new Chart(t,{type:"line",data:o,options:e});break;case"Doughnut":new Chart(t,{type:"doughnut",data:o,options:e});break;case"Pie":new Chart(t,{type:"pie",data:o,options:e});break;case"Bar":new Chart(t,{type:"bar",data:o,options:e});break;case"Radar":new Chart(t,{type:"radar",data:o,options:e});break;case"PolarArea":new Chart(t,{type:"polarArea",data:o,options:e})}}i(window).resize(n),n()},r.prototype.init=function(){var r={legend:{display:!1}};this.respChart(i("#lineChart"),"Line",{labels:["January","February","March","April","May","June","July"],datasets:[{backgroundColor:"rgba(51, 184, 108, 0.75)",borderColor:"rgba(51, 184, 108, 0.75)",pointColor:"#fff",pointStrokeColor:"rgba(51, 184, 108, 0.75)",data:[33,52,63,92,50,53,46]},{backgroundColor:"#dcdcdc",borderColor:"#dcdcdc",pointColor:"#fff",pointStrokeColor:"#dcdcdc",data:[15,25,40,35,32,9,33]}]},r);this.respChart(i("#doughnut"),"Doughnut",{labels:["Jan","Feb","Mar","Apr"],datasets:[{data:[80,50,80,50],backgroundColor:["#4998fa","#ececec","#33b86c","#fab249"]}]});this.respChart(i("#pie"),"Pie",{labels:["Series A","Series B","Series C"],datasets:[{data:[40,80,70],backgroundColor:["#dcdcdc","#33b86c","#999999"]}]});r={legend:{display:!1}};this.respChart(i("#bar"),"Bar",{labels:["January","February","March","April","May","June","July"],datasets:[{backgroundColor:"#33b86c",borderColor:"#33b86c",data:[65,59,90,81,56,55,40]},{backgroundColor:"#dcdcdc",borderColor:"#dcdcdc",data:[28,48,40,19,96,27,100]}]},r);this.respChart(i("#radar"),"Radar",{labels:["Eating","Drinking","Sleeping","Designing","Coding","Partying","Running"],datasets:[{label:"Desktops",backgroundColor:"rgba(51, 184, 108, 0.5)",borderColor:"rgba(51, 184, 108, 0.75)",pointBackgroundColor:"rgba(51, 184, 108, 1)",pointBorderColor:"#fff",pointHoverBackgroundColor:"#fff",data:[65,59,90,81,56,55,40]},{label:"Tablets",backgroundColor:"rgba(220, 220, 220, 0.5)",borderColor:"rgba(220, 220, 220, 0.75)",pointBackgroundColor:"rgba(220, 220, 220,1)",pointBorderColor:"#fff",pointHoverBackgroundColor:"#fff",pointHoverBorderColor:"rgba(255,99,132,1)",data:[28,48,40,19,96,27,100]}]});this.respChart(i("#polarArea"),"PolarArea",{datasets:[{data:[30,90,24,58,82,8],backgroundColor:["#60b1cc","#bac3d2","#4697ce","#6c85bd","#33b86c","#1ca8dd"]}],labels:["Series 1","Series 2","Series 3","Series 4","Series 5","Series 6"]})},i.ChartJs=new r,i.ChartJs.Constructor=r}(window.jQuery),function(r){"use strict";window.jQuery.ChartJs.init()}();