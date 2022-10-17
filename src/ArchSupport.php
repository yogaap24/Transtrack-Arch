<?php

namespace Transtrackid\ArchSupport;

use Illuminate\Support\Facades\Facade;

class ArchSupport extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'arch-support';
    }
}
