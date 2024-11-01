<?php
class AppInsights_Widgets {
	function appinsights_dashboard_widgets() {
		global $AppInsights_Config;
		
		if ($AppInsights_Config->options['appinsights_token']) {
			include_once ($AppInsights_Config->plugin_path . '/appinsights-api.php');
			global $AppInsights_API;
		} else {
			echo '<p>' . __ ( "This plugin needs an authorization:", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "Authorize Plugin", 'appinsights' ), 'secondary' ) . '</form>';
			return;
		}
		
		include_once ($AppInsights_Config->plugin_path . '/appinsights-tools.php');
	    $tools = new AppInsights_Tools();
		
	    $tools->appinsights_cleanup_timeouts();
	    
	    if (! $AppInsights_API->get_access_token()) {
	    	echo '<p>' . __ ( "Something went wrong.", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "Application insights settings", 'appinsights' ), 'secondary' ) . '</form>';
	    	return;
	    }
	    
	    if (current_user_can ( 'manage_options' )) {
	    	if (isset( $_REQUEST['appinsights_component_select'] )) {
	    		$AppInsights_Config->options['appinsights_component'] = $_REQUEST['appinsights_component_select'];
	    	}
	    	
	    	$components = $AppInsights_Config->options['appinsights_component_list'];
	    	
	    	$component_switch = '';
	    	
	    	if (is_array ( $components )) {
	    		if (! $AppInsights_Config->options ['appinsights_component']) {
	    			echo '<p>' . __ ( "Please select an application insights resource:", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "Select component", 'appinsights' ), 'secondary' ) . '</form>';
	    			return;
	    		}
	    		
	    		$component_switch .= '<select id="appinsights_component_select" name="appinsights_component_select" onchange="this.form.submit()">';
	    		foreach ( $components as $component ) {
	    			if (! $AppInsights_Config->options ['appinsights_component']) {
	    				$AppInsights_Config->options ['appinsights_component'] = $component [0];
	    			}
	    			
	    			if (isset ( $component [1] )) {
	    			    $component_switch .= '<option value="' . esc_attr ( $component [0] ) .
	    			        '" ' . selected($component [0], $AppInsights_Config->options ['appinsights_component'], false) .
	    			        ' >' . esc_attr($component [1]) . '</option>';
	    			}
	    		}
	    		$component_switch .= "</select>";
	    	} else {
	    		echo '<p>' . __ ( "Something went wrong while retrieving components list.", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "More details", 'appinsights' ), 'secondary' ) . '</form>';
	    		return;
	    	}
	    }
	    
	    $AppInsights_Config->set_plugin_options ();
	    ?>
<form id="appinsights" method="POST">
        <?php 
        if (current_user_can ( 'manage_options' )) {
            echo $component_switch;
            $component_id = $AppInsights_Config->options ['appinsights_component'];
        }
        
        if (isset ( $_REQUEST ['query'] )) {
            $query = $_REQUEST ['query'];
            $AppInsights_Config->options ['appinsights_default_metric'] = $query;
            $AppInsights_Config->set_plugin_options ();
        } else {
			$query = isset ( $AppInsights_Config->options ['appinsights_default_metric'] ) ? $AppInsights_Config->options ['appinsights_default_metric'] : 'context.session.id.hash';
		}
		
		if (isset ( $_REQUEST ['period'] )) {
			$period = $_REQUEST ['period'];
			$AppInsights_Config->options ['appinsights_default_dimension'] = $period;
			$AppInsights_Config->set_plugin_options ();
		} else {
			$period = isset ( $AppInsights_Config->options ['appinsights_default_dimension'] ) ? $AppInsights_Config->options ['appinsights_default_dimension'] : '30daysAgo';
		}
        ?>
<select id="appinsights_period" name="period" onchange="this.form.submit()">
	<option value="today" <?php selected ( "today", $period, true ); ?>><?php _e("Today",'appinsights'); ?></option>
	<option value="yesterday" <?php selected ( "yesterday", $period, true ); ?>><?php _e("Yesterday",'appinsights'); ?></option>
	<option value="7daysAgo" <?php selected ( "7daysAgo", $period, true ); ?>><?php _e("Last 7 Days",'appinsights'); ?></option>
	<option value="14daysAgo" <?php selected ( "14daysAgo", $period, true ); ?>><?php _e("Last 14 Days",'appinsights'); ?></option>
	<option value="30daysAgo" <?php selected ( "30daysAgo", $period, true ); ?>><?php _e("Last 30 Days",'appinsights'); ?></option>
</select>
<select id="appinsights_query" name="query" onchange="this.form.submit()">
	<option value="context.session.id.hash" <?php selected ( "context.session.id.hash", $query, true ); ?>><?php _e("Sessions",'appinsights'); ?></option>
	<option value="context.user.anonId.hash" <?php selected ( "context.user.anonId.hash", $query, true ); ?>><?php _e("Users",'appinsights'); ?></option>
	<option value="view.count" <?php selected ( "view.count", $query, true ); ?>><?php _e("Page Views",'appinsights'); ?></option>
