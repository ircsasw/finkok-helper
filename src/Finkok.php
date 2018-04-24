<?php
namespace ircsasw\finkok;

use SoapClient;
use SimpleXMLElement;
use Exception;

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
    /**
     *  @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $url;

    public $response;
    public $pre_stamped = false;

    /**
     * Arreglo de errores ocurridos
     * @var array
     */
    public $errors = [];

    function __construct($username, $password, $sandbox = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->url = $sandbox ? 'https://demo-facturacion.finkok.com/servicios/soap/' : 'https://facturacion.finkok.com/servicios/soap/';
    }

    /**
     * Define los datos de acceso al Web Service
     * @param string $username
     * @param string $password
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Timbra el archivo XML proporcionado
     * @param  string $xml ruta al archivo XML
     * @return boolean     si es timbrado o no
     */
    public function timbrar($xml = null)
    {
        if (is_null($xml)) {
            $this->errors = [
                'Falta parámetro XML.'
            ];

            return false;
        }

        if (is_null($this->username) || is_null($this->password)) {
            $this->errors = [
                'Defina credenciales del Web Service.'
            ];

            return false;
        }

        // Lee el archivo XML
        $partes_ruta = pathinfo($xml);
        $invoice_path = $xml;
        $xml_file = fopen($invoice_path, "rb");
        $xml_content = fread($xml_file, filesize($invoice_path));
        fclose($xml_file);

        $soap = new SoapClient("{$this->url}stamp.wsdl", [
            'trace' => 1
        ]);

        $response = $soap->__soapCall('stamp', [
            [
                "xml" => $xml_content,
                "username" => $this->username,
                "password" => $this->password
            ]
        ]);

        // Genera archivo .xml con el Request del web service
        $soapRequest = $partes_ruta['dirname'] . DIRECTORY_SEPARATOR . 'SoapRequest.xml';
        $file = fopen($soapRequest, "a");
        fwrite($file, $soap->__getLastRequest() . "\n");
        fclose($file);

        // Generación de archivo .xml con el Response del web service
        $soapResponse = $partes_ruta['dirname'] . DIRECTORY_SEPARATOR . 'SoapResponse.xml';
        $file = fopen($soapResponse, "a");
        fwrite($file, $soap->__getLastResponse() . "\n");
        fclose($file);

        if (property_exists($response->stampResult->Incidencias, 'Incidencia') && $response->stampResult->Incidencias->Incidencia->CodigoError == 307) {
            $this->response = $response->stampResult;
            $this->pre_stamped = true;

            return true;
        }

        if (property_exists($response->stampResult->Incidencias, 'Incidencia') && !empty($response->stampResult->Incidencias)) {
            if (is_array($response->stampResult->Incidencias)) {
                foreach ($response->stampResult->Incidencias->Incidencia->MensajeIncidencia as $error) {
                    array_push($this->errors, $error->MensajeIncidencia);
                }
            } else {
                $this->errors = [
                    "message" => "Respuesta PAC " . $response->stampResult->Incidencias->Incidencia->MensajeIncidencia
                ];
            }
            $this->response = $xml;

            return false;
        }

        $this->response = $response->stampResult;
        return true;
    }
}
