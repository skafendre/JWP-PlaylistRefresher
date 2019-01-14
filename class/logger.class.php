<?php
class Logger {
    public $logs = [];

    function consoleLog ($data, $name) {
        global $argv;
        if (in_array( "-v", $argv)) {
            $this->printConsoleLog($data, $name);
        }
    }

     function printConsoleLog($data, $name) {
        echo " --- " . $name . " ---" . PHP_EOL;
        print_r($data);
    }

     function formatLogs () {
        $formatedLogs = $this->logs["date"] . ", " . $this->logs["timestamp"] . " \n";
        foreach ($this->logs as $key => $value) {
            if (is_array($value)) {
                $formatedLogs .= trim($key). "=> array : \n";
                foreach ($value as $key1 => $value1) {
                    $formatedLogs .= "\t " . trim($key1) . " => " . trim($value1) . " \n";
                }
            } else {
                $formatedLogs .= trim($key) . " => " . trim($value) . " \n ";
            }
        }
        return $formatedLogs;
    }

     function printLogInFile () {
        if (!file_exists('log/')) {
            mkdir('log/', 0777, true);
        }
        file_put_contents("log/log.txt", $this->formatLogs() ."\n \n", FILE_APPEND);
    }
}