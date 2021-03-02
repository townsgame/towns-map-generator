<?php
/**
 * Generátor map (původně do hry Towns.cz)
 *
 * @copyright 2015 Towns.cz
 * @link http://api.towns.cz/
 * @link http://www.towns.cz/
 * @author     Pavol Hejný
 * @version    2.0
 *
 */

//======================================================================================================================Inicializace
//=========================================================================Konfigurace

ini_set("max_execution_time","1000");
ini_set("memory_limit","200M");
error_reporting(1);

//=========================================================================Načtení hodnot odeslaných z formuláře

//Základní parametry pro generátor
$velikost=          intval($_POST["velikost"]);
$voda=              floatval($_POST["voda"]);
$zrnitostOstrova=   floatval($_POST["zrnitostostrova"]);
$delkaRek=          floatval($_POST["delkarek"]);
$tocivostRek=       floatval($_POST["tocivostrek"]);
$centralitaRek=     floatval($_POST["centralitarek"]);
$zrnitostOstatni=   floatval($_POST["zrnitostostatni"]);

//Terény si může každý upravit/ přepsat , tyhle konkrétní používám ve své hře
$terenDlazba=floatval($_POST["t2"]);    //dlažba
$terenSnih=floatval($_POST["t3"]);      //sníh/led
$terenPisek=floatval($_POST["t4"]);     //písek
$terenHory=floatval($_POST["t5"]);      //kamení
$terenHlina=floatval($_POST["t6"]);     //hlína
$terenSul=floatval($_POST["t7"]);       //sůl
$terenTravaT9=floatval($_POST["t9"]);   //tráva(toxic)
$terenTravaT10=floatval($_POST["t10"]); //les
$terenReka=floatval($_POST["t11"]);     //řeka
$terenTravaT12=floatval($_POST["t12"]); //tráva(jaro)
$terenTravaT13=floatval($_POST["t13"]); //tráva(pozim)

//===================================Pokud hodnoty nejsou = přednastavené hodnoty

if(!$velikost){
    $velikost = 400;        //Rozměr výsledné mapy
    $voda = 40;             //% vody na mapě
    $zrnitostOstrova=0.8;   //Zrnitost pobřeží
    $delkaRek = 400;        //Délka všech řek zadaná jako % z rozměru mapy
    $tocivostRek = 22;      //Maximální změna úhlu řeky za 1 'krok'
    $centralitaRek = 60;    //Velikost oblasti (čtverce) uprostřed mapy jako % z rozměru mapy, kde budou začínat řeky
    $zrnitostOstatni=1;     //Zrnitost všech ostatních trerénů, kromě samotného ostrova

    $terenDlazba=5;//dlažba
    $terenSnih=20;//sníh/led
    $terenPisek=5;//písek
    $terenHory=20;//kamení
    $terenHlina=5;//hlína
    $terenSul=3;//sůl
    $terenTravaT9=30;//tráva(toxic)
    $terenTravaT10=30;//les
    $terenReka=5;//řeka
    $terenTravaT12=80;//tráva(jaro)
    $terenTravaT13=90;//tráva(pozim)
}

//===================================Doúprava hodnot

if($voda>80)$voda=80;
if($velikost>1000)$velikost=1000;
if($zrnitostOstrova<0.5)$zrnitostOstrova=0.5;
if($zrnitostOstatni<0.5)$zrnitostOstatni=0.5;

//======================================================================================================================Generátor

