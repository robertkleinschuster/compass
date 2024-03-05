<?php

declare(strict_types=1);

namespace Compass;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use Mosaic\Renderable;

class Attribute
{
    public function getLazy(mixed $view): ?Lazy
    {
        if ($view instanceof Closure) {
            $reflection = new ReflectionFunction($view);
            $attributes = $reflection->getAttributes(Lazy::class);
            foreach ($attributes as $attribute) {
                return $attribute->newInstance();
            }
        }
        if ($view instanceof Renderable) {
            $reflection = new ReflectionClass($view);
            $attributes = $reflection->getAttributes(Lazy::class);
            foreach ($attributes as $attribute) {
                return $attribute->newInstance();
            }
        }
        return null;
    }

    public function getReactive(mixed $view): ?Reactive
    {
        if ($view instanceof Closure) {
            $reflection = new ReflectionFunction($view);
            $attributes = $reflection->getAttributes(Reactive::class);
            foreach ($attributes as $attribute) {
                return $attribute->newInstance();
            }
        }
        if ($view instanceof Renderable) {
            $reflection = new ReflectionClass($view);
            $attributes = $reflection->getAttributes(Reactive::class);
            foreach ($attributes as $attribute) {
                return $attribute->newInstance();
            }
        }
        return null;
    }
}