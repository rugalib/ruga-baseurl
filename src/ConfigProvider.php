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

use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * ConfigProvider.
 *
 * @see    https://docs.mezzio.dev/mezzio/v3/features/container/config/
 */
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                'services' => [],
                'aliases' => [],
                'factories' => [
                    BasePathHelper::class => InvokableFactory::class,
                    BaseurlMiddleware::class => Container\BaseurlMiddlewareFactory::class,
                ],
                'invokables' => [],
                'delegators' => [],
            ],
            'view_helpers' => [
                'aliases' => [
                    'basePath' => BasePathHelper::class,
                ],
                'factories' => [
                    BasePathHelper::class => Container\BasePathViewHelperFactory::class,
                ],
            ],
        ];
    }
}
