<?php
class RegistroUsuarioEcomerce
{
    //TODO: mirar de sacar este valor por un fichero .env o similar
    public static $API_KEY = "810f0adf11b2c3eea024aeebfbc8c9b8";
    public static $BASE_URL = 'https://cetelem.crmlab.eu/api/v1/Account';
    /**
     * Registrar el usuario actual como usuario activo en Cetelem
     * @param string $nombre Nombre y apellido del usuario
     * @param string $email  Email del usuario
     * @return void
    */
    public static function registrarUsuarioEcomerceCetelem($nombre, $email)
    {        
        //Buscamos el usuario por su email
        $usuario = RegistroUsuarioEcomerce::checkUsuarioRegistrado($email);
        if($usuario !== false)
        {
            //se revisa si está activo, si es así, no hacemos nada
            // si no lo está, se marca como activo
            if($usuario->type == "Activo" || strtolower($usuario->type) == "activo")
            {                
                return;
            }
            //marcamos el usuario como activo            
            RegistroUsuarioEcomerce::modificarActivo($usuario->id);
            return;
        }
        //Es un nuevo usuario que se debe de registrar        
        //nueve digitos aleatorios + el "+34" para el prefijo de telefono
        $idUnico = "+34".substr(number_format(microtime(true) * 1000000, 0, '', ''), -9);

        //registramos el usuario como activo
        RegistroUsuarioEcomerce::registrarUsuarioActivo
        (
            new UsuarioEcomerce
            ([
                'name' => $nombre,
                'emailAddress' => $email,
                'description' => ''.date('Y-m-d H:i'),
                'phoneNumber' => $idUnico,
                'type' => 'Activo'
            ])
        );
        return false;
    }

    /**
     * Comprueba si el usuario con este email está ya registrado como usuario activo de Cetelem
     * Retorna el usuario en caso de que esté registrado o false en caso contrario
     * @param string $email Email del usuario
     * @return UsuarioEcomerce||bool Los datos del usuario en tipo UsuarioEcomerce del primer registro que encuentre o false si no está registrado
    */
    private static function checkUsuarioRegistrado($email)
    {
        try
        {
            //iniciamos el cURL para enviar los datos a la API            
            $params = 
            [
                //'select' => 'name,sicCode,emailAddressIsOptedOut,emailAddress,emailAddressData,phoneNumberIsOptedOut,phoneNumber,phoneNumberData,billingAddressCountry,createdAt,assignedUserId,assignedUserName',
                'select' => 'name,type,emailAddress,phoneNumber,assignedUserId,assignedUserName,description',
                'maxSize' => 20,
                'offset' => 0,
                'orderBy' => 'createdAt',
                'order' => 'desc',
                'where[0][type]' => 'startsWith',
                'where[0][attribute]' => 'emailAddress',
                'where[0][value]' => $email,
            ];
            //montamos la url completa
            $url = RegistroUsuarioEcomerce::$BASE_URL . '?' . http_build_query($params);            
            $curl = curl_init();
            //Configuramos el cURL
            curl_setopt_array($curl, array
            (
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 50,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array
                (
                    'accept: application/json, text/javascript, */*; q=0.01',
                    'X-Api-Key: '.RegistroUsuarioEcomerce::$API_KEY
                ),
            ));
            //guardamos la respuesta del cURL
            $response = curl_exec($curl);
            
            //se tiene que decodificar la respuesta (json_decode) y ver si tiene datos
            $data = json_decode($response, true);
            //si podemos sacar total, implica que se ha realizado la llamada OK 
            // y que nos ha devuelto algo la API
            if(isset($data['total']))
            {
                curl_close($curl);
                /*
                    Deberiamos de tener un nodo $data['list'][0]
                    Desde este nodo, debemos de coger el primer registro
                    y mapearlo a un objeto UsuarioEcomerce, para tratarlo más facilmente
                */                
                
                //SI NO HAY REGISTROS, SALIMOS
                if($data['total'] == 0)
                {
                    //no hay registros
                    return false;
                }
                //devolvemos el primer usuario que haya encontrado
                return new UsuarioEcomerce($data);
            }
        }
        catch(Exception $e)
        {
            //No hacemos nada
            // No interesa gestionar este proceso
            //echo("ERROR<br>");
            //var_dump($e->getMessage());
        }
        curl_close($curl);
        return false;
    }

