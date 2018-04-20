<?
header("Content-Type: text/html; charset=iso-8859-1");
include("config.inc");
include("graph/pData.class.php");
include("graph/pDraw.class.php");
include("graph/pImage.class.php");
?>

<html>
<head>
	<title>Gaszähler</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link href="metro_ui/css/metro-bootstrap.css" rel="stylesheet">
	<link href="metro_ui/css/iconFont.css" rel="stylesheet">
</head>

<body class="metro">
	<div class="tile-area tile-area-grayed">
		<h1 class="tile-area-title fg-white">
			<a href="index.php"><i class="icon-arrow-left-3 fg-white smaller"></i></a>
			Gaszähler<small class="on-right">Übersicht</small>
		</h1>
		<?
		$connection=mysql_connect($mysqlhost,	$mysqluser,	$mysqlpwd) or	die	("Verbindungsversuch fehlgeschlagen");
		$mysqldb=$mysqldbhaus;
		mysql_select_db($mysqldb,$connection) or	die("Konnte die Datenbank nicht waehlen.");

		//*****************
		//Gesamtzaehlerstand
		//*****************
		$sql = "SELECT sum(zaehlerstand) FROM gaszaehler";
		$query = mysql_query($sql) or die("Anfrage 1 nicht erfolgreich");
		while ($wert = mysql_fetch_array($query)) {
			$gesamtwert=($wert[0]*10)+$gas_startwert;
		}
		$gesamtwert=substr($gesamtwert,0,-3).".".substr($gesamtwert,-3,2);

		print "<table class='table'>";
		print "<tr style='background-color:#BCE02E;font-weight:bold;'><td colspan=1>Gesamt Zählerstand:</td><td style='text-align:right;'>".$gesamtwert." m&sup3;</td></tr>";
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
							,sum(zaehlerstand) FROM gaszaehler WHERE DATE(timestamp) >= DATE_SUB(NOW(),INTERVAL ".$anz_tage_gas." DAY) GROUP BY day(timestamp) ORDER BY timestamp";
		$query = mysql_query($sql) or die("Anfrage 1 nicht erfolgreich");
		$i=0;
		$durchschnitt=0;
		while ($wert = mysql_fetch_array($query)) {
			print "<tr><td colspan=1>".$wert[0]."</td><td style='text-align:right;'>".substr(($wert[1]),0,-2).".".substr(($wert[1]),-2)." m&sup3;</td></tr>";
			$durchschnitt=$durchschnitt+$wert[1]/100;
			$i++;
		}
		$durchschnitt=$durchschnitt/$anz_tage_gas;
		//print "<tr><td colspan=2><br></td></tr>";
		print "<tr style='font-style: italic;'><td colspan=1>Durchschnitt der letzten ".$anz_tage_gas." Tage</td><td style='text-align:right;'>".round($durchschnitt,2)." m&sup3;</td></tr>";

		//************************
		//monatlicher Gasverbrauch
		//************************
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
								year(timestamp), sum(zaehlerstand)
								FROM gaszaehler
								GROUP BY month(timestamp), year(timestamp)
								ORDER BY timestamp;";
		$query = mysql_query($sql) or die("Anfrage 2 nicht erfolgreich");
		//WHERE year(timestamp) >= YEAR(CURRENT_DATE - INTERVAL 13 MONTH) AND  month(timestamp) >= month(CURRENT_DATE - INTERVAL 13 MONTH)
		while ($wert = mysql_fetch_array($query)) {
			$gasverbrauch_m[]=substr(($wert[2]),0,-2).".".substr(($wert[2]),-2);
			$monat[]=$wert[0]." ".$wert[1];
		}

		//********************************************
		//Graph fuer Gasverbrauch (monatlich) zeichnen
		//********************************************
		$DataSet = new pData();
		$DataSet->AddPoints($gasverbrauch_m);
		$DataSet->setAxisName(0,"m&sup3;");
		$DataSet->AddPoints($monat,"XLabel");
		$DataSet->setAbscissa("XLabel");
		$Graph = new pImage(1300,300,$DataSet);
		$Graph->Antialias = TRUE;
		$Graph->setFontProperties(array("FontName"=>"graph/verdana.ttf","FontSize"=>8));
		//$Graph->drawText(30,20,"Gasverbrauch der letzten Monate",array("FontSize"=>12));
		$Graph->setGraphArea(40,10,1300,280);
		$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
		$Graph->drawScale($scaleSettings);
		$Palette="";
		$settings	=	array("Gradient"=>FALSE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0,"DisplayShadow"=>TRUE,"Surrounding"=>0,"OverrideColors"=>$Palette);
		$Graph->drawBarChart($settings);
		$Graph->Render("tmp/gasverbrauch_m.png");

		print "<tr><td style='background-color:#A3BDE0;color:#1E3657;'><b>Monatsüberblick</b></td><td style='background-color:#A3BDE0;color:#1E3657;width:300px;'><b>Jahresüberblick</b></td></tr>";
		print "<tr><td><img src=\"tmp/gasverbrauch_m.png\"></td>";

		//************************
		//jaehrlicher Gasverbrauch
		//************************
		$sql = "SELECT
							year(timestamp), sum(zaehlerstand)
							FROM gaszaehler
							GROUP BY year(timestamp) ORDER BY timestamp;";
		$query = mysql_query($sql) or die("Anfrage 2 nicht erfolgreich");
		print "<td>";
		print "<table>";
		while ($wert = mysql_fetch_array($query)) {
			print "<tr><td>".$wert[0]."</td><td style='text-align:right;' nowrap>".substr(($wert[1]),0,-2).".".substr(($wert[1]),-2)." m&sup3;</td></tr>";
		}
		print "</table>";
		print "</td>";
		print "</tr>";
		print "</table>";
		?>
</div>

</body>
</html>
