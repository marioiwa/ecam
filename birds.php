<!--birds eye view:quick assessment view-->
<!doctype html><html><head>
	<?php include'imports.php'?>
	<style>
		table#inputs input {width:70px;transition:background 1s;border:1px solid #ccc}
		table#inputs input.edited {background:lightgreen;}
		table#inputs tr.hidden {display:none}
		table#inputs tr[indic]{text-align:center;color:#999;background:#eee}
		table#inputs th{text-align:left}
		table#inputs td{border-left:none;border-right:none}
	</style>
	<script>
		function init()
		{
			Exceptions.apply();
			BEV.showActive();
			BEV.updateDefaults();
			updateResult();
			drawCharts();
		}

		function drawCharts()
		{
			Graphs.graph1(false,'graph1');
			Graphs.graph2(false,'graph2');
			Graphs.graph3a(false,'graph3a');
			Graphs.graph3b(false,'graph3b');
			Graphs.graph3c(false,'graph3c');
			Graphs.graph3d(false,'graph3d');
		}

		var BEV={}; //'Birds Eye View' namespace

		//Generic f for updating internal values
		BEV.update=function(obj,field,newValue)
		{
			if(obj[field]===undefined)
			{
				alert('field '+field+' undefined');
				return;
			}

			//newValue may be a string from input.value, it should be a float
			newValue=parseFloat(newValue);

			//update
			obj[field]=newValue;
		}

		//Specific behaviours for each formula when user inputs data
		BEV.updateField=function(input)
		{
			//get info from the input element
			var field = input.id;
			var value = parseFloat(input.value);

			//if value is not a number, set to zero
			if(isNaN(value))value=0;

			var days=Global.General.Days();
			switch(field)
			{
				/** x per month -> x */
				case 'ws_nrg_cons':
				case 'ws_nrg_cost':
				case 'ws_run_cost':
				case 'ww_nrg_cons':
				case 'ww_nrg_cost':
				case 'ww_run_cost':
					value = value*days/30; break;

				/** trips/week -> trips */
				case 'ww_num_trip':
					value = value*days/7; break;

				/** L per month -> m3 */
				case 'ws_vol_fuel':
				case 'ww_vol_fuel':
					value = value*days/30/1000; break;

				/** m3 per year -> m3 */
				case 'ws_vol_auth':
					value = value*days/365; break;

				/** m3 per day -> m3 */
				case 'ww_vol_wwtr':
					value = value*days; break;

				/** km -> m */
				case 'ww_dist_dis':
					value = value*1000; break;

				/** mg/L -> kg */
				case 'ww_n2o_effl':
					value = value*Global.Waste.ww_vol_wwtr/1000; break;

				default:break;
			}
			//get L1 name: "Water" or "Waste"
			var L1 = field.search("ws")==0 ? "Water" : "Waste";
			//update
			this.update(Global[L1],field,value);
			//add a color to the field
			input.classList.add('edited');
			init();
		}

		//Refresh default values from the table
		BEV.updateDefaults=function()
		{
			var inputs = document.querySelectorAll('table#inputs input');
			for(var i=0; i<inputs.length; i++)
			{
				var input = inputs[i];
				var field = input.id; 

				//set the longer description in the input <td> element
				input.parentNode.parentNode.childNodes[0].title=translate(field+'_expla');

				var L1 = field.search("ws")==0 ? "Water" : "Waste";

				//the value we are going to put in the input
				var value = Global[L1][field];

				var days=Global.General.Days();
				//modify value according to each case
				switch(field)
				{
					/** x per month -> x */
					case 'ws_nrg_cons':
					case 'ws_nrg_cost':
					case 'ws_run_cost':
					case 'ww_nrg_cons':
					case 'ww_nrg_cost':
					case 'ww_run_cost':
						value = value/days*30; break;

					/** trips/week -> trips */
					case 'ww_num_trip':
						value = value/days*7; break;

					/** L per month -> m3 */
					case 'ws_vol_fuel':
					case 'ww_vol_fuel':
						value = value/days*30*1000; break;

					/** m3 per year -> m3 */
					case 'ws_vol_auth':
						value = value*365/days; break;

					/** m3 per day -> m3 */
					case 'ww_vol_wwtr':
						value = value/days; break;

					/** km -> m */
					case 'ww_dist_dis':
						value = value/1000; break;

					/** mg/L -> kg */
					case 'ww_n2o_effl':
						value = 1000*value/Global.Waste.ww_vol_wwtr||0; break;

					default:break;
				}
				//set the value
				input.value=format(value);
			}
		}

		BEV.showActive=function()
		{
			['water','waste'].forEach(function(stage)
			{
				if(Global.Configuration['Active Stages'][stage]==1)
				{
					//show all rows with stage=stage
					var rows = document.querySelectorAll('table#inputs tr[stage='+stage+']');
					for(var i=0; i<rows.length; rows[i++].classList.remove('hidden')){}
				}
				else //show "Stage not active"
				{
					document.querySelector('table#inputs tr[indic='+stage+']').classList.remove('hidden');
				}
			});
		}

		function makeInactive(input)
		{
			input.setAttribute('disabled',true)
			input.style.background="#eee"
			var tr = input.parentNode.parentNode;
			tr.style.background="#eee"
			tr.title="<?php write('#Inactive')?>"
			tr.style.color="#888"
		}
	</script>
