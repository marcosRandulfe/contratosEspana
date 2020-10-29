<?php

    require './vendor/autoload.php';
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\Remote\WebDriverCapabilityType;
    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

    // Geckodriver
    //$host = 'http://localhost:4444';
    //Sellenium server
    $host = 'http://localhost:4444/wd/hub';
    $desiredCapabilities = new DesiredCapabilities(array(
        WebDriverCapabilityType::BROWSER_NAME => "firefox",
    ));
    $desiredCapabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED,true);
    // Firefox
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
    //pagina contrato simple
    $driver->get('https://contrataciondelestado.es/wps/portal/!ut/p/b0/04_Sj9CPykssy0xPLMnMz0vMAfIjU1JTC3Iy87KtUlJLEnNyUuNzMpMzSxKTgQr0w_Wj9KMyU1zLcvQjvfNd0pyrLJMLzCPygvNDIoyrVA3Myx1tbfULcnMdAb1CjvI!/');
    $numExpediente=$driver->findElement(WebDriverBy::xpath("//span[text()='Expediente:']/following-sibling::span"));
    echo "<p>Número de expediente: ".$numExpediente->getText()."</p>";
    //Ubicacion orgánica
    $localizacion = $numExpediente->findElement(WebDriverBy::xpath('../following-sibling::li/span'));
    echo "<p>Localización: ".$localizacion->getText()."</p>";
    $spans = $localizacion->findElements(
        WebDriverBy::xpath('../../following-sibling::div//li//span'));
    function recorrerSpans($spans){
        echo "<div>";
        $spansRecorridos=0;
        echo "<p>";
        while($spansRecorridos < count($spans)){ 
            $texto = $spans[$spansRecorridos]->getText();
            if($texto=="Euros"){
                unset($spans[$spansRecorridos]);
                $spans=array_values($spans);
                $texto=$texto.'€';
                continue;
            }
            $spansRecorridos++;
            echo $texto;
            if($spansRecorridos%2==0){echo "</p><p>";}
            else{
                echo " : ";
            }
        }
        echo "</p>";
        echo "</div>";
    }
    recorrerSpans($spans);
    $spans = $localizacion->findElements(
        WebDriverBy::xpath("//fieldset[@id='InformacionLicitacionVIS_UOE']/div//span"));
    recorrerSpans($spans);
    echo "<h2>Documentos</h2>";
    $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='myTablaDetalleVISUOE']/tbody/tr"));
    if(count($elementos)>0){
        echo "<h3>Resumen licitacion</h3>";
        foreach($elementos as $elemento) {
           $tds = $elemento->findElements(WebDriverBy::xpath("./td"));
           //echo "<p>Número de elementos: ".count($tds)."</p>";
           $fecha=$tds[0]->findElement(WebDriverBy::xpath('./div'))->getText();
           $nombre=$tds[1]->findElement(WebDriverBy::xpath('./div'))->getText();
           $documento=$tds[2]->findElement(WebDriverBy::xpath("./div/a[text()='Pdf']"));
           echo "<p>Fecha: ".$fecha."</p>";
           echo "<p>Nombre documento: ".$nombre."</p>";
           echo '<a  href="'.$documento->getAttribute('href').'">Enlace al documento </a>';
        }
    }
    echo "<h3>Otros documentos</h3>";
    $elementos = $driver->findElements(WebDriverBy::xpath("//table[@id='datosDocumentosGenerales']//table//table/tbody/tr"));  
    if(count($elementos)>0){
        foreach($elementos as $elemento) {
           $tds = $elemento->findElements(WebDriverBy::xpath("./td"));
           echo "<p>Número de elementos: ".count($tds)."</p>";
           $fecha=$tds[0]->findElement(WebDriverBy::xpath('./span'))->getText();
           $nombre=$tds[1]->findElement(WebDriverBy::xpath('./span'))->getText();
           $documento=$tds[2]->findElement(WebDriverBy::xpath("./a"));
           echo "<p>Fecha: ".$fecha."</p>";
           echo "<p>Nombre documento: ".$nombre."</p>";
           echo '<a  href="'.$documento->getAttribute('href').'">Enlace al documento </a>';
        }
    }

?>