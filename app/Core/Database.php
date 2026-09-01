<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $configFile = __DIR__ . '/../../config/database.php';

        if (!is_file($configFile)) {
            throw new \RuntimeException(
                'Banco ainda não configurado. Copie config/database.example.php para config/database.php e preencha os dados da hospedagem.'
            );
        }

        $config = require $configFile;

        foreach (['host', 'port', 'dbname', 'user', 'password'] as $key) {
            if (!array_key_exists($key, $config)) {
                throw new \RuntimeException("Configuração do banco incompleta: {$key}");
            }
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['dbname']
        );

        try {
            self::$connection = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            /*
             * A hospedagem gratuita pode usar um fuso horário diferente do Brasil.
             * Como as colunas de data/hora do WMS usam TIMESTAMP, definimos o fuso
             * da sessão MySQL como UTC-03:00 para manter as movimentações no horário
             * de São Paulo, independentemente do fuso do servidor.
             */
            self::$connection->exec("SET time_zone = '-03:00'");
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Não foi possível conectar ao MySQL: ' . $e->getMessage()
            );
        }

        return self::$connection;
    }

    private function __construct()
    {
    }
}