</head><body><center>
<!--sidebar--><?php include'sidebar.php'?>
<!--NAVBAR--><?php include"navbar.php"?>
<!--linear--><?php include'linear.php'?>
<!--TITLE--><h1><?php write('#birds_quick_assessment_of')?> [<script>document.write(Global.General.Name)</script>]</h1>
<div style="padding:0;margin-bottom:1em;background:#d7bfaf;height:5px"></div>
</center>

<!--inputs table-->
<div class=inline style="margin-left:10px;width:30%;">
	<!--description-->
	<div style="color:#666;font-size:16px;margin:0.5em 0 0.5em 0">INPUTS - <?php write('#birds_enter_typical')?></div>
	<!--assessment period-->
	<div><a href=variable.php?id=Days>        <?php write('#assessment_period')?></a>: <script>document.write(Global.General.Days())</script> <?php write('#days')?></div> 
	<!--conversion factor-->
	<div><a href=variable.php?id=conv_kwh_co2><?php write('#conversion_factor')?></a>: 
		<script>
			(function(){
				var c = Global.General.conv_kwh_co2;
				var str = c==0 ? "<span style='padding:0 0.5em 0 0.5em;background:red;cursor:help' title='<?php write('#birds_warning_conv_factor')?>'>"+format(c)+" &#9888;</span>" : format(c); 
				document.write(str)
			})();
		</script> kg CO<sub>2</sub>/kWh</div> 
	<!--inputs-->
	<table id=inputs>
		<tr><th colspan=3>
			<img src=img/water.png width=25 style="line-height:4em;vertical-align:middle"> <?php write('#Water')?>
			<tr stage=water class=hidden><td><?php write('#ws_resi_pop_descr')?> <td><input id='ws_resi_pop' onchange="BEV.updateField(this)"> <td><?php write('#birds_people')?>
			<tr stage=water class=hidden><td><?php write('#ws_serv_pop_descr')?> <td><input id='ws_serv_pop' onchange="BEV.updateField(this)"> <td><?php write('#birds_people')?>
			<tr stage=water class=hidden><td><?php write('#birds_ws_vol_auth')?> <td><input id='ws_vol_auth' onchange="BEV.updateField(this)"> <td>m3/<?php write('#birds_year')?>
			<tr stage=water class=hidden><td><?php write('#birds_ws_nrg_cons')?> <td><input id='ws_nrg_cons' onchange="BEV.updateField(this)"> <td>kWh/<?php write('#birds_month')?>
			<tr stage=water class=hidden><td><?php write('#birds_ws_nrg_cost')?> <td><input id='ws_nrg_cost' onchange="BEV.updateField(this)"> <td><script>document.write(Global.General.Currency)</script>/<?php write('#birds_month')?>
			<tr stage=water class=hidden><td><?php write('#birds_ws_run_cost')?> <td><input id='ws_run_cost' onchange="BEV.updateField(this)"> <td><script>document.write(Global.General.Currency)</script>/<?php write('#birds_month')?>
			<tr stage=water class=hidden><td><?php write('#birds_ws_vol_fuel')?> <td><input id='ws_vol_fuel' onchange="BEV.updateField(this)"> <td>L/<?php write('#birds_month')?>
			<script>
				//fuel depends on question #engines_in_water
				(function(){
					if(Global.Configuration["Yes/No"]['engines_in_water']==0)
					{
						var input = document.querySelector('#ws_vol_fuel');
						makeInactive(input);
					}
				})();
			</script>
			<tr stage=water class=hidden><td><?php write('#ws_non_revw_descr')?> <td><input id='ws_non_revw' onchange="BEV.updateField(this)"> <td>%
			<tr indic=water class=hidden><td colspan=3><?php write('#birds_stage_not_active')?>
		<tr><th colspan=3 style=background:#d71d24>
			<img src=img/waste.png width=25 style="line-height:4em;vertical-align:middle"> <?php write('#Waste')?>
			<tr stage=waste class=hidden><td><?php write('#ww_resi_pop_descr')?><td><input id='ww_resi_pop' onchange="BEV.updateField(this)"> <td><?php write('#birds_people')?>
			<tr stage=waste class=hidden><td><?php write('#ww_conn_pop_descr')?><td><input id='ww_conn_pop' onchange="BEV.updateField(this)"> <td><?php write('#birds_people')?>
			<tr stage=waste class=hidden>
				<td><?php write('#ww_serv_pop_descr')?>
					<span title="<?php write('#birds_ww_serv_pop_note')?>" style="color:orange;cursor:help">(<?php write('#birds_note')?>)</span>
				<td><input id='ww_serv_pop' onchange="BEV.updateField(this)"> <td><?php write('#birds_people')?>
			<tr stage=waste class=hidden><td><?php write('#birds_ww_vol_wwtr')?> <td><input id='ww_vol_wwtr' onchange="BEV.updateField(this)"> <td>m<sup>3</sup>/day
			<tr stage=waste class=hidden><td><?php write('#birds_ww_nrg_cons')?> <td><input id='ww_nrg_cons' onchange="BEV.updateField(this)"> <td>kWh/<?php write('#birds_month')?>
			<tr stage=waste class=hidden><td><?php write('#birds_ww_nrg_cost')?> <td><input id='ww_nrg_cost' onchange="BEV.updateField(this)"> <td><script>document.write(Global.General.Currency)</script>/<?php write('#birds_month')?>
			<tr stage=waste class=hidden><td><?php write('#birds_ww_run_cost')?> <td><input id='ww_run_cost' onchange="BEV.updateField(this)"> <td><script>document.write(Global.General.Currency)</script>/<?php write('#birds_month')?>
			<tr stage=waste class=hidden><td><?php write('#birds_ww_vol_fuel')?><td><input id='ww_vol_fuel' onchange="BEV.updateField(this)"> <td>L/<?php write('#birds_month')?>
			<tr stage=waste class=hidden><td><?php write('#birds_ww_num_trip')?> <td><input id='ww_num_trip' onchange="BEV.updateField(this)"> <td><?php write('#birds_trips_week')?>
			<tr stage=waste class=hidden><td><?php write('#ww_dist_dis_descr')?> <td><input id='ww_dist_dis' onchange="BEV.updateField(this)"> <td>km
			<script>
				//fuel depends on question #engines_in_waste
				//trips and distance depend on question #truck_transport_waste
				(function(){
					if(Global.Configuration["Yes/No"]['engines_in_waste']==0)
					{
						var input = document.querySelector('#ww_vol_fuel');
						makeInactive(input);
					}
					if(Global.Configuration["Yes/No"]['truck_transport_waste']==0)
					{
						['#ww_num_trip','#ww_dist_dis'].forEach(function(id)
						{
							var input = document.querySelector(id);
							makeInactive(input);
						})
					}
				})();
			</script>
			<tr stage=waste class=hidden>
				<td><?php write('#birds_ww_n2o_effl')?> 
					<span title="<?php write('#birds_ww_n2o_effl_note')?>" style=color:orange;cursor:help>(<?php write('#birds_note')?>)</span>
				<td><input id='ww_n2o_effl' onchange="BEV.updateField(this)"> <td>mg/L
			<tr stage=waste class=hidden><td><?php write('#birds_ww_prot_con')?><td><input id='ww_prot_con' onchange="BEV.updateField(this)"> <td>kg/<?php write('#birds_people')?>/<?php write('#birds_year')?>
			<tr indic=waste class=hidden><td colspan=3><?php write('#birds_stage_not_active')?>
	</table>
