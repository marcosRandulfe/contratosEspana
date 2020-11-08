<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * Url atom licitaciones del estado
 * https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom
 */

require './vendor/autoload.php';

    use Goutte\Client;
    use Symfony\Component\HttpClient\HttpClient;
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\Remote\WebDriverCapabilityType;
    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;
    use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
    use Box\Spout\Common\Entity\Row;

$url_atom ='https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom';
$link_siguiente="";
$in_entry=false;
$link_contrato_actual='';
$in_cp=false;
$cp=false;
$writer = WriterEntityFactory::createODSWriter();
$writer->openToFile('contratosEspana.ods');

$host = 'http://localhost:4444/wd/hub';
$desiredCapabilities = new DesiredCapabilities(array(
    WebDriverCapabilityType::BROWSER_NAME => "firefox",
 ));

$desiredCapabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED,true);
// Firefox
$driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());

function recorrerSpans($spans){
    $spansRecorridos = 0;
    $valores=[];
    while ($spansRecorridos < count($spans)) {
        $texto = $spans[$spansRecorridos]->getText();
        if ($texto == "Euros") {
            unset($spans[$spansRecorridos]);
            $spans = array_values($spans);
            $texto = $texto.'€';
            continue;
        }
        if ($spansRecorridos % 2 != 0) {
           $valores[]=$texto;
           //echo $texto;
        }
        $spansRecorridos++;
    }
    echo "</p>";
    echo "</div>";
    return $valores;
}

function obtenerDatos($url){
    $driver = $GLOBALS['driver'];
    $datos=[];
    $driver->get('https://contrataciondelestado.es/wps/portal/!ut/p/b0/04_Sj9CPykssy0xPLMnMz0vMAfIjU1JTC3Iy87KtUlJLEnNyUuNzMpMzSxKTgQr0w_Wj9KMyU1zLcvQjvfNd0pyrLJMLzCPygvNDIoyrVA3Myx1tbfULcnMdAb1CjvI!/');
    $numExpediente=$driver->findElement(WebDriverBy::xpath("//span[text()='Expediente:']/following-sibling::span"));
    $datos[]=$numExpediente;
    echo "<p>Número de expediente: ".$numExpediente->getText()."</p>";
    //Ubicacion orgánica
    $localizacion = $numExpediente->findElement(WebDriverBy::xpath('../following-sibling::li/span'));
    echo "<p>Localización: ".$localizacion->getText()."</p>";
    $datos[]=$localizacion;
    $spans = $localizacion->findElements(WebDriverBy::xpath('../../following-sibling::div//li//span'));
    $datos = array_merge($datos, recorrerSpans($spans));
    $spans = $localizacion->findElements(WebDriverBy::xpath("//fieldset[@id='InformacionLicitacionVIS_UOE']/div//span"));
    $datos = array_merge($datos, recorrerSpans($spans));
    return $datos;
}

function escribirDatos($datos){
    $row = WriterEntityFactory::createRow($datos);
    $GLOBALS['writer']->addRow($row);
}


function comporbarCp($cp){
    $cp_galicia=[36, 15, 32, 27];
    if (in_array(substr($cp, 0,2),$cp_galicia)) {
        return true;
    }
    return false;
}

   //Reading XML using the SAX(Simple API for XML) parser 
 
   // Called to this function when tags are opened 
   function startElements($parser, $name, $attrs) {
       var_dump($attrs);
       if(array_key_exists("rel", $attrs)){
           $link_siguiente= $attrs['rel'];
       }
       switch ($name){
           case 'entry':
                $GLOBALS['in_entry']=true;
                break;
           case 'link':
               $GLOBALS['link_contrato_actual']=$attrs['href'];
               if($GLOBALS['in_entry']){
                    $crawler = $client->request('GET', $link_contrato_actual);
                    $numExpediente = $crawler->filterXPath("//span[text()='Expediente:']/following-sibling::span");
                    echo "<p>Número de expediente: ".$numExpediente->text()."</p>";
                    break;
                    $localizacion=$crawler->filterXpath("../following-sibling::li/span");
                    echo "<p>Localización: ".$localizacion->text()."</p>";
               }
               break;               
           case 'cbc:PostalZone':
               $GLOBALS['in_cp']=true;
               break;
               
            }
       
   }
   
   // Called to this function when tags are closed 
   function endElements($parser, $name) {
       switch ($name){
           case 'entry':
               $GLOBALS['in_entry']=false;
               break;
           case 'cbc:PostalZone':
               $GLOBALS['in_cp']=false;
               break;
               
       }
   }
   
   // Called on the text between the start and end of the tags
   function characterData($parser, $data) {
       if($GLOBALS['in_cp']){
           echo "<p>Codigo postal: ".$data."</p>";
           $GLOBALS['cp']=$data;
       }
       if($GLOBALS['in_entry'] && comporbarCp($GLOBALS['cp'])){
           $datos=obtenerDatos($GLOBALS['link_contrato_actual']);
           escribirDatos($datos);
       }
   }
   
   // Creates a new XML parser and returns a resource handle referencing it to be used by the other XML functions. 
   $parser = xml_parser_create(); 
   
   xml_set_element_handler($parser, "startElements", "endElements");
   xml_set_character_data_handler($parser, "characterData");
   
   // open xml file
   if (!($handle = fopen($url_atom, "r"))) {
      die("could not open XML input");
   }
   
   while($data = fread($handle, 4096)){
      xml_parse($parser, $data);  // start parsing an xml document 
   }
   
   xml_parser_free($parser); // deletes the parser
   $i = 1;
   
   $writer->close();

