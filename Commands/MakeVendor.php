<?php

namespace App\Commands;

use Throwable;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Files\File;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

class MakeVendor extends BaseCommand
{
    use GeneratorTrait;
    protected $group = 'custom';
    protected $name = 'make:vendor';
    protected $description = 'Creates a new vendor file.';
    protected $usage = 'make:vendor <vendor> [options]';
    protected $arguments = [
        'vendor' => 'The vendor name.'
    ];
    protected $options = [
        '--config' => 'Include a config file.',
        '--database' => 'Include a database file.'
    ];

    public function run(array $params)
    {
        $vendor = array_shift($params);

        if (empty($vendor)) {
            $vendor = CLI::prompt('Vendors name', null, 'required'); // @codeCoverageIgnore
        }

        // Get an instance of the file locator
        $locator = \Config\Services::locator();

        // Find the template file for the config
        $template = $locator->locateFile('controller.tpl.php', 'Commands/Generators/Views');

        // If the template file is not found
        if ($template === false) {
            // Show an error message and exit
            CLI::error('Unable to locate the template file for the controller.');
            return;
        }

        // Get an instance of the file handler
        $file = new File($template);

        // Read the contents of the template file
        $content = file_get_contents($file->getRealPath());

        // Replace any placeholders with actual values
        $content = str_replace('{vendor}', $vendor, $content);

        // Get the path to the vendors directory
        $path = APPPATH . 'Controllers/Vendors';

        // Create the directory if it does not exist
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Create the new config file with the vendor name
        helper('filesystem');
        write_file($path . '/' . $vendor . '.php', $content);

        // Show a success message
        CLI::write('Created new controller file: ' . CLI::color($vendor . '.php', 'green'));

        if (CLI::getOption('config')) {
            // Find the template file for the config
            $template = $locator->locateFile('config.tpl.php', 'Commands/Generators/Views');

            // If the template file is not found
            if ($template === false) {
                // Show an error message and exit
                CLI::error('Unable to locate the template file for the config.');
                return;
            }

            // Get an instance of the file handler
            $file = new File($template);

            // Read the contents of the template file
            $content = file_get_contents($file->getRealPath());

            // Replace any placeholders with actual values
            $content = str_replace('{vendor}', $vendor, $content);

            // Get the path to the vendors directory
            $path = APPPATH . 'Config/Vendors';

            // Create the directory if it does not exist
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            // Create the new config file with the vendor name
            helper('filesystem');
            write_file($path . '/' . $vendor . '.php', $content);

            // Show a success message
            CLI::write('Created new config file: ' . CLI::color($vendor . '.php', 'green'));
        }

        if (CLI::getOption('database')) {
            // Получаем путь к файлу конфигурации БД
            $dbPath = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Database.php';
            // var_dump(file_get_contents($dbPath));
            // exit();
            // Если файл конфигурации БД существует
            if (is_file($dbPath)) {
                // Читаем содержимое файла конфигурации БД
                $dbContent = file_get_contents($dbPath);

                // Находим секцию с настройками по умолчанию
                preg_match('/public array \$default\s*=\s*\[(.*?)\];/s', $dbContent, $matches);

                // Если секция найдена
                if (isset($matches[1])) {
                    // Копируем ее в новую секцию с именем поставщика
                    $newSection = sprintf("public \$%s = [%s];", $vendor, trim($matches[1]));

                    // Запрашиваем у пользователя значения параметров настройки БД

                    // Получаем имя хоста или используем значение по умолчанию localhost
                    $hostname = CLI::prompt('Database hostname', 'localhost');

                    // Получаем имя пользователя или используем значение по умолчанию root
                    $username = CLI::prompt('Database username', 'root');

                    // Получаем пароль или используем пустое значение по умолчанию
                    $password = CLI::prompt('Database password', '');

                    // Получаем имя базы данных или используем пустое значение по умолчанию
                    $database = CLI::prompt('Database name', $vendor);

                    // Получаем тип драйвера БД из списка доступных или используем значение по умолчанию MySQLi
                    $DBDriver = CLI::prompt(
                        'Database driver',
                        ['MySQLi', 'Postgre', 'SQLite3', 'SQLSRV'],
                        null,
                        null,
                        'MySQLi'
                    );

                    // Заменяем пустые строки или null на введенные значения в новой секции
                    $newSection = str_replace("'hostname' => ''", sprintf("'hostname' => '%s'", addslashes($hostname)), $newSection);
                    $newSection = str_replace("'username' => ''", sprintf("'username' => '%s'", addslashes($username)), $newSection);
                    $newSection = str_replace("'password' => ''", sprintf("'password' => '%s'", addslashes($password)), $newSection);
                    $newSection = str_replace("'database' => ''", sprintf("'database' => '%s'", addslashes($database)), $newSection);
                    $newSection = str_replace("'DBDriver' => null", sprintf("'DBDriver' => '%s'", addslashes($DBDriver)), $newSection);

                    // Добавляем новую секцию в конец файла конфигурации БД и сохраняем его
                    // Находим позицию символа 'public function'
                    $pos = strpos(trim($dbContent), 'public function');
                    // Склеиваем новую строку с текущей строкой перед символом
                    $new_str = substr(trim($dbContent), 0, $pos) . $newSection . PHP_EOL . substr(trim($dbContent), $pos);
                    // Записываем всю строку обратно в файл
                    file_put_contents($dbPath, $new_str);
                    // Показываем сообщение об успехе
                    CLI::write(sprintf('Created new database config section: %s', CLI::color($vendor, 'green')));
                } else {
                    // Показываем сообщение об ошибке и выходим
                    CLI::error('Unable to find the default database config section.');
                    return;
                }
            } else {
                // Показываем сообщение об ошибке и выходим
                CLI::error('Unable to locate the database config file.');
                return;
            }
        }
    }
}
