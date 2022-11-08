<?php
namespace MPV\Framework\Environment;

use MPV\Framework\Environment\Exceptions\EnvironmentException;
use MPV\Tools\TXT;
use PDO;
use PDOException;

abstract class Environment
{
    /** @var string|null */
    private static ?string $rootPath = null;
    
    /** @var EnvConfig|null */
    private static ?EnvConfig $config = null;

    /**
     * @param string|null $pathConfig
     */
    static public function init(string $pathConfig = null)
    {
        /*
         * Recupera la ruta del archivo que invoca a Environment::init().
         */
        $backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );
        self::$rootPath = dirname($backtrace[ 0 ][ 'file' ]);

        if(isset($pathConfig))
        {
            self::$config = new EnvConfig($pathConfig);

            self::configPHPDateDefaultTimezoneSet();

            self::configPHPErrorLog();
        }
    }

    /**
     * Configuración la zona horaria del servidor.
     */
    private static function configPHPDateDefaultTimezoneSet() 
    {
        $date_default_timezone_set = self::$config->PHPDateDefaultTimezoneSet();
        if(isset($date_default_timezone_set))
        {
            date_default_timezone_set($date_default_timezone_set);
        }
    }

    /**
     * Configuracion de captura y salida de errores
     */
    private static function configPHPErrorLog()
    {
        $error_log_path = self::$config->PHPErrorLog();
        if(isset($error_log_path))
        {
            $filePathLog = self::path($error_log_path);
            
            if(!file_exists($filePathLog))
            {
                $answer = @file_put_contents($filePathLog, '');
                if($answer === false)            
                {
                    EnvironmentException::throwUnableToCreateFile($filePathLog);
                }
            }            
            
            ini_set('log_errors', TRUE);
            ini_set('error_log', realpath($filePathLog));
            error_reporting( E_ALL );
        }
    }

    /**
     * @return EnvConfig
     */
    public static function config():EnvConfig
    {
        return self::$config;
    }

    /**
     * @param bool|string|array|object|null $value
     */
    public static function log(bool|string|array|object|null $value)
    {
        //Print call log method
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        
        $lineNumber = $backtrace[0]['line'];
        $file = $backtrace[0]['file'];
        
        $fileTxt = TXT::create($file);
        if($fileTxt->contains('src'))
        {
            $file = $fileTxt->lastPart('src');
        }
        elseif ($fileTxt->contains('tests')) 
        {
            $file = $fileTxt->lastPart('tests');
        }
        
        $content = "{$file}({$lineNumber})\n";
        if(is_null($value))
        {
            $content .= 'NULL';
        }
        elseif(is_bool($value))
        {
            $content .= $value?'true':'false';
        }
        elseif(is_string($value))
        {
            $content .= $value;
        }
        elseif(is_object($value)) 
        {
            $content .= json_encode($value, JSON_PRETTY_PRINT);
        }
        else 
        {
            $content .= print_r($value, true);
        }
        error_log("{$content}\n");
    }

    /**
     * @param string $key
     * @return null|string|array
     */
    public static function attr(string $key): null|string|array
    {
        return self::config()->attr($key);
    }

     /**
     * Retorna una conexión a la base de datos.
     * En caso de no poder obtener una conexión lanza una exception.
     * 
     * @throws PDOException
     * @return PDO|null
     */
    public static function dbConnection(): ?PDO
    {
        $connection = NULL;     
        if(self::config()->hasDB())
        {
            $txtdns = TXT::create(self::config()->DBDNS());
            $dnsPrefix = $txtdns->firstPart(':');
            $dnsValue = $txtdns->lastPart(':');
            
            if($dnsPrefix == 'sqlite')
            {
                if($txtdns->contains(':memory:'))
                {
                    $dnsValue = $txtdns->lastPart('::');
                }
                $dnsValue = self::dnsSqlite3Adapter($dnsValue);
            }
            //TODO: https://electrictoolbox.com/php-pdo-dsn-connection-string/
            
            $connection = new PDO( 
                "{$dnsPrefix}:{$dnsValue}", 
                self::config()->DBUsername(), 
                self::config()->DBPassword(),
                self::config()->DBOptions()
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $connection;
    }

    /**
     * @param string $dnsValue
     * @return string
     */
    public static function dnsSqlite3Adapter(string $dnsValue): string
    {
        if(!extension_loaded('sqlite3'))
        {
           EnvironmentException::throwSqlite3ExtensionNotLoaded();
        }
        
        if(TXT::create($dnsValue)->startWith('memory:'))
        {
            $dnsValue = ':memory:';
        }
        else 
        {
            $dnsValue = self::path($dnsValue);
            if(!file_exists($dnsValue))
            {
                $answer = @file_put_contents($dnsValue, '');
                if($answer === false)
                {
                    EnvironmentException::throwUnableToCreateFile($dnsValue);
                }
            }
            $dnsValue = realpath($dnsValue);                
        }
        return $dnsValue;
    }

    /**
     * @param string $extension
     * @return string
     */
    public static function path(string $extension = ''): string
    {
        return self::$rootPath.$extension;
    }

    /**
     * @param string $extension
     * @return string
     */
    public static function url(string $extension = ''): string
    {
        return self::urlbase().$extension;
    }

    /**
     * @return string
     */
    public static function urlbase(): string
    {
        $response = self::$config->urlHost();
        if(!TXT::create($response)->endWith('/'))
        {
            $response .= '/';
        }
        return $response;
    }
    
}