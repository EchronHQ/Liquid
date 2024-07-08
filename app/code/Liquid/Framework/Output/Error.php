<?php
declare(strict_types=1);

namespace Liquid\Framework\Output;

use Liquid\Framework\Exception\ContextException;

class Error
{
    public static function getExceptionTraceAsString(\Throwable $exception): array
    {
        $count = 0;

        $output = [];
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = [];
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = implode(", ", $args);
            }


            $output[] = [
                'c' => $count,
                'file' => $frame['file'] ?? '[nofile]',
                'line' => $frame['line'] ?? '[noline]',
                'class' => $frame['class'] ?? '',
                'type' => $frame['type'] ?? '', // "->" or "::"
                'function' => $frame['function'],
                'args' => $args,
            ];

            $count++;
        }
        return $output;
    }

    public static function toHtml(\Throwable $ex): string
    {
        $html = '<div style="font-weight: bold;padding-bottom: 5px">' . $ex->getMessage() . '</div>';
        if ($ex instanceof ContextException) {
            if (count($ex->context) > 0) {
                $html .= '<div style="padding-bottom:5px">';
                foreach ($ex->context as $key => $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $html .= '<div>' . $key . ': ' . $value . '</div>';
                }
                $html .= '</div>';
            }


        }

        $lines = self::getExceptionTraceAsString($ex);

        $rootPath = null;
        /** Detect possible root path to hide it */
        foreach ($lines as $line) {
            if ($rootPath === null) {
                $x = strpos($line['file'], 'vendor');
                if ($x > 0) {
                    $rootPath = substr($line['file'], 0, $x);
                }
            }
        }

        $html .= '<div style="padding-bottom: 5px">';

        foreach ($lines as $line) {
            $html .= '<div>';
            $filePath = $line['file'];
            if ($rootPath !== null) {
                $filePath = str_replace($rootPath, '/', $filePath);
            }

            $html .= sprintf(
                "<span style='color:#48d4fc'>#%s</span> %s(%s): %s%s%s(%s)\n",
                $line['c'],
                $filePath,
                $line['line'],
                $line['class'],
                $line['type'],
                $line['function'],
                $line['args']
            );
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
