<?php
namespace MPV\Framework\Environment\Exceptions;

use Exception;

/**
 * @author Máximo Perez Villalba
 */
class EnvConfigException extends Exception
{

    const FILE_PATH_NOT_EXIST = 1;
    
    /**
     * @param string $path
     * @throws EnvConfigException
     */
    public static function throwFilePathNotExists(string $path) 
    {
        throw new EnvConfigException("File path not exists: {$path}", self::FILE_PATH_NOT_EXIST);
    }

    const INVALID_FILE_EXTENSION = 2;

    /**
     * @param string $extension
     * @throws EnvConfigException
     */
    public static function throwInvalidFileExtension(string $extension) 
    {
        throw new EnvConfigException("Invalid file extension. Expected extension is 'php', '{$extension}' is received.", self::INVALID_FILE_EXTENSION);
    }
    
}
