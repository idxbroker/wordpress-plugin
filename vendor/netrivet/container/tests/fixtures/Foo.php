<?php

namespace NetRivet\Container\Test;

class Foo
{
    public function __construct(BarInterface $bar, Baz $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}
