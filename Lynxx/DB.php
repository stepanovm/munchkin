<?php


namespace Lynxx;


use app\config\Config;
use PDO;

class DB
{
    /**
     * @var PDO
     */
    private static $pdo;

    private function __construct() {}

    /**
     * @return PDO
     */
    public static function instance(): PDO
    {
        // create new PDO, if not exist
        if(!isset(self::$pdo)){
            $dbInfo = Config::$config['db'];
            $dsn = $dbInfo['sqlType'].':host='.$dbInfo['host'].';dbname='.$dbInfo['dbname'].';charset='.$dbInfo['charset'];
            $pdo_params = array (
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => TRUE
            );
            self::$pdo = new PDO("$dsn", $dbInfo['username'], $dbInfo['password'], $pdo_params);
            self::$pdo->query('SET NAMES '.$dbInfo['charset']);
        }

        return self::$pdo;
    }

    /**
     * @param string $sqlQuery
     * @param array|null $args
     * @return array
     */
    public static function select(string $sqlQuery, array $args = null): array
    {
        $stmt = self::instance()->prepare($sqlQuery);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    /**
     * @param string $sqlQuery
     * @param array|null $args
     * @return array
     */
    public static function selectUnique(string $sqlQuery, array $args = null): array
    {
        $stmt = self::instance()->prepare($sqlQuery);
        $stmt->execute($args);
        return $stmt->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     * @param string $sqlQuery
     * @param array|null $args
     * @return int
     */
    public static function update(string $sqlQuery, array $args = null): int
    {
        $stmt = self::instance()->prepare($sqlQuery);
        $stmt->execute($args);
        return $stmt->rowCount();
    }

    /**
     * @param string $sqlQuery
     * @param array|null $args
     * @return string lastInsertId
     */
    public static function insert(string $sqlQuery, array $args = null): string
    {
        $stmt = self::instance()->prepare($sqlQuery);
        $stmt->execute($args);
        return self::instance()->lastInsertId();
    }

    /**
     * @param string $sqlQuery
     * @param array|null $args
     * @return int
     */
    public static function delete(string $sqlQuery, array $args = null): int
    {
        $stmt = self::instance()->prepare($sqlQuery);
        $stmt->execute($args);
        return $stmt->rowCount();
    }



    /**
     * функция для работы с методами класса PDO напрямую
     * теперь попытка вызова метода $method($args), который отсутствует в текущем классе DBHelper,
     * будет приводить к вызову $pdo->$method($args)
     *
     * @param string $method PDO method
     * @param array $args query arguments
     * @return mixed PDO method
     */
    public static function __callStatic(string $method, array $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }
}