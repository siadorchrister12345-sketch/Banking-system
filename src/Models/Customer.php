<?php
namespace src\Models;

class Customer
{
    private string $name;
    private string $id;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return "Customer {$this->id} - {$this->name}";
    }
}
