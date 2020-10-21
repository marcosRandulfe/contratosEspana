<?php

    //shell_exec('/home/marcos/geckodriver');

    require './vendor/autoload.php';
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\WebDriverBy;
    use Facebook\WebDriver\WebDriverExpectedCondition;

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

    //$elemento=$driver->findElement(WebDriverBy::id("myTablaBusquedaCustom"));
    var_dump(WebDriverBy::id('myTablaBusquedaCustom'));
    $driver->wait(10,100)->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('myTablaBusquedaCustom')));
    $elementos=$driver->findElements(WebDriverBy::xpath("//table[@id='myTablaBusquedaCustom']/tbody/tr/td[1]//a"));
    $num_elementos = (count($elementos));
    $num_elemento=0;
    //$elementos[0]->click();
    while($num_elemento<$num_elementos){
        $elementos[$num_elemento]->click();
        $driver->
        $num_elemento++;
        break;
    }
?>