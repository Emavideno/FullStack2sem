<?php

class Product {
    private int $id;
    private string $title;
    private float $price;

    public function __construct(int $id, string $title, float $price) {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getPrice(): float {
        return $this->price;
    }
}
