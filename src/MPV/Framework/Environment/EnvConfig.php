<?php
namespace MPV\Framework\Environment;

use JsonSerializable;
use MPV\Framework\Environment\Exceptions\EnvConfigException;
use Stringable;

/**
 * Description of EnvConfig
 *
 * @author Máximo Perez Villalba
 */
class EnvConfig implements Stringable, JsonSerializable
{
    
    /** @var array */
    private array $appConfig = [];

    /**
     * @param string $fullPathFileConfig
     * @throws EnvConfigException
     */
    public function __construct(string $fullPathFileConfig) 
    {
        if(file_exists($fullPathFileConfig))
        {
            $extension = pathinfo($fullPathFileConfig, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            if($extension == 'php')
            {
                include $fullPathFileConfig;
                $this->appConfig = $appConfig;        
            }
            else
            {
                EnvConfigException::throwInvalidFileExtension($extension);
            }
        }
        else 
        {
            EnvConfigException::throwFilePathNotExists($fullPathFileConfig);
        }
    }

    /**
     * @return string|null
     */
    public function urlHost(): ?string
    {
        $url = null;
        if(isset($this->appConfig['url-host']))
        {
            $url = $this->appConfig['url-host'];
        }
        return$url;
    }
    
    /**
     * @return bool
     */
    public function hasDB():bool
    {
        $response = false;
        if(isset($this->appConfig['db']))
        {
            $response = isset($this->appConfig['db']['dns']);
        }
        return $response;
    }
    
    /**
     * @return string|null
     */
    public function DBDNS(): ?string
    {
        $response = NULL;
        if($this->hasDB())
        {
            $response = $this->appConfig['db']['dns'];
        }
        return $response;
    }
    
    /**
     * @return string|null
     */
    public function DBUsername(): ?string
    {
        $response = NULL;
        if($this->hasDB() && isset($this->appConfig['db']['username']))
        {
            $response = $this->appConfig['db']['username'];
        }
        return $response;
    }

    /**
     * @return string|null
     */
    public function DBPassword(): ?string
    {
        $response = NULL;
        if($this->hasDB() && isset($this->appConfig['db']['password']))
        {
            $response = $this->appConfig['db']['password'];
        }
        return $response;
    }
    
    /**
     * @return array
     */
    public function DBOptions(): array
    {
        $response = [];
        if($this->hasDB() && isset($this->appConfig['db']['options']))
        {
            $response = $this->appConfig['db']['options'];
        }
        return $response;
    }

    /**
     * @return bool
     */
    public function hasPHP():bool
    {
        return isset($this->appConfig['php'])
                && is_array($this->appConfig['php'])
                && !empty($this->appConfig['php']);
    }

    /**
     * @return string|null
     */
    public function PHPErrorLog(): ?string 
    {
        $response = NULL;
        $isValid = $this->hasPHP()
                    && isset($this->appConfig['php']['error_log'])
                    && is_string($this->appConfig['php']['error_log']);
        if($isValid)
        {
            $response = $this->appConfig['php']['error_log'];
        }
        return $response;
    }
    
    /**
     * @return string|null
     */
    public function PHPDateDefaultTimezoneSet(): ?string 
    {
        $response = NULL;
        $isValid = $this->hasPHP()
                    && isset($this->appConfig['php']['date_default_timezone_set'])
                    && is_string($this->appConfig['php']['date_default_timezone_set']);
        if($isValid)
        {
            $response = $this->appConfig['php']['date_default_timezone_set'];
        }
        return $response;
    }

    /**
     * @return string
     */
    public function __toString(): string 
    {
        return $this->jsonSerialize();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed 
    {
        $jsonContent = [
            'class' => get_called_class(),
            'config'=> $this->appConfig
        ];
        return json_encode($jsonContent, JSON_PRETTY_PRINT);
    }

}
