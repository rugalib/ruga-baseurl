<?php
/*
 * SPDX-FileCopyrightText: 2024 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 *
 * Copyright (c) 2024 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 *
 * This file is part of rugalib/ruga-baseurl, which is distributed under the terms
 * of the GNU Affero General Public License v3.0 only. You should have received a copy of the AGPL 3.0
 * License along with rugalib/ruga-baseurl. If not, see <https://spdx.org/licenses/AGPL-3.0-only.html>.
 *
 * ----------------------------------------------------------------------------
 * Portions of the code are derived from Mateusz Tymek's work,
 * which is licensed under unknown License. You can find the original work at:
 * <https://github.com/mtymek/blast-base-url>
 * ----------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace Ruga\Baseurl;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class BaseurlMiddleware
 *
 * Middleware that detects the base URL and base path of the application and adds them as attributes to the
 * incoming request. It also modifies the request URI path by removing the base URL, if present.
 *
 * @package YourPackageName
 */
class BaseurlMiddleware implements MiddlewareInterface
{
    const BASE_URL = '_base_url';
    const BASE_PATH = '_base_path';
    
    private $urlHelper;
    
    /** @var BasePathHelper */
    private $basePathHelper;
    
    
    
    public function setUrlHelper($urlHelper): void
    {
        $this->urlHelper = $urlHelper;
    }
    
    
    
    public function setBasePathHelper(BasePathHelper $basePathHelper): void
    {
        $this->basePathHelper = $basePathHelper;
    }
    
    
    
    private function findBaseUrl(array $serverParams, $uriPath)
    {
        $filename = $serverParams['SCRIPT_FILENAME'] ?? '';
        $scriptName = $serverParams['SCRIPT_NAME'] ?? null;
        $phpSelf = $serverParams['PHP_SELF'] ?? null;
        $origScriptName = $serverParams['ORIG_SCRIPT_NAME'] ?? null;
        
        if ($scriptName !== null && basename($scriptName) === $filename) {
            $baseUrl = $scriptName;
        } elseif ($phpSelf !== null && basename($phpSelf) === $filename) {
            $baseUrl = $phpSelf;
        } elseif ($origScriptName !== null && basename($origScriptName) === $filename) {
            // 1and1 shared hosting compatibility.
            $baseUrl = $origScriptName;
        } else {
            // Backtrack up the SCRIPT_FILENAME to find the portion
            // matching PHP_SELF.
            
            $baseUrl = '/';
            $basename = basename($filename);
            if ($basename) {
                $path = ($phpSelf ? trim($phpSelf, '/') : '');
                $basePos = strpos($path, $basename) ?: 0;
                $baseUrl .= substr($path, 0, $basePos) . $basename;
            }
        }
        
        // If the baseUrl is empty, then simply return it.
        if (empty($baseUrl)) {
            return '';
        }
        
        // Full base URL matches.
        if (0 === strpos($uriPath, $baseUrl)) {
            return $baseUrl;
        }
        
        // Directory portion of base path matches.
        $baseDir = str_replace('\\', '/', dirname($baseUrl));
        if (0 === strpos($uriPath, $baseDir)) {
            return $baseDir;
        }
        
        $basename = basename($baseUrl);
        
        // No match whatsoever
        if (empty($basename) || false === strpos($uriPath, $basename)) {
            return '';
        }
        
        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of the base path. $pos !== 0 makes sure it is not matching a
        // value from PATH_INFO or QUERY_STRING.
        if (strlen($uriPath) >= strlen($baseUrl)
            && (false !== ($pos = strpos($uriPath, $baseUrl)) && $pos !== 0)
        ) {
            $baseUrl = substr($uriPath, 0, $pos + strlen($baseUrl));
        }
        
        return $baseUrl;
    }
    
    
    
    private function detectBasePath($serverParams, $baseUrl)
    {
        // Empty base url detected
        if ($baseUrl === '') {
            return '';
        }
        
        $filename = basename($serverParams['SCRIPT_FILENAME'] ?? '');
        
        // basename() matches the script filename; return the directory
        if (basename($baseUrl) === $filename) {
            return str_replace('\\', '/', dirname($baseUrl));
        }
        
        // Base path is identical to base URL
        return $baseUrl;
    }
    
    
    
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $uriPath = $uri->getPath();
        
        $baseUrl = $this->findBaseUrl($request->getServerParams(), $uriPath);
        $basePath = $this->detectBasePath($request->getServerParams(), $baseUrl);
        
        $request = $request->withAttribute(self::BASE_URL, $baseUrl);
        $request = $request->withAttribute(self::BASE_PATH, $basePath);
        
        
        if (!empty($baseUrl) && strpos($uriPath, $baseUrl) === 0) {
            $path = substr($uriPath, strlen($baseUrl));
            $path = '/' . ltrim($path, '/');
            $request = $request->withUri($uri->withPath($path));
        }
        
        if ($this->urlHelper) {
            $this->urlHelper->setBasePath($baseUrl);
        }
        
        if ($this->basePathHelper) {
            $this->basePathHelper->setBasePath($basePath);
        }
        
        return $handler->handle($request);
    }
}