<?php
/**
 *  DB - A simple database class 
 *
 * @author		Author: Vivek Wicky Aswal. (https://twitter.com/#!/VivekWickyAswal)
 * @git 		https://github.com/indieteq/PHP-MySQL-PDO-Database-Class
 * @version      0.2ab
 *
 */
class DB
{
    # @object, The PDO object
    private $pdo;
    
    # @object, PDO statement object
    private $sQuery;
    
    # @array,  The database settings
    private $settings;
    
    # @bool ,  Connected to the database
    private $bConnected = false;
    
    # @object, Object for logging exceptions	
    private $log;
    
    # @array, The parameters of the SQL query
    private $parameters;
    
    /**
     *   Default Constructor 
     *
     *	1. Instantiate Log class.
     *	2. Connect to database.
     *	3. Creates the parameter array.
     */
    public function __construct()
    {
        $this->log = new Log();
        $this->Connect();
        $this->parameters = array();
    }
    
    /**
     *	This method makes connection to the database.
     *	
     *	1. Reads the database settings from a ini file. 
     *	2. Puts  the ini content into the settings array.
     *	3. Tries to connect to the database.
     *	4. If connection failed, exception is displayed and a log file gets created.
     */
    private function Connect()
    {
        $settingsFile = __DIR__ . '/settings.ini.php';
        $this->settings = file_exists($settingsFile) ? @parse_ini_file($settingsFile) : [];

        $host = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: ($this->settings['host'] ?? 'localhost'));
        $dbname = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: ($this->settings['dbname'] ?? ''));
        $user = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: ($this->settings['user'] ?? ''));
        $password = getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASSWORD') ?: ($this->settings['password'] ?? ''));
        $port = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: ($this->settings['port'] ?? null));

        $dsn = "mysql:dbname={$dbname};host={$host}";
        if (!empty($port)) {
            $dsn .= ";port={$port}";
        }

        try {
            # Use Pdo\Mysql::ATTR_INIT_COMMAND for PHP 8.4+ and fallback to PDO::MYSQL_ATTR_INIT_COMMAND for older versions
            $initCommand = defined('Pdo\Mysql::ATTR_INIT_COMMAND')
                ? \Pdo\Mysql::ATTR_INIT_COMMAND
                : PDO::MYSQL_ATTR_INIT_COMMAND;

            $options = [
                $initCommand => 'SET NAMES utf8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            # SSL / TLS Configuration
            $sslCa = getenv('MYSQL_SSL_CA') ?: (getenv('DB_SSL_CA') ?: ($this->settings['ssl_ca'] ?? null));
            $sslCert = getenv('MYSQL_SSL_CERT') ?: (getenv('DB_SSL_CERT') ?: ($this->settings['ssl_cert'] ?? null));
            $sslKey = getenv('MYSQL_SSL_KEY') ?: (getenv('DB_SSL_KEY') ?: ($this->settings['ssl_key'] ?? null));
            $sslCipher = getenv('MYSQL_SSL_CIPHER') ?: (getenv('DB_SSL_CIPHER') ?: ($this->settings['ssl_cipher'] ?? null));

            $sslEnabled = getenv('MYSQL_SSL') ?: (getenv('DB_SSL') ?: ($this->settings['ssl'] ?? false));
            $sslEnabled = filter_var($sslEnabled, FILTER_VALIDATE_BOOLEAN) || !empty($sslCa) || !empty($sslCert);

            if ($sslEnabled) {
                $attrSslCa = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
                $attrSslVerify = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
                $attrSslKey = defined('Pdo\Mysql::ATTR_SSL_KEY') ? \Pdo\Mysql::ATTR_SSL_KEY : PDO::MYSQL_ATTR_SSL_KEY;
                $attrSslCert = defined('Pdo\Mysql::ATTR_SSL_CERT') ? \Pdo\Mysql::ATTR_SSL_CERT : PDO::MYSQL_ATTR_SSL_CERT;
                $attrSslCipher = defined('Pdo\Mysql::ATTR_SSL_CIPHER') ? \Pdo\Mysql::ATTR_SSL_CIPHER : PDO::MYSQL_ATTR_SSL_CIPHER;

                # Resolve relative path for CA certificate
                if (!empty($sslCa)) {
                    if (!file_exists($sslCa)) {
                        $appRoot = dirname(__DIR__, 2);
                        if (file_exists($appRoot . '/' . ltrim($sslCa, '/'))) {
                            $sslCa = $appRoot . '/' . ltrim($sslCa, '/');
                        } elseif (file_exists(__DIR__ . '/' . ltrim($sslCa, '/'))) {
                            $sslCa = __DIR__ . '/' . ltrim($sslCa, '/');
                        }
                    }
                } else {
                    # Auto-discover OS CA bundle if SSL is requested but no custom CA file specified
                    $systemCaBundles = [
                        '/etc/ssl/certs/ca-certificates.crt', // Debian / Ubuntu (Azure Web Apps Linux)
                        '/etc/pki/tls/certs/ca-bundle.crt',     // RHEL / CentOS / AlmaLinux
                        '/etc/ssl/ca-bundle.pem',              // openSUSE
                        '/etc/ssl/cert.pem',                   // macOS / Alpine / FreeBSD
                    ];
                    foreach ($systemCaBundles as $caPath) {
                        if (file_exists($caPath)) {
                            $sslCa = $caPath;
                            break;
                        }
                    }
                }

                if (!empty($sslCa)) {
                    $options[$attrSslCa] = $sslCa;
                }
                if (!empty($sslKey)) {
                    $options[$attrSslKey] = $sslKey;
                }
                if (!empty($sslCert)) {
                    $options[$attrSslCert] = $sslCert;
                }
                if (!empty($sslCipher)) {
                    $options[$attrSslCipher] = $sslCipher;
                }

                $sslVerify = getenv('MYSQL_SSL_VERIFY_SERVER_CERT');
                if ($sslVerify === false || $sslVerify === null) {
                    $sslVerify = $this->settings['ssl_verify'] ?? true;
                }
                $options[$attrSslVerify] = filter_var($sslVerify, FILTER_VALIDATE_BOOLEAN);
            }

            # Connect using PDO
            $this->pdo = new PDO($dsn, $user, $password, $options);
            
            # Connection succeeded, set the boolean to true.
            $this->bConnected = true;
        }
        catch (PDOException $e) {
            # Write into log
            echo $this->ExceptionLog($e->getMessage());
            die();
        }
    }
    
    /*
     *   You can use this little method if you want to close the PDO connection
     *
     */
    public function CloseConnection()
    {
        # Set the PDO object to null to close the connection
        # http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }
    
    /**
     *	Every method which needs to execute a SQL query uses this method.
     *	
     *	1. If not connected, connect to the database.
     *	2. Prepare Query.
     *	3. Parameterize Query.
     *	4. Execute Query.	
     *	5. On exception : Write Exception into the log + SQL query.
     *	6. Reset the Parameters.
     */
    private function Init($query, $parameters = "")
    {
        # Connect to database
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {
            # Prepare query
            $this->sQuery = $this->pdo->prepare($query);
            
            # Add parameters to the parameter array	
            $this->bindMore($parameters);
            
            # Bind parameters
            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param => $value) {
                    $type = PDO::PARAM_STR;
                    if (is_int($value[1])) {
                        $type = PDO::PARAM_INT;
                    } elseif (is_bool($value[1])) {
                        $type = PDO::PARAM_BOOL;
                    } elseif (is_null($value[1])) {
                        $type = PDO::PARAM_NULL;
                    }
                    // Add type when binding the values to the column
                    $this->sQuery->bindValue($value[0], $value[1], $type);
                }
            }

            # Execute SQL 
            $this->sQuery->execute();
        }
        catch (PDOException $e) {
            # Write into log and display Exception
            echo $this->ExceptionLog($e->getMessage(), $query);
            die();
        }
        
        # Reset the parameters
        $this->parameters = array();
    }
    
    /**
     *	@void 
     *
     *	Add the parameter to the parameter array
     *	@param string $para  
     *	@param string $value 
     */
    public function bind($para, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [":" . $para , $value];
    }
    
    /**
     *	@void
     *	
     *	Add more parameters to the parameter array
     *	@param array $parray
     */
    public function bindMore($parray)
    {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    
    /**
     *  If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
     *	If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     *
     *  @param  string $query
     *	@param  array  $params
     *	@param  int    $fetchmode
     *	@return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace("\r", " ", $query));
        
        $this->Init($query, $params);
        
        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $query));
        
        # Which SQL statement is used 
        $statement = strtolower($rawStatement[0]);
        
        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }
    
    /**
     *  Returns the last inserted id.
     *  @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Starts the transaction
     * @return boolean, true on success or false on failure
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     *  Execute Transaction
     *  @return boolean, true on success or false on failure
     */
    public function executeTransaction()
    {
        return $this->pdo->commit();
    }
    
    /**
     *  Rollback of Transaction
     *  @return boolean, true on success or false on failure
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     *	Returns an array which represents a column from the result set 
     *
     *	@param  string $query
     *	@param  array  $params
     *	@return array
     */
    public function column($query, $params = null)
    {
        $this->Init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
        
        $column = null;
        
        foreach ($Columns as $cells) {
            $column[] = $cells[0];
        }
        
        return $column;
        
    }
    
    /**
     *	Returns an array which represents a row from the result set 
     *
     *	@param  string $query
     *	@param  array  $params
     *   	@param  int    $fetchmode
     *	@return array
     */
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->fetch($fetchmode);
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued,
        return $result;
    }
    
    /**
     *	Returns the value of one single field/column
     *
     *	@param  string $query
     *	@param  array  $params
     *	@return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->fetchColumn();
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued
        return $result;
    }
    
    /**
     *	Row count of results
     *
     *	@param  string $query
     *	@param  array  $params
     *	@return string
     */
    public function resultCount($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->rowCount();
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued
        return $result;
    }

    /**
     * Checks whether a column exists on a table in the active database schema.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    public function hasColumn($table, $column)
    {
        $this->bind('tableName', $table);
        $this->bind('columnName', $column);
        return (int)$this->single("SELECT COUNT(*)
                                   FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                     AND TABLE_NAME = :tableName
                                     AND COLUMN_NAME = :columnName") > 0;
    }

    /**
     * Checks whether an index exists on a table in the active database schema.
     *
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    public function hasIndex($table, $indexName)
    {
        $this->bind('tableName', $table);
        $this->bind('indexName', $indexName);
        return (int)$this->single("SELECT COUNT(*)
                                   FROM INFORMATION_SCHEMA.STATISTICS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                     AND TABLE_NAME = :tableName
                                     AND INDEX_NAME = :indexName") > 0;
    }

    /**
     * Executes a DDL/non-fatal SQL statement without terminating on PDOException.
     *
     * @param string $sql
     * @return bool
     */
    public function tryExec($sql)
    {
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            $this->log->warning("Non-fatal SQL exec failed: " . $e->getMessage(), ['sql' => $sql]);
            return false;
        }
    }
    
    /**	
     * Writes the log and returns the exception
     *
     * @param  string $message
     * @param  string $sql
     * @return string
     */
    private function ExceptionLog($message, $sql = "")
    {
        $context = [];
        if (!empty($sql)) {
            $context['sql'] = $sql;
        }

        $this->log->critical("Database PDO Exception: " . $message, $context);

        $isApi = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false)
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($isApi) {
            header('Content-Type: application/json');
            return json_encode(['error' => 'A database error occurred. The incident has been logged.']);
        }

        $exception = 'Unhandled Database Exception. <br />';
        $exception .= htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $exception .= "<br /> The error has been logged.";
        return $exception;
    }
}
?>