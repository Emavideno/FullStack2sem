<?php

class ArrayWrapper
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function __toString(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public function __invoke(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function __clone(): void
    {
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                $this->data[$key] = clone $value;
            }
        }
    }
}