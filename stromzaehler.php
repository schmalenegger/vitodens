<?
header("Content-Type: text/html; charset=iso-8859-1");
include("config.inc");
include("graph/pData.class.php");
include("graph/pDraw.class.php");
include("graph/pImage.class.php");
?>

<html>
<head>
	<title>Stromzähler</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link href="metro_ui/css/metro-bootstrap.css" rel="stylesheet">
	<link href="metro_ui/css/iconFont.css" rel="stylesheet">
</head>

<body style="font-family:verdana,arial;">

<body class="metro">
	<div class="tile-area tile-area-grayed">
		<h1 class="tile-area-title fg-white">
			<a href="index.php"><i class="icon-arrow-left-3 fg-white smaller"></i></a>
			Stromzähler<small class="on-right">Übersicht</small>
		</h1>
		<?
		$connection=mysql_connect($mysqlhost,	$mysqluser,	$mysqlpwd) or	die	("Verbindungsversuch fehlgeschlagen");
		$mysqldb=$mysqldbhaus;
		mysql_select_db($mysqldb,$connection) or	die("Konnte	die	Datenbank	nicht	waehlen.");

		//Gesamtzählerstand
		$sql = "SELECT zaehlerstand FROM stromzaehler";
		$query = mysql_query($sql) or die("Anfrage 1 nicht erfolgreich");
		while ($wert = mysql_fetch_array($query)) {
			$gesamtwert=$wert[0];
		}

		print "<table class='table'>";
		print "<tr style='background-color:#BCE02E;font-weight:bold;'><td>Gesamt Zählerstand:</td><td style='text-align:right;'>".$gesamtwert." kWh</td></tr>";
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
						,max(zaehlerstand)-min(zaehlerstand) FROM stromzaehler WHERE DATE(timestamp) >= DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY day(timestamp) ORDER BY timestamp";
		$query = mysql_query($sql) or die("Anfrage 1 nicht erfolgreich");
		$i=0;
		$durchschnitt=0;
		while ($wert = mysql_fetch_array($query)) {
			if ($i>=0) {
				print "<tr><td>".$wert[0]."</td><td style='text-align:right;'>".round($wert[1],1)." kWh</td></tr>";
				$durchschnitt=$durchschnitt+round($wert[1],1);
			}
			$i++;
		}
		$durchschnitt=$durchschnitt/$i;
		print "<tr style='font-style: italic;'><td>Durchschnitt der letzten 7 Tage</td><td style='text-align:right;'>".round($durchschnitt,1)." kWh</td></tr>";
		//exit();

		//***************************
		//monatlicher Stromverbrauch
		//***************************
		$sql = "SELECT CASE DATE_FORMAT(timestamp,'%m')
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
								year(timestamp),
							max(zaehlerstand)-min(zaehlerstand) FROM stromzaehler WHERE DATE(timestamp) >= DATE_SUB(NOW(), INTERVAL 13 MONTH) GROUP BY year(timestamp), month(timestamp) ORDER BY year(timestamp) asc, month(timestamp)";
		$query = mysql_query($sql) or die("Anfrage 2 nicht erfolgreich");
		//WHERE year(timestamp) >= YEAR(CURRENT_DATE - INTERVAL 13 MONTH) AND  month(timestamp) >= month(CURRENT_DATE - INTERVAL 13 MONTH)
		while ($wert = mysql_fetch_array($query)) {
			$stromverbrauch_m[]=round($wert[2],1);
			$monat[]=$wert[0]." ".$wert[1];
		}

		//**********************************************
		//Graph fuer Stromverbrauch (monatlich) zeichnen
		//**********************************************
		$DataSet = new pData();
		$DataSet->AddPoints($stromverbrauch_m);
		$DataSet->setAxisName(0,"kWh");
		$DataSet->AddPoints($monat,"XLabel");
		$DataSet->setAbscissa("XLabel");
		$Graph = new pImage(1300,300,$DataSet);
		$Graph->Antialias = TRUE;
		$Graph->setFontProperties(array("FontName"=>"graph/verdana.ttf","FontSize"=>8));
		//$Graph->drawText(30,20,"Stromverbrauch der letzten Monate",array("FontSize"=>12));
		$Graph->setGraphArea(40,10,1300,280);
		$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
		$Graph->drawScale($scaleSettings);
		$Palette="";
		$settings	=	array("Gradient"=>FALSE,"GradientMode"=>GRADIENT_EFFECT_CAN,"DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"DisplayR"=>0,"DisplayG"=>0,"DisplayB"=>0,"DisplayShadow"=>TRUE,"Surrounding"=>0,"OverrideColors"=>$Palette);
		$Graph->drawBarChart($settings);
		$Graph->Render("tmp/stromverbrauch_m.png");

		print "<tr><td style='background-color:#A3BDE0;color:#1E3657;'><b>Monatsüberblick</b></td><td style='background-color:#A3BDE0;color:#1E3657;width:300px;'><b>Jahresüberblick</b></td></tr>";
		print "<tr><td><img src=\"tmp/stromverbrauch_m.png\"></td>";

		//***************************
		//jaehrlicher Stromverbrauch
		//***************************
		$sql = "SELECT year(timestamp),max(zaehlerstand)-min(zaehlerstand) FROM stromzaehler WHERE DATE(timestamp) >= DATE_SUB(NOW(), INTERVAL 10 YEAR) GROUP BY year(timestamp) ORDER BY year(timestamp) ASC;";
		$query = mysql_query($sql) or die("Anfrage 2 nicht erfolgreich");
		print "<td>";
		print "<table>";
		while ($wert = mysql_fetch_array($query)) {
			print "<tr><td>".$wert[0]."</td><td style='text-align:right;' nowrap>".round($wert[1],1)." kWh</td></tr>";
		}
		print "</table>";
		print "</td>";
		print "</tr>";
		print "</table>";
		?>
	</div>
</div>

</body>
</html>
