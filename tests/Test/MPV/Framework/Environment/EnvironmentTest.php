<?php
namespace Test\MPV\Framework\Environment;

use MPV\Framework\Environment\Env;
use MPV\Framework\Environment\Exceptions\EnvConfigException;
use MPV\Framework\Environment\Exceptions\EnvironmentException;
use MPV\Tools\TXT;
use PHPUnit\Framework\TestCase;

/**
 * @author Máximo Perez Villalba
 */
class EnvironmentTest extends TestCase
{

    /** @var string|null */
    private ?string $resourcesPath = null;
    
    public function setUp(): void 
    {
        $path = TXT::create(__DIR__)->firstPart('/tests/', true);
        $this->resourcesPath = "{$path}resources";
    }
    
    public function testInitPath()
    {
        Env::init();
        $fullPath = Env::path();
        
        $this->assertEquals(dirname(__FILE__), $fullPath);
        $this->assertEquals(__FILE__, Env::path('/EnvironmentTest.php'));
    }
    
    public function testInitPathCustom()
    {
        $rootPath = TXT::create(__DIR__)->firstPart('/tests');
        Env::init(null, $rootPath);
        
        $fullPath = Env::path('/tests/resources/app-config-php-error-log-exception.php');
        
        $this->assertTrue(file_exists($fullPath));
    }
    
    public function testInitThrowFilePathNotExists()
    {
        $this->expectException(EnvConfigException::class); 
        $this->expectExceptionCode(EnvConfigException::FILE_PATH_NOT_EXIST); 
        Env::init("fake-path-to-db");
    }    
    
    public function testInitThrowInvalidFileExtension()
    {
        $this->expectException(EnvConfigException::class); 
        $this->expectExceptionCode(EnvConfigException::INVALID_FILE_EXTENSION); 
        Env::init("{$this->resourcesPath}/app-config-fake.json");
    }    
    
    public function testInitUrlHost()
    {
        Env::init("{$this->resourcesPath}/app-config-url-host.php");
        
        $this->assertEquals('https://example.net/', Env::urlbase());
        $this->assertEquals('https://example.net/', Env::url());
        $this->assertEquals('https://example.net/something', Env::url('something'));
        $this->assertNotEquals('https://example.net/', Env::url('something'));
    }
    
    public function testPHPDateDefaultTimezone()
    {
        Env::init("{$this->resourcesPath}/app-config-php.php");

        $this->assertEquals('America/Argentina/Buenos_Aires', date_default_timezone_get());
    }
    
    public function testThrowUnableToCreateLogFile()
    {
        $this->expectException(EnvironmentException::class);
        $this->expectExceptionCode(EnvironmentException::UNABLE_CREATE_FILE);
        Env::init("{$this->resourcesPath}/app-config-php-error-log-exception.php");
    }

    public function testPHPErrorLog()
    {
        Env::init("{$this->resourcesPath}/app-config-php.php");
        
        $txt = 'Al contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio.';
        $filePathLog = "{$this->resourcesPath}/output.log";
        
        file_put_contents($filePathLog,'');
        
        error_log($txt);
        
        $content = file_get_contents($filePathLog);
        
        $this->assertTrue(TXT::create($content)->contains($txt));
    }
    
    public function testLog()
    {
        Env::init("{$this->resourcesPath}/app-config-php.php");
        
        $txt = 'Al contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio.';
        $filePathLog = "{$this->resourcesPath}/output.log";

        file_put_contents($filePathLog,'');
        
        Env::log($txt);
        
        $content = file_get_contents($filePathLog);
        
        $this->assertTrue(TXT::create($content)->contains($txt));
    }
    
    public function testDBConnections() 
    {
        Env::init("{$this->resourcesPath}/app-config-db.php");

        $connection = Env::dbConnection();
        $this->assertIsObject($connection);
    }
    
    public function testDBConnectionSqliteThrowUnableToCreateFile()
    {
        Env::init("{$this->resourcesPath}/app-config-db-dns-exception.php");
        
        $this->expectException(EnvironmentException::class);
        $this->expectExceptionCode(EnvironmentException::UNABLE_CREATE_FILE);
    
        Env::dbConnection();        
    }
    
    public function testDBConnectionSqliteInMemory() 
    {
        Env::init("{$this->resourcesPath}/app-config-db-sqlite-memory.php");

        $connection = Env::dbConnection();
        $this->assertIsObject($connection);
    }
    
    public function testAttributes()
    {
        Env::init("{$this->resourcesPath}/app-config-attributes.php");

        $this->assertEquals('attrValueTypeString', Env::attr('attr-name'));        
    }
}