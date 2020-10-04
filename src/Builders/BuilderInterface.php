<?php

namespace Tkeer\Flattable\Builders;

interface BuilderInterface
{
    public function create();

    public function update();

    public function delete();
}