<?php
/*==============================================================================
Description : Display the best runs of the current track of all playerInfo,
Last Revision : 02/01/2011
Version : 1.5
Author : galaad
==============================================================================*/
Aseco::registerEvent('onStartup',          'OnStartup_bestruns');
Aseco::registerEvent('onNewChallenge',     'OnNewChallenge_bestruns');
Aseco::registerEvent('onPlayerFinish',     'OnPlayerFinish_bestruns');
Aseco::addChatCommand('bestruns',        'Load config file of bestruns plugin');

/* Variables globales */
$bestruns;
$count;
$cps;
/* Config file */
$x;
$y;
$scale;
$nb_bestruns;
$nb_bestruns_with_cp;
$nb_max_checkpoints;

function chat_bestruns($aseco, $command){
    $author = $command["author"];
	if ($aseco->isMasterAdmin($author) OR $aseco->isAdmin($author)){
		$aseco->client->query('ChatSendServerMessage', 
			$aseco->formatColors("Load Config BestRuns OK"));
		LoadConfig_bestruns();
	}
}

function OnStartup_bestruns($aseco, $empty){
	global $bestruns, $count;
	$bestruns = array();
	$count = 0;
	LoadConfig_bestruns ();
}

function OnNewChallenge_bestruns($aseco, $challenge){
	global $count, $cps;
	$cps = $challenge->nbchecks - 1;
	$count = 0;
	Clear_bestruns($aseco, $challenge);
}

function OnPlayerFinish_bestruns($aseco, $record){
	global $bestruns, $nb_bestruns, $count;
	if ($record->score > 0){
		if ($count == 0){
			$bestruns[0] = $record;
			$count++;
		}
		else if ($count < $nb_bestruns){
			for ($pos = 0; $pos < $count; $pos++){
				if ($record->score < $bestruns[$pos]->score){
					break;
				}
			}
			for ($i = $count - 1; $i >= $pos; $i--){
				$bestruns[$i + 1] = $bestruns[$i];
			}
			$bestruns[$pos] = $record;
			$count++;
		}
		else{
			if ($record->score < $bestruns[$nb_bestruns - 1]->score){
				for ($pos = 0; $pos < $count; $pos++){
				if ($record->score < $bestruns[$pos]->score){
					break;
				}
			}
			for ($i = $count - 1; $i >= $pos; $i--){
				$bestruns[$i + 1] = $bestruns[$i];
			}
			$bestruns[$pos] = $record;
			}
		}	
		Display_bestruns($aseco);
	}
}

function LoadConfig_bestruns(){
	global $x, $y, $scale, $orientation, $nb_bestruns;
	global $nb_bestruns_with_cp, $nb_max_checkpoints;
	$config = simplexml_load_file('bestruns.xml');
	$x = $config->x;
	$y = $config->y;
	$scale = floatval($config->scale);
	$nb_bestruns = $config->nb_bestruns;
	$nb_bestruns_with_cp = $config->nb_bestruns_with_cp;
	$nb_max_checkpoints = $config->nb_max_checkpoints;
}

