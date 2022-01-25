<?php

class ApacheLogParser
{
    private $_pattern = "/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")$/";
    private $_file;

    /**
     * Parser constructor.
     * @param string $pathToFile
     * @throws Exception
     */
    public function __construct(string $pathToFile)
    {
        if (!is_readable($pathToFile)) {
            throw new \Exception('Access Denied!');
        }

        $this->openFile($pathToFile);
    }

    /**
     * @return Generator
     */
    public function handle(): Generator
    {
        while ($line = $this->line()) {
            $parsedLine = $this->parseLine($line);
            if (!$parsedLine) {
                continue;
            }
            yield $parsedLine;
        }
        $this->closeFile($this->_file);
    }

    /**
     * @param string $pathToFile
     * @return void
     */
    private function openFile(string $pathToFile)
    {
        $this->_file = @fopen($pathToFile, 'r');
    }

    /**
     * @return string
     */
    private function line():string
    {
        return fgets($this->_file);
    }

    /**
     * @param string $line
     * @return array
     */
    private function parseLine(string $line): array
    {
        preg_match($this->_pattern, $line, $matches);

        if (count($matches) < 1) {
            return [];
        }

        $formattedLog = [];
        $formattedLog['path'] = filter_var($matches[8], FILTER_SANITIZE_STRING);
        $formattedLog['statusCode'] = filter_var($matches[10], FILTER_SANITIZE_NUMBER_INT);
        $formattedLog['bytes'] = filter_var($matches[11], FILTER_SANITIZE_NUMBER_INT);
        $formattedLog['userAgent'] = filter_var($matches[13], FILTER_SANITIZE_STRING);

        return $formattedLog;
    }

    /**
     * @param resource $file
     * @return void
     */
    private function closeFile($file)
    {
        fclose($file);
    }
}