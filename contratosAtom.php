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
    use PhpOffice\PhpSpreadsheet\Reader\Ods;
    use PhpOffice\PhpSpreadsheet\Writer\Ods as Writter;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;


$url_atom ='https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom';
$link_siguiente="";
$in_entry=false;
$link_contrato_actual='';
$in_cp=false;
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
               $link_contrato_actual=$attrs['href'];
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
   