//Stačí odkomentovat ' or true', pokud je potřeba generovat mapu i bez odeslání formuláře
if($_POST["velikost"]/* or true*/){

    //=========================================================================Generování mapy
    //===================================Příprava prázdné mapy (pouze voda) - terén 1


    for($y=1;$y<=$velikost;$y++)
        for($x=1;$x<=$velikost;$x++)
            $mapa[$x][$y] = 1;

    //===================================Ostrov - terén 8

    $pevnina = 100-$voda;

    /*Ostrov se generuje pomocí náhodně kmitacícího bodu. Pozice bodu se na začátku náhodně vygererují. Jakmile bod narazí na okraj mapy, jeho pozice se vygeneruje znovu.*/
    $q = ($velikost*$velikost)*($pevnina/100);//Počet políček ostrova na základě % pevniny
    $x = rand(1,$velikost);//Prvotní pozice
    $y = rand(1,$velikost);
    while($q > 0){

        //Nastavím pevninu a snížím počet, pokud právě nejsem na pevnině:
        if($mapa[round($x)][round($y)] != 8){
                $mapa[round($x)][round($y)] = 8;
                $q--;
        }

        //Náhodná zmena pozice, čím větší zrnitost (Ostrova) tím více
        $x += rand(0,$zrnitostOstrova*200)/100 -$zrnitostOstrova;
        $y += rand(0,$zrnitostOstrova*200)/100 -$zrnitostOstrova;

        //Pokud jsem mimo mapy, vynuluju
        if($x > $velikost-1 or $x < 1 or $y > $velikost-1 or $y < 1){
            $x = rand(1,$velikost);//Prvotní pozice
            $y = rand(1,$velikost);
        }

    }

    //===================================Řeky - terén 11
    
    $q = ($delkaRek*$velikost)/100;//Počet políček řeky na základě délky řek
    $u = $centralitaRek/100/2;
    while($q > 0){
        
        $x = rand(intval($velikost*(0.5-$u)),intval($velikost*(0.5+$u)));//Náhodná prvotní pozice na základě centrality řek
        $y = rand(intval($velikost*(0.5-$u)),intval($velikost*(0.5+$u)));
        
        $uhel = rand(1,360);//Náhodný směr
        $px = cos($uhel/180*pi())/1.41;//x, y daného směru
        $py = sin($uhel/180*pi())/1.41;

        //Kreslím řeku dokud jsem na pevnině a zároveň na mapě
        while($mapa[round($x)][round($y)] != 1/*moře*/ and !($x > $velikost-1 or $x < 1 or $y > $velikost-1 or $y < 1) ){

            $q = $q - 1;

            $mapa[round($x)][round($y)] = 11;


            $px = cos($uhel/180*pi())/1.41;//Přepočet směru
            $py = sin($uhel/180*pi())/1.41;
            $x = $x+$px;//Posun pozice
            $y = $y+$py;
            
            $uhel=$uhel+rand(0,$tocivostRek*2) -$tocivostRek;//Modifikace úhlu podle točivosti řek
        }
    }

    //===================================Zbytek terénů

    $tereny=array(
        array($terenDlazba, 2 ),
        array($terenSnih, 3 ),
        array($terenPisek, 4 ),
        array($terenHory, 5 ),
        array($terenHlina, 6 ),
        array($terenSul, 7 ),
        array($terenTravaT9, 9 ),
        array($terenTravaT10, 10 ),
        array($terenReka, 11 ),
        array($terenTravaT12, 12 ),
        array($terenTravaT13, 13 )
    );

    shuffle($tereny);

    //Procházení jednotlivých terénů a jejich % zastoupení
    foreach($tereny as $procerntoTeren){

        list($procento,$teren)=$procerntoTeren;


        /*Jednotlivé terény pracují obdobně jako generování ostrova.*/

        $q = intval($velikost*$velikost)*($pevnina/100)*($procento/100);//Počet políček daného terénu


        $x = rand(1,$velikost);//Prvotní pozice
        $y = rand(1,$velikost);
        while($q > 0){
    
            if($mapa[round($x)][round($y)] == 1 or $mapa[round($x)][round($y)] == 11){//Narážím na řeku či moře

                $x = rand(1,$velikost);//Nová pozice
                $y = rand(1,$velikost);

            }else{

                $mapa[round($x)][round($y)] = $teren;

                //Náhodná zmena pozice, čím větší zrnitost (Ostatní) tím více
                $x = $x + rand(0,$zrnitostOstatni*200)/100 -$zrnitostOstatni;
                $y = $y + rand(0,$zrnitostOstatni*200)/100 -$zrnitostOstatni;


                //Pokud jsem mimo mapy, vynuluju.
                //Oproti generování ostrova nuluju pouze jednu souřadnici - vytváří to podobné terény na podobných 'rovnobězkách' a 'polednících'
                if($x > $velikost-1)    $x = rand(1,$velikost);
                if($x < 1)              $x = rand(1,$velikost);
                if($y > $velikost-1)    $y = rand(1,$velikost);
                if($y < 1)              $y = rand(1,$velikost);

            }

            $q = $q - 1;//Oproti generování ostrova odečítám vždy, abych zajistil větší nahodilost podílu daného terénu.

        }
    
    }

    //=========================================================================Výroba obrázku
    //===================================-Vytvoření obrázku
    
    $im=imagecreate($velikost,$velikost);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefill($im,0,0,$black);

    //===================================-Vykreslení terénů

    $colors=array();
    $y=0;
    foreach($mapa as $row){
        $x=0;
        foreach($row as $p){

                if($p == 1){ $color = "5299F9"; }//moře
            elseif($p == 2){ $color = "545454"; }//dlažba
            elseif($p == 3){ $color = "EFF7FB"; }//sníh/led
            elseif($p == 4){ $color = "F9F98D"; }//písek
            elseif($p == 5){ $color = "878787"; }//kamení
            elseif($p == 6){ $color = "5A2F00"; }//hlína
            elseif($p == 7){ $color = "DCDCAC"; }//sůl
            elseif($p == 8){ $color = "2A7302"; }//tráva(normal)
            elseif($p == 9){ $color = "51F311"; }//tráva(toxic)
            elseif($p == 10){ $color = "535805"; }//les
            elseif($p == 11){ $color = "337EFA"; }//řeka
            elseif($p == 12){ $color = "8ABC02"; }//tráva(jaro)
            elseif($p == 12){ $color = "8A9002"; }//tráva(pozim)



            if(!isset($colors[$p])){

                $red=hexdec(substr($color,0,2));
                $green=hexdec(substr($color,2,2));
                $blue=hexdec(substr($color,4,2));
                if($red>255){$red=255;}if($red<1){$red=1;}
                if($green>255){$green=255;}if($green<1){$green=1;}
                if($blue>255){$blue=255;}if($blue<1){$blue=1;}
                $colors[$p] = imagecolorallocate($im,$red,$green,$blue);

            }
            imagesetpixel($im,$x,$y,$colors[$p]);

            $x++;
        }

        $y++;
    }

    //===================================-Výroba obrázku .png

    ob_start();
    imagepng($im);
    $src=ob_get_contents();
    ob_end_clean();
    $src='data:image/png;base64,'.base64_encode($src);
    
    imagedestroy($im);

    //=========================================================================

}else{//Pokud nebyl odeslán formulář

	$src='default.png';

}

