<?php
header("Content-Type: text/html; charset=iso-8859-1");
include("config.inc");
include("graph/pData.class.php");
include("graph/pDraw.class.php");
include("graph/pImage.class.php");
?>

<html>
<head>
	<title>Aussentemperatur</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link href="metro_ui/css/metro-bootstrap.css" rel="stylesheet">
	<link href="metro_ui/css/iconFont.css" rel="stylesheet">
</head>

<body class="metro">
	<div class="tile-area tile-area-grayed">
		<h1 class="tile-area-title fg-white">
			<a href="index.php"><i class="icon-arrow-left-3 fg-white smaller"></i></a>
			Aussentemperatur<small class="on-right">Übersicht</small>
		</h1>
		<?php
		$pdo = new PDO("mysql:host=$mysqlhost;dbname=$mysqldbvito", "$mysqluser", "$mysqlpwd");
		//aktuelle Temperatur
		$sql = "SELECT aussentemperatur FROM temperaturen ORDER BY timestamp DESC LIMIT 1";
		foreach ($pdo->query($sql) as $temp) {
		}

		//min/max Temperaturen
		$sql = "SELECT MIN(aussentemperatur), MAX(aussentemperatur) FROM temperaturen WHERE timestamp >= DATE(NOW()) ORDER BY timestamp";
		foreach ($pdo->query($sql) as $minmax) {
			$mintemp=$minmax[0];
			$maxtemp=$minmax[1];
		}

		print "<table class='table'>";
		print "<tr style='background-color:#BCE02E;font-weight:bold;'><td colspan=1>Werte aktuell:</td><td style='text-align:right;'>".$temp[0]." °C, min ".round($mintemp,1)." °C, max ".round($maxtemp,1)." °C</td></tr>";
		print "<tr><td colspan=2 style='background-color:#A3BDE0;color:#1E3657;'><b>Wochenüberblick</b></td></tr>";
		
		$sql = "SELECT
							CASE DATE_FORMAT(timestamp,'%w')
								WHEN 0 THEN 'Sonntag'
								WHEN 1 THEN 'Montag'
								WHEN 2 THEN 'Dienstag'
								WHEN 3 THEN 'Mittwoch'
								WHEN 4 THEN 'Donnerstag'
								WHEN 5 THEN 'Freitag'
								WHEN 6 THEN 'Samstag'
								ELSE 'fehler' END
							,avg(aussentemperatur) FROM temperaturen WHERE DATE(timestamp) >= DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY day(timestamp) ORDER BY timestamp";
		$i=0;
		$durchschnitt=0;
		foreach ($pdo->query($sql) as $wert) {
			print "<tr><td colspan=1>".$wert[0]."</td><td style='text-align:right;'>".round($wert[1],1)." °C</td></tr>";
			$durchschnitt=$durchschnitt+$wert[1];
			$i++;
		}
		$durchschnitt=$durchschnitt/7;
		print "<tr style='font-style: italic;'><td colspan=1>Durchschnitt der letzten 7 Tage</td><td style='text-align:right;'>".round($durchschnitt,1)." °C</td></tr>";

		//monatliche Durchschnittstemperatur
		$sql = "SELECT
							CASE DATE_FORMAT(timestamp,'%m')
								WHEN 1 THEN 'Jan'
								WHEN 2 THEN 'Feb'
								WHEN 3 THEN 'Mär'
								WHEN 4 THEN 'Apr'
								WHEN 5 THEN 'Mai'
								WHEN 6 THEN 'Jun'
								WHEN 7 THEN 'Jul'
								WHEN 8 THEN 'Aug'
								WHEN 9 THEN 'Sep'
								WHEN 10 THEN 'Okt'
								WHEN 11 THEN 'Nov'
								WHEN 12 THEN 'Dez'
							ELSE 'fehler' END as Monat,
								year(timestamp), avg(aussentemperatur) FROM temperaturen WHERE DATE(timestamp) >= DATE_SUB(NOW(), INTERVAL 14 MONTH) GROUP BY year(timestamp), month(timestamp) ORDER BY year(timestamp) asc, month(timestamp)";
		foreach ($pdo->query($sql) as $wert) {
			$aussentemperatur_m[]=round($wert[2],1);
			$monat[]=$wert[0]." ".$wert[1];
		}

		//Graph fuer Durchschnittstemperatur (monatlich) zeichnen
		$DataSet = new pData();
		$DataSet->AddPoints($aussentemperatur_m);
		$DataSet->setAxisName(0,"°C");
		$DataSet->AddPoints($monat,"XLabel");
		$DataSet->setAbscissa("XLabel");
		$Graph = new pImage(1300,300,$DataSet);
		$Graph->Antialias = TRUE;
		$Graph->setFontProperties(array("FontName"=>"graph/verdana.ttf","FontSize"=>10));
		$Graph->setGraphArea(40,10,1300,280);
		$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
		$Graph->drawScale($scaleSettings);
		$Palette="";
		$settings	=	array("Gradient"=>FALSE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0,"DisplayShadow"=>TRUE,"Surrounding"=>0,"OverrideColors"=>$Palette);
		$Graph->drawBarChart($settings);
		$Graph->Render("tmp/temperatur_m.png");

		print "<tr><td style='background-color:#A3BDE0;color:#1E3657;'><b>Monatsüberblick</b></td><td style='background-color:#A3BDE0;color:#1E3657;width:300px;'><b>Jahresüberblick</b></td></tr>";
		print "<tr><td><img src=\"tmp/temperatur_m.png\"></td>";

		//jaehrliche Durchschnittstemperatur
		$sql = "SELECT
							year(timestamp), avg(aussentemperatur),min(aussentemperatur),max(aussentemperatur)
							FROM temperaturen
							GROUP BY year(timestamp) ORDER BY timestamp;";
		print "<td>";
		print "<table>";
		print "<tr style='text-align:center;background-color:#dddddd;'><td>Jahr</td><td>&empty;</td><td>min</td><td>max</td></tr>";
		foreach ($pdo->query($sql) as $wert) {
			print "<tr>
					<td>".$wert[0]."</td>
					<td style='text-align:right;' nowrap>".round($wert[1],1)." °C</td>
					<td style='text-align:right;' nowrap>".round($wert[2],1)." °C</td>
					<td style='text-align:right;' nowrap>".round($wert[3],1)." °C</td>
					</tr>";
		}
		print "</table>";
		print "</td>";
		print "</tr>";
		print "</table>";
		?>
</div>

</body>
</html>
