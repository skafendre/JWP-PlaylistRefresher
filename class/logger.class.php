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
        echo " --- FUNCTION " . $name . " ---" . PHP_EOL;
        print_r($data);
    }

     function formatLogs () {
        $formatedLogs = "";
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

    function displayLogInConsole () {
         global $argv;
        if (!in_array( "-v", $argv)) {
            // non verbose
            if (array_key_exists("errors", $this->logs)) {
                foreach($this->logs["errors"] as $key=>$value) {
                    echo "[" . $key . "] error : " . $value, " \n";
                }
            }
            echo "Refresh status : " . $this->logs["status"] . ", timestamp : " . $this->logs["timestamp"];
        } else {
            // verbose
            echo $this->formatLogs();
        }
    }
}