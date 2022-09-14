<?php

namespace App\Spi\MabangArchDemo\Bo;
class HelloBo
{
    private string $a;
    private string $b;
    /**
     * @return string
     */
    public function getA(): string
    {
        return $this->a;
    }

    /**
     * @param string $a
     */
    public function setA(string $a): void
    {
        $this->a = $a;
    }

    /**
     * @return string
     */
    public function getB(): string
    {
        return $this->b;
    }

    /**
     * @param string $b
     */
    public function setB(string $b): void
    {
        $this->b = $b;
    }



}