//======================================================================================================================HTML Stránka

?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" /> 
	<title>Generátor map</title>
	<style type="text/css">
        body {
            background-color: #FFFFFF;

        }
        body,td,th {
            color: #000000;
            font-size: 14px;
            font-family: "trebuchet ms";
        }
        h1{
            font-size: 25px;
        }
        a:link {
            color: #5555dd;
        }
        a:visited {
            color: #5555dd;
        }
        a:hover {
            color: #5555dd;
        }
        a:active {
            color: #5555dd;
        }
        .tabulka_hodnot{
            border-spacing: 8px;
        }
        .tabulka_hodnot tr td {
            text-align:left;
            font-weight: 600;
            height:25px;
        }
        input[type="text"] {
            width:55px;
        }
        input[type="submit"] {
            display:inline-block;
            font-weight:bold;
            font-size:22px;
            color:#000000;
            background:#cccccc;
            border: 2px solid #444444;
        }
        .mapa {
            width: <?=intval($velikost)?>px;
            border: 2px solid #444444;
            box-shadow: 0px 0px 4px #111111;
        }

        .teren {
            width:25px;
            height:25px;
            box-shadow: 0px 0px 6px #000000;
        }
	</style>

	<script type="text/javascript">

	 var _gaq = _gaq || [];
	 _gaq.push(['_setAccount', 'UA-16346522-9']);
	 _gaq.push(['_trackPageview']);

	 (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	 })();

	</script>


</head>
<body>

<div style="text-align:center;">

<h1>Generátor map</h1>

<table align="center">
<tr>
<td>

