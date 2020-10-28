<?php

require './vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\SheetView;
use PhpOffice\PhpSpreadsheet\Writter\Ods;

define('RUTA_ODS', 'contratosEspana.ods');

function recorrerSpans($spans) {
    echo "<div>";
    $spansRecorridos = 0;
    echo "<p>";
    while ($spansRecorridos < count($spans)) {
        $texto = $spans[$spansRecorridos]->getText();
        if ($texto == "Euros") {
            unset($spans[$spansRecorridos]);
            $spans = array_values($spans);
            $texto = $texto . '€';
            continue;
        }
        $spansRecorridos++;
        echo $texto;
        if ($spansRecorridos % 2 == 0) {
            echo "</p><p>";
        } else {
            echo " : ";
        }
    }
    echo "</p>";
    echo "</div>";
}

function recorrerSpansHoja($spans, $sheet, $letra, $fila) {
    $spansRecorridos = 0;
    while ($spansRecorridos < count($spans)) {
        $texto = $spans[$spansRecorridos]->getText();
        if ($texto == "Euros") {
            unset($spans[$spansRecorridos]);
            $spans = array_values($spans);
            $texto = $texto . '€';
            continue;
        }
        if ($spansRecorridos % 2 != 0) {
            $sheet->setCellValue($letra . $fila, $texto);
            echo "<p> Número de celda: " . $texto . "</p>";
            echo "<p>Texto recorrer snap hoja : " . $texto . "</p>";
            $letra++;
        }
        $spansRecorridos++;
    }
    echo "</p>";
    echo "</div>";
}

// Geckodriver
//$host = 'http://localhost:4444';
//Sellenium server
$host = 'http://localhost:4444/wd/hub';
$desiredCapabilities = new DesiredCapabilities(array(
    WebDriverCapabilityType::BROWSER_NAME => "firefox",
        ));
$desiredCapabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED, true);

// Firefox
$driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());

/**
 * Funcion que coloca el driver en la busqueda de Galicia
 * @param type $driver
 * @return type
 */
function searchGalicia($driver) {

    $driver->get('https://contrataciondelestado.es/wps/portal/licitaciones');
    $elemento = $driver->findElement(WebDriverBy::xpath("//span[text()='Licitaciones']"));
    $elemento->click();
    $elemento = $driver->findElement(WebDriverBy::xpath("//select/option[@value='ES']"));
    $elemento->click();
    $elemento = $driver->findElement(WebDriverBy::xpath("//span[text()='Seleccione demarcación territorial (NUTS)']"));
    $driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//span[text()='Seleccione demarcación territorial (NUTS)']")));
    $elemento->click();
    $elemento = $driver->findElement(WebDriverBy::xpath("//select/option[text()='ES11   Galicia']"));
    $driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//select/option[text()='ES11   Galicia']")));
    $elemento->click();
    $elemento = $driver->findElement(WebDriverBy::xpath("//input[@value='Aceptar']"));
    $elemento->click();
    //ES11   Galicia
    $elemento = $driver->findElement(WebDriverBy::xpath("//input[@value='Buscar']"));
    $elemento->click();
    $driver->wait(10, 100)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('myTablaBusquedaCustom')));
    return $driver;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$num_fila = 2;
$sheet->setCellValue("A1", 'Número de expediente');
$sheet->setCellValue("B1", "Ubicación organica");
$sheet->setCellValue("C1", "Órgano de Contratación");
$sheet->setCellValue("D1", "Estado de la Licitación");
$sheet->setCellValue("E1", "Objeto del contrato");
$sheet->setCellValue("D1", "Presupuesto base de licitación sin impuestos");
$sheet->setCellValue("F1", "Valor estimado del contrato");
$sheet->setCellValue("G1", "Tipo de Contrato");
$sheet->setCellValue("H1", "Código CPV");
$sheet->setCellValue("I1", "Lugar de Ejecución");
$sheet->setCellValue("J1", "Procedimiento de contratación");
$sheet->setCellValue("K1", "Fecha fin de presentación de oferta");

