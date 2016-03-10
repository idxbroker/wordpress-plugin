<?php

namespace NetRivet\Container\Test;

class Qux
{
    public function __construct(Baz $baz)
    {
        $this->baz = $baz;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}