<?php
//======================================================================================================================Tabulka hodnot
?>

    <form method="post" action="?">

        <table class="tabulka_hodnot">

            <tr><td width="25"></td>
            <td>Velikost:</td>
            <td><input name="velikost" type="text" value="<?=$velikost?>" /></td></tr>

            <tr><td bgcolor="#5299F9" class="teren" ></td>
            <td>Voda [%]:</td>
            <td><input name="voda" type="text" value="<?=$voda?>" /></td></tr>

            <tr><td></td>
            <td>Zrnitost pobřeží:</td>
            <td><input name="zrnitostostrova" type="text" value="<?=$zrnitostOstrova?>" /></td></tr>

            <tr><td ></td>
            <td>Zrnitost ostatní:</td>
            <td><input name="zrnitostostatni" type="text" value="<?=$zrnitostOstatni?>" /></td></tr>


            <tr><td bgcolor="#337EFA" class="teren" ></td>
            <td>Délka řek [%]:</td>
            <td><input name="delkarek" type="text" value="<?=$delkaRek?>" /></td></tr>

            <tr><td bgcolor="#337EFA" class="teren" ></td>
            <td>Točivost řek [°]:</td>
            <td><input name="tocivostrek" type="text" value="<?=$tocivostRek?>" /></td></tr>

            <tr><td bgcolor="#337EFA" class="teren" ></td>
            <td>Centralita řek [%]:</td>
            <td><input name="centralitarek" type="text" value="<?=$centralitaRek?>" /></td></tr>


            <tr><td bgcolor="#545454" class="teren" ></td>
            <td>Dlažba [%]:</td>
            <td><input name="t2" type="text" value="<?=$terenDlazba?>" /></td></tr>


            <tr><td bgcolor="#EFF7FB" class="teren" ></td>
            <td>Sníh / led [%]:</td>
            <td><input name="t3" type="text" value="<?=$terenSnih?>" /></td></tr>

            <tr><td bgcolor="#F9F98D" class="teren" ></td>
            <td>Písek [%]:</td>
            <td><input name="t4" type="text" value="<?=$terenPisek?>" /></td></tr>

            <tr><td bgcolor="#878787" class="teren" ></td>
            <td>Kamení [%]:</td>
            <td><input name="t5" type="text" value="<?=$terenHory?>" /></td></tr>

            <tr><td bgcolor="#5A2F00" class="teren" ></td>
            <td>Hlína [%]:</td>
            <td><input name="t6" type="text" value="<?=$terenHlina?>" /></td></tr>

            <tr><td bgcolor="#DCDCAC" class="teren" ></td>
            <td>Solná poušť [%]:</td>
            <td><input name="t7" type="text" value="<?=$terenSul?>" /></td></tr>

            <tr><td bgcolor="#51F311" class="teren" ></td>
            <td>Tráva(typ toxic) [%]:</td>
            <td><input name="t9" type="text"  value="<?=$terenTravaT9?>" /></td></tr>

            <tr><td bgcolor="#535805" class="teren" ></td>
            <td>Lesy [%]:</td>
            <td><input name="t10" type="text"  value="<?=$terenTravaT10?>" /></td></tr>

            <tr><td bgcolor="#337EFA" class="teren" width="25"></td>
            <td>Jezera [%]:</td>
            <td><input name="t11" type="text" value="<?=$terenReka?>" /></td></tr>

            <tr><td bgcolor="#8ABC02" class="teren" ></td>
            <td>Tráva (typ jaro) [%]:</td>
            <td><input name="t12" type="text"  value="<?=$terenTravaT12?>" /></td></tr>

            <tr><td bgcolor="#8A9002" class="teren" ></td>
            <td>Tráva(typ podzim) [%]:</td>
            <td><input name="t13" type="text"  value="<?=$terenTravaT13?>" /></td></tr>


        </table>
        <br>
        <input type="submit" name="submit" value="Vygenerovat" />

    </form>

<?php
//======================================================================================================================Zobrazení mapy, Copy, Konec
?>


</td><td>
<img src="<?=$src?>" class="mapa"/>


</td></tr>
</table>

<br>
<?='&copy;&nbsp;Pavol Hejný&nbsp;|&nbsp;<a href="http://towns.cz" target="_blank">Towns.cz</a>&nbsp;|&nbsp;'.date('Y')?>


</div>


</body>
</html>


