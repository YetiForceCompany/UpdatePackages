<?php

namespace Notihnio\MultipartFormDataParser;

/**
 * Class MultipartFormDataParser
 *
 * @package Notihnio\MultipartFormDataParser
 */
class MultipartFormDataParser
{

    public static function parse() : ?MultipartFormDataset
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $dataset = new MultipartFormDataset();

        if ($method == "POST") {
            $dataset->files = $_FILES;
            $dataset->params = $_POST;
            return $dataset;
        }

        if ($method == "GET") {
            return $dataset;
        }

        $GLOBALS["_".$method] = [];

        //get raw input data
        $rawRequestData = file_get_contents("php://input");
        if (empty($rawRequestData)) {
            return null;
        }

        $contentType = $_SERVER["CONTENT_TYPE"];

        if (!preg_match('/boundary=(.*)$/is', $contentType, $matches)) {
            return null;
        }

        $boundary = $matches[1];
        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundary, '/') . '/s', $rawRequestData);
        array_pop($bodyParts);

        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }
            list($headers, $value) = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers =self::parseHeaders($headers);
            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }
            if (isset($headers['content-disposition']['filename'])) {
                $file = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => array_key_exists('content-type', $headers) ? $headers['content-type'] : 'application/octet-stream',
                    'size' => mb_strlen($value, '8bit'),
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => null,
                ];

                if ($file['size'] > self::toBytes(ini_get('upload_max_filesize'))) {
                    $file['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $file['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $file['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $file['tmp_name'] = $tmpFileName;
                            $file['tmp_resource'] = $tmpResource;
                        }
                    }
                }
                $file["size"] = self::toFormattedBytes($file["size"]);
                $_FILES[$headers['content-disposition']['name']] = $file;
                $dataset->files[$headers['content-disposition']['name']] = $file;
            } else {
                //parameters
                $dataset->params[$headers['content-disposition']['name']] = $value;

                if ($method !="POST" && $method != "GET") {
                    $GLOBALS["_".$method][$headers['content-disposition']['name']] = $value;
                }
            }
        }
        return $dataset;
    }


    /**
     * Parses body param headers
     * @param $headerContent
     *
     * @return array
     */
    private static function parseHeaders(string $headerContent) : array
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (strpos($headerPart, ':') === false) {
                continue;
            }
            list($headerName, $headerValue) = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);
            if (strpos($headerValue, ';') === false) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (strpos($part, '=') === false) {
                        $headers[$headerName][] = $part;
                    } else {
                        list($name, $value) = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }
        return $headers;
    }

    /**
     * Converts bytes to kb mb etc..
     * Taken from https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     *
     * @param int $bytes
     *
     * @return string
     */
    private static function toFormattedBytes(int $bytes) : string
    {
        $precision = 2;
        $base = log($bytes, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }


    /**
     * Formatted bytes to bytes
     * @param string $formattedBytes
     *
     * @return int|null
     */
    private static function toBytes(string $formattedBytes): ?int {
        $units = ['B', 'K', 'M', 'G', 'T', 'P'];
        $unitsExtended = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $number = (int)preg_replace("/[^0-9]+/", "", $formattedBytes);
        $suffix = preg_replace("/[^a-zA-Z]+/", "", $formattedBytes);

        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $formattedBytes);
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            $exponent = array_flip($unitsExtended)[$suffix] ?? null;
        }

        if($exponent === null) {
            return null;
        }
        return $number * (1024 ** $exponent);
    }
}
