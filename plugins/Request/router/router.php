<?php

namespace plugins\Request;

use plugins\Extension\plugins;
use plugins\Start\cache;

class router
{
    public static function callBack($request, $response)
    {
        $uriReplaceLocation = $request->header['host'];
        if (!in_array($request->server['remote_addr'], cache::global()['interface']['server']['allowRemoteClients'])) return $response->end('denied');
        $request->header['host'] = plugins::getHost(cache::global()['interface']['server']['remoteAddress']);
        $remoteURI = cache::global()['interface']['server']['remoteAddress'] . $request->server['request_uri'];
        if (!empty($request->server['query_string'])) $remoteURI .= '?' . $request->server['query_string'];
        $httpHeaderRequest = plugins::headerParser($request->header);
        $curlCookies = plugins::buildCookie($request->cookie);
        $curlOptions = [
            CURLOPT_URL => $remoteURI,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_COOKIEFILE => 'cookie.txt',
            CURLOPT_HTTPHEADER => $httpHeaderRequest,
            CURLOPT_COOKIE => $curlCookies,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        if ($request->server['request_method'] === 'POST') $curlOptions[CURLOPT_POSTFIELDS] = $request->rawContent();
        $curl = curl_init();
        curl_setopt_array($curl, $curlOptions);
        $cUrlResponse = curl_exec($curl);
        $splitHeaders = explode(PHP_EOL, $cUrlResponse);
        foreach ($splitHeaders as $splitHeader) {
            if (strpos($splitHeader, 'Location:') !== false) {
                $ePatch = explode('://', $splitHeader);
                if (empty($ePatch[1])) {
                    $newURIlocation = $ePatch[0];
                    break;
                } else {
                    $prefix = (empty($GLOBALS['interface']['server']['autoGenerateSslCerificate'])) ? 'http' : 'https';
                    $exps = explode('/', $ePatch[1]);
                    unset($exps[0]);
                    $imploded = implode('/', $exps);
                    $newURIlocation = $prefix . '://' . $uriReplaceLocation . '/' . trim($imploded);
                    break;
                }
            }
        }
        $lengthBodyHeader = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        $response->status($statusCode);
        $lastHeaders = plugins::getLastHeaders($cUrlResponse, $lengthBodyHeader)['headers'];
        $responseBody = plugins::getLastHeaders($cUrlResponse, $lengthBodyHeader)['response'];
        foreach ($lastHeaders as $k => $v) {
            if (
                strpos($v, 'gzip') !== false or
                strpos($v, 'chunked') !== false or
                strpos($k, 'Content-Length') !== false or
                strpos($k, 'content-length') !== false or
                strpos($k, 'Pragma') !== false or
                strpos($v, 'Encoding') !== false or
                strpos(strtolower($k), 'pragma') !== false or
                strpos(strtolower($k), 'encoding') !== false or
                strpos(strtolower($k), 'location') !== false
            ) unset($lastHeaders[$k]);
        }
        foreach ($lastHeaders as $k => $v) {
            if (!empty($k) and !empty($v)) {
                $response->header($k, $v);
            }
        }
        if (!empty($newURIlocation)) $response->header('Location', $newURIlocation);

        $prefixOne = (strpos(cache::global()['interface']['server']['remoteAddress'], 'https://') !== false) ? 'https://' : 'http://';
        $stringSearchReplace = '="'. $prefixOne . plugins::getHost(cache::global()['interface']['server']['remoteAddress']);
        $ternarySearchReplace = (empty($GLOBALS['interface']['server']['autoGenerateSslCerificate'])) ? 'http' : 'https';
        $needleSearchReplace = '="' . $ternarySearchReplace . '://' . $uriReplaceLocation;
        $responseBody = str_replace($stringSearchReplace, $needleSearchReplace, $responseBody);
        $response->write($responseBody);
    }
}