</select>
</form>
        <?php 
	    switch ($period) {
				
		    case 'today' :
				$from = date(DATE_ISO8601, strtotime('today'));
				$to = date(DATE_ISO8601, strtotime('tomorrow'));
				break;
				
			case 'yesterday' :
				$from = date(DATE_ISO8601, strtotime('yesterday'));
				$to = date(DATE_ISO8601, strtotime('today'));
				break;
				
			case '7daysAgo' :
				$from = date(DATE_ISO8601, strtotime('7daysAgo'));
				$to = date(DATE_ISO8601, strtotime('today'));
				break;
				
			case '14daysAgo' :
				$from = date(DATE_ISO8601, strtotime('14daysAgo'));
				$to = date(DATE_ISO8601, strtotime('today'));
				break;
				
			case '30daysAgo' :
				$from = date(DATE_ISO8601, strtotime('30daysAgo'));
				$to = date(DATE_ISO8601, strtotime('today'));
				break;
				
			default :
				$from = date(DATE_ISO8601, strtotime('30daysAgo'));
				$to = date(DATE_ISO8601, strtotime('today'));
				break;
		}
		
		switch ($query) {
				
			case 'context.session.id.hash' :
				$title = __ ( "Sessions", 'appinsights' );
				break;
					
			case 'view.count' :
				$title = __ ( "Page Views", 'appinsights' );
				break;
					
			case 'context.user.anonId.hash' :
				$title = __ ( "Users", 'appinsights' );
				break;
					
			default :
				$title = __ ( "Sessions", 'appinsights' );
		}
		
		$appinsights_statsdata = $AppInsights_API->appinsights_main_charts( $component_id, $period, $from, $to, $query );
		
		if (! $appinsights_statsdata) {
			echo '<p>' . __ ( "No stats available.", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "Change settings", 'appinsights' ), 'secondary' ) . '</form>';
			return;
		}
		
		$appinsights_bottom_stats = $AppInsights_API->appinsights_bottom_stats( $component_id, $period, $from, $to );
		
		if (! $appinsights_bottom_stats) {
			echo '<p>' . __ ( "No stats available.", 'appinsights' ) . '</p><form action="' . menu_page_url ( 'appinsights', false ) . '" method="POST">' . get_submit_button ( __ ( "Change settings", 'appinsights' ), 'secondary' ) . '</form>';
			return;
		}
        ?>
<div id="appinsights_statsdata" class='with-transitions'>
    <svg></svg>
</div>
<script type="text/javascript">
   var appinsights_data = [
       {
           key: "<?php echo $title; ?>",
           values: [ <?php echo $appinsights_statsdata; ?> ],
           color: "#2ca02c"
       }
    ];
    nv.addGraph(function () {
        var chart = nv.models.lineChart()
            .margin({left: 30, right: 30})
            .forceY([0, 10])
            .x(function(d) { return d[0] })
            .y(function(d) { return d[1] })
            .useInteractiveGuideline(true);
        chart.xAxis
            .tickValues(d3.time.weeks.utc(appinsights_data[0].values[0][0], appinsights_data[0].values[appinsights_data[0].values.length - 1][0], 1))
            .axisLabel("Date")
            .tickFormat(function (d) {
                return d3.time.format.utc("%b %d")(new Date(d))
            });
        chart.yAxis
            .axisLabel("Sessions")
            .tickFormat(d3.format(","));
        
        d3.select("#appinsights_statsdata svg")
            .attr('width', 600)
            .attr('height', 375)
            .datum(appinsights_data)
            .call(chart);

        nv.utils.windowResize(function () {
            d3.select("#appinsights_statsdata svg").call(chart)
        });
        
        return chart;
    });
</script>
<div id="details_div">
    <table class="aitable" cellpadding="4">
		<tr>
			<td width="24%"><?php _e( "Users:", 'appinsights' );?></td>
			<td width="12%" class="aivalue"><a
				href="?query=context.user.anonId.hash&period=<?php echo $period; ?>" class="aitable"><?php echo ( int ) $appinsights_bottom_stats ['users'];?></td>
			<td width="24%"><?php _e( "Sessions:", 'appinsights' );?></td>
			<td width="12%" class="aivalue"><a
				href="?query=context.session.id.hash&period=<?php echo $period; ?>" class="aitable"><?php echo ( int ) $appinsights_bottom_stats ['sessions'];?></a></td>
			<td width="24%"><?php _e( "Requests:", 'appinsights' );?></td>
			<td width="12%" class="aivalue"><a
				href="?query=request.count&period=<?php echo $period; ?>"
				class="aitable"><?php echo ( int ) $appinsights_bottom_stats ['requests']; ?></a></td>
		</tr>
		<tr>
			<td><?php _e( "Page Views:", 'appinsights' );?></td>
			<td class="aivalue"><a
				href="?query=view.count&period=<?php echo $period; ?>"
				class="aitable"><?php echo ( int ) $appinsights_bottom_stats ['views']; ?></a></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
	    <?php 
	}
}