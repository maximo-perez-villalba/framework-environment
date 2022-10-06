<?php
namespace MPV\Framework\Environment\Exceptions;

use Exception;

/**
 * @author Máximo Perez Villalba
 */
class EnvironmentException extends Exception
{
    
    const SQLITE_NOT_LOADED = 1;
    
    public static function throwSqlite3ExtensionNotLoaded()
    {
        throw new EnvironmentException("Sqlite3 extension not loaded. Installation is required to use the Sqlite3 PDO driver.", self::SQLITE_NOT_LOADED);
    }
    
    const UNABLE_CREATE_FILE = 2;

    public static function throwUnableToCreateFile(string $pathFile)
    {
        throw new EnvironmentException("Unable to create log file. The path received is {$pathFile}.", self::UNABLE_CREATE_FILE);        
    }
}
