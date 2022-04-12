<?php
namespace framework\environment;

use ErrorException;
use PDO;
use PDOException;

abstract class Environment
{
    
    /**
     *
     * @var string
     */
    static private $path = '';
    
    /**
     *
     * @var array
     */
    static private $appConfig = [];
    
    /**
     *
     * @param string $pathConfig
     * @throws ErrorException
     */
    static public function init( string $pathConfig )
    {
        /*
         * Recupera la ruta del archivo que invoca a Env::init().
         */
        $backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );
        
        self::$path = dirname( $backtrace[ 0 ][ 'file' ] );
        
        /*
         * Configuración la zona horaria del servidor.
         */
        date_default_timezone_set( 'America/Argentina/Buenos_Aires' );
        
        /*
         * Levanta el archivo de configuración de la aplicación
         */
        $appConfig;
        include_once self::path( $pathConfig );
        self::$appConfig = $appConfig;
        
        self::errorConfig();
    }
    
    /**
     * Configuracion de captura y salida de errores
     * @param array $appConfig
     */
    static private function errorConfig()
    {
        ini_set( 'log_errors', TRUE );
        ini_set( 'error_log', self::pathErrorsLog() );
        error_reporting( E_ALL );
    }
    
    /**
     *
     * @return string
     */
    static private function pathErrorsLog(): string
    {
        $path = '/errors-default.log';
        if( isset( self::$appConfig[ 'path-log' ] ) )
        {
            $path = self::$appConfig[ 'path-log' ];
        }
        return self::path( $path );
    }
    
    /**
     * Retorna una conexión a la base de datos.
     * @return PDO|NULL
     */
    static public function dbConnection(): ?PDO
    {
        $conn = NULL;
        if ( self::$appConfig[ 'db' ] )
        {
            $dns = self::$appConfig[ 'db' ][ 'dns' ];
            $username = self::$appConfig[ 'db' ][ 'username' ];
            $password = self::$appConfig[ 'db' ][ 'password' ];
            try
            {
                $conn = new PDO( $dns, $username, $password );
                $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
            catch ( PDOException $e )
            {
                self::console( $e->getMessage() );
            }
        }
        return $conexion;
    }
    
    /**
     *
     * @param string $extension
     * @return string
     */
    static public function path( ?string $extension ): string
    {
        if ( isset( $extension ) )
        {
            return self::$path.$extension;
        }
        return self::$path;
    }
    
    /**
     *
     * @param string $extension
     * @return string
     */
    static public function url( string $extension = NULL ): string
    {
        if ( isset( $extension ) )
        {
            return self::urlbase().$extension;
        }
        return self::urlbase();
    }
    
    /**
     * Esta función devuelve la URL completa, incluyendo al protocolo y el host en casos
     * donde `$_SERVER['HTTP_HOST']` no esté configurado o cuando se está detrás de un proxy.
     * @link https://es.stackoverflow.com/questions/49890/c%C3%B3mo-obtener-la-url-completa-en-php
     * @return string
     */
    static private function urlbase( $forwarded_host = false ) : string
    {
        $ssl   = !empty( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on';
        $proto = strtolower( $_SERVER[ 'SERVER_PROTOCOL' ] );
        $proto = substr( $proto, 0, strpos( $proto, '/' ) ) . ( $ssl ? 's' : '' );
        if  ($forwarded_host && isset( $_SERVER[ 'HTTP_X_FORWARDED_HOST' ] ) )
        {
            $host = $_SERVER[ 'HTTP_X_FORWARDED_HOST' ];
        }
        else
        {
            if ( isset( $_SERVER[ 'HTTP_HOST' ] ) )
            {
                $host = $_SERVER[ 'HTTP_HOST' ];
            }
            else
            {
                $port = $_SERVER[ 'SERVER_PORT' ];
                $port = ( ( !$ssl && $port == '80' ) || ( $ssl && $port == '443' ) ) ? '' : ':' . $port;
                $host = $_SERVER[ 'SERVER_NAME' ] . $port;
            }
        }
        $request = $_SERVER[ 'REQUEST_URI' ];
        
        $request = substr( $request , 0, strlen( $request ) -1 );
        return "{$proto}://{$host}{$request}";
    }
    
    /**
     *
     * @param mixed $anObject
     * @param boolean $stopExecution
     */
    static public function console( $anObject, $stopExecution = FALSE )
    {
        if( is_null( $anObject ) )
        {
            $data = 'NULL';
        }
        else
        {
            $data = print_r( $anObject, TRUE );
        }
        $data .= chr( 13 );
        
        file_put_contents( self::pathErrorsLog(), $data, FILE_APPEND );
        
        if( $stopExecution )
        {
            die();
        }
    }
}