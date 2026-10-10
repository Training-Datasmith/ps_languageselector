<?php

class GuardProcess
{
    /**
     * @param string $scriptPath
     * @param array  $args
     *
     * @return array exitCode, stdout, stderr
     */
    public static function runPhpScript($scriptPath, array $args = array())
    {
        $php = getenv('PHP_BINARY') ? getenv('PHP_BINARY') : 'php';
        $cmd = escapeshellarg($php)
            . ' -d error_reporting=-1'
            . ' -d display_errors=1'
            . ' -d display_startup_errors=1'
            . ' -d log_errors=0'
            . ' ' . escapeshellarg($scriptPath);
        foreach ($args as $arg) {
            $cmd .= ' ' . escapeshellarg($arg);
        }

        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $process = proc_open($cmd, $descriptors, $pipes, dirname(dirname(__DIR__)));
        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start child PHP process');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return array(
            'exitCode' => (int) $exitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
        );
    }
}
