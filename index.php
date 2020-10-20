<?php

    require './vendor/autoload.php';
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\WebDriverBy;

    // Geckodriver
    $host = 'http://localhost:4444';
    // Firefox
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
    $driver->get('https://contrataciondelestado.es/wps/portal/licitaciones');
    //span[text()='Licitaciones']/../..
    $elemento=$driver->findElement(WebDriverBy::xpath("//span[text()='Licitaciones']"));
    $elemento->click();
    $elemento=$driver->findElement(WebDriverBy::xpath("//select/option[@value='ES']"));
    $elemento->click();
    $elemento=$driver->findElement(WebDriverBy::xpath("//span[text()='Seleccione demarcación territorial (NUTS)']"));
    $elemento->click();    

    //$elemento= $driver->findElement(WebDriverBy::cssSelector("#capa_oculta"));
    //$elemento->clear();

    //input[@title='Buscar']
    $elemento=$driver->findElement(WebDriverBy::xpath("//select/option[text()='ES11   Galicia']"));
    $elemento->click();
    
    $elemento=$driver->findElement(WebDriverBy::xpath("//input[@value='Aceptar']"));
    $elemento->click();

    /*
        $elemento= $driver->findElement(WebDriverBy::cssSelector("#capa_oculta"));
        $elemento->clear();
    */
    //ES11   Galicia
    $elemento=$driver->findElement(WebDriverBy::xpath("//input[@value='Buscar']"));
    $elemento->click();

    $driver->wait()->until($driver->findElement(WebDriverBy::id("myTablaBusquedaCustom")));
    $elementos=$driver->findElements(WebDriverBy::xpath("//table[@id='myTablaBusquedaCustom']/tbody/tr/td[1]//a"));
    var_dump($elementos);

    $elemento->click();
    
    




?>