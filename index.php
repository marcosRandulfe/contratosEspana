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
require __DIR__.'/vendor/autoload.php';

    use Goutte\Client;
    use Symfony\Component\HttpClient\HttpClient;
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\Remote\WebDriverCapabilityType;
    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;
    use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
    use Box\Spout\Common\Entity\Row;
   
    define("FICHERO", __DIR__.'/contratosEspana.ods');
    
     function renameExistingFile($filename){
                $oldname=$filename;
                $increment = 0;
                $name=pathinfo($filename, PATHINFO_FILENAME);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                while(file_exists($filename)) {
                    $increment++;
                    $loc = $name. $increment . '.' . $ext;
                    $filename = $name. $increment . '.' . $ext;
                }
                rename($oldname,$filename);
        }     
        
        function delete_olders($inicio){
            // 1 mes -> 2595600
            $ficheros= scandir('.');
            for ($i=0;$i<count($ficheros);$i++){
                if (preg_match('/('.$inicio.')\d+.*/', $ficheros[$i])) {
                     $tiempo=time()-filectime($ficheros[$i]);
                     if($tiempo> 2595600){
                         unlink($ficheros[$i]);
                    }
                }
            } 
        }

    
    // -----------------------------------------------------------------------------------
    // ShareWithUser
    // -----------------------------------------------------------------------------------
    function addShared($service, $fileId, $userEmail, $role ){
        // role can be reader, writer, etc
        $userPermission = new Google_Service_Drive_Permission(array(
            'type' => 'user',
            'role' => $role,
            'emailAddress' => $userEmail
        ));
        
        $request = $service->permissions->create(
            $fileId, $userPermission, array('fields' => 'id')
        );
    }
    //Subir Fichero
    $googleClient = new Google_Client();
    // Get your credentials from the console
   /* $googleClient->setClientId('39513400380-8cu6edlhs67k42t695i4bgscj5h7jar9.apps.googleusercontent.com');
    $googleClient->setClientSecret('eGxFx-IXrTGdahAJ9VR253H1');
    $googleClient->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
    $googleClient->setScopes(array('https://www.googleapis.com/auth/drive.file'));
     */   
    
    putenv('GOOGLE_APPLICATION_CREDENTIALS='.__DIR__.'/contratosEspana.json');
    
    $googleClient = new \Google\Client();
    $googleClient->useApplicationDefaultCredentials();
    $googleClient->addScope(Google_Service_Drive::DRIVE);
    //if (isset($_GET['code']) || (isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
       
    if(file_exists(FICHERO)){
        //unlink(FICHERO);
        renameExistingFile(FICHERO);
        $nombre=pathinfo(FICHERO,PATHINFO_FILENAME);
        delete_olders($nombre);
    }
    
    
    
    
    
    $url_atom ='https://contrataciondelestado.es/sindicacion/sindicacion_643/licitacionesPerfilesContratanteCompleto3.atom';
    $GLOBALS['link_siguiente']="";
    $in_entry=false;
    $link_contrato_actual='';
    $in_cp=false;
    $cp=false;
    $GLOBALS['writer'] = WriterEntityFactory::createODSWriter();
    $GLOBALS['writer']->openToFile(__DIR__.'/contratosEspana.ods');
    $client = new Client();
    $host = 'http://cactusweb.ddns.net:4444/wd/hub';
    $desiredCapabilities = new DesiredCapabilities(array(
        WebDriverCapabilityType::BROWSER_NAME => "firefox",
     ));
    
   $cabecera= ["Número Expediente",
    "Localización orgánica",
    "Órgano de Contratación",
    "Estado de la Licitación",
    "Objeto del contrato",
    "Presupuesto base de licitación sin impuestos",
    "Valor estimado del contrato",
    "Tipo de Contrato", 
    "Código CPV",
    "Lugar de Ejecución",
    "Procedimiento de contratación",	
    "Fecha fin de presentación de oferta",
    "Resultado",
    "Adjudicatario",
    "Nº de Licitadores Presentados",
    "Importe de Adjudicación"];
    $GLOBALS['writer']->addRow(WriterEntityFactory::createRowFromArray($cabecera));
    $desiredCapabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED,true);
    // Firefox
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox(),40*1000,40*1000);
try{
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
              
            }
            $spansRecorridos++;
        }
        return $valores;
    }

    function obtenerDatos($url){
        try{
            $driver = $GLOBALS['driver'];
            $datos=[];
            $driver->get(stripslashes($url));
            $numExpediente=$driver->findElement(WebDriverBy::xpath("//span[text()='Expediente:']/following-sibling::span"));
            $datos[]=$numExpediente->getText();
            $localizacion = $numExpediente->findElement(WebDriverBy::xpath('../following-sibling::li/span'));
            $datos[]=$localizacion->getText();
            $spans = $localizacion->findElements(WebDriverBy::xpath('../../following-sibling::div//li//span'));
            //echo "<h2>Recorrer spans</h2>";
            //echo(var_dump(recorrerSpans($spans)));
            //echo "<h3>Ver datos</h3>";
            $datos = array_merge($datos, recorrerSpans($spans));
            //echo(var_dump($datos));
            $spans = $localizacion->findElements(WebDriverBy::xpath("//fieldset[@id='InformacionLicitacionVIS_UOE']/div//span"));
            if (count($spans)>2 && count($spans)<10) {
                $datos[]="";
            }
            $datos = array_merge($datos, recorrerSpans($spans));
            //echo "<p>Array de datos</p>";
            //var_dump($datos);
            return $datos;
        }catch(Exception $e){
            //echo $e;
            return [];
        }
        
    }

    function escribirDatos($datos){
        //echo "<p>Datos</p>";
        $row = WriterEntityFactory::createRowFromArray($datos);
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
       if(array_key_exists("REL", $attrs) && $attrs['REL']=='next'){
           $GLOBALS['link_siguiente']= $attrs['HREF'];
       }
       switch ($name){
           case 'ENTRY':
                $GLOBALS['in_entry']=true;
                break;
           case 'LINK':
               if($GLOBALS['in_entry']){
                   $GLOBALS['link_contrato_actual']=$attrs['HREF'];
               }
               break;               
           case 'CBC:POSTALZONE':
               $GLOBALS['in_cp']=true;
               break;  
            }
   }
   
   // Called to this function when tags are closed 
   function endElements($parser, $name) {
       switch ($name){
           case 'ENTRY':
               $GLOBALS['in_entry']=false;
               break;
           case 'CBC:POSTALZONE':
               $GLOBALS['in_cp']=false;
               break;
               
               
       }
       flush();
   }
   
   // Called on the text between the start and end of the tags
   function characterData($parser, $data) {
       if($GLOBALS['in_cp']){
           //echo "<p>Codigo postal: ".$data."</p>";
           $cp=$data;
           //echo "<p>Comprobacion del codigo postal en galicia</p>";
           if(comporbarCp($cp)){
               $datos = obtenerDatos($GLOBALS['link_contrato_actual']);
               escribirDatos($datos);
           }
       }
       if($GLOBALS['in_entry'] ){
           //echo "<p> Url contrato</p>";
           //echo "<p>".$GLOBALS['link_contrato_actual']."</p>";
           //$datos=obtenerDatos($GLOBALS['link_contrato_actual']);
           //escribirDatos($datos);
       }
   }
   /*
    * Maximo de hojas que se pueden meter en el ods 2000
    * 
    */
   $num_consultas=1;
   $num_max_consultas=10;
    do{
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
         $num_consultas++;
         $url_atom=$GLOBALS['link_siguiente'];
         //echo "<p><i>Número de páginas recorridas:".$num_consultas."</i></p>";
         //echo "<p>Link siguiente en la página: ".$GLOBALS['link_siguiente']."</p>";
    }while($num_consultas<$num_max_consultas && $GLOBALS['link_siguiente']!=null && $GLOBALS['link_siguiente']!="");
} catch (Exception $e){
    //echo $e->getMessage();
    //var_dump($e);
}
$GLOBALS['writer']->close();

 $service = new Google_Service_Drive($googleClient);

            //Insert a file
            $file = new Google_Service_Drive_DriveFile();
            $file->setName(FICHERO);
            $file->setDescription('Contratos y licitaciones del estado');
            $file->setMimeType('application/vnd.oasis.opendocument.spreadsheet');
            $fileId=$file->getId();
            $data = file_get_contents(FICHERO);

            $createdFile = $service->files->create($file, array(
                'data' => $data,
                'mimeType' => 'application/vnd.oasis.opendocument.spreadsheet',
                'uploadType' => 'multipart'
            ));
           
            addShared($service,$createdFile->getId(), "jaime.barreiro.laredo@gmail.com", "writer");
            addShared($service,$createdFile->getId(), "marcosrandulfegarrido@gmail.com", "writer");
            



        