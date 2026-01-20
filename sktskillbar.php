<?php
/**
* Plugin Name: SKT Skill Bar
* Description: Skill Bar plugin to show skill bar or progress bar or circular bar or vertical bar or half circular bars using fancy animated jquery.
* Plugin URI:  https://www.sktthemes.org
* Author:      SKT Themes
* Author URI:  https://www.sktthemes.org
* Text Domain: skt-skill-bar
* Version:     2.6
* License: 	   GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
define('SB_VER','2.6');
add_action('wp_print_scripts', 'sbar_register_scripts');
add_action('wp_print_styles', 'sbar_register_styles');
define( 'SKT_sbar_URI', plugins_url( '', __FILE__ ) );

function sbar_register_scripts() {
	if ( !is_admin() ) {
		wp_enqueue_script('jquery');
		
		wp_register_script('bar_script', plugins_url('skill_bar/bar/jquery.appear.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('bar_script');

		wp_register_script('circle_script', plugins_url('skill_bar/circle/jquery.easy-pie-chart.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('circle_script');

		wp_register_script('circle_custom_script', plugins_url('skill_bar/circle/custom.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('circle_custom_script');
		
		wp_register_script('gage_script', plugins_url('skill_bar/gage/justgage.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('gage_script');

		wp_register_script('gage_raphael_script', plugins_url('skill_bar/gage/raphael-2.1.4.min.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('gage_raphael_script');
		wp_register_script('chart-js-script', plugins_url('skill_bar/js/Chart.js', __FILE__),'',SB_VER,false);
		wp_enqueue_script('chart-js-script');
		
		wp_register_script( 'chart.min.js-script', SKT_sbar_URI . '/skill_bar/js/chart.min.js', array( 'jquery' ),SB_VER,false);
		wp_enqueue_script('chart.min.js-script');
	}
}

function sbar_register_styles() {
	wp_register_style('bar_styles', plugins_url('skill_bar/bar/sbar.css', __FILE__),'',SB_VER,false);	// register
	wp_enqueue_style('bar_styles');	// enqueue

	wp_register_style('circle_styles', plugins_url('skill_bar/circle/jquery.easy-pie-chart.css', __FILE__),'',SB_VER,false);	// register
	wp_enqueue_style('circle_styles');	// enqueue

	wp_register_style('skt_verticleline_css', plugins_url('skill_bar/css/custom.css', __FILE__),'',SB_VER,false);	// register
	wp_enqueue_style('skt_verticleline_css');// enqueue
}

//	[skillwrapper type="circle" track_color="#dddddd" chart_color="#333333" chart_size="150"][/skillwrapper]
function skillwrapper_func( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'type' => 'bar',
		'track_color' => '#dddddd',
		'chart_color' => '#333333',
		'chart_size' => '150',
		'align' => 'center',
		'chart_fontsize' =>'',
		'chart_headingfontsize' =>'',
		'bar_titlefontsize' =>'',
		'bar_titlecolor' =>'',
		'bar_percentfontszie' => '',
		'bar_percentcolor' => '',
		'lineid' => '1',
	), $atts ) );
	switch ( $type ){

		case 'bar':
			$wrapCode = '<div id="skillbar_straight" style="padding:10px 0;">'.str_replace('<br />', "\n", do_shortcode($content))."\n".'<div style="clear:both;"></div>'."\n".'</div>'."\n".'<div style="clear:both; height:10px; overflow:hidden;"></div>'."\n";
			$wrapCode .= '<style type="text/css">.skillbar-title{font-size:'.esc_attr($bar_titlefontsize).'px;color:'.esc_attr($bar_titlecolor).';}.skill-bar-percent{font-size:'.esc_attr($bar_percentfontszie).'px;color:'.esc_attr($bar_percentcolor).';}</style>';
			$wrapCode .= '<script>
				function sbar(){
					jQuery(".skillbar").each(function(){
						jQuery(this).find(".skillbar-bar").animate({
							width:jQuery(this).attr("data-percent")
						},3000);
					});	
				}
				if ( jQuery("#skillbar_straight").next().is(":appeared") ){
					sbar();
				} else {
					jQuery( window ).scroll(function() {
						if ( jQuery("#skillbar_straight").next().is(":appeared") ){
							sbar();
						}
					});
				}
				</script>';
			break;

		case 'gage':
			static $gage_counter = 0;
			$gage_counter++;

			$wrapCode = '';
			$content  = wp_strip_all_tags($content);
			$start    = strpos($content, '[');
			$end      = strrpos($content, '"]');
			$len      = strlen($content);
			$diff     = $end - $len;
			$content  = substr($content, $start, $diff);
			$content  = str_replace(
				array('[skill ', '"]', '" ]', '" ', '="'),
				array('', '', '', ':', '='),
				$content
			);

			$cntStrAr = explode("\n", $content);
			$numAr    = array();
			foreach ($cntStrAr as $cntk => $cntv) {
				if (trim($cntv) != '') {
					$cnStr = str_replace(
						array('bar_foreground=', 'bar_background=', 'percent=', 'title='),
						array('', '', '', ''),
						trim($cntv)
					);
					$numAr[] = explode(':', $cnStr);
				}
			}

			// unique wrapper id
			$gage_wrapper_id = 'gage_chart_' . $gage_counter;

			$wrapCode .= '<style type="text/css">';
			$cssVar = '';
			foreach ($numAr as $n => $b) {
				$n++;
				$cssVar .= (count($numAr) == $n)
					? '#' . $gage_wrapper_id . ' #g' . $n . '_' . $gage_counter
					: '#' . $gage_wrapper_id . ' #g' . $n . '_' . $gage_counter . ', ';
			}
			$wrapCode .= $cssVar . '{ width:200px; height:160px; display:inline-block; margin:0.5em; }
				#' . $gage_wrapper_id . '{ text-align:' . esc_attr($align) . '; }';
			$wrapCode .= '</style>';

			$wrapCode .= '<script>';
			$sbIds = '';
			foreach ($numAr as $n => $b) {
				$n++;
				$sbIds .= (count($numAr) == $n)
					? 'g' . $n . '_' . $gage_counter
					: 'g' . $n . '_' . $gage_counter . ', ';
			}
			$wrapCode .= 'var ' . $sbIds . ';' . "\n";

			$wrapCode .= 'function gager_' . $gage_counter . '(){';
			foreach ($numAr as $n => $v) {
				$n++;
				$value      = isset($v[0]) ? floatval($v[0]) : 0;
				$title      = isset($v[1]) ? sanitize_text_field($v[1]) : '';
				$color      = isset($v[2]) ? sanitize_text_field($v[2]) : '#000';
				$gaugeColor = isset($v[3]) ? sanitize_text_field($v[3]) : '#eee';

				$wrapCode .= 'var g' . esc_attr($n) . '_' . esc_attr($gage_counter) . ' = new JustGage({
					id: "g' . esc_attr($n) . '_' . esc_attr($gage_counter) . '",
					value: ' . esc_attr($value) . ',
					title: "' . esc_attr($title) . '",
					valueFontColor: "' . esc_attr($color) . '",
					levelColors: ["' . esc_attr($color) . '"],
					titleFontColor: "' . esc_attr($color) . '",
					labelFontColor: "' . esc_attr($color) . '",
					gaugeColor: "' . esc_attr($gaugeColor) . '",
					min: 0,
					max: 100,
					label: "%",
					levelColorsGradient: false,
					showMinMax: "hide",
					shadowOpacity: "0.2",
					shadowSize: "5",
					startAnimationType: "easein"
				});' . "\n";
			}
			$wrapCode .= '};' . "\n";

			$wrapCode .= 'jQuery(document).ready(function(){
				if ( jQuery("#' . $gage_wrapper_id . '").next().is(":appeared") ){
					if ( ! jQuery("#' . $gage_wrapper_id . '").hasClass("gc_active") ) {
						gager_' . $gage_counter . '();
						jQuery("#' . $gage_wrapper_id . '").addClass("gc_active");
					}
				} else {
					jQuery(window).scroll(function(){
						if ( jQuery("#' . $gage_wrapper_id . '").next().is(":appeared") ){
							if ( ! jQuery("#' . $gage_wrapper_id . '").hasClass("gc_active") ) {
								gager_' . $gage_counter . '();
								jQuery("#' . $gage_wrapper_id . '").addClass("gc_active");
							}
						}
					});
				}
			});';
			$wrapCode .= '</script>';

			$wrapCode .= '<div id="' . $gage_wrapper_id . '">';
			foreach ($numAr as $n => $b) {
				$n++;
				$wrapCode .= '<div id="g' . esc_attr($n) . '_' . esc_attr($gage_counter) . '"></div>';
			}
			$wrapCode .= '</div>';
			$wrapCode .= '<div style="clear:both; height:10px; overflow:hidden;"></div>';
		break;

		case 'circle':
			$wrapCode = '';
			$content = wp_strip_all_tags($content);
			$start = strpos($content, '[');
			$end = strrpos($content, '"]');
			$len =  strlen($content);
			$diff = $end - $len;
			$content = substr( $content, $start, $diff);
			$content = str_replace(array('[skill ', '"]', '" ]', '" ', '="' ), array('', '', '', ':', '='), $content);
			$cntStrAr = explode( "\n", $content );
		
			$numAr = array();
			foreach($cntStrAr as $cntk => $cntv){
				if($cntv != ''){
					$cnStr = str_replace( array( 'percent=', 'title='), array('',''), trim($cntv) );
					$numAr[] = explode(':', $cnStr);
				}
			}
			$cssVar = '';
			foreach($numAr as $n => $b){
				$n++; 
				$cssVar .= (count($numAr) == $n) ? '#g'.$n : '#g'.$n.', ';  
			}
			$sbIds = '';
			foreach($numAr as $n => $b){
				$n++;
				$sbIds .= (count($numAr) == $n) ? 'g'.$n : 'g'.$n.', ';  
			}
		
			$rgb_track_color = sbar_hex2rgb ( $track_color );
		
			$wrapCode .= '<style>.sktb_pie_graph {
			  --w:'.$chart_size.'px;
			  width: 200px;
			  aspect-ratio: 1;
			  position: relative;
			  display: inline-grid;
			  place-content: center;
			  margin: 5px 1em;
			  
			  font-weight: bold;
			  font-family: sans-serif;
			  border-radius: 50%;
		   }
		   .sktb_pie_graph:before {
			  content: "";
			  position: absolute;
			  border-radius: 50%;
			  inset: 0;
			  background: conic-gradient(var(--c) calc(var(--p)*1%),'.esc_attr($chart_color).' 0);
			  -webkit-mask:radial-gradient(farthest-side,#0000 calc(99% - var(--b)),#000 calc(100% - var(--b)));
					 mask:radial-gradient(farthest-side,#0000 calc(99% - var(--b)),#000 calc(100% - var(--b)));
		   }</style>';

				$wrapCode .= '<div class="skt_skill_flex-wrapper" style="font-size:12px;text-align:'.esc_attr($align).'">';
		        foreach($numAr as $n => $v){
		         	$wrapCode .= '<div class="sktb_pie_graph">
                         <div class="sktb_pie_graph" style="--p:'.esc_attr($v[0]).';--b:30px;--c:'.esc_attr($track_color).';font-size: '.esc_attr($chart_fontsize).'px;color:'.esc_attr($chart_color).'""><span style="color:'.esc_attr($track_color).'">'.esc_attr($v[0]).'%</span></div>
                         <span style="font-size: '.esc_attr($chart_headingfontsize).'px;text-align:center;color:'.esc_attr($track_color).'">'.esc_attr($v[1]).'</span>
                    </div>';
		        }
				$wrapCode .= '</div>';
				break;

			case 'skt_verticalgraph':
				$wrapCode = '';
				$content = wp_strip_all_tags($content);
				$start = strpos($content, '[');
				$end = strrpos($content, '"]');
				$len =  strlen($content);
				$diff = $end - $len;
				$content = substr( $content, $start, $diff);

				$content = str_replace(array('[skill ', '"]', '" ]', '" ', '="' ), array('', '', '', ':', '='), $content);
				$cntStrAr = explode( "\n", $content );

				$numAr = array();
				foreach($cntStrAr as $cntk => $cntv){
					if($cntv != ''){
						$cnStr = str_replace( array( 'percent=', 'title=', 'verticalgraph_background=', 'verticalgraph_titlecolor='), array('','','',''), trim($cntv) );
						$numAr[] = explode(':', $cnStr);
					}
				}

				$cssVar = '';
				foreach($numAr as $n => $b){ 
					$n++; 
					$cssVar .= (count($numAr) == $n) ? '#g'.$n : '#g'.$n.', ';  
				}
				$sbIds = '';
				foreach($numAr as $n => $b){ 
					$n++; 
					$sbIds .= (count($numAr) == $n) ? 'g'.$n : 'g'.$n.', ';  
				}

				$wrapCode .= '<div id="skillbarstraight">';
				$wrapCode .= '<ul class="chart_line skt_skill_bar-graph">';
		        foreach($numAr as $n => $v){
		         	$wrapCode .= '<li class="skt_skill_bar" style="height:'.$v[0].'%;background:'.$v[2].';" title="'.$v[1].'">
						<div class="percent" style="color:'.$v[3].';">'.$v[0].'%</div>
						<div class="description" style="color:'.$v[3].';">'.$v[1].'</div></li>';
		        }
				$wrapCode .= '</ul></div>';
			break;

			case 'skt_piegraph':

		    $wrapCode = '';
		    $content = wp_strip_all_tags($content);
		    $start = strpos($content, '[');
		    $end = strrpos($content, '"]');
		    if ($start === false || $end === false || $end <= $start) break;

		    $len  = strlen($content);
		    $diff = $end - $len;
		    $content = substr($content, $start, $diff);

		    $content = str_replace(
		        array('[skill ', '"]', '" ]', '" ', '="'),
		        array('', '', '', ':', '='),
		        $content
		    );

		    $cntStrAr = explode("\n", $content);

		    $numAr = array();
		    foreach ($cntStrAr as $cntv) {
		        if (trim($cntv) != '') {
		            $cnStr = str_replace(
		                array('percent=', 'title=', 'piegraph_background=', 'piegraph_titlecolor='),
		                array('', '', '', ''),
		                trim($cntv)
		            );
		            $numAr[] = explode(':', $cnStr);
		        }
		    }

		    $title = array();
		    $percentage = array();
		    $piegraph_background = array();

		    foreach ($numAr as $v) {
		        $percentage[] = floatval($v[0]);
		        $title[] = sanitize_text_field($v[1]);
		        $piegraph_background[] = sanitize_hex_color($v[2]) ?: '#cccccc';
		    }

		    static $chart_count = 0;
		    $chart_count++;
		    $chart_id = 'skt_skills_myChart_' . $chart_count;

		    $wrapCode .= '<canvas id="' . esc_attr($chart_id) . '" style="width:100%;max-width:350px;height:350px; margin: 0 auto;"></canvas>';

		    $percentage_json = wp_json_encode($percentage);
		    $title_json = wp_json_encode($title);
		    $background_json = wp_json_encode($piegraph_background);

		    $wrapCode .= '<script>
		        (function(){
		            const ctx = document.getElementById("' . esc_js($chart_id) . '");
		            if(!ctx) return;
		            new Chart(ctx, {
		                type: "pie",
		                data: {
		                    labels: ' . $title_json . ',
		                    datasets: [{
		                        backgroundColor: ' . $background_json . ',
		                        data: ' . $percentage_json . '
		                    }]
		                },
		                options: {
		                    title: { display: false }
		                }
		            });
		        })();
		    </script>';

		    break;

			case 'skt_polygraph':
			static $poly_counter = 0;
			$poly_counter++;

			$wrapCode = '';
			$content = wp_strip_all_tags($content);
			$start = strpos($content, '[');
			$end = strrpos($content, '"]');
			$len = strlen($content);
			$diff = $end - $len;
			$content = substr($content, $start, $diff);

			$content = str_replace(array('[skill ', '"]', '" ]', '" ', '="'), array('', '', '', ':', '='), $content);
			$cntStrAr = explode("\n", $content);

			$numAr = array();
			foreach ($cntStrAr as $cntk => $cntv) {
				if (trim($cntv) != '') {
					$cnStr = str_replace(
						array('percent=', 'title=', 'polygraph_background=', 'polygraph_titlecolor='),
						array('', '', '', ''),
						trim($cntv)
					);
					$numAr[] = explode(':', $cnStr);
				}
			}

			$title = array();
			$percentage = array();
			$polygraph_background = array();

			foreach ($numAr as $n => $v) {
				$percentage[] = isset($v[0]) ? ltrim(ltrim($v[0], '&nbsp;'), ' ') : 0;
				$title[] = isset($v[1]) ? ltrim(ltrim($v[1], '&nbsp;'), ' ') : '';
				$polygraph_background[] = isset($v[2]) ? ltrim(ltrim($v[2], '&nbsp;'), ' ') : '#000';
			}

			$percentage_json = wp_json_encode($percentage);
			$title_json = wp_json_encode($title);
			$polygraph_background_json = wp_json_encode($polygraph_background);
			$canvas_id = 'skt_skills_polychart_' . esc_attr($poly_counter);

			$wrapCode .= '<canvas id="' . $canvas_id . '" aria-label="chart" height="350" width="580" style="margin:0 auto;"></canvas>';
			$wrapCode .= '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var xValues_' . $poly_counter . ' = ' . $title_json . ';
				var yValues_' . $poly_counter . ' = ' . $percentage_json . ';
				var barColors_' . $poly_counter . ' = ' . $polygraph_background_json . ';
				var chrt_' . $poly_counter . ' = document.getElementById("' . $canvas_id . '").getContext("2d");

				new Chart(chrt_' . $poly_counter . ', {
					type: "polarArea",
					data: {
						labels: xValues_' . $poly_counter . ',
						datasets: [{
							label: "",
							data: yValues_' . $poly_counter . ',
							backgroundColor: barColors_' . $poly_counter . '
						}],
					},
					options: {
						responsive: false
					}
				});
			});
			</script>';
			break;

			case 'skt_linegraph':
				static $line_counter = 0;
				$line_counter++;

				$wrapCode = '';
				$content = wp_strip_all_tags($content);
				$start = strpos($content, '[');
				$end = strrpos($content, '"]');
				$len = strlen($content);
				$diff = $end - $len;
				$content = substr($content, $start, $diff);

				$content = str_replace(array('[skill ', '"]', '" ]', '" ', '="'), array('', '', '', ':', '='), $content);
				$cntStrAr = explode("\n", $content);

				$numAr = array();
				foreach ($cntStrAr as $cntk => $cntv) {
					if (trim($cntv) != '') {
						$cnStr = str_replace(
							array('percent=', 'title=', 'linegraph_background=', 'linegraph_titlecolor='),
							array('', '', '', ''),
							trim($cntv)
						);
						$numAr[] = explode(':', $cnStr);
					}
				}

				$title = array();
				$percentage = array();
				$linegraph_background = array();

				foreach ($numAr as $n => $v) {
					$percentage[] = isset($v[0]) ? ltrim(ltrim($v[0], '&nbsp;'), ' ') : 0;
					$title[] = isset($v[1]) ? ltrim(ltrim($v[1], '&nbsp;'), ' ') : '';
					$linegraph_background[] = isset($v[2]) ? ltrim(ltrim($v[2], '&nbsp;'), ' ') : '#000';
				}

				$percentage_json = wp_json_encode($percentage);
				$title_json = wp_json_encode($title);
				$linegraph_background_json = wp_json_encode($linegraph_background);

				// Unique canvas ID for each instance
				$canvas_id = 'toolTip' . esc_attr($line_counter);

				$wrapCode .= '<canvas class="linegraphskill" id="' . $canvas_id . '" aria-label="chart" height="350" width="580" style="margin:0 auto;"></canvas>';
				$wrapCode .= '<script>
					document.addEventListener("DOMContentLoaded", function() {
						var xValues_' . $line_counter . ' = ' . $title_json . ';
						var yValues_' . $line_counter . ' = ' . $percentage_json . ';
						var barColors_' . $line_counter . ' = ' . $linegraph_background_json . ';
						var chartTooltip_' . $line_counter . ' = document.getElementById("' . $canvas_id . '").getContext("2d");
						new Chart(chartTooltip_' . $line_counter . ', {
							type: "line",
							data: {
								labels: xValues_' . $line_counter . ',
								datasets: [{
									label: "",
									data: yValues_' . $line_counter . ',
									backgroundColor: barColors_' . $line_counter . ',
									borderColor: ["black"],
									borderWidth: 1,
									pointRadius: 5,
								}],
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										display: false,
										position: "bottom",
										align: "center",
										labels: {
											color: "darkred",
											font: { weight: "bold" }
										}
									}
								}
							}
						});
					});
				</script>';
			break;

	}
	return $wrapCode;
}
add_shortcode( 'skillwrapper', 'skillwrapper_func' );


//[skill title_background="#f7a53b" bar_foreground="#f7a53b" bar_background="#eeeeee" percent="90" title="CSS3"]
function skilldata_func( $atts ) {
	extract( shortcode_atts( array(
		'title_background' => '',
		'bar_foreground' => '',
		'bar_background' => '',
		'percent' => '0',
		'title' => '',
	), $atts ) );


	if( $title_background != '' ){
		$skillHtml = '<div class="skillbar clearfix" data-percent="'.esc_attr($percent).'%" style="background: '.esc_attr($bar_background).';">
				<div class="skillbar-title" style="background: '.esc_attr($title_background).' !important;"><span>'.esc_attr($title).'</span></div>
				<div class="skillbar-bar" style="background: '.esc_attr($bar_foreground).';"></div>
				<div class="skill-bar-percent">'.esc_attr($percent).'%</div>
			</div>';
	}elseif( $title_background == '' && $bar_foreground != '' && $bar_background != '' ){
		$skillHtml = '<div class="skillbar clearfix " data-percent="'.esc_attr($percent).'%" style="background: '.esc_attr($bar_background).';">
				<div class="skillbar-title" style="background: '.esc_attr($title_background).' !important;;"><span>'.esc_attr($title).'</span></div>
				<div class="skillbar-bar" style="background: '.esc_attr($bar_foreground).';"></div>
				<div class="skill-bar-percent">'.esc_attr($percent).'%</div>
			</div>';
	}elseif( $title_background == '' && $bar_foreground == '' && $bar_background == '' ){
		$skillHtml = '<li>
				<div class="chartbox">
					<div class="chart" data-percent="'.esc_attr($percent).'">
						<span>'.esc_attr($percent).'%</span>
					</div>
					<p>'.wp_strip_all_tags(esc_attr($title)).'</p>
				</div>
			</li>';
	}

	return $skillHtml;
}
add_shortcode( 'skill', 'skilldata_func' );


// create skt skillbar option page
function sbar_admin() {  
    include('sktskillbar_option.php');  
}
function sbar_admin_actions() {
	add_options_page('SKT Skill Bar', 'SKT Skill Bar', 'manage_options', 'sktskillbar_admin', 'sbar_admin');
}
add_action('admin_menu', 'sbar_admin_actions');

function sktskillbar_admin_action_links($links, $file) {
    static $tb_plugin;
    if (!$tb_plugin) {
        $tb_plugin = plugin_basename(__FILE__);
    }
    if ($file == $tb_plugin) {
        $settings_link = '<a href="options-general.php?page=sktskillbar_admin">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'sktskillbar_admin_action_links', 10, 2);


function sbar_hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);
 
   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   return implode(",", $rgb); // returns the rgb values separated by commas
   //return $rgb; // returns an array with the rgb values
}
?>