//$elementos[0]->click();
$fila = 2;
$siguiente_pagina=true;
$num_pagina=0;
while ($siguiente_pagina) {
    $elementos=[];
   /* try {
            $driver= searchGalicia($driver);
            $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='myTablaBusquedaCustom']/tbody/tr/td[1]//a"));
        } catch (Exception $ex) {
            echo "<p>Excepcion: ";
            echo $ex->getMessage();
            echo "</p>";
        }
    * 
    */
    $num_elementos = 0;
    $num_elemento = 0;
 
    echo '<h1 color="green">While superior</h1>';
    do{
        $driver = searchGalicia($driver);
        for($a=0;$a<$num_pagina;$a++){
            $btn_siguiente=$driver->findElement(WebDriverBy::xpath("//input[@title='Siguiente']"));
            $driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::xpath("//input[@title='Siguiente']")));
            $btn_siguiente->click();
        }
         try {
            $driver->wait(30, 200)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('myTablaBusquedaCustom')));
            $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='myTablaBusquedaCustom']/tbody/tr/td[1]//a"));
            $num_elementos= count($elementos);
        } catch (Exception $ex) {
            echo "<p>Excepcion: ";
            echo $ex->getMessage();
            echo "</p>";
        }
        echo "<p>Numero de elementos:".count($elementos)."<p>";
        $elementos[$num_elemento]->click();
        //Numero de expediente
        $driver->wait(50, 500)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath("//span[text()='Expediente:']/following-sibling::span")));
        $numExpediente = $driver->findElement(WebDriverBy::xpath("//span[text()='Expediente:']/following-sibling::span"));
        echo "<p>Número de expediente: " . $numExpediente->getText() . "</p>";
        //Ubicacion orgánica
        $sheet->setCellValue('A' . $fila, $numExpediente->getText());
        $localizacion = $numExpediente->findElement(WebDriverBy::xpath('../following-sibling::li/span'));
        $sheet->setCellValue('B' . $fila, $localizacion->getText());
        echo "<p>Localización: " . $localizacion->getText() . "</p>";
        $spans = $localizacion->findElements(
                WebDriverBy::xpath('../../following-sibling::div//li//span')
        );
        recorrerSpans($spans);
        recorrerSpansHoja($spans, $sheet, 'C', $fila);
        $spans = $localizacion->findElements(
                WebDriverBy::xpath("//fieldset[@id='InformacionLicitacionVIS_UOE']/div//span")
        );
        recorrerSpans($spans);
        recorrerSpansHoja($spans, $sheet, 'L', $fila);
        echo "<h2>Documentos</h2>";
        $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='myTablaDetalleVISUOE']/tbody/tr"));
        if (count($elementos) > 0) {
            echo "<h3>Resumen licitacion</h3>";
            foreach ($elementos as $elemento) {
                $tds = $elemento->findElements(WebDriverBy::xpath("./td"));
                //echo "<p>Número de elementos: ".count($tds)."</p>";
                $fecha = $tds[0]->findElement(WebDriverBy::xpath('./div'))->getText();
                $nombre = $tds[1]->findElement(WebDriverBy::xpath('./div'))->getText();
                try {
                    $documento = $tds[2]->findElement(WebDriverBy::xpath("./div/a[text()='Pdf']"));
                } catch (Exception $ex) {
                    
                }
                echo "<p>Fecha: " . $fecha . "</p>";
                echo "<p>Nombre documento: " . $nombre . "</p>";
                echo '<a  href="' . $documento->getAttribute('href') . '">Enlace al documento </a>';
            }
        }
        echo "<h3>Otros documentos</h3>";
        $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='datosDocumentosGenerales']//table//table/tbody/tr"));
        if (count($elementos) > 0) {
            foreach ($elementos as $elemento) {
                $tds = $elemento->findElements(WebDriverBy::xpath("./td"));
                echo "<p>Número de elementos: " . count($tds) . "</p>";
                $fecha = $tds[0]->findElement(WebDriverBy::xpath('./span'))->getText();
                $nombre = $tds[1]->findElement(WebDriverBy::xpath('./span'))->getText();
                $documento = $tds[2]->findElement(WebDriverBy::xpath("./a"));
                echo "<p>Fecha: " . $fecha . "</p>";
                echo "<p>Nombre documento: " . $nombre . "</p>";
                echo '<a  href="' . $documento->getAttribute('href') . '">Enlace al documento </a>';
            }
        }
        $num_elemento++;
        $fila++;
    }while($num_elemento < $num_elementos);
    echo "<p>Salto de página</p>";
    try{
        $btn_siguiente=$driver->findElement(WebDriverBy::xpath("//input[@title='Siguiente']"));
        $siguiente_pagina++;
        $num_elemento=0;
        
    } catch (Exception $e){
        $siguiente_pagina=false;
    }
}

$writter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($spreadsheet);
$writter->save(RUTA_ODS);
