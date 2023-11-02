<?php
declare(strict_types=1);

namespace Liquid\Framework\Output;

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
                'file' => $frame['file'],
                'line' => $frame['line'],
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
        $html .= '<div style="padding-bottom: 5px">';
        $lines = self::getExceptionTraceAsString($ex);
        foreach ($lines as $line) {
            $html .= '<div>';
            $html .= sprintf(
                "<span style='color:#48d4fc'>#%s</span> %s(%s): %s%s%s(%s)\n",
                $line['c'],
                $line['file'],
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