    /**
     * Modifica el usuario para que esté como activo / no activo junto a la fecha de modificación
     * @param string $idUsuario Id del usuario en Cetelem
     * @return void
    */
    private static function modificarActivo($idUsuario)
    {
        //si $idUsuario no es valido, salimos
        if($idUsuario == null || $idUsuario == "" || !$idUsuario)
        {
            return;
        }
        //iniciamos el cURL para enviar los datos a la API        
        try
        {
            // URL final con el ID al final
            $url = RegistroUsuarioEcomerce::$BASE_URL . '/' . urlencode($idUsuario);            

            // Headers personalizados
            $headers = 
            [
                'x-api-key: '.RegistroUsuarioEcomerce::$API_KEY.'',
                'Content-Type: application/json'
            ];

            // Datos en formato JSON
            $jsonData = json_encode
            ([
                'type' => 'Activo',
                'description' => date('Y-m-d H:i')
            ]);

            // Initialize cURL session
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
             curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Para capturar respuesta
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);            
            curl_exec($ch);            
        }
        catch(Exception $e)
        {            
            // echo("ERROR<br>");
            // var_dump($e->getMessage());
        }
        curl_close($ch);
    }
    /**
     * Registra un nuevo usuario como activo de Cetelem en la BD
     * @param UsuarioEcomerce $Usuario Datos del usuario a registrar
     * @return void
    */
    private static function registrarUsuarioActivo($usuario)
    {        
        //iniciamos el cURL para enviar los datos a la API
        try
        {
            //Pasamos los campos para el nuevo usuario
            //en la descripcion ponemos la fecha de creación
            //Los codificamos en JSON            
            $fields = json_encode
            ([
                'name' => $usuario->name, 
                'emailAddress' => $usuario->emailAddress, 
                'phoneNumber' => $usuario->phoneNumber, 
                'type' => $usuario->type,
                'description' => $usuario->descripction
            ]);

            //Cabeceras para poder hacer la llamada
            $headers = 
            [
                'x-api-key: '.RegistroUsuarioEcomerce::$API_KEY.'',
                'Content-Type: application/json'
            ];
            //iniciamos el cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, RegistroUsuarioEcomerce::$BASE_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
        }
        catch(Exception $e)
        {
            //No hacemos nada
            // No interesa gestionar este proceso
            // echo("ERROR<br>");
            // var_dump($e->getMessage());
        }
        curl_close($ch);
    }
}
//Clase para mapear los datos que nos retorna la API
class UsuarioEcomerce
{    
    public $id ="";
    public $name ="";
    public $emailAddress ="";
    public $phoneNumber ="";
    public $type ="";//Indica si esta o no activo
    public $descripction ="";
    public $createdById ="";
    public $assignedUserId ="";
    public $assignedUserName ="";

    /** Constructor que mapea los datos del array recibido a las propiedades de la clase
     * Deberia conteneer un array list con uno o mas usuarios, nos quedaremos con el primero     
     * @param array $data Array asociativo con los datos del usuario
     * 
    */
    public function __construct($data)
    {
        try
        {        
            //Miramos si nos han pasado un array con varios usuarios o solo uno
            //y nos quedamos con el primero en caso de que sea un array de varios
            $usuario = isset($data['list'][0])? $data['list'][0] : $data;

            /*
            * asignamos los valores a las propiedades
            * usamos isset para evitar warnings en caso de que no venga algun campo
            * y trim para limpiar espacios en blanco
            * si no viene el campo, lo dejamos a ""
            */
            $this->id = isset($usuario['id']) ? trim($usuario['id']) : "";
            $this->name = isset($usuario['name']) ? trim($usuario['name']) : "";
            $this->emailAddress = isset($usuario['emailAddress']) ? trim($usuario['emailAddress']) : "";
            $this->phoneNumber = isset($usuario['phoneNumber']) ? trim($usuario['phoneNumber']) : "";
            $this->type = isset($usuario['type']) ? trim($usuario['type']) : "";
            $this->descripction = isset($usuario['description']) ? trim($usuario['description']) : "";
            $this->createdById = isset($usuario['createdById']) ? trim($usuario['createdById']) : "";
            $this->assignedUserId = isset($usuario['assignedUserId']) ? trim($usuario['assignedUserId']) : "";
            $this->assignedUserName = isset($usuario['assignedUserName']) ? trim($usuario['assignedUserName']) : "";            
        }
        catch(Exception $e)
        {
            //Para estos casos, si hay un error, dejamos el objecto como esté y
            //los campos que no estén completados, quedarán como ""
            //No deberia de llegar nunca aquí
        }
    }
}