</div>

<!--graphs-->
<div id=graphs class=inline style="width:65%;">
	<style> 
		#graphs table{margin:auto}
		#graphs button{margin:0.5em;margin-top:0;font-size:10px} 
		#graphs div{text-align:center} 
		#graphs div[id^=graph] {border:1px solid #ccc;}
		#graphs div div {padding:0}
		#graphs div.options {text-align:center;padding:1em}
	</style>

	<div style=margin-top:2px>
		<div id=graph1 class=inline style=width:49%><?php write('#loading')?></div>
		<div id=graph2 class=inline style=width:49%><?php write('#loading')?></div>
	</div>
	<div style=margin-top:2px>
		<div id=graph3a class=inline style=width:49%><?php write('#loading')?></div>
		<div id=graph3b class=inline style=width:49%><?php write('#loading')?></div>
	</div>
	<div style=margin-top:2px>
		<div id=graph3c class=inline style=width:49%><?php write('#loading')?></div>
		<div id=graph3d class=inline style=width:49%><?php write('#loading')?></div>
	</div>
	<script>
		google.charts.load('current',{'packages':['corechart']});
		google.charts.setOnLoadCallback(init)
	</script>
</div>

<!--PREV & NEXT BUTTONS-->
<div style=margin-top:4em;text-align:center> 
	<script>
		//find first available stage to start entering data
		function nextPage()
		{
			event.stopPropagation();
			//default location to go
			var location = "edit.php?level=Water";
			if(Global.Configuration['Active Stages'].water==0 && Global.Configuration['Active Stages'].waste==0)
			{
				alert("<?php write('#configuration_active_stages_error')?>");
				return;
			}
			if(Global.Configuration['Active Stages'].water==0)
			{
				location = "edit.php?level=Waste";
			}
			window.location=location;
		}
	</script>
	<button class="button prev" onclick="event.stopPropagation();window.location='configuration.php'"><?php write('#previous')?></button><!--
	--><button class="button next" onclick=nextPage()><?php write('#next')?></button>
</div>

<!--FOOTER--><?php include'footer.php'?>
<!--CURRENT JSON--><?php include'currentJSON.php'?>