function Display_bestruns($aseco){ // affiche les best runs
	global $x, $y, $nb_bestruns_with_cp, $nb_max_checkpoints, $scale;
	global $bestruns, $count, $cps;
	
	/* Frame widget */
	$x_frame_widget = $x;
	$y_frame_widget = $y;
	
	/* Internal Config */
	$textsize = 1;
	
	/* Cps config */
	$nb_col = 3;
	$textsize_cp = 0.9;
	
	$width_bestrun = 14;
	$height_main = 2.2;
	
	$xml='<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml.='<manialink id="342312">'. "\n";
	$xml.='<frame posn="' .$x_frame_widget. ' ' .$y_frame_widget. '">'. "\n";
	$xml.='<format textsize="'. $textsize .'"/>'. "\n";
	
	for($i = 0; $i < $count; $i++) { //for each bestruns
		/* Frame Best Run */
		$x_frame_bestrun = $i * $width_bestrun * $scale;
		echo "FRAME BEST RUN X = $x_frame_bestrun , scale = $scale, i = $i, w = $width_bestrun\n";
		$y_frame_bestrun = 0;
		
		/* Frame Main */
		$x_frame_main = 0;
		$y_frame_main = 0;
		
		$x_quad_main = 0;
		$y_quad_main = 0;
		$width_quad_main = $width_bestrun;
		$height_quad_main = $height_main;
		
		$x_offset_label_time = 0.6;
		$y_offset_label_time = -0.3;
		$x_label_time = (0 + $x_offset_label_time) * $scale;
		$y_label_time = (0 + $y_offset_label_time) * $scale;
		$width_label_time = 5.8;
		$height_label_time = 2;
		
		$x_offset_label_nickname = 0.6;
		$y_offset_label_nickname = -0.3;
		$x_label_nickname = ($width_label_time + $x_offset_label_nickname) * $scale;
		$y_label_nickname = (0 + $y_offset_label_nickname) * $scale;
		$width_label_nickname = 6.9;
		$height_label_nickname = 2;
		
		/* Frame Checkpoints */
		$x_frame_cps = 0;
		$y_frame_cps = (-$height_main) * $scale;
		
		/* Time */
		$minutes = (int) ($bestruns[$i]->score / 60000);
		$secondes = (int) (($bestruns[$i]->score - $minutes * 60000) / 1000);
		$centiemes = (int) (($bestruns[$i]->score - $minutes*60000 - $secondes*1000) / 10);
		$time ="$z". ($i + 1) .". $fff";
		$time .= "$minutes:";
		if ($secondes < 10) $time .= "0";	
		$time .= $secondes.'.';
		if ($centiemes < 10) $time .= "0";
		$time .= $centiemes;
		
		$xml.='<frame posn="'.$x_frame_bestrun.' '.$y_frame_bestrun.'">' ."\n";
		$xml.='<frame posn="'.$x_frame_main.' '.$y_frame_main.'">' . "\n";
		$xml.='<quad scale="'.$scale.'" posn="0 0" '
			.'sizen="'. $width_quad_main .' '. $height_quad_main 
			.'" halign="left" valign="top" style="Bgs1InRace" substyle="NavButton" />' ."\n";
		;
		$xml.='<label scale="'.$scale.'" posn="'.$x_label_time.' '
			.$y_label_time.'" sizen="'.$width_label_time.' '.$height_label_time
			.'" halign="left" valign="top" text="'.$time.'"/>'."\n";
			
		$xml.='<label scale="'.($scale).'" posn="' .$x_label_nickname. ' '
			.$y_label_nickname.'" sizen="'.$width_label_nickname.' '.$height_label_nickname
			.'" halign="left" valign="top" text="'.$bestruns[$i]->player->nickname.'"/>'."\n";
		$xml.='</frame>'."\n";
		
		/* Checkpoints */
		if ($i < $nb_bestruns_with_cp){
			$xml.='<frame posn="'.$x_frame_cps.' '.$y_frame_cps.'">'. "\n";
			$xml.='<format textsize="'. $textsize_cp .'"/>'. "\n";
			$j = 0;
			for ($j = 0; $j < $cps  AND $j < $nb_max_checkpoints; $j++){
			
				$cp = $bestruns[$i]->checks[$j];
				$minn = (int) (($cp) / 60000);
				$secc = (int) ((($cp) - $minn * 60000) / 1000);
				$cenn = (int) (($cp - $minn*60000 - $secc*1000) / 10);
				$textee ='$z$fff';
				$textee .= "$minn:";
				if ($secc < 10) $textee .= "0";	
				$textee .= $secc.'.';
				if ($cenn < 10) $textee .= "0";
				$textee .= $cenn;
				
				$width_quad_cp = 4.6;
				$height_quad_cp = 1.6;
				$x_quad_cp = ($j % $nb_col) * $width_quad_cp * $scale;
				$y_quad_cp = (- floor($j/$nb_col) * $height_quad_cp) * $scale;
				
				$y_offset_label_cp = -0.3;
				$x_label_cp = 
					(($j % $nb_col) * $width_quad_cp + $width_quad_cp/2) * $scale;
				$y_label_cp = ((- floor($j/$nb_col) * $height_quad_cp)  + $y_offset_label_cp) * $scale;
				$width_label_cp = $width_quad_cp;
				$height_label_cp = $height_quad_cp;
				
				
				$xml.='<quad scale="'.$scale.'" posn="' .$x_quad_cp. " " 
					. $y_quad_cp
					. '" sizen="'.$width_quad_cp.' '.$height_quad_cp
					.'" halign="left" valign="top" style="Bgs1InRace" substyle="NavButton" />' . "\n";
				$xml.='<label scale="'.$scale.'" posn="' 
					.$x_label_cp. " " 
					. $y_label_cp
					. '" sizen="'.$width_label_cp.' '.$height_label_cp
					.'" halign="center" valign="top" text="'.$textee.'"/>' . "\n";
			}
			$xml .= '</frame>'. "\n";
		}
		$xml .= '</frame>'. "\n";
	}
	
	$xml.= '</frame>' . "\n"
		.'</manialink>'."\n";
	echo $xml;
	$aseco->client->query("SendDisplayManialinkPage", $xml, 0, false); //requete d'affichage
}


function Clear_bestruns($aseco, $challenge){//efface le widget entre 2 challenges
	$xml = '<manialink id="342312"></manialink>';
    $aseco->client->query("SendDisplayManialinkPage", $xml, 1, false);
}
?>
