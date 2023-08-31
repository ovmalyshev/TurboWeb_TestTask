<?php

interface DogInterface
{
    public function bark();
    public function sound();
    public function hunt();
}

interface ToyInterface
{
    public function play();
}

interface SoundToyInterface
{
    public function sound();
}

class Dog implements DogInterface
{
    public function bark()
    {
        return $this::class . ': bark! bark!';
    }

    public function sound()
    {
        return $this::class . ': woof! woof!';
    }

    public function hunt()
    {
        return $this::class . ': ah! uh!';
    }
}

class ShibaInu extends Dog implements DogInterface
{
    public function hunt()
    {
        return 'ok';
    }
}

class Mops extends Dog implements DogInterface
{
    public function hunt()
    {
        return 'maybe later';
    }
}

class Dachshund extends Dog implements DogInterface
{
    public function sound()
    {
        return 'um...';
    }
}

class Labrador extends Dog implements DogInterface
{
    public function sound()
    {
        return 'wooooooof!';
    }

    public function hunt()
    {
        return 'take the duck';
    }
}

class PlushLabrador implements ToyInterface
{
    public function play()
    {
        return self::class . ': waiting for play';
    }
}

class GumDachshund implements SoundToyInterface
{
    public function sound()
    {
        return self::class . ': beep! beep!';
    }
}

function handle($command)
{
    list($className, $action) = (is_array($value=preg_split('/\s+/', $command, 2)) && count($value) == 2
        ? $value : ['', '']);

    $action = preg_replace(
        '/\s+/',
        '',
        preg_replace_callback(
            '/\s+\w/',
            function ($x) {
                return trim(strtoupper($x[0]));
            },
            (string)$action
        )
    ) ?? '';

    $className = ucwords($className);
    if (!class_exists($className)) {
        return "unknown subject '$className'";
    }
    $class = new $className();
    if (!method_exists($class, $action)) {
        if ($class instanceof DogInterface) {
            return "dog '$className' can't '$action', it can: " . implode(', ', get_class_methods(DogInterface::class));
        } elseif ($class instanceof SoundToyInterface) {
            return "toy '$className' can't '$action', it can: " . implode(', ', get_class_methods(SoundToyInterface::class));
        } elseif ($class instanceof ToyInterface) {
            return "toy '$className' can't '$action', it can: " . implode(', ', get_class_methods(ToyInterface::class));
        }
        return "unknown '$className' action '$action'";
    }
    return $class->$action();
}

if (class_exists('PHPUnit\Framework\TestCase')) {
    // ref. https://docs.phpunit.de/en/9.6/installation.html
    final class DogsTest extends \PHPUnit\Framework\TestCase
    {
        /** @test */
        public function functionalTest()
        {
            $this->assertStringContainsString('woof! woof!', handle('mops sound'));
            $this->assertStringNotContainsString('woof! woof!', handle('mops open'));
            $this->assertStringContainsString('um...', handle('dachshund sound'));
            $this->assertStringContainsString('no way', handle('ShibaInu hunt'));
            $this->assertStringNotContainsString('ah', handle('mops hunt'));
            $this->assertStringContainsString('unknown', handle('test hunt'));
        }
    }
} else {
    if ($argc == 1) {
        echo <<<END
Usage:
    dogsTest.php {class} {action}

Samples:
    > phpunit dogsTest.php
    > php dogsTest.php mops sound
    > php dogsTest.php ShibaInu hunt
END;
    }

    echo handle(implode(' ', array_slice($argv, 1) ?? [])) . "\n";
}
