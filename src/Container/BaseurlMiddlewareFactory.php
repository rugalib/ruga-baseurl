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

namespace Ruga\Baseurl\Container;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Ruga\Baseurl\BasePathHelper;
use Ruga\Baseurl\BaseurlMiddleware;

class BaseurlMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareInterface
    {
        $middleware = new BaseurlMiddleware();
        
        if ($container->has('\Mezzio\LaminasView\UrlHelper')) {
            $middleware->setUrlHelper($container->get('\Mezzio\LaminasView\UrlHelper'));
        }
        
        
        if ($container->has('\Mezzio\Helper\UrlHelper')) {
            $middleware->setUrlHelper($container->get('\Mezzio\Helper\UrlHelper'));
        }
        
        if($container->has('\Laminas\View\Helper\BasePath')) {
            $middleware->setBasePathHelper($container->get('\Laminas\View\Helper\BasePath'));
        }
        
//        if ($container->has(BasePathHelper::class)) {
//            $middleware->setBasePathHelper($container->get(BasePathHelper::class));
//        }
        
        return $middleware;
    }
    
}