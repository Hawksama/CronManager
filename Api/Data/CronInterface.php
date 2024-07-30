<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Api\Data;

interface CronInterface
{
    /**
     * Constants for keys of data array.
     */
    public const INSTANCE = 'instance';
    public const METHOD = 'method';

    /**
     * Get the instance class name.
     *
     * @return string
     */
    public function getInstance(): string;

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Set the instance class name.
     *
     * @param string $instance
     * @return void
     */
    public function setInstance(string $instance): void;

    /**
     * Set the method name.
     *
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void;
}
