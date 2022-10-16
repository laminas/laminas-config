<?php

declare(strict_types=1);

namespace LaminasTest\Config\Writer\TestAssets;

class PhpReader
{
    /**
     * @param string $filename
     * @return mixed
     */
    public function fromFile($filename)
    {
        return include $filename;
    }
}
