<?php
namespace ircsasw\finkok;

/**
*  Librería auxiliar para el uso del web service del PAC Fonkok
*
*  La librería provee de funciones auxiliares en el uso y consumo del web service
*  del PAC Finkok para timbrar, cancelar y consultar.
*
*  @author Arturo Ramos
*/
class Finkok
{
    private $username;
    private $password;
    private $url;

    function __construct($username, $password, $sandbox = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $sandbox ? 'https://demo-facturacion.finkok.com/servicios/soap/' : 'https://facturacion.finkok.com/servicios/soap/';
    }
}
